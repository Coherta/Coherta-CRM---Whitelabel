<?php
require_once "./base.php";
$PageTitle = "Advanced Notifications - Search words";

$notifications = $client->get("notifications");
$interactors = $client->get("notifications/interactions");
include('partial/header.php');
include('partial/advanced-notifications.php');
$plugin_installed = True;

?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<?php include('partial/loader.php') ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include('partial/topbar.php');?>
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php');?>
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <?php advanced_notifications_nav('setup'); ?>
                    </div>
                    <?php if(!$plugin_installed): ?>
                        <div class="col-md-12">
                            <?php advanced_notifications_locked_card($company_data); ?>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>What words are you looking for?</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="input-group mb-2">
                                            <input type="text" id="jobtitle-input" class="form-control" placeholder="Enter word to search for">
                                            <button class="btn btn-primary" type="button" id="add-jobtitle-btn">Add to list</button>
                                        </div>
                                        <ul id="jobtitle-list" class="list-group mb-2"></ul>
                                    </div>
                                    <div class="text-end btn-mb">
                                        <button class="btn btn-primary" id="nextBtn" type="button"
                                                onclick="SaveNotifications(<?php echo $client->getCurrentCompanies()[0]["id"]; ?>)">Save new list and start the search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Top interactors with your current search words</h5>
                                </div>
                                <div class="card-body">
                                    <?php if(count($interactors)>0): ?>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Scrambled URL</th>
                                                <th>Interaction Count</th>
                                                <th>Subject Breakdown</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $i = 0;
                                            foreach ($interactors as $notification) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($notification['rank']) . "</td>";
                                                if(strlen($notification['image']) > 0)
                                                    echo "<td><img src='https://api.coherta.com/images/" . htmlspecialchars($notification['image']) . "' alt='Image' class='rounded-circle me-3'  style='width:40px;height:40px;'></td>";
                                                else
                                                    echo "<td></td>";
                                                echo "<td>" . htmlspecialchars($notification['name']) . "</td>";
                                                echo "<td><a href='" . htmlspecialchars($notification['scrambled_url']) . "' target='_blank'>Profile</a></td>";
                                                echo "<td>" . htmlspecialchars($notification['likes_count']) . "</td>";
                                                echo "<td>" . htmlspecialchars($notification['subject_breakdown']) . "</td>";
                                                echo "</tr>";
                                                $i++;
                                                if($i >= 2) break;
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                        <a href="interaction-scoreboard.php" class="btn btn-outline-primary">See full scoreboard</a>
                                    <?php else: ?>
                                        <div class="text-muted">No interaction data yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if(count($notifications)>0): ?>
                            <div class="col-md-12">
                                <div class="card notification-management-card">
                                    <div class="card-header">
                                        <div>
                                            <h5>Saved notification searches</h5>
                                            <p>Use these compact chips only when you need to change pipeline targeting or delete a saved search.</p>
                                        </div>
                                        <span class="badge badge-light-primary"><?php echo count($notifications); ?> saved</span>
                                    </div>
                                    <div class="list-of-notification-headers">
                                        <?php foreach ($notifications as $notification):
                                            echo '<button type="button" data-pipeline-id="'.htmlspecialchars($notification['pipeline_id']).'" data-notification-id="'.htmlspecialchars($notification['id']).'" class="btn btn-outline-primary notification-action-btn">'
                                                . htmlspecialchars($notification['subject']) . '</button>';
                                        endforeach;?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include('partial/footer.php');?>
    </div>
</div>

<?php if($plugin_installed): ?>
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
<?php endif; ?>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/tooltip-init.js"></script>
<script src="assets/js/select2/select2.full.min.js"></script>
<script src="assets/js/select2/select2-custom.js"></script>
<script src="assets/js/addNotifications.js"></script>
<script>
    var sessionId = $('body').attr('session_id');
    let notificationId = null;
    let pipeline_id = null;

    document.addEventListener('click', function(event) {
        const btn = event.target.closest('.notification-action-btn');
        if (!btn) return;
        notificationId = btn.getAttribute('data-notification-id');
        pipeline_id = btn.getAttribute('data-pipeline-id');
        $('#pipelineSelect').val(pipeline_id);
        $('#pipelineModal').modal('show');
    });

    document.getElementById('deleteNotificationBtn')?.addEventListener('click', function() {
        if (!notificationId) return;
        fetch('https://api.coherta.com/notifications', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + sessionId  },
            body: JSON.stringify({ notification_id: notificationId })
        }).then(res => {
            $('#pipelineModal').modal('hide');
            document.querySelector('[data-notification-id="' + notificationId + '"]')?.remove();
        });
    });

    document.getElementById('saveNotificationBtn')?.addEventListener('click', function() {
        const selectedValue = document.getElementById('pipelineSelect').value;
        if (!notificationId) return;
        fetch('https://api.coherta.com/notifications', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + sessionId  },
            body: JSON.stringify({ id: notificationId, pipeline_id: selectedValue })
        }).then(res => {
            $('#pipelineModal').modal('hide');
            document.querySelector('[data-notification-id="' + notificationId + '"]')?.setAttribute('data-pipeline-id', selectedValue);
        });
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
        const item = e.target.closest('li');
        if (!item) return;
        const title = item.getAttribute('data-title') || '';
        jobtitles = jobtitles.filter(jt => jt !== title);
        renderJobtitles();
    });

    function renderJobtitles() {
        if (!jobtitleList) return;
        jobtitleList.innerHTML = '';
        jobtitles.forEach(title => {
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action';
            li.setAttribute('data-title', title);
            li.textContent = title;
            jobtitleList.appendChild(li);
        });
    }
</script>
<?php include('partial/footer-end.php'); ?>
