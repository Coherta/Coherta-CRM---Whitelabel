<?php
require_once "./base.php";

$PageTitle = "People Leads";
$sidebar_close = true;
$pipeline_id = isset($_GET["pipeline_id"]) ? (int)$_GET["pipeline_id"] : 0;
$peoplePayload = [];
$title = "";
$totalPeople = 0;
$pipelineDeleteCount = [];
$users = [];

if ($pipeline_id > 0) {
    $peoplePayload = $client->get("pipeline/all/leads/people?pipeline_id={$pipeline_id}&page=1");
    $usersPayload = $client->get("company/users");
    $users = is_array($usersPayload) ? $usersPayload : [];
    if (is_array($peoplePayload)) {
        $title = $peoplePayload["pipeline"]["title"] ?? "";
        $totalPeople = (int)($peoplePayload["pagination"]["total"] ?? 0);
        $pipelineDeleteCount = [
            [
                "total_leads" => $totalPeople,
                "status" => 0,
            ],
        ];
        if ($title !== "") {
            $PageTitle = $title;
        }
    }
}

include('partial/header.php');

$columns = [
    ['key' => 'name', 'type' => 'string', 'label' => 'Person', 'checked' => 'yes'],
    ['key' => 'added', 'type' => 'string', 'label' => 'Created', 'checked' => 'yes'],
    ['key' => 'job_title', 'type' => 'string', 'label' => 'Job Title', 'checked' => 'yes'],
    ['key' => 'company_name', 'type' => 'string', 'label' => 'Company', 'checked' => 'yes'],
    ['key' => 'stage', 'type' => 'enum', 'label' => 'Stage', 'checked' => 'yes'],
    ['key' => 'owner', 'type' => 'string', 'label' => 'Lead Owner', 'checked' => 'yes'],
    ['key' => 'email', 'type' => 'string', 'label' => 'Email', 'checked' => 'yes'],
    ['key' => 'phone_number', 'type' => 'string', 'label' => 'Phone', 'checked' => 'yes'],
    ['key' => 'entry_keyword', 'type' => 'string', 'label' => 'Keyword', 'checked' => 'yes'],
    ['key' => 'linkedin_url', 'type' => 'string', 'label' => 'LinkedIn', 'checked' => 'yes'],
    ['key' => 'location', 'type' => 'string', 'label' => 'Location', 'checked' => 'yes'],
    ['key' => 'city', 'type' => 'string', 'label' => 'City', 'checked' => 'yes'],
    ['key' => 'region', 'type' => 'string', 'label' => 'Region'],
    ['key' => 'followers', 'type' => 'numeric', 'label' => 'Followers'],
    ['key' => 'connections', 'type' => 'numeric', 'label' => 'Connections'],
    ['key' => 'connected_user_count', 'type' => 'numeric', 'label' => 'Connected Colleagues'],
    ['key' => 'skills', 'type' => 'string', 'label' => 'Skills'],
    ['key' => 'company_linkedin_url', 'type' => 'string', 'label' => 'Company LinkedIn'],
    ['key' => 'last_updated', 'type' => 'date', 'label' => 'Lead Updated'],
    ['key' => 'participant_last_updated', 'type' => 'date', 'label' => 'Person Updated'],
    ['key' => 'people_delete', 'type' => 'string', 'label' => 'Remove Lead', 'checked' => 'yes'],
];
?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/datatables.css">
<script>
    const activeColumns = <?php
        $activeCols = [];
        foreach ($columns as $col) {
            if (($col["checked"] ?? "") === "yes") {
                $activeCols[] = array_filter($col, function($k) {
                    return in_array($k, ['key', 'type', 'subkey', 'combine_with', 'enum_value']);
                }, ARRAY_FILTER_USE_KEY);
            }
        }
        echo json_encode($activeCols);
    ?>;
</script>

<?php include('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card people-leads-card">
                            <div class="card-body people-leads-body">
                                <div class="people-leads-hero">
                                    <div>
                                        <h1 class="people-leads-title">
                                            <?php echo $title !== "" ? "People leads - " . htmlspecialchars($title) : "People leads"; ?>
                                        </h1>
                                        <p>People matched from companies in this lead list.</p>
                                    </div>
                                    <div class="people-leads-actions">
                                        <span class="people-leads-count" id="peopleLeadTotal"><?php echo (int)$totalPeople; ?> people</span>
                                        <?php if ($pipeline_id > 0) { ?>
                                            <button type="button" class="btn btn-light people-leads-btn" data-bs-toggle="modal" data-bs-target="#peopleLeadListSettingsModal">
                                                <i class="fa fa-trash"></i>
                                                <span>Delete list</span>
                                            </button>
                                            <button type="button" class="btn btn-primary people-leads-btn" data-bs-toggle="modal" data-bs-target="#addPeopleLeadModal">
                                                <i class="fa fa-user-plus"></i>
                                                <span>Add Person</span>
                                            </button>
                                            <button type="button" class="btn btn-light people-leads-btn" data-bs-toggle="modal" data-bs-target="#exportLeadListModal">
                                                <i class="fa fa-download"></i>
                                                <span>Export</span>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </div>

                                <?php if ($pipeline_id <= 0) { ?>
                                    <div class="alert alert-warning mb-0">Choose a people lead list from the sidebar.</div>
                                <?php } else { ?>
                                    <div class="people-leads-toolbar">
                                        <div id="export-button"></div>
                                        <div class="people-leads-search-slot" id="leadsSearchSlot"></div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="display datatables" id="ajax-data-array">
                                            <thead>
                                            <tr>
                                                <?php foreach ($columns as $col) {
                                                    if (($col["checked"] ?? "") === "yes") {
                                                        echo "<th>" . htmlspecialchars($col["label"]) . "</th>";
                                                    }
                                                } ?>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <?php foreach ($columns as $col) {
                                                    if (($col["checked"] ?? "") === "yes") {
                                                        echo "<th>" . htmlspecialchars($col["label"]) . "</th>";
                                                    }
                                                } ?>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="people-floating-scroll" id="peopleFloatingScroll" aria-hidden="true">
                                        <div class="people-floating-scroll-inner" id="peopleFloatingScrollInner"></div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php'); ?>
        </div>
    </div>

    <?php if ($pipeline_id > 0) { ?>
    <div class="modal fade" id="peopleLeadListSettingsModal" tabindex="-1" aria-labelledby="peopleLeadListSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="peopleLeadListSettingsModalLabel">Delete list</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="people-settings-section">
                        <div>
                            <h6>Danger zone</h6>
                            <p>Delete this people pipeline permanently.</p>
                        </div>
                        <button type="button" data-count="<?php echo htmlspecialchars(json_encode($pipelineDeleteCount), ENT_QUOTES, 'UTF-8'); ?>" data-pipeline-id="<?php echo $pipeline_id; ?>" data-delete-redirect="/app/campaign" class="btn btn-outline-danger delete-pipeline">
                            <i class="fa fa-trash"></i>
                            <span>Delete list</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php if ($pipeline_id > 0) { ?>
    <div class="modal fade" id="addPeopleLeadModal" tabindex="-1" aria-labelledby="addPeopleLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="addPeopleLeadForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPeopleLeadModalLabel">Add person to list</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addPeopleLeadUrl" class="form-label">Profile URL</label>
                        <input type="url" class="form-control" id="addPeopleLeadUrl" name="linkedin_url" placeholder="https://www.linkedin.com/in/example" required>
                    </div>
                    <div class="people-add-status" id="addPeopleLeadStatus" aria-live="polite"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="savePeopleLeadUrl">Add Person</button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <div class="modal fade" id="invalidSubscription" tabindex="-1" aria-labelledby="invalidSubscriptionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invalidSubscriptionLabel">Invalid Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert" role="alert">
                        The pipeline data you want to access requires another subscription. Please contact support for more information about which subscription you should purchase.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportLeadListModal" tabindex="-1" aria-labelledby="exportLeadListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportLeadListModalLabel">Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body export-choice-body">
                    <button type="button" class="export-choice-card" data-export-trigger="csv">
                        <i class="fa fa-file-csv"></i>
                        <strong>Export as CSV</strong>
                    </button>
                    <button type="button" class="export-choice-card" data-export-trigger="excel">
                        <i class="fa fa-file-excel"></i>
                        <strong>Export as Excel</strong>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include('partial/scripts.php'); ?>
    <?php if ($pipeline_id > 0) { ?>
    <script>
        window.PEOPLE_PIPELINE_ID = "<?php echo $pipeline_id; ?>";
        window.PEOPLE_LEAD_USERS = <?php echo json_encode($users); ?>;
        window.LEADS_ENDPOINT = 'https://api.coherta.com/pipeline/all/leads/people';
        window.LEADS_SHOW_ACTIONS = false;
        window.LEADS_EMPTY_TEXT = 'No people found';
        window.LEAD_EDITOR = 'lead_people.php';
        window.PEOPLE_LEAD_USERS_BY_ID = new Map((window.PEOPLE_LEAD_USERS || []).map(function(user) {
            return [String(user.id || ''), user];
        }));
        window.LEADS_BUILD_URL = function(args) {
            const page = Math.floor((args.offset || 0) / (args.limit || 100)) + 1;
            return args.endpoint + '?pipeline_id=' + encodeURIComponent(window.PEOPLE_PIPELINE_ID) + '&page=' + encodeURIComponent(page);
        };
        window.LEADS_EXTRACT_ROWS = function(payload) {
            if (payload && payload.pipeline && payload.pipeline.title) {
                document.title = 'Coherta - ' + payload.pipeline.title;
            }
            if (payload && payload.pagination) {
                const totalEl = document.getElementById('peopleLeadTotal');
                if (totalEl) totalEl.textContent = String(payload.pagination.total || 0) + ' people';
            }
            return Array.isArray(payload && payload.data) ? payload.data : [];
        };
        window.LEADS_COLUMN_RENDERERS = {
            job_title: function(data, type, row) {
                const rawTitle = data || row.job_title || row.title || row.participant_jobtitle || row.participant_job_title || row.position || '';
                const title = String(rawTitle || '').trim();
                if (!title) return '-';
                if (type !== 'display') return title;
                return $('<div>').text(title).html();
            },
            name: function(data, type, row) {
                if (!data) return '—';
                const safeName = $('<div>').text(data).html();
                const image = row.image ? '<img src="https://api.coherta.com/images/' + row.image + '" alt="" class="people-leads-avatar">' : '<span class="people-leads-avatar people-leads-avatar-empty"></span>';
                const rawTitle = row.job_title || row.title || row.participant_jobtitle || row.participant_job_title || row.position || '';
                const safeTitle = rawTitle ? $('<div>').text(rawTitle).html() : '';
                const subtitle = safeTitle ? '<span title="' + safeTitle + '">' + safeTitle + '</span>' : '';
                if (type !== 'display') return data;
                const leadId = row.id || row.pipeline_company_id || '';
                if (!leadId) {
                    return '<span class="people-leads-person" title="' + safeName + '">' + image + '<strong>' + safeName + '</strong>' + subtitle + '</span>';
                }
                return '<a class="people-leads-person" title="' + safeName + '" href="lead_people.php?id=' + encodeURIComponent(leadId) + '&pipeline_id=' + encodeURIComponent(window.PEOPLE_PIPELINE_ID) + '">' + image + '<strong>' + safeName + '</strong>' + subtitle + '</a>';
            },
            company_name: function(data, type, row) {
                const company = data || row.company_name || row.company || row.organization || '';
                if (!company) return '-';
                const safeCompany = $('<div>').text(company).html();
                if (type !== 'display') return company;
                if (row.company_linkedin_url) {
                    return '<a target="_blank" href="' + row.company_linkedin_url + '">' + safeCompany + '</a>';
                }
                return safeCompany;
            },
            entry_keyword: function(data, type, row) {
                const keyword = data || row.entry_keyword || row.keyword || '';
                if (!keyword) return '—';
                if (type !== 'display') return keyword;
                const safeKeyword = $('<div>').text(keyword).html();
                const keywordUrl = String(row.keyword_url || '').trim();
                if (!keywordUrl) return safeKeyword;

                try {
                    const parsedKeywordUrl = new URL(keywordUrl, window.location.origin);
                    if (!['http:', 'https:'].includes(parsedKeywordUrl.protocol)) return safeKeyword;
                    const safeKeywordUrl = $('<div>').text(parsedKeywordUrl.href).html();
                    return '<a target="_blank" rel="noopener noreferrer" href="' + safeKeywordUrl + '">' + safeKeyword + '</a>';
                } catch (error) {
                    return safeKeyword;
                }
            },
            owner: function(data, type, row) {
                const ownerValue = row.owner || data || '';
                const ownerIsObject = typeof ownerValue === 'object' && ownerValue !== null;
                const ownerPrimitive = ownerIsObject ? '' : String(ownerValue || '');
                const ownerPrimitiveLooksLikeId = ownerPrimitive !== '' && /^[0-9]+$/.test(ownerPrimitive);
                const ownerId = row.owner_id || row.lead_owner_id || row.assigned_user_id || (ownerIsObject ? (ownerValue.id || '') : (ownerPrimitiveLooksLikeId ? ownerPrimitive : ''));
                const fallbackName = row.owner_name || row.owner_full_name || row.owner_email || row.lead_owner || row.lead_owner_name || (ownerIsObject ? (ownerValue.name || ownerValue.full_name || ownerValue.email || '') : (ownerPrimitiveLooksLikeId ? '' : ownerPrimitive));
                if ((!ownerId || String(ownerId) === '0') && !fallbackName) return 'Unassigned';
                const owner = ownerId ? window.PEOPLE_LEAD_USERS_BY_ID.get(String(ownerId)) : null;
                const ownerName = (owner ? (owner.name || owner.full_name || owner.email || '') : '') || fallbackName || 'Unassigned';
                if (type !== 'display') return ownerName;
                return $('<div>').text(ownerName).html();
            },
            linkedin_url: function(data, type) {
                if (!data) return '—';
                if (type !== 'display') return data;
                return '<a target="_blank" href="' + data + '">Open profile</a>';
            },
            company_linkedin_url: function(data, type) {
                if (!data) return '—';
                if (type !== 'display') return data;
                return '<a target="_blank" href="' + data + '">Open company</a>';
            },
            phone_number: function(data, type, row) {
                const phone = data || row.phonenumber || row.phonenumber_2 || '';
                if (!phone) return '—';
                if (type !== 'display') return phone;
                return '<a href="tel:' + String(phone).replace(/[^+0-9]/g, '') + '">' + $('<div>').text(phone).html() + '</a>';
            },
            email: function(data, type) {
                if (!data) return '—';
                if (type !== 'display') return data;
                return '<a href="mailto:' + data + '">' + $('<div>').text(data).html() + '</a>';
            },
            skills: function(data, type) {
                if (!Array.isArray(data) || data.length === 0) return '—';
                const labels = data.map(function(item) { return item && item.skill ? item.skill : ''; }).filter(Boolean).slice(0, 4);
                if (type !== 'display') return labels.join(', ');
                return labels.map(function(label) {
                    return '<span class="people-skill-pill">' + $('<div>').text(label).html() + '</span>';
                }).join('');
            },
            people_delete: function(data, type, row) {
                if (type !== 'display') return '';
                const leadId = row.id || row.pipeline_company_id || '';
                if (!leadId) return '';
                const safeName = $('<div>').text(row.name || 'this person').html();
                return '<ul class="action"><li class="delete"><a href="#" class="people-delete-person" data-lead-id="' + encodeURIComponent(leadId) + '" data-person-name="' + safeName + '" aria-label="Delete person from list" title="Delete person from list"><i class="icon-trash"></i></a></li></ul>';
            }
        };
    </script>
    <script src="assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>
    <link rel="stylesheet" href="assets/css/lead-contact-editor.css?v=<?php echo filemtime(__DIR__ . '/assets/css/lead-contact-editor.css'); ?>">
    <script src="assets/js/lead-contact-editor.js?v=<?php echo filemtime(__DIR__ . '/assets/js/lead-contact-editor.js'); ?>"></script>
    <script src="assets/js/datatable/datatables/datatable.custom.js?v=<?php echo filemtime(__DIR__ . '/assets/js/datatable/datatables/datatable.custom.js'); ?>"></script>
    <script src="assets/js/datepicker/date-picker/datepicker.js"></script>
    <script src="assets/js/datepicker/date-picker/datepicker.en.js"></script>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/date-picker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        const pipelineId = "<?php echo $pipeline_id > 0 ? $pipeline_id : ''; ?>";
        const exportOnlyFirst5 = false;

        function getBootstrapModal(element) {
            if (!element || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                return bootstrap.Modal.getOrCreateInstance(element);
            }
            if (typeof bootstrap.Modal.getInstance === 'function') {
                return bootstrap.Modal.getInstance(element) || new bootstrap.Modal(element);
            }
            return new bootstrap.Modal(element);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('sidebar-close');
            const pageWrapper = document.getElementById('pageWrapper');
            if (pageWrapper) pageWrapper.classList.add('sidebar-close');

            const moveSearchIntoToolbar = function() {
                const searchNode = document.querySelector('.dataTables_filter');
                const slot = document.getElementById('leadsSearchSlot');
                if (searchNode && slot && !slot.contains(searchNode)) {
                    slot.appendChild(searchNode);
                    const label = searchNode.querySelector('label');
                    const input = searchNode.querySelector('input');
                    if (label && input && !searchNode.dataset.ready) {
                        if (label.childNodes[0]) label.childNodes[0].textContent = '';
                        input.placeholder = 'Search people, title, company...';
                        input.classList.add('form-control');
                        searchNode.dataset.ready = '1';
                    }
                }
            };
            moveSearchIntoToolbar();
            window.setInterval(moveSearchIntoToolbar, 500);

            const peopleTableScroller = document.querySelector('.people-leads-card .table-responsive');
            const peopleFloatingScroll = document.getElementById('peopleFloatingScroll');
            const peopleFloatingScrollInner = document.getElementById('peopleFloatingScrollInner');
            let syncingPeopleScroll = false;

            function updatePeopleFloatingScroll() {
                if (!peopleTableScroller || !peopleFloatingScroll || !peopleFloatingScrollInner) return;

                const rect = peopleTableScroller.getBoundingClientRect();
                const hasHorizontalOverflow = peopleTableScroller.scrollWidth > peopleTableScroller.clientWidth + 1;
                const tableIsVisible = rect.top < window.innerHeight && rect.bottom > 0;
                const nativeScrollbarIsVisible = rect.bottom <= window.innerHeight;
                const shouldShow = hasHorizontalOverflow && tableIsVisible && !nativeScrollbarIsVisible;

                peopleFloatingScroll.classList.toggle('is-visible', shouldShow);
                peopleFloatingScroll.style.left = Math.max(0, rect.left) + 'px';
                peopleFloatingScroll.style.width = Math.max(0, Math.min(rect.right, window.innerWidth) - Math.max(0, rect.left)) + 'px';
                peopleFloatingScrollInner.style.width = peopleTableScroller.scrollWidth + 'px';

                if (!syncingPeopleScroll) peopleFloatingScroll.scrollLeft = peopleTableScroller.scrollLeft;
            }

            peopleTableScroller?.addEventListener('scroll', function() {
                if (syncingPeopleScroll) return;
                syncingPeopleScroll = true;
                peopleFloatingScroll.scrollLeft = peopleTableScroller.scrollLeft;
                syncingPeopleScroll = false;
            });

            peopleFloatingScroll?.addEventListener('scroll', function() {
                if (syncingPeopleScroll) return;
                syncingPeopleScroll = true;
                peopleTableScroller.scrollLeft = peopleFloatingScroll.scrollLeft;
                syncingPeopleScroll = false;
            });

            window.addEventListener('scroll', updatePeopleFloatingScroll, { passive: true });
            window.addEventListener('resize', updatePeopleFloatingScroll);
            new ResizeObserver(updatePeopleFloatingScroll).observe(peopleTableScroller);
            updatePeopleFloatingScroll();

            async function waitForPeopleTable() {
                for (let i = 0; i < 40; i++) {
                    if (window.leadsTable) return window.leadsTable;
                    await new Promise(resolve => window.setTimeout(resolve, 100));
                }
                return null;
            }

            async function loadPeopleLeads() {
                const table = await waitForPeopleTable();
                if (!pipelineId || !table) return;

                table.clear().draw(false);
                setPeopleTableLoading(true);
                let page = 1;
                let totalRows = 0;

                try {
                    for (;;) {
                        const url = window.LEADS_ENDPOINT + '?pipeline_id=' + encodeURIComponent(pipelineId) + '&page=' + encodeURIComponent(page);
                        const response = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                                Authorization: 'Bearer ' + document.body.getAttribute('session_id')
                            }
                        });

                        if (!response.ok) {
                            const invalidSubscriptionModal = document.getElementById('invalidSubscription');
                            getBootstrapModal(invalidSubscriptionModal)?.show();
                            throw new Error('People leads API error: ' + response.status);
                        }

                        const payload = await response.json();
                        const rows = window.LEADS_EXTRACT_ROWS(payload);
                        if (!Array.isArray(rows) || rows.length === 0) break;

                        table.rows.add(rows).draw(false);
                        totalRows += rows.length;
                        updatePeopleFloatingScroll();

                        if (!payload.pagination || !payload.pagination.has_next_page) break;
                        page += 1;
                    }

                    if (totalRows === 0) {
                        document.querySelector('#ajax-data-array tbody').innerHTML =
                            '<tr class="odd"><td valign="top" colspan="' + activeColumns.length + '" class="dataTables_empty">No people found</td></tr>';
                    }
                } finally {
                    setPeopleTableLoading(false);
                    updatePeopleFloatingScroll();
                }
            }
            window.loadPeopleLeads = loadPeopleLeads;

            function setPeopleTableLoading(show) {
                const wrapper = document.querySelector('#ajax-data-array_wrapper');
                const tableEl = document.querySelector('#ajax-data-array');
                if (tableEl) {
                    tableEl.style.opacity = show ? '0.3' : '1';
                    tableEl.style.pointerEvents = show ? 'none' : '';
                }
                if (!wrapper) return;
                wrapper.querySelectorAll('.dt-loading-overlay').forEach(function(overlay) {
                    overlay.style.display = show ? 'flex' : 'none';
                });
            }

            loadPeopleLeads().catch(function(error) {
                console.error('Failed loading people leads:', error);
            });

            document.querySelectorAll('[data-export-trigger]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const targetClass = btn.getAttribute('data-export-trigger') === 'csv' ? '.buttons-csv' : '.buttons-excel';
                    const exportBtn = document.querySelector('.dt-buttons ' + targetClass) || document.querySelector(targetClass);
                    if (exportBtn) exportBtn.click();
                    const exportModalEl = document.getElementById('exportLeadListModal');
                    getBootstrapModal(exportModalEl)?.hide();
                });
            });

            const addPeopleLeadForm = document.getElementById('addPeopleLeadForm');
            const addPeopleLeadUrl = document.getElementById('addPeopleLeadUrl');
            const addPeopleLeadStatus = document.getElementById('addPeopleLeadStatus');
            const savePeopleLeadUrl = document.getElementById('savePeopleLeadUrl');

            function setAddPeopleLeadStatus(type, text) {
                if (!addPeopleLeadStatus) return;
                addPeopleLeadStatus.className = 'people-add-status' + (type ? ' is-' + type : '');
                addPeopleLeadStatus.textContent = text || '';
            }

            function normalizePeopleLinkedInUrl(value) {
                const trimmed = String(value || '').trim();
                if (!trimmed) return '';

                try {
                    const parsedUrl = new URL(trimmed);
                    const hostname = parsedUrl.hostname.toLowerCase();
                    const isLinkedInUrl = hostname === 'linkedin.com' || hostname.endsWith('.linkedin.com');
                    if (isLinkedInUrl && parsedUrl.pathname && !parsedUrl.pathname.endsWith('/')) {
                        parsedUrl.pathname += '/';
                    }
                    return parsedUrl.toString();
                } catch (error) {
                    return trimmed;
                }
            }

            async function postPeopleLeadUrl(payload) {
                const endpoints = [
                    'https://api.coherta.com/pipeline/leads/people',
                    'https://api.coherta.com/pipeline/all/leads/people'
                ];

                let lastError = null;
                for (const endpoint of endpoints) {
                    try {
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                Authorization: 'Bearer ' + document.body.getAttribute('session_id')
                            },
                            body: JSON.stringify(payload)
                        });
                        const responseText = await response.text();
                        let responseBody = null;
                        if (responseText) {
                            try {
                                responseBody = JSON.parse(responseText);
                            } catch (error) {
                                responseBody = responseText;
                            }
                        }
                        if (response.ok) {
                            return responseBody;
                        }
                        const apiError = responseBody && (responseBody.message || responseBody.detail || responseBody.error)
                            ? (responseBody.message || responseBody.detail || responseBody.error)
                            : 'HTTP ' + response.status;
                        const endpointPath = new URL(endpoint).pathname;
                        if (response.status === 405) {
                            lastError = 'API route ' + endpointPath + ' does not accept POST yet. Deploy the People List backend update and try again.';
                        } else {
                            lastError = endpointPath + ': ' + apiError;
                        }
                    } catch (error) {
                        lastError = error.message || String(error);
                    }
                }
                throw new Error(lastError || 'Could not add person');
            }

            async function deletePeopleLeadFromList(payload) {
                const endpoints = [
                    {
                        url: 'https://api.coherta.com/pipeline/leads/people/delete',
                        body: payload
                    },
                    {
                        url: 'https://api.coherta.com/pipeline/leads/delete',
                        body: {
                            pipeline_id: payload.pipeline_id,
                            id: payload.id
                        }
                    },
                    {
                        url: 'https://api.coherta.com/pipeline/leads/delete',
                        body: {
                            pipeline_id: payload.pipeline_id,
                            lead_id: payload.id
                        }
                    }
                ];

                let lastError = null;
                for (const endpoint of endpoints) {
                    try {
                        const response = await fetch(endpoint.url, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                Authorization: 'Bearer ' + document.body.getAttribute('session_id')
                            },
                            body: JSON.stringify(endpoint.body)
                        });
                        const responseText = await response.text();
                        let responseBody = null;
                        if (responseText) {
                            try {
                                responseBody = JSON.parse(responseText);
                            } catch (error) {
                                responseBody = responseText;
                            }
                        }
                        if (response.ok) return responseBody;
                        const apiError = responseBody && (responseBody.message || responseBody.detail || responseBody.error)
                            ? (responseBody.message || responseBody.detail || responseBody.error)
                            : 'HTTP ' + response.status;
                        lastError = new URL(endpoint.url).pathname + ': ' + apiError;
                    } catch (error) {
                        lastError = error.message || String(error);
                    }
                }
                throw new Error(lastError || 'Could not delete person from list');
            }

            addPeopleLeadUrl?.addEventListener('blur', function() {
                addPeopleLeadUrl.value = normalizePeopleLinkedInUrl(addPeopleLeadUrl.value);
            });

            addPeopleLeadForm?.addEventListener('submit', async function(event) {
                event.preventDefault();
                const url = normalizePeopleLinkedInUrl(addPeopleLeadUrl ? addPeopleLeadUrl.value : '');
                if (!url) {
                    setAddPeopleLeadStatus('error', 'Enter a profile URL.');
                    addPeopleLeadUrl?.focus();
                    return;
                }
                if (addPeopleLeadUrl) addPeopleLeadUrl.value = url;

                const payload = {
                    pipeline_id: Number(pipelineId),
                    linkedin_url: url
                };

                savePeopleLeadUrl.disabled = true;
                setAddPeopleLeadStatus('', 'Adding person...');

                try {
                    await postPeopleLeadUrl(payload);
                    setAddPeopleLeadStatus('success', 'Person added. Refreshing list...');
                    await loadPeopleLeads();
                    $("#addPeopleLeadModal").modal("hide")
                } catch (error) {
                    setAddPeopleLeadStatus('error', error.message || 'Could not add person.');
                } finally {
                    savePeopleLeadUrl.disabled = false;
                }
            });

            document.addEventListener('click', async function(event) {
                const deleteBtn = event.target.closest('.people-delete-person');
                if (!deleteBtn) return;
                event.preventDefault();

                const leadId = decodeURIComponent(deleteBtn.getAttribute('data-lead-id') || '');
                const personName = deleteBtn.getAttribute('data-person-name') || 'this person';
                if (!leadId || !pipelineId) return;

                if (!window.confirm('Delete ' + personName + ' from this People Leads list?')) return;

                const rowEl = deleteBtn.closest('tr');
                deleteBtn.disabled = true;
                deleteBtn.classList.add('is-loading');

                try {
                    await deletePeopleLeadFromList({
                        pipeline_id: Number(pipelineId),
                        id: Number(leadId),
                        lead_id: Number(leadId),
                        pipeline_company_id: Number(leadId)
                    });
                    const table = window.leadsTable || $('#ajax-data-array').DataTable();
                    table.row(rowEl).remove().draw(false);
                    const totalEl = document.getElementById('peopleLeadTotal');
                    if (totalEl) {
                        const currentTotal = parseInt(totalEl.textContent, 10);
                        if (!Number.isNaN(currentTotal) && currentTotal > 0) {
                            totalEl.textContent = (currentTotal - 1) + ' people';
                        }
                    }
                } catch (error) {
                    alert(error.message || 'Could not delete person from list.');
                    deleteBtn.disabled = false;
                    deleteBtn.classList.remove('is-loading');
                }
            });

        });
    </script>
    <?php } ?>
    <style>
        .people-leads-card { border: 0; border-radius: 22px; overflow: hidden; box-shadow: 0 18px 50px rgba(15, 23, 42, .08); }
        .people-leads-body { padding: 0; background: #fff; }
        .people-leads-hero { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 26px; background: #f8fafc; border-bottom: 1px solid rgba(15,23,42,.08); }
        .people-leads-title { margin: 0; font-size: 28px; line-height: 1.2; color: #111827; }
        .people-leads-hero p { margin: 6px 0 0; color: #667085; }
        .people-leads-actions, .people-leads-toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .people-leads-count { display: inline-flex; align-items: center; min-height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid rgba(15,23,42,.08); font-weight: 700; color: #344054; }
        .people-leads-btn { display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(15,23,42,.08); border-radius: 12px; }
        .people-delete-person.is-loading { opacity: .55; pointer-events: none; }
        .people-add-status { min-height: 20px; color: #667085; font-size: 13px; font-weight: 700; }
        .people-add-status.is-success { color: #15803d; }
        .people-add-status.is-error { color: #b91c1c; }
        .people-settings-section { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 16px; border: 1px solid rgba(220, 38, 38, .18); border-radius: 12px; background: #fff7f7; }
        .people-settings-section h6 { margin: 0 0 4px; color: #991b1b; font-weight: 800; }
        .people-settings-section p { margin: 0; color: #667085; }
        .people-leads-toolbar { padding: 18px 24px; border-bottom: 1px solid rgba(15,23,42,.08); justify-content: space-between; }
        .people-leads-search-slot { margin-left: auto; }
        .people-leads-search-slot .dataTables_filter { margin: 0; }
        .people-leads-search-slot .dataTables_filter label { margin: 0; display: block; }
        .people-leads-search-slot .dataTables_filter input { width: min(360px, 70vw); height: 44px; border-radius: 12px; border: 1px solid rgba(15,23,42,.08); background: #fff; padding: 0 14px; margin-left: 0 !important; }
        .table-responsive { overflow-x: auto; background: #fff; }
        .people-floating-scroll { display: none; position: fixed; bottom: 0; z-index: 1040; height: 18px; overflow-x: auto; overflow-y: hidden; background: #fff; border-top: 1px solid rgba(15,23,42,.12); box-shadow: 0 -4px 12px rgba(15,23,42,.08); }
        .people-floating-scroll.is-visible { display: block; }
        .people-floating-scroll-inner { height: 1px; }
        table.dataTable { margin-top: 0 !important; width: 100% !important; }
        table.dataTable thead th { background: #f8fafc !important; color: #344054; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; border-bottom: 1px solid rgba(15,23,42,.08) !important; padding: 15px 14px !important; white-space: nowrap; }
        table.dataTable tbody td { padding: 15px 14px !important; border-bottom: 1px solid rgba(15,23,42,.06) !important; vertical-align: middle; white-space: nowrap; }
        table.dataTable tbody tr:hover td { background: #f8fbff; }
        .people-leads-person { display: grid; grid-template-columns: 34px minmax(0, 1fr); grid-template-rows: auto auto; column-gap: 10px; align-items: center; color: #111827; width: 320px; max-width: 42vw; min-width: 260px; overflow: hidden; }
        .people-leads-person:hover { color: #0d6efd; }
        .people-leads-person strong { grid-column: 2; font-weight: 800; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .people-leads-person span:not(.people-leads-avatar) { grid-column: 2; color: #667085; font-size: 12px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .people-leads-avatar { grid-row: 1 / span 2; width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #e5e7eb; }
        .people-leads-avatar-empty { display: inline-block; }
        .people-skill-pill { display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; margin: 2px 4px 2px 0; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: 12px; font-weight: 700; }
        .export-choice-body { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .export-choice-card { width: 100%; display: flex; align-items: center; justify-content: center; gap: 14px; padding: 24px 18px; border-radius: 18px; border: 1px solid rgba(15,23,42,.08); background: #f8fafc; text-align: center; font-size: 16px; font-weight: 700; }
        .export-choice-card i { font-size: 34px; color: #4f46e5; }
        .leads-hidden-export-buttons { display: none !important; }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { padding: 16px 24px 20px; }
        @media (max-width: 991px) {
            .people-leads-hero { align-items: flex-start; flex-direction: column; }
            .people-leads-actions, .people-leads-toolbar, .people-leads-search-slot { width: 100%; }
            .people-leads-btn, .people-leads-search-slot .dataTables_filter input { width: 100%; justify-content: center; }
            .export-choice-body { grid-template-columns: 1fr; }
        }
    </style>
<?php include('partial/footer-end.php'); ?>
