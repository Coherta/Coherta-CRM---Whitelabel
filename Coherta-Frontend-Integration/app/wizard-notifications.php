<?php
require_once "./base.php";
$PageTitle = "Advanced Notifications";

if(!isset($_GET['status'])) {
    $notifications = $client->get("notifications");
} else {
    $notifications = $client->get("notifications?status=1");
}
include('partial/header.php');
include('partial/advanced-notifications.php');
require_once __DIR__ . '/partial/advanced-notification-date.php';
$plugin_installed = true;

function notification_first_value(array $source, array $keys): string {
    foreach ($keys as $key) {
        if (isset($source[$key]) && trim((string)$source[$key]) !== '') {
            return trim((string)$source[$key]);
        }
    }

    return '';
}

function notification_first_nested_value(array $source, array $keys): string {
    $value = notification_first_value($source, $keys);
    if ($value !== '') {
        return $value;
    }

    foreach ($source as $item) {
        if (!is_array($item)) {
            continue;
        }

        $value = notification_first_nested_value($item, $keys);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function notification_normalized_text(string $value): string {
    $normalized = strtolower(trim($value));
    return str_replace(["\xc3\xa5", "\xc3\xa6", "\xc3\xb8"], ['aa', 'ae', 'oe'], $normalized);
}

function notification_search_term(string $subject): string {
    $subject = html_entity_decode(trim($subject), ENT_QUOTES);
    if ($subject === '') {
        return '';
    }

    if (preg_match_all('/["“”]([^"“”]+)["“”]/u', $subject, $matches) && !empty($matches[1])) {
        return trim((string)end($matches[1]));
    }

    if (strpos($subject, '%') !== false) {
        $parts = array_values(array_filter(array_map(function ($part) {
            return trim($part, " \t\n\r\0\x0B\\\"'“”");
        }, explode('%', $subject)), 'strlen'));

        if (!empty($parts)) {
            return (string)end($parts);
        }
    }

    return trim($subject, " \t\n\r\0\x0B\\\"'“”");
}

function notification_post_matches_subject(array $post, string $subject): bool {
    $term = notification_normalized_text(notification_search_term($subject));
    if ($term === '') {
        return true;
    }

    $searchableText = notification_normalized_text(implode("\n", array_filter([
        notification_first_nested_value($post, ['text', 'post_text', 'postText', 'content', 'description']),
        notification_first_nested_value($post, ['title', 'job_title', 'jobTitle']),
    ], 'strlen')));

    return $searchableText !== '' && strpos($searchableText, $term) !== false;
}

function notification_post_timestamp(array $post): ?int {
    $publicationDate = advanced_notification_publication_date($post);
    return $publicationDate['timestamp'] ?? null;
}

function notification_post_date_label(array $post): string {
    $publicationDate = advanced_notification_publication_date($post);
    return $publicationDate['label'] ?? '';
}

function notification_linkedin_post_url(array $post): string {
    return advanced_notification_linkedin_url($post['post_url']) ?? '';
}

function notification_post_deduplication_key(array $post): string {
    return advanced_notification_post_key($post);
}

function notification_url(array $params): string {
    return '?' . http_build_query($params);
}
?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<?php include('partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php');?>
        <!-- Page Header Ends -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php');?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <div class="container-fluid">
                    <div class="row">
    <div class="col-md-12">
        <?php advanced_notifications_nav('feed'); ?>
    </div>
    </div>
    </div>
    <?php
    if(count($notifications)>0) {
        $notificationDateSort = (($_GET['sort_date'] ?? 'desc') === 'asc') ? 'asc' : 'desc';
        $nextDateSort = $notificationDateSort === 'asc' ? 'desc' : 'asc';
        $baseNotificationParams = [];
        if (isset($_GET['status'])) {
            $baseNotificationParams['status'] = $_GET['status'];
        }
        $dateSortUrl = notification_url(array_merge($baseNotificationParams, ['sort_date' => $nextDateSort]));
        $historyUrl = notification_url(array_merge(['status' => 1], ['sort_date' => $notificationDateSort]));
        $activeUrl = notification_url(['sort_date' => $notificationDateSort]);
        $dateSortLabel = $notificationDateSort === 'asc' ? 'Date ascending' : 'Date descending';
        $notificationRows = [];
        $notificationMetadataIndex = advanced_notification_metadata_index($notifications);
        foreach ($notifications as $notification) {
            if (empty($notification['notifications'])) {
                continue;
            }
            foreach (advanced_notification_deduplicate_posts($notification['notifications']) as $sub) {
                $sub = advanced_notification_hydrate_post($sub, $notificationMetadataIndex);
                if (!notification_post_matches_subject($sub, (string)($notification['subject'] ?? ''))) {
                    continue;
                }
                $notificationRows[] = [
                    'notification' => $notification,
                    'sub' => $sub,
                    'timestamp' => notification_post_timestamp($sub),
                    'date_label' => notification_post_date_label($sub),
                ];
            }
        }
        usort($notificationRows, function ($a, $b) use ($notificationDateSort) {
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }
            if ($a['timestamp'] === null) {
                return 1;
            }
            if ($b['timestamp'] === null) {
                return -1;
            }

            return $notificationDateSort === 'asc'
                ? $a['timestamp'] <=> $b['timestamp']
                : $b['timestamp'] <=> $a['timestamp'];
        });
        ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="/app/wizard-notifications-setup" class="btn btn-outline-primary">Manage search words</a>
                    <a href="<?php echo htmlspecialchars($dateSortUrl); ?>" class="btn btn-outline-primary">
                        <?php echo htmlspecialchars($dateSortLabel); ?>
                    </a>
                </div>
                <?php if(!isset($_GET['status'])): ?>
                    <a href="<?php echo htmlspecialchars($historyUrl); ?>" class="btn btn-outline-info">See all historical notifications</a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($activeUrl); ?>" class="btn btn-outline-info">See active notifications</a>
                <?php endif; ?>
            </div>
            <div class="row mb-4">
                <?php foreach ($notificationRows as $row): ?>
                    <?php $notification = $row['notification']; ?>
                    <?php $sub = $row['sub']; ?>
                    <?php $postDateLabel = $row['date_label']; ?>
                    <?php $postUrl = "";
                    if(str_contains($sub['post_id'], "activity")) {
                        $postUrl = "https://www.linkedin.com/feed/update/".$sub['post_id']."/";
                    }
                    if(str_contains($sub['post_id'], "urn:li:jobPosting:")) {
                        $postUrl = "https://www.linkedin.com/jobs/view/".str_replace("urn:li:jobPosting:", "", $sub['post_id'])."/";
                    }
                    ?>
                    <?php $authorUrl = advanced_notification_linkedin_url($sub['linkedin_url'] ?? null); ?>
                    <?php $postKey = notification_post_deduplication_key($sub); ?>
                            <div class="col-md-4">
                                <div class="card notification-card"<?php echo $postKey !== '' ? ' data-post-key="' . htmlspecialchars($postKey) . '"' : ''; ?>>
                                    <?php if ($postDateLabel !== ''): ?>
                                        <div class="notification-post-date">
                                            <?php echo htmlspecialchars($postDateLabel); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="notification-match-box">
                                        <span>Matched search</span>
                                        <strong><?php echo htmlspecialchars($notification['subject']); ?></strong>
                                    </div>
                                    <div class="card-header">
                                        <div class="d-flex align-items-start rounded p-3 mb-3 bg-white notification-card-content">
                                            <div class="flex-grow-1">
                                                <div class="notification-person mb-1">
                                                    <?php if ($authorUrl !== null): ?>
                                                        <a href="<?php echo htmlspecialchars($authorUrl); ?>" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-none text-dark">
                                                            <?php echo htmlspecialchars((string)($sub['name'] ?? '')); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars((string)($sub['name'] ?? '')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="badge bg-secondary ms-2" style="font-size:0.8em;"><?php echo htmlspecialchars((string)($sub['seen'] ?? '')); ?></span>
                                                    <div class="text-muted notification-person-title"><?php echo htmlspecialchars((string)($sub['title'] ?? '')); ?></div>
                                                </div>
                                                <div class="mb-2 text-muted" style="font-size:0.95em;">
                                                    <?php echo htmlspecialchars((string)($sub['seen'] ?? '')); ?>
                                                </div>
                                                <div class="mb-2 position-relative notification-text-preview" style="font-size:1.1em;" aria-expanded="false" title="Click to expand or collapse">
                                                    <?php echo nl2br(htmlspecialchars(advanced_notification_text($sub))); ?>
                                                    <div class="fade-overlay"></div>
                                                </div>
                                                <?php
                                                if(!empty($sub['matched'])) {
                                                    echo '<span class="btn btn-success">Marked as seen</span>';
                                                } else {
                                                    ?>
                                                    <a href="#seen=<?php echo htmlspecialchars((string)($sub['id'] ?? '')); ?>" data-notification-id="<?php echo htmlspecialchars((string)($notification['id'] ?? '')); ?>" data-participant="<?php echo htmlspecialchars((string)($sub['participant_id'] ?? '')); ?>" data-id="<?php echo htmlspecialchars((string)($sub['id'] ?? '')); ?>" class="seen btn btn-outline-secondary btn-sm">Mark it as seen</a>
                                                    <?php
                                                }
                                                ?>
                                                <?php if ($postUrl !== ''): ?>
                                                    <a href="<?php echo htmlspecialchars($postUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">Open on LinkedIn</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                <?php endforeach; ?>
            </div>
        </div>





    <?php } ?>
    </div>
    <?php include('partial/footer.php');?>
    </div>
    </div>
    <?php include('partial/scripts.php'); ?>
    <!-- Modal for Pipeline Selection -->
    <div class="modal fade" id="pipelineModal" tabindex="-1" aria-labelledby="pipelineModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pipelineModalLabel">Select Pipeline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="pipelineSelect" class="form-label">Only search for specific pipeline</label>
                            <select class="form-select" id="pipelineSelect">
                                <option value="0">All pipelines</option>
                                <?php
                                foreach($client->getCurrentPipelines() as $pipeline) {
                                    echo '<option value="' . htmlspecialchars($pipeline['id']) . '">' . htmlspecialchars($pipeline['title']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" id="deleteNotificationBtn">Delete Notification</button>
                    <button type="button" class="btn btn-success" id="saveNotificationBtn">Save Notification</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var sessionId = $('body').attr('session_id');

        let notificationId = null;
        let pipeline_id = null;
        document.querySelectorAll('.notification-action-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                showModal($(btn));
            });
        });
        function showModal($this) {
            notificationId = $this.attr('data-notification-id');
            pipeline_id = $this.attr('data-pipeline-id');
            $('#pipelineSelect').val(pipeline_id);
            $('#pipelineModal').modal('show');
            console.log('Showing modal for notification ID:', notificationId);
        }
        // Example event handlers for modal buttons
        document.getElementById('deleteNotificationBtn')?.addEventListener('click', function() {
            if (!notificationId) return;
            fetch('https://api.coherta.com/notifications', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + sessionId  },
                body: JSON.stringify({ notification_id: notificationId })
            }).then(res => {
                $('#pipelineModal').modal('hide');
                $(`button[data-notification-id="${notificationId}"]`).remove();
            });
        });
        document.getElementById('saveNotificationBtn')?.addEventListener('click', function() {
            // Implement save logic here
            $('#pipelineModal').modal('hide');
            var selectedValue = document.getElementById('pipelineSelect').value;
            if (!notificationId) return;
            fetch('https://api.coherta.com/notifications', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + sessionId  },
                body: JSON.stringify({ id: notificationId, pipeline_id: selectedValue })
            }).then(res => {
                $('#pipelineModal').modal('hide');
                $(`button[data-notification-id="${notificationId}"]`).attr('data-pipeline-id', selectedValue);
            });
        });
    </script>

    <script src="assets/js/tooltip-init.js"></script>
    <script src="assets/js/select2/select2.full.min.js"></script>
    <script src="assets/js/select2/select2-custom.js"></script>
    <script src="assets/js/addNotifications.js"></script>
    <script>
        // Mark notification as seen
        document.addEventListener('click', function(e) {
            if (e.target.matches('a.seen')) {
                e.preventDefault();
                let id = e.target.getAttribute('data-id');
                let notification_id = e.target.getAttribute('data-notification-id');
                let participant_id = e.target.getAttribute('data-participant');

                fetch('https://api.coherta.com/notifications', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + sessionId
                    },
                    body: JSON.stringify({
                        id: id,
                        notification_id: notification_id,
                        participant_id: participant_id,
                        matched: 1
                    })
                })
                    .then(response => {
                        if (response.ok) {
                            // Optionally update UI, e.g., disable button or remove notification
                            e.target.textContent = 'Marked as seen';
                            e.target.classList.remove('btn-outline-secondary');
                            e.target.classList.add('btn-success');
                            e.target.setAttribute('disabled', 'disabled');
                        } else {
                            alert('Failed to mark as seen.');
                        }
                    })
                    .catch(() => alert('Network error.'));
            }
        });

        const jobtitleInput = document.getElementById('jobtitle-input');
        const addJobtitleBtn = document.getElementById('add-jobtitle-btn');
        const jobtitleList = document.getElementById('jobtitle-list');
        let jobtitles = [];

        jobtitleInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addJobtitleBtn.click();
                e.preventDefault();
            }
        });

        addJobtitleBtn?.addEventListener('click', function() {
            const title = jobtitleInput.value.trim();
            if (title && !jobtitles.includes(title)) {
                jobtitles.push(title);
                renderJobtitles();
                jobtitleInput.value = '';
            }
        });

        jobtitleList?.addEventListener('click', function(e) {
            if (e.target.tagName === 'LI') {
                const title = e.target.tagName === 'LI' ? e.target.childNodes[e.target.childNodes.length - 1].textContent.trim() : '';
                jobtitles = jobtitles.filter(jt => jt !== title);
                renderJobtitles();
            }
        });
        function renderJobtitles() {
            if (!jobtitleList) return;
            jobtitleList.innerHTML = '';
            jobtitles.forEach(title => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" style="float:left;padding-right: 10px;" width="32" height="32" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"></path>
                                              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"></path>
                                            </svg> ${title}`;
                li.style.lineHeight = '32px';
                jobtitleList.appendChild(li);
            });
        }


        document.querySelectorAll('.btn-group[aria-label="Job Title Filters"] .btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-group[aria-label="Job Title Filters"] .btn').forEach(function(b) {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            });
        });

        document.querySelectorAll('.btn-group[aria-label="Job Title Transfer Filters"] .btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.btn-group[aria-label="Job Title Transfer Filters"] .btn').forEach(function(b) {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            });
        });
        renderJobtitles();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var interactiveSelector = 'a, button, input, select, textarea, label, .btn, [role="button"]';

            document.querySelectorAll('.notification-card').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    if (e.target.closest(interactiveSelector)) {
                        return;
                    }

                    var preview = card.querySelector('.notification-text-preview');
                    if (!preview) {
                        return;
                    }

                    var isExpanded = card.classList.toggle('is-expanded');
                    preview.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                });
            });
        });
    </script>
    <style>
        .notification-card {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .notification-card a,
        .notification-card button,
        .notification-card .btn {
            cursor: auto;
        }
        .notification-card .card-header {
            padding-top: 48px;
        }
        .notification-card-content {
            max-width: 600px;
        }
        .notification-post-date {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            max-width: 150px;
            padding: 0 10px;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #fff;
            color: #475467;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
            font-size: 12px;
            line-height: 1;
            font-weight: 800;
            white-space: nowrap;
        }
        .notification-match-box {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 1;
            display: grid;
            gap: 2px;
            max-width: min(260px, calc(100% - 192px));
            padding: 7px 10px;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #f2f4f7;
            color: #667085;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
        }
        .notification-match-box span {
            font-size: 10px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #98a2b3;
        }
        .notification-match-box strong {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            line-height: 1.25;
            font-weight: 700;
            color: #475467;
        }
        .notification-person {
            padding-right: 96px;
        }
        .notification-person-title {
            margin-top: 4px;
            font-size: .88em;
            line-height: 1.35;
        }
        .notification-text-preview {
            max-height: 150px;
            overflow: hidden;
        }
        .notification-card.is-expanded {
            overflow: visible;
        }
        .notification-card.is-expanded .notification-text-preview {
            max-height: none;
            overflow: visible;
        }
        .notification-text-preview .fade-overlay {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 50px;
            pointer-events: none;
            display: block;
        }
        .notification-card.is-expanded .notification-text-preview .fade-overlay {
            display: none;
        }
        .notification-text-preview .fade-overlay {
            background: linear-gradient(transparent, #f8f9fa 90%);
        }
        @media (max-width: 575px) {
            .notification-card .card-header {
                padding-top: 104px;
            }
            .notification-match-box {
                top: 56px;
                left: 14px;
                right: 14px;
                max-width: none;
            }
            .notification-person {
                padding-right: 0;
            }
        }
    </style>
<?php
include('partial/footer-end.php'); ?>
