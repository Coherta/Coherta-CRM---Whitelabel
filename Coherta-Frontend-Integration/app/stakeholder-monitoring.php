<?php
require_once './base.php';
require_once __DIR__ . '/../includes/stakeholder_monitoring_credits.php';
$sidebar_close = true;
$PageTitle = 'Lead Monitoring';
$meta_description = 'Monitor key stakeholders and get notified when contacts change role or company.';
include('partial/header.php');

$stakeholder_monitoring_credit_context = isset($company_data) && is_array($company_data)
    ? $company_data
    : [];
$stakeholder_monitoring_credit_context = array_merge(
    $stakeholder_monitoring_credit_context,
    stakeholder_monitoring_credit_context_from_token($_SESSION['token'] ?? '')
);
$stakeholder_monitoring_credit_limit = stakeholder_monitoring_credit_limit($stakeholder_monitoring_credit_context, null);
?>
<link rel="stylesheet" href="/app/assets/css/stakeholder-monitoring.css?v=<?php echo filemtime(__DIR__ . '/assets/css/stakeholder-monitoring.css'); ?>">
<link rel="stylesheet" href="/app/assets/css/lead-contact-editor.css?v=<?php echo filemtime(__DIR__ . '/assets/css/lead-contact-editor.css'); ?>">
<script src="/app/assets/js/lead-contact-editor.js?v=<?php echo filemtime(__DIR__ . '/assets/js/lead-contact-editor.js'); ?>"></script>
<?php
include('partial/loader.php');
?>
<script>
    window.COHERTA_STAKEHOLDER_CREDITS = <?php echo json_encode(['total' => $stakeholder_monitoring_credit_limit]); ?>;
</script>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body no-top-margin stakeholder-monitoring-page">
            <main class="stakeholder-shell" data-stakeholder-app>
                <section class="stakeholder-hero-row">
                    <div>
                        <h1>Lead Monitoring</h1>
                        <p>Monitor key contacts and get notified when they change role or company.</p>
                    </div>
                    <div class="stakeholder-actions">
                        <button class="sm-btn sm-btn-light" type="button" id="openCsvFormatBtn">
                            <i data-feather="upload"></i>
                            <span>Upload CSV</span>
                        </button>
                        <input id="stakeholderCsvInput" type="file" accept=".csv,text/csv" hidden>
                        <button class="sm-btn sm-btn-light" type="button" id="downloadStakeholdersBtn">
                            <i data-feather="download"></i>
                            <span>Download list</span>
                        </button>
                        <button class="sm-btn sm-btn-light" type="button" id="enrichMissingPhonesBtn">
                            <i data-feather="phone"></i>
                            <span>Find missing phones</span>
                        </button>
                        <button class="sm-btn sm-btn-light" type="button" id="enrichMissingEmailsBtn">
                            <i data-feather="mail"></i>
                            <span>Find missing e-mails</span>
                        </button>
                        <button class="sm-btn sm-btn-primary" type="button" data-open-add-stakeholder>
                            <i data-feather="user-plus"></i>
                            <span>Add stakeholder</span>
                        </button>
                    </div>
                </section>

                <section class="stakeholder-grid">
                    <div class="stakeholder-main">
                        <section class="sm-overview-card">
                            <div class="sm-overview-icon"><i data-feather="users"></i></div>
                            <div>
                                <h2>Tracks stakeholders proactively</h2>
                                <p>Preserve the relationship when key people change role or company.</p>
                            </div>
                            <div class="sm-sparkline" aria-label="Monitoring activity">
                                <div class="sm-graph-metrics">
                                    <div>
                                        <span>Monitored</span>
                                        <strong id="stakeholderGraphMonitored">-</strong>
                                    </div>
                                    <div>
                                        <span>Changes detected</span>
                                        <strong id="stakeholderGraphChanges">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="sm-stat">
                                <span>Monitored stakeholders</span>
                                <strong id="stakeholderTotal">-</strong>
                                <small><span id="stakeholderCreditDelta">0</span> Available credits</small>
                            </div>
                        </section>

                        <section class="sm-toolbar">
                            <div class="sm-tabs" role="tablist" aria-label="Stakeholder filters">
                                <button class="active" type="button" data-status-filter="all">All</button>
                                <button type="button" data-status-filter="active">Active</button>
                                <button type="button" data-status-filter="changes">Changes</button>
                                <button type="button" data-status-filter="paused">Paused</button>
                            </div>
                            <div class="sm-toolbar-actions">
                                <label class="sm-campaign-filter">
                                    <i data-feather="layers"></i>
                                    <span class="sr-only">Filter stakeholders by campaign</span>
                                    <select id="stakeholderCampaignFilter" aria-label="Filter stakeholders by campaign">
                                        <option value="">All campaigns</option>
                                    </select>
                                </label>
                                <button class="sm-filter-button" type="button" data-scope-toggle>
                                    <i data-feather="filter"></i>
                                    <span data-scope-label>Company</span>
                                </button>
                            </div>
                        </section>

                        <section class="sm-table-card">
                            <header>
                                <h2>Your stakeholders <span id="stakeholderCountPill">0</span></h2>
                                <div id="stakeholderTableStatus">Loading...</div>
                            </header>
                            <div class="sm-table-wrap">
                                <table>
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Title</th>
                                        <th>Contact data</th>
                                        <th>LinkedIn</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Last change</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="stakeholderTableBody">
                                    <tr><td colspan="9">Loading stakeholders...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <aside class="sm-notification-board is-minimized" aria-label="Notification board">
                        <button class="sm-notification-fab" type="button" data-toggle-notifications aria-label="Open notification board">
                            <i data-feather="bell"></i>
                            <span id="stakeholderNotificationBadge">0</span>
                        </button>
                        <header>
                            <div>
                                <h2>Notification board</h2>
                                <p>Contacts that need your attention</p>
                            </div>
                            <div>
                                <button type="button" data-toggle-notifications>Minimize</button>
                                <button type="button" data-notification-seen-filter>
                                    <i data-feather="filter"></i>
                                    <span>Unseen</span>
                                </button>
                            </div>
                        </header>
                        <div id="stakeholderNotifications" class="sm-notification-list">
                            <div class="sm-empty">Loading notifications...</div>
                        </div>
                    </aside>
                </section>
            </main>
        </div>
    </div>
</div>

<div class="sm-modal-backdrop" id="stakeholderCsvFormatModal" aria-hidden="true">
    <div class="sm-modal" role="dialog" aria-modal="true" aria-labelledby="stakeholderCsvFormatTitle">
        <header>
            <div>
                <h2 id="stakeholderCsvFormatTitle">Upload CSV</h2>
                <p>Upload a CSV. We'll find all LinkedIn profile URLs in the file.</p>
            </div>
            <button type="button" data-close-csv-format aria-label="Close">x</button>
        </header>
        <div class="sm-modal-body">
            <div class="sm-csv-format">
                <pre><code>https://www.linkedin.com/in/example-person</code></pre>
            </div>
        </div>
        <footer>
            <label class="sm-btn sm-btn-primary" for="stakeholderCsvInput">
                <i data-feather="upload"></i>
                <span>Upload CSV</span>
            </label>
            <button class="sm-btn sm-btn-light" type="button" id="downloadCsvTemplateBtn">
                <i data-feather="download"></i>
                <span>Download template</span>
            </button>
            <button class="sm-btn sm-btn-primary" type="button" data-close-csv-format>Done</button>
        </footer>
    </div>
</div>

<div class="sm-modal-backdrop" id="stakeholderAddModal" aria-hidden="true">
    <div class="sm-modal" role="dialog" aria-modal="true" aria-labelledby="stakeholderAddTitle">
        <header>
            <div>
                <h2 id="stakeholderAddTitle">Add stakeholder</h2>
                <p>Add a contact by LinkedIn URL.</p>
            </div>
            <button type="button" data-close-add-stakeholder aria-label="Close">x</button>
        </header>
        <div class="sm-modal-body">
            <label>LinkedIn URL
                <input id="stakeholderLinkedInInput" type="url" placeholder="https://www.linkedin.com/in/...">
            </label>
            <label>Note
                <textarea id="stakeholderNoteInput" rows="3" placeholder="Relationship owner, context, or next step"></textarea>
            </label>
        </div>
        <footer>
            <button class="sm-btn sm-btn-light" type="button" data-close-add-stakeholder>Cancel</button>
            <button class="sm-btn sm-btn-primary" type="button" id="saveStakeholderBtn">Add stakeholder</button>
        </footer>
    </div>
</div>

<div class="sm-toast" id="stakeholderToast" role="status" aria-live="polite"></div>

<?php include('partial/scripts.php'); ?>
<script src="/app/assets/js/stakeholder-monitoring.js?v=<?php echo filemtime(__DIR__ . '/assets/js/stakeholder-monitoring.js'); ?>-source-column"></script>
<?php include('partial/footer-end.php'); ?>
