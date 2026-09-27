<?php
$PageTitle = 'Coherta Campaigns';
$embeddedLeadModal = isset($_GET['embedded']) && $_GET['embedded'] === '1';
include 'campaigns-shared.php';
require_once __DIR__ . '/playbooks-static.php';
$campaignPlaybooks = coherta_campaign_static_playbooks();
$campaignAdvancedNotifications = $client->get('notifications') ?? [];
$campaignAdvancedNotifications = array_values(array_filter(array_map(function ($notification) {
    if (!is_array($notification) || empty($notification['id'])) {
        return null;
    }
    $id = (int)$notification['id'];
    return [
        'id' => $id,
        'subject' => trim((string)($notification['subject'] ?? '')) !== '' ? (string)$notification['subject'] : 'Signal #' . $id,
        'pipeline_id' => (int)($notification['pipeline_id'] ?? 0),
    ];
}, is_array($campaignAdvancedNotifications) ? $campaignAdvancedNotifications : [])));
?>
<?php if ($embeddedLeadModal): ?>
<style>
    html,body{background:transparent!important;overflow:hidden!important}
    body *{visibility:hidden!important}
    body #addLeadsModal,body #addLeadsModal *{visibility:visible!important}
</style>
<?php endif; ?>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="page-body-wrapper">
            <?php include('../partial/sidebar.php'); ?>
            <div class="page-body no-top-margin">
                <div class="app-shell">
                    <main class="content">
                        <?php campaign_topbar('dashboard'); ?>
                        <section class="panel hero">
                            <div class="panel-inner">
                                <div class="hero-grid">
                                    <div>
                                        <h2>Coherta Campaigns</h2>
                                        <p>Manage campaigns, assign employees, tune activity periods, and push new leads into campaigns from CSV, manual, lead list, signal, or search sources.</p>
                                        <div class="hero-actions">
                                            <button class="btn btn-success" type="button" data-open-create-modal>New Campaign</button>
                                        </div>
                                    </div>
                                    <div class="hero-stats">
                                        <div class="hero-stat"><strong id="statCampaigns">-</strong><span>Active campaigns</span></div>
                                        <div class="hero-stat"><strong id="statAttached">-</strong><span>Opportunities</span></div>
                                        <div class="hero-stat"><strong id="statConnected">-</strong><span>Accepted</span></div>
                                        <div class="hero-stat"><strong id="statReplies">-</strong><span>Reply rate (Threads)</span></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <div id="campaignRampUpNotice"></div>
                        <section class="panel">
                            <div class="panel-inner">
                                <div class="section-head">
                                    <div>
                                        <h3>All Campaigns</h3>
                                        <p>Overview of every campaign with quick access to lead imports, workflow builder, settings, and report export.</p>
                                    </div>
                                    <div class="campaign-table-actions">
                                        <button class="btn btn-outline campaign-csv-btn" type="button" id="downloadCampaignTableCsvBtn">Download Table as CSV</button>
                                        <div class="count-pill" id="campaignCountPill">Loading...</div>
                                    </div>
                                </div>
                                <div class="table-shell">
                                    <table class="data-table campaign-dashboard-table">
                                        <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Opportunities</th>
                                            <th>Connection Requests</th>
                                            <th>Accepted</th>
                                            <th>Acceptance Rate</th>
                                            <th>Settings</th>
                                        </tr>
                                        </thead>
                                        <tbody id="campaignTableBody">
                                        <tr><td colspan="6"><div class="empty-state">Loading campaigns...</div></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-overlay" id="createCampaignModal">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="createCampaignModalTitle">
            <div class="modal-head">
                <div>
                    <h3 id="createCampaignModalTitle">Create new Campaign</h3>
                    <p id="campaignWizardSubtitle">What do you want to achieve?</p>
                </div>
                <button class="modal-close" type="button" data-close-create-modal>×</button>
            </div>
            <div class="modal-body">
                <div class="campaign-wizard-progress" id="campaignWizardProgress" aria-label="Campaign creation progress"></div>
                <div class="campaign-wizard-step" id="campaignWizardGoalStep">
                    <div class="campaign-goal-grid" role="radiogroup" aria-label="Campaign goal">
                        <button class="campaign-goal-card" type="button" data-campaign-goal="connections" aria-pressed="false"><span class="campaign-goal-icon" aria-hidden="true">↗</span><strong>More Connections</strong><span>Grow an employee's LinkedIn network with relevant people.</span></button>
                        <button class="campaign-goal-card" type="button" data-campaign-goal="sales" aria-pressed="false"><span class="campaign-goal-icon" aria-hidden="true">◎</span><strong>More Sales</strong><span>Find, qualify, connect with, and message new sales leads.</span></button>
                        <button class="campaign-goal-card" type="button" data-campaign-goal="advanced" aria-pressed="false"><span class="campaign-goal-icon" aria-hidden="true">⚙</span><strong>Advanced Setup</strong><span>Choose a lead source and build the workflow yourself.</span></button>
                    </div>
                </div>
                <div class="campaign-wizard-step" id="campaignWizardAdvancedModeStep" hidden>
                    <div class="campaign-goal-grid campaign-advanced-mode-grid" role="radiogroup" aria-label="Advanced campaign setup type">
                        <button class="campaign-goal-card" type="button" data-advanced-create-mode="scratch" aria-pressed="false"><span class="campaign-goal-icon" aria-hidden="true">＋</span><strong>Create new Campaign</strong><span>Start with a new campaign and build the workflow yourself.</span></button>
                        <button class="campaign-goal-card" type="button" data-advanced-create-mode="duplicate" aria-pressed="false"><span class="campaign-goal-icon" aria-hidden="true">⧉</span><strong>Duplicate Campaign</strong><span>Copy the setup and workflow from an existing campaign.</span></button>
                    </div>
                </div>
                <div class="campaign-wizard-step" id="campaignWizardEmployeeStep" hidden>
                    <div class="field">
                        <label id="campaignEmployeeQuestion">Which employee needs more connections?</label>
                        <div class="checkbox-grid" id="campaignEmployeeGrid">
                            <?php foreach ($campaignEmployees as $employee): ?>
                                <?php $isLinkedinConnected = (($employee['connect'] ?? 0) == 1); ?>
                                <label class="checkbox-card">
                                    <input type="radio" name="campaign_employees[]" value="<?php echo (int)$employee['id']; ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($employee['full_name']); ?></strong><br>
                                        <span style="color: var(--muted); font-size: 13px;"><?php echo htmlspecialchars($employee['email'] ?? 'No email'); ?></span>
                                        <?php if (!$isLinkedinConnected): ?>
                                            <div style="display:flex;align-items:center;gap:8px;margin-top:8px;color:#b45309;font-size:12px;font-weight:600;">
                                                <span aria-hidden="true" style="font-size:14px;line-height:1;">⚠</span>
                                                <span>LinkedIn is not yet connected</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="campaign-wizard-step" id="campaignWizardAudienceStep" hidden>
                    <div class="lead-search-grid">
                        <div class="field"><label for="campaignDecisionMakerTitle" id="campaignDecisionMakerLabel">Who do you want to connect with?</label><input class="input" id="campaignDecisionMakerTitle" type="text" placeholder="Example: CFO"></div>
                        <div class="field"><label for="campaignAudienceCountry">Country</label><select class="select" id="campaignAudienceCountry"><option value="">Select a country</option></select></div>
                    </div>
                    <div class="field" id="campaignQualificationField" style="margin-top:16px;" hidden><label for="campaignLeadQualification">What qualifies a lead as good?</label><textarea class="textarea" id="campaignLeadQualification" placeholder="Describe the company, seniority, responsibilities, or other signals that make someone a good sales lead."></textarea></div>
                </div>
                <div class="campaign-wizard-step" id="campaignWizardDuplicateStep" hidden>
                    <div class="field">
                        <label for="duplicateCampaignSearch">Duplicate Campaign</label>
                        <input class="input" id="duplicateCampaignSearch" type="text" placeholder="Search campaigns by title or employee">
                    </div>
                    <div class="duplicate-campaign-list" id="duplicateCampaignList" style="margin-top:16px;">
                        <div class="empty-state">Loading campaigns...</div>
                    </div>
                </div>
                <div class="campaign-wizard-step" id="campaignWizardTitleStep" hidden>
                    <div class="field"><label for="campaignTitle">Title of Campaign</label><input class="input" id="campaignTitle" type="text" placeholder="Enter a campaign title"><small id="campaignTitleHelp" style="display:block;margin-top:7px;color:var(--muted);">A suggested title is generated from the campaign details. You can change it before creating the campaign.</small></div>
                    <div class="campaign-workflow-preview" id="campaignWorkflowPreview"></div>
                </div>

                <div class="campaign-create-status" id="campaignCreateStatus" role="status" aria-live="polite" hidden>
                    <span class="campaign-create-spinner" aria-hidden="true"></span>
                    <span id="campaignCreateStatusText">Creating campaign...</span>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-outline" type="button" data-close-create-modal>Cancel</button>
                    <div class="campaign-wizard-actions"><button class="btn btn-outline" type="button" id="campaignWizardBackBtn" hidden>Back</button><button class="btn btn-success campaign-create-submit" type="button" id="saveCampaignBtn"><span data-create-button-text>Continue</span></button></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-overlay" id="addLeadsModal">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="addLeadsModalTitle">
            <div class="modal-head">
                <div>
                    <h3 id="addLeadsModalTitle">Add new leads to campaign</h3>
                    <p>Choose how new leads should be added to the selected campaign.</p>
                </div>
                <button class="modal-close" type="button" data-close-add-leads-modal>×</button>
            </div>
            <div class="modal-body">
                <div id="leadSearchInputContent">
                    <input type="hidden" id="addLeadsCampaignId" value="">
                    <div class="field">
                        <label for="addLeadsCampaignTitle">Campaign</label>
                        <input class="input" id="addLeadsCampaignTitle" type="text" readonly>
                    </div>
                    <div class="field" style="margin-top:16px;">
                        <label for="leadSourceType">Source</label>
                        <select class="select" id="leadSourceType">
                            <option value="csv">Upload CSV-file</option>
                            <option value="manual">Manual Added</option>
                            <option value="pipeline">Lead list</option>
                            <option value="signal">Signal</option>
                            <option value="employee_title_location">Employee Title + Location</option>
                            <option value="linkedin_search_url">URL to LinkedIn Search</option>
                            <option value="like_of_posts">Like of Posts</option>
                            <option value="company_followers">Company Followers</option>
                            <option value="connections">Connections</option>
                        </select>
                        <div id="leadSourceSavedSummary" style="margin-top:8px;color:var(--muted);font-size:13px;">No lead source selected yet.</div>
                    </div>
                    <div id="leadSourceCsvSection" style="margin-top:16px;">
                        <div class="field">
                            <label for="leadCsvFile">CSV-file</label>
                            <input class="input" id="leadCsvFile" type="file" accept=".csv,text/csv">
                        </div>
                        <div class="card" style="margin-top:16px;">
                            <h4>LinkedIn User URL's found</h4>
                            <p id="leadCsvSummary">Upload a CSV-file and the UI will extract all LinkedIn profile URLs it can find.</p>
                            <textarea class="textarea" id="leadCsvLinkedinUrls" placeholder="https://www.linkedin.com/in/..." readonly></textarea>
                        </div>
                    </div>
                    <div id="leadSourceManualSection" style="display:none;margin-top:16px;">
                        <div class="card">
                            <h4>LinkedIn User URL's</h4>
                            <p id="leadManualSummary">Paste LinkedIn profile URLs into the field below.</p>
                            <textarea class="textarea" id="leadManualLinkedinUrls" placeholder="https://www.linkedin.com/in/..."></textarea>
                        </div>
                    </div>
                    <div id="leadSourceExistingPolicySection" style="margin-top:16px;padding:14px 16px;border:1px solid rgba(148,163,184,.28);border-radius:14px;background:rgba(148,163,184,.08);">
                        <label for="addMissingCampaignOpportunities" style="display:flex;align-items:flex-start;gap:14px;cursor:pointer;user-select:none;">
                            <input type="checkbox" id="addMissingCampaignOpportunities" checked style="width:22px;height:22px;min-width:22px;margin-top:1px;cursor:pointer;accent-color:#2563eb;">
                            <span style="font-size:15px;line-height:1.45;font-weight:600;color:#0f172a;">Add all opportunities that do not exist in this specific campaign</span>
                        </label>
                    </div>
                    <div id="leadSourceConnectionsSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label for="leadConnectionsMode">Connections</label>
                            <select class="select" id="leadConnectionsMode">
                                <option value="not_yet_accepted">Not yet accepted</option>
                                <option value="accepted_latest_48_hours">Accepted within latest 48 hours</option>
                                <option value="all_profile_visits">All Profile Visits (+LinkedIn Business required)</option>
                            </select>
                        </div>
                    </div>
                    <div id="leadSourcePipelineSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label for="leadPipelineId">Select your lead list</label>
                            <select id="leadPipelineId" class="select">
                                <option value="0">Select a lead list</option>
                                <?php
                                $pipelines = $client->getCurrentPipelines() ?? [];
                                foreach ($pipelines as $pipeline): ?>
                                    <option value="<?php echo (int)$pipeline['id']; ?>"><?php echo htmlspecialchars($pipeline['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (count($pipelines) === 0): ?>
                            <div class="empty-state">No lead lists were found. Create a lead list before using it as a source.</div>
                        <?php else: ?>
                            <div class="lead-search-note">Add leads from the selected lead list to this campaign.</div>
                        <?php endif; ?>
                    </div>
                    <div id="leadSourceSignalSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label>Advanced Notification signals</label>
                            <input id="leadSignalId" type="hidden" value="">
                            <?php if (count($campaignAdvancedNotifications) > 0): ?>
                                <div class="signal-picker" id="leadSignalPicker" role="group" aria-label="Advanced Notification signals">
                                    <label class="signal-option signal-option-all">
                                        <input type="checkbox" id="leadSignalAll" value="all">
                                        <span>
                                            <strong>All signals</strong>
                                            <small>Add leads from every saved Advanced Notification.</small>
                                        </span>
                                    </label>
                                    <?php foreach ($campaignAdvancedNotifications as $notification): ?>
                                        <label class="signal-option">
                                            <input type="checkbox" name="lead_signal_ids[]" value="<?php echo (int)$notification['id']; ?>">
                                            <span>
                                                <strong><?php echo htmlspecialchars($notification['subject']); ?></strong>
                                                <small>Signal ID <?php echo (int)$notification['id']; ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="lead-search-note" id="leadSignalSelectionSummary">Choose one or more Advanced Notifications to listen to.</div>
                            <?php else: ?>
                                <div class="empty-state">
                                    No Advanced Notifications were found. Create one under Advanced Notifications before using Signal as a source.
                                </div>
                                <div class="lead-search-note" id="leadSignalSelectionSummary">No saved signals available.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="leadSourceTitleLocationSection" style="display:none;margin-top:16px;">
                        <div class="lead-search-grid">
                            <div class="field">
                                <label for="leadEmployeeTitle">Employee Title</label>
                                <input class="input" id="leadEmployeeTitle" type="text" placeholder="Example: CEO">
                            </div>
                            <div class="field">
                                <label for="leadTitleCountry">Location</label>
                                <select class="select" id="leadTitleCountry">
                                    <option value="">Select a country</option>
                                </select>
                            </div>
                        </div>
                        <div class="lead-search-note">The search will identify people matching the selected title and country before they are added to the campaign.</div>
                    </div>
                    <div id="leadSourceLinkedinUrlSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label for="leadLinkedinSearchUrl">LinkedIn Search URL</label>
                            <input class="input" id="leadLinkedinSearchUrl" type="url" placeholder="https://www.linkedin.com/search/results/people/?keywords=CEO&geoUrn=%5B%22104514075%22%5D">
                        </div>
                        <div class="lead-url-preview" id="leadLinkedinUrlPreview">
                            Paste a LinkedIn people or Sales Navigator search URL. Keyword and country are saved when they can be detected.
                        </div>
                    </div>
                    <div id="leadSourceLikePostsSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label for="leadLikePostsMode">Posts</label>
                            <select class="select" id="leadLikePostsMode">
                                <option value="all_posts">All my own posts</option>
                                <option value="user_posts">Other user's posts</option>
                                <option value="specific_post">Specific post</option>
                            </select>
                        </div>
                        <div class="field" id="leadLikePostsUserUrlField" style="display:none;margin-top:16px;">
                            <label for="leadLikePostsUserUrl">LinkedIn user or company URL</label>
                            <input class="input" id="leadLikePostsUserUrl" type="url" placeholder="https://www.linkedin.com/in/...">
                        </div>
                        <div class="field" id="leadLikePostsPostUrlField" style="display:none;margin-top:16px;">
                            <label for="leadLikePostsPostUrl">LinkedIn post URL</label>
                            <input class="input" id="leadLikePostsPostUrl" type="url" placeholder="https://www.linkedin.com/posts/...">
                        </div>
                        <div class="lead-search-note" id="leadLikePostsSummary">Collects people who liked LinkedIn posts.</div>
                    </div>
                    <div id="leadSourceCompanyFollowersSection" style="display:none;margin-top:16px;">
                        <div class="field">
                            <label for="leadCompanyFollowersUrl">LinkedIn Company URL</label>
                            <input class="input" id="leadCompanyFollowersUrl" type="url" placeholder="https://www.linkedin.com/company/company-name/">
                        </div>
                        <div class="lead-search-note">Add the LinkedIn link for the company whose followers should be collected.</div>
                    </div>
                </div>
                <div id="leadSearchProgressSection" class="lead-search-progress" style="display:none;">
                    <div class="lead-search-save-message" id="leadSearchSaveMessage">The subjects will be added to your campaign within 24 hours.</div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-outline" type="button" data-close-add-leads-modal>Cancel</button>
                    <button class="btn btn-success" type="button" id="saveAddLeadsBtn">Add leads</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="attachedContactsModal">
        <div class="modal-card modal-card-lg" role="dialog" aria-modal="true" aria-labelledby="attachedContactsModalTitle">
            <div class="modal-head">
                <div>
                    <h3 id="attachedContactsModalTitle">Attached Contacts</h3>
                    <p id="attachedContactsModalSubtitle">Review all contacts currently attached to this campaign.</p>
                </div>
                <div class="modal-head-actions">
                    <select class="select compact-select" id="attachedContactsOpportunityFilter" aria-label="Opportunity filter">
                        <option value="all">All Opportunities</option>
                        <option value="active">Active Opportunities</option>
                        <option value="paused">Stopped Opportunities</option>
                        <option value="completed">Completed Opportunities</option>
                    </select>
                    <button class="modal-close" type="button" data-close-attached-contacts-modal>×</button>
                </div>
            </div>
            <div class="modal-body">
                <input type="hidden" id="attachedContactsCampaignId" value="">
                <div class="table-shell">
                    <table class="data-table">
                        <thead id="attachedContactsTableHead">
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Screening score</th>
                            <th>Current state</th>
                            <th>Next movement</th>
                            <th>LinkedIn</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="attachedContactsTableBody">
                        <tr><td colspan="8"><div class="empty-state">Loading attached contacts...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="dashboardToast"></div>
    <style>
        .modal-card-lg { width: min(1080px, calc(100vw - 32px)); }
        .contact-name-unknown {
            display: grid;
            gap: 3px;
            max-width: 230px;
        }
        .contact-name-unknown > span {
            font-weight: 700;
            color: #111827;
        }
        .contact-name-unknown i {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.35;
            font-style: italic;
        }
        .modal-head-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .compact-select {
            width: auto;
            min-width: 190px;
            min-height: 42px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
        }
        .opportunity-state-cell {
            display: grid;
            gap: 6px;
            align-items: start;
        }
        .opportunity-status-pill {
            display: inline-flex;
            align-items: center;
            width: max-content;
            min-height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(107, 114, 128, .12);
            color: #4b5563;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .opportunity-status-pill.is-completed {
            background: rgba(22, 163, 74, .12);
            color: #15803d;
        }
        .opportunity-action-btn {
            min-height: 34px;
            white-space: nowrap;
        }
        .data-table .pill[data-open-attached-contacts],
        .data-table .pill[data-open-connected-contacts] { cursor: pointer; }
        @media (max-width: 1200px) {
            .campaign-dashboard-table .campaign-small-screen-hidden {
                display: none;
            }
        }
        .campaign-table-actions {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }
        .campaign-csv-btn {
            min-height: 40px;
            border-radius: 999px;
            padding: 0 14px;
            font-size: 13px;
            white-space: nowrap;
        }
        .ramp-up-info {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 10px;
            align-items: start;
            margin: 0 0 22px;
            padding: 16px 18px;
            border: 1px solid rgba(245, 158, 11, .28);
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(255, 251, 235, .96), rgba(254, 243, 199, .52));
            color: #78350f;
            box-shadow: 0 12px 30px rgba(120, 53, 15, .06);
        }
        .ramp-up-info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: rgba(245, 158, 11, .18);
            color: #b45309;
            font-size: 15px;
            font-weight: 800;
        }
        .ramp-up-info strong {
            display: block;
            color: #78350f;
            font-size: 13px;
            line-height: 1.25;
        }
        .ramp-up-info span {
            display: block;
            margin-top: 4px;
            color: #92400e;
            font-size: 12px;
            line-height: 1.45;
        }
        .ramp-up-info a {
            display: inline-flex;
            width: max-content;
            margin-top: 8px;
            color: #78350f;
            font-size: 12px;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .ramp-up-campaign-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .ramp-up-campaign-pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(245, 158, 11, .24);
            color: #78350f;
            font-size: 12px;
            font-weight: 800;
        }
        .status-toggle {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--panel);
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .status-toggle-label {
            font-size: 12px;
            color: var(--muted);
            margin-right: 4px;
            padding-left: 4px;
        }
        .status-toggle-btn {
            border: 0;
            background: transparent;
            color: var(--muted);
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s ease, color .2s ease, opacity .2s ease;
        }
        .status-toggle-btn.is-active {
            color: #fff;
        }
        .status-toggle-btn.is-active.is-on {
            background: #1f9d55;
        }
        .status-toggle-btn.is-active.is-off {
            background: #6b7280;
        }
        .status-toggle-btn[disabled] {
            opacity: .6;
            cursor: wait;
        }
        .campaign-health-card {
            margin-top: 12px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, .26);
            background: #f8fafc;
            max-width: 360px;
        }
        .campaign-health-card.is-ready {
            border-color: rgba(18, 183, 106, .28);
            background: rgba(18, 183, 106, .08);
        }
        .campaign-health-card.is-warning {
            border-color: rgba(245, 158, 11, .34);
            background: rgba(245, 158, 11, .10);
        }
        .campaign-health-card.is-off {
            border-color: rgba(107, 114, 128, .24);
            background: rgba(107, 114, 128, .08);
        }
        .campaign-health-card.is-timeslot {
            border-color: rgba(79, 70, 229, .26);
            background: rgba(79, 70, 229, .08);
        }
        .campaign-health-card.is-collecting {
            border-color: rgba(37, 99, 235, .26);
            background: rgba(37, 99, 235, .08);
        }
        .campaign-health-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }
        .campaign-health-label {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: #eef2ff;
            color: #3447c7;
        }
        .campaign-health-label.is-ready {background: rgba(18, 183, 106, .14);color: #027a48;}
        .campaign-health-label.is-warning {background: rgba(245, 158, 11, .18);color: #b45309;}
        .campaign-health-label.is-off {background: rgba(107, 114, 128, .16);color: #4b5563;}
        .campaign-health-label.is-timeslot {background: rgba(79, 70, 229, .14);color: #3447c7;}
        .campaign-health-label.is-collecting {background: rgba(37, 99, 235, .14);color: #1d4ed8;}
        .campaign-health-progress {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .campaign-health-bar {
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, .22);
            margin-bottom: 10px;
        }
        .campaign-health-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #4f46e5, #12b76a);
        }
        .campaign-health-copy {
            margin: 0 0 8px;
            color: #475467;
            font-size: 12px;
            line-height: 1.45;
        }
        .campaign-health-missing {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .campaign-health-missing span {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .76);
            border: 1px solid rgba(148, 163, 184, .24);
            color: #475467;
            font-size: 11px;
            font-weight: 700;
        }
        .campaign-health-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 8px;
            border-top: 1px solid rgba(148, 163, 184, .22);
        }
        .campaign-health-footnote {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
        }
        .campaign-health-link {
            display: inline-flex;
            flex: 0 0 auto;
            color: #3447c7;
            font-size: 12px;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .create-mode-picker {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 10px;
        }
        .create-mode-option {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 22px;
            padding: 22px;
            text-align: left;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
            min-height: 132px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
        }
        .create-mode-option:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }
        .create-mode-option strong {
            display: block;
            font-size: 18px;
            line-height: 1.3;
            color: #0f172a;
        }
        .create-mode-option span {
            display: block;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }
        .create-mode-option.is-active {
            border-color: var(--brand, #111827);
            box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.08);
        }
        .create-mode-selected {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
        }
        .create-mode-selected strong {
            display: block;
            font-size: 15px;
            color: #0f172a;
        }
        .create-mode-selected span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .campaign-create-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            padding: 12px 14px;
            border: 1px solid rgba(18, 183, 106, .22);
            border-radius: 16px;
            background: rgba(18, 183, 106, .08);
            color: #027a48;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.4;
        }
        .campaign-create-status[hidden] {
            display: none;
        }
        .campaign-create-spinner,
        .campaign-create-submit.is-loading::before {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            border-radius: 999px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            animation: campaignCreateSpin .8s linear infinite;
        }
        .campaign-create-submit.is-loading {
            opacity: 1;
            cursor: progress;
        }
        .campaign-create-submit.is-loading::before {
            content: '';
            color: #fff;
        }
        @keyframes campaignCreateSpin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 760px) {
            .create-mode-picker {
                grid-template-columns: 1fr;
            }
            .create-mode-selected {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        .duplicate-campaign-list {
            display: grid;
            gap: 12px;
            max-height: 420px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .duplicate-campaign-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .duplicate-campaign-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .duplicate-campaign-card.is-selected {
            border-color: var(--brand, #111827);
            box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.08);
        }
        .duplicate-campaign-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .duplicate-campaign-card h4 {
            margin: 0;
            font-size: 16px;
        }
        .duplicate-campaign-card p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .duplicate-campaign-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .duplicate-campaign-employees {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .duplicate-employee-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid var(--border);
            font-size: 12px;
        }
        .duplicate-campaign-radio {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--brand, #111827);
            pointer-events: none;
        }
        .playbook-install-list {
            display: grid;
            gap: 12px;
            max-height: 460px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .playbook-install-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .playbook-install-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .playbook-install-card.is-selected {
            border-color: var(--brand, #111827);
            box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.08);
        }
        .playbook-install-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }
        .playbook-install-card h4 {
            margin: 0 0 8px;
            font-size: 16px;
            color: #0f172a;
        }
        .playbook-install-card p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }
        .playbook-install-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }
        .playbook-install-note {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(79, 70, 229, .06);
            color: #344054;
            font-weight: 600;
        }
        .playbook-install-steps {
            display: none;
            gap: 10px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }
        .playbook-install-card.is-selected .playbook-install-steps {
            display: grid;
        }
        .playbook-install-step {
            display: grid;
            grid-template-columns: 108px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 10px;
            border: 1px solid rgba(148, 163, 184, .26);
            border-radius: 14px;
            background: #f8fafc;
        }
        .playbook-install-step img {
            width: 108px;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            object-position: top left;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, .28);
            background: #fff;
        }
        .playbook-install-step strong,
        .playbook-install-step span {
            display: block;
        }
        .playbook-install-step strong {
            color: #101828;
            font-size: 13px;
            line-height: 1.25;
        }
        .playbook-install-step span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
        }
        .playbook-install-radio {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--brand, #111827);
            pointer-events: none;
        }
        .lead-search-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .lead-search-note,
        .lead-url-preview {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(148, 163, 184, .10);
            border: 1px solid rgba(148, 163, 184, .22);
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }
        .lead-url-preview.is-valid {
            background: rgba(34, 197, 94, .10);
            border-color: rgba(34, 197, 94, .28);
            color: #166534;
            font-weight: 600;
        }
        .lead-url-preview.is-warning {
            background: rgba(245, 158, 11, .12);
            border-color: rgba(245, 158, 11, .30);
            color: #92400e;
            font-weight: 600;
        }
        .signal-picker {
            display: grid;
            gap: 10px;
            max-height: 280px;
            overflow: auto;
            padding: 2px;
        }
        .signal-option {
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            gap: 12px;
            align-items: flex-start;
            padding: 13px 14px;
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
        }
        .signal-option:hover {
            border-color: rgba(79, 70, 229, .30);
            background: rgba(79, 70, 229, .04);
        }
        .signal-option input {
            width: 20px;
            height: 20px;
            margin-top: 1px;
            accent-color: #4f46e5;
            cursor: pointer;
        }
        .signal-option strong,
        .signal-option small {
            display: block;
        }
        .signal-option strong {
            color: #101828;
            line-height: 1.25;
        }
        .signal-option small {
            margin-top: 3px;
            color: #667085;
            font-size: 12px;
            line-height: 1.35;
        }
        .signal-option-all {
            border-color: rgba(79, 70, 229, .25);
            background: rgba(79, 70, 229, .06);
        }
        .lead-search-progress {
            margin-top: 16px;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(34, 197, 94, .22);
            background: linear-gradient(135deg, rgba(34, 197, 94, .10), rgba(22, 163, 74, .07));
        }
        .lead-search-save-message {
            color: #166534;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
        }
        .employee-avatar {
            overflow: hidden;
            position: relative;
        }
        .employee-avatar img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }
        .employee-avatar img + span {
            display: none;
        }
        .employee-avatar.employee-avatar-fallback span {
            display: inline;
        }
        .campaign-goal-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .campaign-advanced-mode-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .campaign-goal-card{min-height:190px;padding:24px 20px;border:1px solid rgba(148,163,184,.32);border-radius:18px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;display:flex;flex-direction:column;gap:10px;transition:.18s ease}
        .campaign-goal-card:hover,.campaign-goal-card.is-active{border-color:#2563eb;box-shadow:0 12px 30px rgba(37,99,235,.12);transform:translateY(-2px)}
        .campaign-goal-card strong{font-size:18px}.campaign-goal-card>span:last-child{color:var(--muted);font-size:13px;line-height:1.55}
        .campaign-goal-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:#eff6ff;color:#2563eb;font-size:22px}
        .campaign-wizard-progress{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:22px}
        .campaign-wizard-progress span{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:#e2e8f0;color:#64748b;font-size:12px;font-weight:800;position:relative}
        .campaign-wizard-progress span.is-active{background:#2563eb;color:#fff}.campaign-wizard-progress span:not(:last-child):after{content:'';position:absolute;left:30px;width:10px;height:2px;background:#cbd5e1}
        .campaign-wizard-step[hidden]{display:none!important}.campaign-wizard-actions{display:flex;gap:10px;margin-left:auto}
        .campaign-workflow-preview{margin-top:18px;padding:16px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;flex-direction:column;gap:5px}
        .campaign-workflow-preview strong{color:#0f172a}.campaign-workflow-preview span{font-weight:650;color:#334155}.campaign-workflow-preview small{color:#64748b;line-height:1.45}
        @media (max-width: 760px) {
            .campaign-goal-grid{grid-template-columns:1fr}.campaign-goal-card{min-height:0}
            .lead-search-grid {
                grid-template-columns: 1fr;
            }
            .lead-search-step-head {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="screening-results.css?v=1">
    <style>
        .campaign-screening-score{display:inline-flex;flex-direction:column;align-items:flex-start;gap:4px;padding:8px 10px;border:1px solid #d7deea;border-radius:10px;background:#f7faff;color:#214bb8;cursor:pointer;white-space:nowrap;font:inherit}
        .campaign-screening-score:hover{border-color:#1f6fff;background:#eef4ff}.campaign-screening-score:focus-visible{outline:3px solid #1f6fff;outline-offset:2px}
        .campaign-screening-score strong{font-size:14px}.campaign-screening-score span{font-size:12px}
        .campaign-screening-score[data-decision="accepted"] span{color:#027a48}.campaign-screening-score[data-decision="declined"] span{color:#b42318}
        .campaign-screening-empty{color:var(--muted);font-size:13px;white-space:nowrap}
    </style>
    <script src="screening-results.js?v=1"></script>
    <script>
        (function(){
            const api = window.CohertaCampaignsApi;
            const STATIC_PLAYBOOKS = <?php echo json_encode($campaignPlaybooks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
            const CAMPAIGN_EMPLOYEES = <?php echo json_encode($campaignEmployees, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
            const advancedNotificationSignals = <?php echo json_encode($campaignAdvancedNotifications, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
            const DEFAULT_EMPLOYEE_IMAGE = '/app/assets/images/user/user.png';
            const CAMPAIGN_EMPLOYEE_IMAGES = CAMPAIGN_EMPLOYEES.reduce((map, employee) => {
                const image = String(employee && (employee.image || employee.profile_image || employee.avatar) || '').trim();
                if (employee && employee.id != null) map.id[String(employee.id)] = image;
                if (employee && employee.email) map.email[String(employee.email).toLowerCase()] = image;
                return map;
            }, {id: {}, email: {}});
            const createModal = document.getElementById('createCampaignModal');
            const addLeadsModal = document.getElementById('addLeadsModal');
            const attachedContactsModal = document.getElementById('attachedContactsModal');
            const titleInput = document.getElementById('campaignTitle');
            const createModeButtons = Array.from(document.querySelectorAll('[data-create-mode]'));
            const campaignGoalButtons = Array.from(document.querySelectorAll('[data-campaign-goal]'));
            const advancedCreateModeButtons = Array.from(document.querySelectorAll('[data-advanced-create-mode]'));
            const campaignWizardProgress = document.getElementById('campaignWizardProgress');
            const campaignWizardSubtitle = document.getElementById('campaignWizardSubtitle');
            const campaignWizardBackBtn = document.getElementById('campaignWizardBackBtn');
            const campaignEmployeeQuestion = document.getElementById('campaignEmployeeQuestion');
            const campaignDecisionMakerLabel = document.getElementById('campaignDecisionMakerLabel');
            const campaignDecisionMakerTitle = document.getElementById('campaignDecisionMakerTitle');
            const campaignAudienceCountry = document.getElementById('campaignAudienceCountry');
            const campaignQualificationField = document.getElementById('campaignQualificationField');
            const campaignLeadQualification = document.getElementById('campaignLeadQualification');
            const campaignAdvancedSourceField = document.getElementById('campaignAdvancedSourceField');
            const campaignAdvancedLeadSource = document.getElementById('campaignAdvancedLeadSource');
            const campaignWorkflowPreview = document.getElementById('campaignWorkflowPreview');
            const campaignTitleHelp = document.getElementById('campaignTitleHelp');
            const campaignWizardSteps = [document.getElementById('campaignWizardGoalStep'), document.getElementById('campaignWizardAdvancedModeStep'), document.getElementById('campaignWizardEmployeeStep'), document.getElementById('campaignWizardAudienceStep'), document.getElementById('campaignWizardDuplicateStep'), document.getElementById('campaignWizardTitleStep')];
            const createModePicker = document.getElementById('campaignCreateModePicker');
            const createModeSelected = document.getElementById('campaignCreateModeSelected');
            const createModeSelectedTitle = document.getElementById('campaignCreateModeSelectedTitle');
            const createModeSelectedText = document.getElementById('campaignCreateModeSelectedText');
            const changeCampaignCreateModeBtn = document.getElementById('changeCampaignCreateModeBtn');
            const saveCampaignBtn = document.getElementById('saveCampaignBtn');
            const saveCampaignBtnLabel = saveCampaignBtn ? saveCampaignBtn.querySelector('[data-create-button-text]') : null;
            const createCampaignStatus = document.getElementById('campaignCreateStatus');
            const createCampaignStatusText = document.getElementById('campaignCreateStatusText');
            const createCampaignControls = Array.from(createModal ? createModal.querySelectorAll('input, select, textarea, button') : []);
            const scratchSection = document.getElementById('campaignScratchSection');
            const duplicateSection = document.getElementById('campaignDuplicateSection');
            const playbookSection = document.getElementById('campaignPlaybookSection');
            const playbookList = document.getElementById('campaignPlaybookList');
            const duplicateCampaignSearch = document.getElementById('duplicateCampaignSearch');
            const duplicateCampaignList = document.getElementById('duplicateCampaignList');
            const sourceType = document.getElementById('leadSourceType');
            const csvSection = document.getElementById('leadSourceCsvSection');
            const manualSection = document.getElementById('leadSourceManualSection');
            const existingPolicySection = document.getElementById('leadSourceExistingPolicySection');
            const connectionsSection = document.getElementById('leadSourceConnectionsSection');
            const connectionsModeInput = document.getElementById('leadConnectionsMode');
            const pipelineSection = document.getElementById('leadSourcePipelineSection');
            const signalSection = document.getElementById('leadSourceSignalSection');
            const titleLocationSection = document.getElementById('leadSourceTitleLocationSection');
            const linkedinUrlSection = document.getElementById('leadSourceLinkedinUrlSection');
            const likePostsSection = document.getElementById('leadSourceLikePostsSection');
            const companyFollowersSection = document.getElementById('leadSourceCompanyFollowersSection');
            const leadSearchInputContent = document.getElementById('leadSearchInputContent');
            const leadSearchProgressSection = document.getElementById('leadSearchProgressSection');
            const leadEmployeeTitleInput = document.getElementById('leadEmployeeTitle');
            const leadTitleCountryInput = document.getElementById('leadTitleCountry');
            const leadLinkedinSearchUrlInput = document.getElementById('leadLinkedinSearchUrl');
            const leadLinkedinUrlPreview = document.getElementById('leadLinkedinUrlPreview');
            const leadLikePostsModeInput = document.getElementById('leadLikePostsMode');
            const leadLikePostsUserUrlField = document.getElementById('leadLikePostsUserUrlField');
            const leadLikePostsPostUrlField = document.getElementById('leadLikePostsPostUrlField');
            const leadLikePostsUserUrlInput = document.getElementById('leadLikePostsUserUrl');
            const leadLikePostsPostUrlInput = document.getElementById('leadLikePostsPostUrl');
            const leadLikePostsSummary = document.getElementById('leadLikePostsSummary');
            const leadCompanyFollowersUrlInput = document.getElementById('leadCompanyFollowersUrl');
            const leadSearchSaveMessage = document.getElementById('leadSearchSaveMessage');
            const saveAddLeadsBtn = document.getElementById('saveAddLeadsBtn');
            const csvFileInput = document.getElementById('leadCsvFile');
            const csvUrls = document.getElementById('leadCsvLinkedinUrls');
            const csvSummary = document.getElementById('leadCsvSummary');
            const manualUrls = document.getElementById('leadManualLinkedinUrls');
            const manualSummary = document.getElementById('leadManualSummary');
            const pipelineInput = document.getElementById('leadPipelineId');
            const signalInput = document.getElementById('leadSignalId');
            const signalAllInput = document.getElementById('leadSignalAll');
            const signalInputs = Array.from(document.querySelectorAll('input[name="lead_signal_ids[]"]'));
            const signalSelectionSummary = document.getElementById('leadSignalSelectionSummary');
            const campaignIdInput = document.getElementById('addLeadsCampaignId');
            const campaignTitleInput = document.getElementById('addLeadsCampaignTitle');
            const attachedContactsCampaignIdInput = document.getElementById('attachedContactsCampaignId');
            const attachedContactsTableHead = document.getElementById('attachedContactsTableHead');
            const attachedContactsTableBody = document.getElementById('attachedContactsTableBody');
            const attachedContactsModalTitle = document.getElementById('attachedContactsModalTitle');
            const attachedContactsModalSubtitle = document.getElementById('attachedContactsModalSubtitle');
            const attachedContactsOpportunityFilter = document.getElementById('attachedContactsOpportunityFilter');
            const campaignTableBody = document.getElementById('campaignTableBody');
            const campaignRampUpNotice = document.getElementById('campaignRampUpNotice');
            const downloadCampaignTableCsvBtn = document.getElementById('downloadCampaignTableCsvBtn');
            const dashboardToast = document.getElementById('dashboardToast');
            let campaigns = [];
            let campaignDashboardStats = null;
            let connectedContactsRefreshToken = 0;
            let campaignSettingsRefreshToken = 0;
            let attachedContactsCurrentList = [];
            let attachedContactsCurrentCampaignId = null;
            let attachedContactsRequestId = 0;
            let campaignCreateMode = '';
            let campaignWizardStep = 0;
            let advancedCreateMode = '';
            let selectedDuplicateCampaignId = null;
            let selectedPlaybookSlug = '';
            let isCreatingCampaign = false;
            const createModeMeta = {
                scratch: {
                    title: 'Create from scratch',
                    text: 'Start a completely new campaign with a title and selected employees.'
                },
                duplicate: {
                    title: 'Duplicate another campaign',
                    text: 'Use an existing campaign as your starting point and copy its setup.'
                },
                    playbook: {
                    title: 'Install Playbook',
                    text: 'Use a static playbook with workflow and either prepared Opportunities or automatic LinkedIn subjects.'
                }
            };
            const linkedInCountries = [
                {code:'AF', name:'Afghanistan'}, {code:'AL', name:'Albania'}, {code:'DZ', name:'Algeria'}, {code:'AD', name:'Andorra'},
                {code:'AO', name:'Angola'}, {code:'AR', name:'Argentina'}, {code:'AM', name:'Armenia'}, {code:'AU', name:'Australia', geoUrn:'101452733'},
                {code:'AT', name:'Austria'}, {code:'AZ', name:'Azerbaijan'}, {code:'BH', name:'Bahrain'}, {code:'BD', name:'Bangladesh'},
                {code:'BY', name:'Belarus'}, {code:'BE', name:'Belgium', geoUrn:'100565514'}, {code:'BZ', name:'Belize'}, {code:'BJ', name:'Benin'},
                {code:'BO', name:'Bolivia'}, {code:'BA', name:'Bosnia and Herzegovina'}, {code:'BW', name:'Botswana'}, {code:'BR', name:'Brazil', geoUrn:'106057199'},
                {code:'BG', name:'Bulgaria'}, {code:'BF', name:'Burkina Faso'}, {code:'KH', name:'Cambodia'}, {code:'CM', name:'Cameroon'},
                {code:'CA', name:'Canada', geoUrn:'101174742'}, {code:'CL', name:'Chile'}, {code:'CN', name:'China', geoUrn:'102890883'}, {code:'CO', name:'Colombia'},
                {code:'CR', name:'Costa Rica'}, {code:'HR', name:'Croatia'}, {code:'CY', name:'Cyprus'}, {code:'CZ', name:'Czech Republic'},
                {code:'DK', name:'Denmark', geoUrn:'104514075'}, {code:'DO', name:'Dominican Republic'}, {code:'EC', name:'Ecuador'}, {code:'EG', name:'Egypt'},
                {code:'EE', name:'Estonia'}, {code:'FI', name:'Finland'}, {code:'FR', name:'France', geoUrn:'105015875'}, {code:'GE', name:'Georgia'},
                {code:'DE', name:'Germany', geoUrn:'101282230'}, {code:'GH', name:'Ghana'}, {code:'GR', name:'Greece'}, {code:'GT', name:'Guatemala'},
                {code:'HK', name:'Hong Kong'}, {code:'HU', name:'Hungary'}, {code:'IS', name:'Iceland'}, {code:'IN', name:'India', geoUrn:'102713980'},
                {code:'ID', name:'Indonesia'}, {code:'IE', name:'Ireland'}, {code:'IL', name:'Israel'}, {code:'IT', name:'Italy', geoUrn:'103350119'},
                {code:'JP', name:'Japan', geoUrn:'101355337'}, {code:'JO', name:'Jordan'}, {code:'KE', name:'Kenya'}, {code:'KR', name:'South Korea', geoUrn:'105149562'},
                {code:'KW', name:'Kuwait'}, {code:'LV', name:'Latvia'}, {code:'LB', name:'Lebanon'}, {code:'LT', name:'Lithuania'},
                {code:'LU', name:'Luxembourg'}, {code:'MY', name:'Malaysia'}, {code:'MT', name:'Malta'}, {code:'MX', name:'Mexico', geoUrn:'103323778'},
                {code:'MA', name:'Morocco'}, {code:'NL', name:'Netherlands', geoUrn:'102890719'}, {code:'NZ', name:'New Zealand'}, {code:'NG', name:'Nigeria'},
                {code:'NO', name:'Norway'}, {code:'PK', name:'Pakistan'}, {code:'PA', name:'Panama'}, {code:'PE', name:'Peru'},
                {code:'PH', name:'Philippines'}, {code:'PL', name:'Poland'}, {code:'PT', name:'Portugal'}, {code:'QA', name:'Qatar'},
                {code:'RO', name:'Romania'}, {code:'RU', name:'Russia', geoUrn:'101728296'}, {code:'SA', name:'Saudi Arabia'}, {code:'RS', name:'Serbia'},
                {code:'SG', name:'Singapore', geoUrn:'102454443'}, {code:'SK', name:'Slovakia'}, {code:'SI', name:'Slovenia'}, {code:'ZA', name:'South Africa'},
                {code:'ES', name:'Spain', geoUrn:'105646813'}, {code:'LK', name:'Sri Lanka'}, {code:'SE', name:'Sweden', geoUrn:'105117694'}, {code:'CH', name:'Switzerland', geoUrn:'106693272'},
                {code:'TW', name:'Taiwan'}, {code:'TH', name:'Thailand'}, {code:'TN', name:'Tunisia'}, {code:'TR', name:'Turkey'},
                {code:'AE', name:'United Arab Emirates', geoUrn:'104305776'}, {code:'GB', name:'United Kingdom'}, {code:'US', name:'United States', geoUrn:'103644278'},
                {code:'UY', name:'Uruguay'}, {code:'VE', name:'Venezuela'}, {code:'VN', name:'Vietnam'}
            ];
            const linkedInGeoCountryByUrn = linkedInCountries.reduce((map, country) => {
                if (country.geoUrn) map[String(country.geoUrn)] = country;
                return map;
            }, {});
            const linkedInCountryByCode = linkedInCountries.reduce((map, country) => {
                map[String(country.code || '').toUpperCase()] = country;
                return map;
            }, {});
            function normalizeLeadSourceValue(campaign){
                let source = String(campaign && campaign.lead_source ? campaign.lead_source : '').trim().toLowerCase();
                if (!source) {
                    const linkedinUrl = String(campaign && (campaign.linkedin_url || campaign.linkedin_search_url || campaign.search_url) || '').trim();
                    if (String(campaign && (campaign.like_posts_mode || campaign.post_like_mode || campaign.linkedin_post_url || campaign.post_url || campaign.linkedin_user_url || campaign.user_url) || '').trim() || isLinkedinPostUrl(linkedinUrl) || isLinkedinActorPostsUrl(linkedinUrl)) {
                        source = 'like_of_posts';
                    } else if (linkedinUrl) {
                        source = 'linkedin_search_url';
                    } else if (String(campaign && (campaign.employee_title || campaign.keyword || campaign.keywords) || '').trim() || String(campaign && (campaign.country || campaign.country_name || campaign.country_code) || '').trim()) {
                        source = 'employee_title_location';
                    }
                }
                const aliases = {
                    lead_list: 'pipeline',
                    linkedin_url: 'linkedin_search_url',
                    linkedin_search: 'linkedin_search_url',
                    url_to_linkedin_search: 'linkedin_search_url',
                    manual_added: 'manual',
                    'manual added': 'manual',
                    manualadded: 'manual',
                    manual_list: 'manual',
                    employee_title_country: 'employee_title_location',
                    employee_title_location: 'employee_title_location',
                    title_location: 'employee_title_location',
                    like_posts: 'like_of_posts',
                    post_likes: 'like_of_posts',
                    linkedin_post_likes: 'like_of_posts',
                    linkedin_post_likers: 'like_of_posts',
                    like_of_posts: 'like_of_posts',
                    company_follower: 'company_followers',
                    linkedin_company_followers: 'company_followers',
                    not_accepted_connections: 'connections',
                    accepted_connections_48_hours: 'connections',
                    accepted_latest_48_hours: 'connections',
                    all_profile_visits: 'connections'
                };
                const normalized = aliases[source] || source;
                const sourceLinkedinUrl = String(campaign && (campaign.linkedin_url || campaign.linkedin_search_url || campaign.search_url) || '').trim();
                if (normalized === 'linkedin_search_url' && (isLinkedinPostUrl(sourceLinkedinUrl) || isLinkedinActorPostsUrl(sourceLinkedinUrl))) return 'like_of_posts';
                const supportedSources = ['csv', 'manual', 'pipeline', 'signal', 'employee_title_location', 'linkedin_search_url', 'like_of_posts', 'company_followers', 'connections'];
                if (normalized && !supportedSources.includes(normalized)) {
                    const linkedinUrl = String(campaign && (campaign.linkedin_url || campaign.linkedin_search_url || campaign.search_url) || '').trim();
                    if (String(campaign && (campaign.like_posts_mode || campaign.post_like_mode || campaign.linkedin_post_url || campaign.post_url || campaign.linkedin_user_url || campaign.user_url) || '').trim() || isLinkedinPostUrl(linkedinUrl) || isLinkedinActorPostsUrl(linkedinUrl)) return 'like_of_posts';
                    if (String(campaign && (campaign.linkedin_url || campaign.linkedin_search_url || campaign.search_url) || '').trim()) return 'linkedin_search_url';
                    if (String(campaign && (campaign.employee_title || campaign.keyword || campaign.keywords) || '').trim() || String(campaign && (campaign.country || campaign.country_name || campaign.country_code) || '').trim()) return 'employee_title_location';
                }
                return normalized;
            }
            function getCampaignSearchValues(campaign){
                return {
                    linkedinUrl: String(campaign && (campaign.linkedin_url || campaign.linkedin_search_url || campaign.search_url) || '').trim(),
                    employeeTitle: String(campaign && (campaign.employee_title || campaign.keyword || campaign.keywords) || '').trim(),
                    country: String(campaign && (campaign.country || campaign.country_name) || '').trim(),
                    countryCode: String(campaign && campaign.country_code || '').trim(),
                    geoUrn: String(campaign && (campaign.geo_urn || campaign.geoUrn) || '').trim()
                };
            }
            function normalizeLikePostsMode(value){
                const raw = String(value || '').trim().toLowerCase().replace(/[\s-]+/g, '_');
                const aliases = {
                    all: 'all_posts',
                    all_post: 'all_posts',
                    all_posts: 'all_posts',
                    own_posts: 'all_posts',
                    user: 'user_posts',
                    user_post: 'user_posts',
                    user_posts: 'user_posts',
                    other_user_posts: 'user_posts',
                    other_users_posts: 'user_posts',
                    other_users: 'user_posts',
                    specific: 'specific_post',
                    specific_posts: 'specific_post',
                    specific_post: 'specific_post',
                    post: 'specific_post'
                };
                return aliases[raw] || 'all_posts';
            }
            function getLikePostsModeLabel(mode){
                const labels = {
                    all_posts: 'All my own posts',
                    user_posts: "Other user's posts",
                    specific_post: 'Specific post'
                };
                return labels[normalizeLikePostsMode(mode)] || labels.all_posts;
            }
            function getCampaignLikePostsValues(campaign){
                const config = campaign && typeof campaign.lead_source_config === 'object' ? campaign.lead_source_config : {};
                const likePosts = campaign && typeof campaign.like_posts === 'object' ? campaign.like_posts : {};
                const sources = [campaign || {}, config || {}, likePosts || {}];
                const modeValue = firstFilledValue(sources, ['like_posts_mode', 'post_like_mode', 'posts_mode', 'mode', 'source_mode']);
                let userUrl = String(firstFilledValue(sources, ['linkedin_user_url', 'user_url', 'profile_url', 'author_url', 'poster_url']) || '').trim();
                let postUrl = String(firstFilledValue(sources, ['linkedin_post_url', 'post_url', 'specific_post_url', 'activity_url']) || '').trim();
                const legacyLinkedinUrl = String(firstFilledValue(sources, ['linkedin_url']) || '').trim();
                if (!userUrl && isLinkedinActorPostsUrl(legacyLinkedinUrl)) {
                    userUrl = legacyLinkedinUrl;
                }
                if (!postUrl && isLinkedinPostUrl(legacyLinkedinUrl)) {
                    postUrl = legacyLinkedinUrl;
                }
                return {
                    mode: modeValue ? normalizeLikePostsMode(modeValue) : (postUrl ? 'specific_post' : (userUrl ? 'user_posts' : 'all_posts')),
                    userUrl,
                    postUrl
                };
            }
            function getCampaignCompanyFollowersUrl(campaign){
                const config = campaign && typeof campaign.lead_source_config === 'object' ? campaign.lead_source_config : {};
                return String(firstFilledValue([campaign || {}, config || {}], [
                    'company_linkedin_url', 'linkedin_company_url', 'company_url', 'linkedin_url'
                ]) || '').trim();
            }
            let leadSearchState = {
                isActive: false,
                searchId: null,
                payload: null
            };
            function esc(value){
                return String(value ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
            }
            function extractLinkedinProfileUrls(text){
                const matches = Array.from(String(text || '').matchAll(/(?:https?:\/\/)?(?:www\.)?linkedin\.com\/(?:in|pub)\/[^\s,;"'<>?#]+/gi)).map(item => {
                    const url = /^https?:\/\//i.test(item[0]) ? item[0] : 'https://' + item[0];
                    try {
                        return new URL(url).toString();
                    } catch (error) {
                        return null;
                    }
                }).filter(Boolean);
                return [...new Set(matches)];
            }
            function getLeadTextareaUrls(text){
                const extracted = extractLinkedinProfileUrls(text);
                if (extracted.length) return extracted;
                return [...new Set(String(text || '').split(/[\r\n,;\s]+/).map(value => value.trim()).filter(value => /^(?:https?:\/\/)?(?:www\.)?linkedin\.com\/(?:in|pub)\//i.test(value)).map(value => /^https?:\/\//i.test(value) ? value : 'https://' + value))];
            }
            function normalizeLinkedinUrl(rawUrl, emptyMessage){
                const value = String(rawUrl || '').trim();
                if (!value) return {error: emptyMessage || 'Enter a LinkedIn URL.'};
                try {
                    const parsed = new URL(value);
                    const host = String(parsed.hostname || '').toLowerCase().replace(/^www\./, '');
                    if (host !== 'linkedin.com' && !host.endsWith('.linkedin.com')) {
                        return {error: 'Enter a LinkedIn URL.'};
                    }
                    return {url: parsed.toString()};
                } catch (error) {
                    return {error: 'Enter a valid LinkedIn URL.'};
                }
            }
            function initials(name){ return String(name||'').split(/\s+/).filter(Boolean).map(p => p[0]).join('').slice(0,2).toUpperCase(); }
            const signalById = advancedNotificationSignals.reduce((map, signal) => {
                if (signal && signal.id != null) map[String(signal.id)] = signal;
                return map;
            }, {});
            function normalizeSignalIdList(value){
                if (value === undefined || value === null || value === '') return [];
                if (Array.isArray(value)) {
                    return [...new Set(value.flatMap(item => normalizeSignalIdList(item)).map(String))].map(Number).filter(id => Number.isFinite(id) && id > 0);
                }
                if (typeof value === 'object') {
                    if (Object.prototype.hasOwnProperty.call(value, 'id')) return normalizeSignalIdList(value.id);
                    if (Array.isArray(value.ids)) return normalizeSignalIdList(value.ids);
                    if (Array.isArray(value.signal_ids)) return normalizeSignalIdList(value.signal_ids);
                    return [];
                }
                const raw = String(value).trim();
                if (!raw) return [];
                if (raw[0] === '[') {
                    try { return normalizeSignalIdList(JSON.parse(raw)); } catch (error) {}
                }
                return [...new Set(raw.split(/[,\s;]+/).map(item => Number(item)).filter(id => Number.isFinite(id) && id > 0).map(String))].map(Number);
            }
            function getCampaignSignalIds(campaign, fallbackSourceId = ''){
                const candidates = [
                    campaign && campaign.lead_source_signal_ids,
                    campaign && campaign.signal_ids,
                    campaign && campaign.signals,
                    campaign && campaign.lead_source_ids,
                    fallbackSourceId
                ];
                for (const candidate of candidates) {
                    const ids = normalizeSignalIdList(candidate);
                    if (ids.length) return ids;
                }
                return [];
            }
            function getSignalLabel(signalId){
                const signal = signalById[String(signalId)];
                const subject = String(signal && signal.subject || '').trim();
                return subject || ('Signal #' + signalId);
            }
            function formatSignalSelection(signalIds, emptyLabel = 'No signal selected'){
                const ids = normalizeSignalIdList(signalIds);
                if (!ids.length) return emptyLabel;
                if (signalInputs.length && ids.length === signalInputs.length) {
                    return 'All signals (' + ids.length + ')';
                }
                const labels = ids.map(getSignalLabel);
                return labels.slice(0, 3).join(', ') + (labels.length > 3 ? ' +' + (labels.length - 3) + ' more' : '');
            }
            function getSelectedSignalIds(){
                return signalInputs
                    .filter(input => input.checked)
                    .map(input => Number(input.value))
                    .filter(id => Number.isFinite(id) && id > 0);
            }
            function updateSignalSelectionSummary(){
                if (!signalSelectionSummary) return;
                const selectedIds = getSelectedSignalIds();
                if (!signalInputs.length) {
                    signalSelectionSummary.textContent = 'No saved signals available.';
                    return;
                }
                signalSelectionSummary.textContent = selectedIds.length
                    ? 'Selected: ' + formatSignalSelection(selectedIds)
                    : 'Choose one or more Advanced Notifications to listen to.';
            }
            function syncSignalSelectionState(){
                const selectedIds = getSelectedSignalIds();
                if (signalAllInput) {
                    signalAllInput.checked = signalInputs.length > 0 && selectedIds.length === signalInputs.length;
                    signalAllInput.indeterminate = selectedIds.length > 0 && selectedIds.length < signalInputs.length;
                }
                if (signalInput) {
                    signalInput.value = selectedIds.length === 1 ? String(selectedIds[0]) : '';
                }
                updateSignalSelectionSummary();
                updateAddLeadsButtonState();
            }
            function setSelectedSignalIds(signalIds){
                const selectedSet = new Set(normalizeSignalIdList(signalIds).map(String));
                signalInputs.forEach(input => {
                    input.checked = selectedSet.has(String(input.value));
                });
                syncSignalSelectionState();
            }
            function employeeImageUrl(employee){
                if (!employee || typeof employee !== 'object') return '';
                const direct = String(employee.image || employee.profile_image || employee.avatar || employee.avatar_url || '').trim();
                const mapped = employee.id != null ? CAMPAIGN_EMPLOYEE_IMAGES.id[String(employee.id)] : '';
                const emailMapped = employee.email ? CAMPAIGN_EMPLOYEE_IMAGES.email[String(employee.email).toLowerCase()] : '';
                const image = direct || mapped || emailMapped || '';
                return image && image !== DEFAULT_EMPLOYEE_IMAGE ? image : '';
            }
            function employeeAvatarHtml(employee, options = {}){
                const name = String(employee && (employee.full_name || employee.email) || 'Unknown employee');
                const image = employeeImageUrl(employee);
                const extraClass = options.className ? ' ' + options.className : '';
                const fallback = esc(initials(name) || '?');
                const imageHtml = image ? `<img src="${esc(image)}" alt="${esc(name)}" loading="lazy" onerror="this.remove();this.parentElement.classList.add('employee-avatar-fallback');">` : '';
                return `<span class="employee-avatar${extraClass}${image ? '' : ' employee-avatar-fallback'}" title="${esc(name)}">${imageHtml}<span>${fallback}</span></span>`;
            }
            function firstFilledValue(sources, keys){
                for (const source of sources) {
                    if (!source || typeof source !== 'object') continue;
                    for (const key of keys) {
                        const value = source[key];
                        if (value !== undefined && value !== null && String(value).trim() !== '') return value;
                    }
                }
                return '';
            }
            function isTruthyRampValue(value){
                if (value === true) return true;
                if (typeof value === 'number') return value > 0;
                const normalized = String(value ?? '').trim().toLowerCase();
                return ['1', 'true', 'yes', 'active', 'ongoing', 'in_progress', 'in progress', 'running'].includes(normalized);
            }
            function parseRampDate(value, endOfDay = false){
                if (!value) return null;
                const date = new Date(value);
                if (endOfDay && /^\d{4}-\d{2}-\d{2}$/.test(String(value).trim())) {
                    date.setHours(23, 59, 59, 999);
                }
                return Number.isNaN(date.getTime()) ? null : date;
            }
            function getRampUpInfo(campaign){
                if (!campaign || typeof campaign !== 'object') return null;
                const ramp = campaign.ramp_up || campaign.rampUp || campaign.rampup || campaign.warm_up || campaign.warmUp || {};
                const sources = [ramp, campaign];
                const explicitActive = firstFilledValue(sources, [
                    'is_active',
                    'active',
                    'ongoing',
                    'is_ongoing',
                    'in_progress',
                    'is_in_progress',
                    'enabled'
                ]);
                const status = firstFilledValue(sources, ['status', 'state', 'ramp_up_status', 'rampUpStatus', 'warm_up_status']);
                const level = firstFilledValue(sources, [
                    'level',
                    'current_level',
                    'currentLevel',
                    'ramp_up_level',
                    'rampUpLevel',
                    'warm_up_level'
                ]);
                const maxLevel = firstFilledValue(sources, ['max_level', 'maxLevel', 'total_levels', 'totalLevels', 'levels']);
                const startsAt = parseRampDate(firstFilledValue(sources, ['starts_at', 'start_at', 'start_date', 'ramp_up_starts_at', 'ramp_up_start_date']));
                const endsAt = parseRampDate(firstFilledValue(sources, ['ends_at', 'end_at', 'end_date', 'ramp_up_ends_at', 'ramp_up_end_date']), true);
                const now = new Date();
                const isWithinPeriod = !!endsAt && endsAt >= now && (!startsAt || startsAt <= now);
                const isFinished = ['complete', 'completed', 'finished', 'done', 'ended', 'inactive'].includes(String(status || '').trim().toLowerCase());
                const isActive = !isFinished && (isTruthyRampValue(explicitActive) || isTruthyRampValue(status) || isWithinPeriod);
                if (!isActive) return null;
                return {level, maxLevel};
            }
            function isActiveCampaign(campaign){
                return Number(campaign && campaign.status) === 0;
            }
            function formatRampUpLevel(info){
                if (!info || !info.level) return 'Ramp-up active';
                return 'Level ' + esc(info.level) + (info.maxLevel ? ' of ' + esc(info.maxLevel) : '');
            }
            function renderCampaignRampUpNotice(campaignList){
                if (!campaignRampUpNotice) return;
                const rampUpCampaigns = (Array.isArray(campaignList) ? campaignList : [])
                    .filter(campaign => isActiveCampaign(campaign))
                    .map(campaign => ({campaign, info: getRampUpInfo(campaign)}))
                    .filter(item => item.info);

                if (!rampUpCampaigns.length) {
                    campaignRampUpNotice.innerHTML = '';
                    return;
                }

                const campaignLabel = rampUpCampaigns.length === 1 ? '1 active campaign is' : rampUpCampaigns.length + ' active campaigns are';
                const levelPills = rampUpCampaigns.map(item => {
                    const title = String(item.campaign && item.campaign.title || 'Campaign').trim();
                    return `<span class="ramp-up-campaign-pill">${esc(title)}: ${formatRampUpLevel(item.info)}</span>`;
                }).join('');
                campaignRampUpNotice.innerHTML = `<div class="ramp-up-info" role="note" aria-label="Ramp-up information">
                    <span class="ramp-up-info-icon" aria-hidden="true">i</span>
                    <div>
                        <strong>Ramp-up period is ongoing</strong>
                        <span>${campaignLabel} currently in ramp-up. These campaigns are deliberately paced, so do not expect full speed yet.</span>
                        <div class="ramp-up-campaign-list">${levelPills}</div>
                        <a href="/uk/campaign/ramp-up" target="_blank" rel="noopener">Read more about Ramp-up periods</a>
                    </div>
                </div>`;
            }
            function showToast(message){ dashboardToast.textContent = message; dashboardToast.classList.remove('show'); requestAnimationFrame(()=>dashboardToast.classList.add('show')); }
            function openModal(modal){ if(modal) modal.classList.add('show'); }
            function closeModal(modal){
                if (modal === createModal && isCreatingCampaign) return;
                if (modal === attachedContactsModal) attachedContactsRequestId++;
                if (modal === addLeadsModal) {
                    clearLeadSearchTimers();
                }
                if(modal) modal.classList.remove('show');
                if (modal === addLeadsModal && new URLSearchParams(window.location.search || '').get('embedded') === '1' && window.parent !== window) {
                    window.parent.postMessage({type:'coherta-lead-modal-close'}, window.location.origin);
                }
            }
            function populateCountrySelect(){
                const options = '<option value="">Select a country</option>' + linkedInCountries.map(country => {
                    const geo = country.geoUrn ? ' data-geourn="' + esc(country.geoUrn) + '"' : '';
                    return '<option value="' + esc(country.code) + '" data-country="' + esc(country.name) + '"' + geo + '>' + esc(country.name) + '</option>';
                }).join('');
                [leadTitleCountryInput, campaignAudienceCountry].forEach(select => {
                    if (!select || select.dataset.loaded === '1') return;
                    select.innerHTML = options;
                    select.dataset.loaded = '1';
                });
            }
            function isLeadSearchSource(value){
                return value === 'employee_title_location' || value === 'linkedin_search_url' || value === 'like_of_posts' || value === 'company_followers';
            }
            function setLeadSearchFocusMode(isFocused){
                if (leadSearchInputContent) leadSearchInputContent.style.display = '';
            }
            function syncSourceSections(){
                const value = sourceType.value;
                if (csvSection) csvSection.style.display = value === 'csv' ? '' : 'none';
                if (manualSection) manualSection.style.display = value === 'manual' ? '' : 'none';
                if (existingPolicySection) existingPolicySection.style.display = (value === 'csv' || value === 'manual') ? '' : 'none';
                if (connectionsSection) connectionsSection.style.display = value === 'connections' ? '' : 'none';
                if (pipelineSection) pipelineSection.style.display = value === 'pipeline' ? '' : 'none';
                if (signalSection) signalSection.style.display = value === 'signal' ? '' : 'none';
                if (titleLocationSection) titleLocationSection.style.display = value === 'employee_title_location' ? '' : 'none';
                if (linkedinUrlSection) linkedinUrlSection.style.display = value === 'linkedin_search_url' ? '' : 'none';
                if (likePostsSection) likePostsSection.style.display = value === 'like_of_posts' ? '' : 'none';
                if (companyFollowersSection) companyFollowersSection.style.display = value === 'company_followers' ? '' : 'none';
                if (leadSearchProgressSection && !leadSearchState.isActive) {
                    leadSearchProgressSection.style.display = 'none';
                }
                syncLikePostsFields();
                setLeadSearchFocusMode(false);
                updateAddLeadsButtonState();
            }
            function clearLeadSearchTimers(){
                leadSearchState.isActive = false;
            }
            function resetLeadSearchState(){
                clearLeadSearchTimers();
                leadSearchState = {
                    isActive: false,
                    searchId: null,
                    payload: null
                };
                if (leadSearchProgressSection) leadSearchProgressSection.style.display = 'none';
                if (leadSearchSaveMessage) leadSearchSaveMessage.style.display = '';
                setLeadSearchFocusMode(false);
                updateAddLeadsButtonState();
            }
            function updateAddLeadsButtonState(){
                if (!saveAddLeadsBtn || !sourceType) return;
                const type = sourceType.value;
                saveAddLeadsBtn.disabled = false;
                if (type === 'pipeline') {
                    saveAddLeadsBtn.textContent = 'Add leads';
                    saveAddLeadsBtn.disabled = !getSelectedLeadListId();
                    return;
                }
                if (type === 'signal') {
                    saveAddLeadsBtn.textContent = 'Add signals';
                    saveAddLeadsBtn.disabled = !signalInputs.length || getSelectedSignalIds().length === 0;
                    return;
                }
                if (isLeadSearchSource(type)) {
                    if (leadSearchState.isActive) {
                        saveAddLeadsBtn.textContent = 'Saving search...';
                        saveAddLeadsBtn.disabled = true;
                    } else {
                        saveAddLeadsBtn.textContent = 'Start search';
                        if (type === 'linkedin_search_url') {
                            const parsed = parseLinkedinSearchUrl(leadLinkedinSearchUrlInput?.value || '');
                            saveAddLeadsBtn.disabled = !!(parsed && !parsed.error && !parsed.keywords && !parsed.is_sales_search);
                        }
                        if (type === 'like_of_posts') {
                            const mode = normalizeLikePostsMode(leadLikePostsModeInput?.value || 'all_posts');
                            if (mode === 'user_posts') {
                                saveAddLeadsBtn.disabled = !String(leadLikePostsUserUrlInput?.value || '').trim();
                            }
                            if (mode === 'specific_post') {
                                saveAddLeadsBtn.disabled = !String(leadLikePostsPostUrlInput?.value || '').trim();
                            }
                        }
                        if (type === 'company_followers') {
                            saveAddLeadsBtn.disabled = !String(leadCompanyFollowersUrlInput?.value || '').trim();
                        }
                    }
                    return;
                }
                saveAddLeadsBtn.textContent = 'Add leads';
            }
            function syncLikePostsFields(){
                if (!leadLikePostsModeInput) return;
                const mode = normalizeLikePostsMode(leadLikePostsModeInput.value);
                leadLikePostsModeInput.value = mode;
                if (leadLikePostsUserUrlField) leadLikePostsUserUrlField.style.display = mode === 'user_posts' ? '' : 'none';
                if (leadLikePostsPostUrlField) leadLikePostsPostUrlField.style.display = mode === 'specific_post' ? '' : 'none';
                if (leadLikePostsSummary) {
                    if (mode === 'user_posts') {
                        leadLikePostsSummary.textContent = 'Collects people who liked posts from the selected LinkedIn user or company.';
                    } else if (mode === 'specific_post') {
                        leadLikePostsSummary.textContent = 'Collects people who liked the selected LinkedIn post.';
                    } else {
                        leadLikePostsSummary.textContent = 'Collects people who liked posts from your connected LinkedIn accounts.';
                    }
                }
                updateAddLeadsButtonState();
            }
            const leadSourceSavedSummary = document.getElementById('leadSourceSavedSummary');
            function normalizeConnectionsMode(value){
                const normalized = String(value || '').trim().toLowerCase().replace(/[\s-]+/g, '_');
                const aliases = {
                    not_accepted_connections: 'not_yet_accepted',
                    accepted_connections_48_hours: 'accepted_latest_48_hours'
                };
                return aliases[normalized] || normalized;
            }
            function getCampaignConnectionsMode(campaign, source){
                const config = campaign && campaign.lead_source_config && typeof campaign.lead_source_config === 'object'
                    ? campaign.lead_source_config
                    : {};
                const explicitMode = campaign && (campaign.connections_mode || campaign.connection_mode)
                    || config.connections_mode
                    || config.connection_mode
                    || '';
                if (String(explicitMode).trim()) return normalizeConnectionsMode(explicitMode);

                const rawSource = String(campaign && campaign.lead_source || '').trim().toLowerCase();
                if (rawSource === 'not_accepted_connections') return 'not_yet_accepted';
                if (rawSource === 'accepted_connections_48_hours' || rawSource === 'accepted_latest_48_hours') return 'accepted_latest_48_hours';
                if (rawSource === 'all_profile_visits') return 'all_profile_visits';
                return source === 'connections' ? 'not_yet_accepted' : '';
            }
            function getConnectionsModeLabel(value){
                const labels = {
                    not_yet_accepted: 'Not yet accepted',
                    accepted_latest_48_hours: 'Accepted within latest 48 hours',
                    all_profile_visits: 'All Profile Visits'
                };
                const normalized = normalizeConnectionsMode(value);
                return labels[normalized] || normalized;
            }
            function getSelectedLeadListId(){
                const id = Number(pipelineInput?.value || 0);
                return Number.isSafeInteger(id) && id > 0 ? id : null;
            }
            function getLeadListTitle(sourceId){
                const option = Array.from(pipelineInput?.options || []).find(item => item.value === String(sourceId));
                return option && Number(option.value) > 0 ? option.textContent.trim() : '';
            }
            function getLeadSourceMeta(campaign){
                const source = normalizeLeadSourceValue(campaign);
                const sourceId = campaign && campaign.lead_source_id != null && String(campaign.lead_source_id).trim() !== ''
                    ? String(campaign.lead_source_id).trim()
                    : '';
                const searchValues = getCampaignSearchValues(campaign);
                const signalIds = source === 'signal' ? getCampaignSignalIds(campaign, sourceId) : [];
                const sourceMap = {
                    csv: 'CSV',
                    manual: 'Manual Added',
                    pipeline: 'Lead list',
                    signal: 'Signal',
                    employee_title_location: 'Employee Title + Location',
                    linkedin_search_url: 'LinkedIn Search URL',
                    like_of_posts: 'Like of Posts',
                    company_followers: 'Company Followers',
                    connections: 'Connections'
                };
                return {
                    source,
                    sourceId,
                    label: sourceMap[source] || source,
                    searchValues,
                    likePostsValues: source === 'like_of_posts' ? getCampaignLikePostsValues(campaign) : null,
                    companyFollowersUrl: source === 'company_followers' ? getCampaignCompanyFollowersUrl(campaign) : '',
                    signalIds,
                    connectionsMode: getCampaignConnectionsMode(campaign, source)
                };
            }
            function updateLeadSourceSummary(campaign){
                const meta = getLeadSourceMeta(campaign);
                if (!meta.source && !meta.sourceId) {
                    leadSourceSavedSummary.textContent = 'No lead source selected yet.';
                    return;
                }
                const details = [];
                if (meta.source === 'pipeline' && getLeadListTitle(meta.sourceId)) {
                    details.push(getLeadListTitle(meta.sourceId));
                }
                if (meta.source === 'employee_title_location') {
                    if (meta.searchValues.employeeTitle) details.push(meta.searchValues.employeeTitle);
                    if (meta.searchValues.country) details.push(meta.searchValues.country);
                }
                if (meta.source === 'linkedin_search_url') {
                    if (meta.searchValues.employeeTitle) details.push(meta.searchValues.employeeTitle);
                    if (meta.searchValues.country) details.push(meta.searchValues.country);
                    if (!details.length && meta.searchValues.linkedinUrl) details.push('URL saved');
                }
                if (meta.source === 'connections') {
                    details.push(getConnectionsModeLabel(meta.connectionsMode));
                }
                if (meta.source === 'signal') {
                    details.push(formatSignalSelection(meta.signalIds));
                }
                if (meta.source === 'like_of_posts') {
                    const values = meta.likePostsValues || {};
                    details.push(getLikePostsModeLabel(values.mode));
                    if (values.mode === 'user_posts' && values.userUrl) details.push('User URL saved');
                    if (values.mode === 'specific_post' && values.postUrl) details.push('Post URL saved');
                }
                if (meta.source === 'company_followers' && meta.companyFollowersUrl) details.push('Company URL saved');
                const detailText = details.length ? ' · ' + details.join(' · ') : '';
                const sourceIdText = meta.source === 'signal' ? '' : (meta.sourceId ? ' · ID: ' + meta.sourceId : '');
                leadSourceSavedSummary.textContent = 'Current setting: ' + meta.label + detailText + sourceIdText;
            }
            function getCreateCampaignLoadingText(mode){
                if (mode === 'duplicate') return 'Duplicating campaign...';
                if (mode === 'playbook') return 'Installing Playbook...';
                return 'Creating campaign...';
            }
            function setCreateCampaignLoading(isLoading, message){
                isCreatingCampaign = !!isLoading;
                if (createModal) createModal.setAttribute('aria-busy', isLoading ? 'true' : 'false');
                if (createCampaignStatus) createCampaignStatus.hidden = !isLoading;
                if (createCampaignStatusText) createCampaignStatusText.textContent = message || 'Creating campaign...';
                if (saveCampaignBtn) {
                    saveCampaignBtn.classList.toggle('is-loading', isLoading);
                    saveCampaignBtn.disabled = isLoading;
                    saveCampaignBtn.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
                }
                if (saveCampaignBtnLabel) saveCampaignBtnLabel.textContent = isLoading ? (message || 'Creating campaign...') : 'Create Campaign';
                createCampaignControls.forEach(control => {
                    if (!control) return;
                    if (isLoading) {
                        control.dataset.createWasDisabled = control.disabled ? '1' : '0';
                        control.disabled = true;
                    } else {
                        if (control.dataset.createWasDisabled !== '1') control.disabled = false;
                        delete control.dataset.createWasDisabled;
                    }
                });
            }
            function resetCreateCampaignModal(){
                setCreateCampaignLoading(false);
                campaignCreateMode = '';
                campaignWizardStep = 0;
                advancedCreateMode = '';
                selectedDuplicateCampaignId = null;
                selectedPlaybookSlug = '';
                titleInput.value = '';
                if (duplicateCampaignSearch) duplicateCampaignSearch.value = '';
                if (campaignDecisionMakerTitle) campaignDecisionMakerTitle.value = '';
                if (campaignAudienceCountry) campaignAudienceCountry.value = '';
                if (campaignLeadQualification) campaignLeadQualification.value = '';
                if (campaignAdvancedLeadSource) campaignAdvancedLeadSource.value = '';
                document.querySelectorAll('input[name="campaign_employees[]"]').forEach(el => { el.checked = false; });
                syncCreateModeUI();
                renderDuplicateCampaigns();
                renderPlaybooks();
            }
            function setCreateMode(mode){
                campaignCreateMode = ['connections', 'sales', 'advanced'].includes(mode) ? mode : '';
                advancedCreateMode = '';
                campaignWizardStep = campaignCreateMode === 'advanced' ? 1 : (campaignCreateMode ? 2 : 0);
                syncCreateModeUI();
            }
            function generateCampaignWizardTitle(){
                if (!titleInput || !campaignCreateMode) return '';
                if (campaignCreateMode === 'advanced' && advancedCreateMode === 'duplicate') {
                    const sourceCampaign = campaigns.find(item => Number(item.id) === Number(selectedDuplicateCampaignId));
                    titleInput.value = 'Copy of ' + String(sourceCampaign?.title || 'Campaign');
                    return titleInput.value;
                }
                const selectedEmployee = document.querySelector('input[name="campaign_employees[]"]:checked');
                const employeeName = String(selectedEmployee?.closest('.checkbox-card')?.querySelector('strong')?.textContent || '').trim();
                const typeLabels = {connections:'More Connections', sales:'More Sales', advanced:'Advanced Setup'};
                const parts = [typeLabels[campaignCreateMode] || 'Campaign', employeeName];
                if (campaignCreateMode !== 'advanced') {
                    const countryOption = campaignAudienceCountry?.options[campaignAudienceCountry.selectedIndex];
                    parts.push(String(campaignDecisionMakerTitle?.value || '').trim(), String(countryOption?.textContent || '').trim());
                }
                titleInput.value = parts.filter(Boolean).join(' - ');
                return titleInput.value;
            }
            function syncCreateModeUI(){
                campaignGoalButtons.forEach(button => {
                    const isActive = button.dataset.campaignGoal === campaignCreateMode;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
                advancedCreateModeButtons.forEach(button => {
                    const isActive = button.dataset.advancedCreateMode === advancedCreateMode;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
                campaignWizardSteps.forEach((step, index) => { if (step) step.hidden = index !== campaignWizardStep; });
                if (campaignQualificationField) campaignQualificationField.hidden = campaignCreateMode !== 'sales';
                if (campaignAdvancedSourceField) campaignAdvancedSourceField.hidden = true;
                if (campaignDecisionMakerTitle) campaignDecisionMakerTitle.closest('.field').hidden = campaignCreateMode === 'advanced';
                if (campaignAudienceCountry) campaignAudienceCountry.closest('.field').hidden = campaignCreateMode === 'advanced';
                if (campaignEmployeeQuestion) campaignEmployeeQuestion.textContent = campaignCreateMode === 'connections' ? 'Which employee needs more connections?' : 'Which employee should we get more sales leads for?';
                if (campaignDecisionMakerLabel) campaignDecisionMakerLabel.textContent = campaignCreateMode === 'connections' ? 'Which job title do you want to connect with?' : 'Job title of the decision maker';
                const subtitles = ['What do you want to achieve?', 'How do you want to set up the campaign?', 'Choose the employee this campaign will run for.', 'Define the people you want to reach.', 'Choose the campaign you want to duplicate.', 'Review the campaign title.'];
                if (campaignWizardSubtitle) campaignWizardSubtitle.textContent = subtitles[campaignWizardStep] || subtitles[0];
                if (campaignWizardBackBtn) campaignWizardBackBtn.hidden = campaignWizardStep === 0;
                if (saveCampaignBtnLabel && !isCreatingCampaign) saveCampaignBtnLabel.textContent = campaignWizardStep === 5 ? (advancedCreateMode === 'duplicate' ? 'Duplicate Campaign' : 'Create Campaign') : 'Continue';
                if (campaignTitleHelp) campaignTitleHelp.textContent = advancedCreateMode === 'duplicate' ? 'A suggested title is generated from the campaign being duplicated. You can change it.' : (campaignCreateMode === 'advanced' ? 'A suggested title is generated from the campaign type and employee. You can change it.' : 'A suggested title is generated from the campaign details. You can change it before creating the campaign.');
                if (campaignWizardProgress) {
                    const route = campaignCreateMode === 'advanced' ? (advancedCreateMode === 'duplicate' ? [0,1,4,5] : [0,1,2,5]) : [0,2,3,5];
                    const total = route.length;
                    const visibleStep = Math.max(0, route.indexOf(campaignWizardStep));
                    campaignWizardProgress.innerHTML = Array.from({length: total}, (_, index) => `<span class="${index <= visibleStep ? 'is-active' : ''}">${index + 1}</span>`).join('');
                }
                if (campaignWorkflowPreview && campaignWizardStep === 5) {
                    generateCampaignWizardTitle();
                    const previews = {
                        connections: '<strong>Workflow created automatically</strong><span>Connect → If not accepted → Wait 35 days → Delete Connection Request</span><small>No connection message or follow-up message will be sent.</small>',
                        sales: '<strong>Workflow created automatically</strong><span>Automated Screening → Connect → accepted/not accepted paths</span><small>Accepted: wait 1 day, then send “Thank you for accepting my connection” in the opportunity’s language. Not accepted: wait 35 days, then delete the connection request.</small>',
                        advanced: '<strong>No workflow will be created</strong><span>You can build the campaign workflow after creation.</span>'
                    };
                    campaignWorkflowPreview.innerHTML = previews[campaignCreateMode] || '';
                }
            }
            function parseWorkflowNodes(workflow){
                if (!workflow) return [];
                if (typeof workflow === 'string') {
                    try {
                        workflow = JSON.parse(workflow);
                    } catch (error) {
                        return [];
                    }
                }
                if (!workflow || !Array.isArray(workflow.nodes)) return [];
                return workflow.nodes;
            }
            function summarizeWorkflow(workflow){
                const nodes = parseWorkflowNodes(workflow);
                if (!nodes.length) return 'No workflow configured yet.';
                const typeOrder = ['opportunity', 'icp_screening', 'connect', 'accept_connection_request', 'if_accepted', 'not_accepted', 'write_message', 'write_to_person', 'calendly_booking', 'office365_booking', 'wait', 'like_post', 'add_to_stakeholder_monitoring', 'enrich_stakeholder_monitoring', 'phoner_bluenordix', 'phoner_enreach', 'send_to_lead_list', 'delete_connection_request'];
                const typeLabels = {
                    opportunity: 'Opportunity',
                    icp_screening: 'Automated Screening',
                    connect: 'Connect',
                    accept_connection_request: 'Accept Connection Request',
                    if_accepted: 'If Accepted',
                    not_accepted: 'Not Accepted',
                    write_message: 'Write to person',
                    write_to_person: 'Write to person',
                    calendly_booking: 'Automatically book Calendly link',
                    office365_booking: 'Automatically book Office 365 calendar',
                    wait: 'Wait',
                    like_post: 'Like post',
                    add_to_stakeholder_monitoring: 'Add to Lead Monitoring',
                    enrich_stakeholder_monitoring: 'Enrich Stakeholder',
                    phoner_bluenordix: 'Send to BlueNordix',
                    phoner_enreach: 'Enreach',
                    send_to_lead_list: 'Send to lead list',
                    delete_connection_request: 'Delete connection request'
                };
                const counts = new Map();
                nodes.forEach(node => {
                    const key = String(node && node.type || '').trim();
                    if (!key) return;
                    counts.set(key, (counts.get(key) || 0) + 1);
                });
                const ordered = Array.from(counts.keys()).sort((a, b) => {
                    const aIndex = typeOrder.indexOf(a);
                    const bIndex = typeOrder.indexOf(b);
                    if (aIndex === -1 && bIndex === -1) return a.localeCompare(b);
                    if (aIndex === -1) return 1;
                    if (bIndex === -1) return -1;
                    return aIndex - bIndex;
                });
                const summary = ordered.slice(0, 5).map(type => {
                    const count = counts.get(type) || 0;
                    const label = typeLabels[type] || type.replaceAll('_', ' ');
                    return count > 1 ? `${label} × ${count}` : label;
                });
                const remainder = ordered.length - summary.length;
                if (remainder > 0) summary.push('+' + remainder + ' more');
                return summary.join(' · ');
            }
            function renderDuplicateCampaigns(){
                if (!duplicateCampaignList) return;
                if (!campaigns.length) {
                    duplicateCampaignList.innerHTML = '<div class="empty-state">No campaigns available to duplicate yet.</div>';
                    return;
                }
                const query = String(duplicateCampaignSearch && duplicateCampaignSearch.value || '').trim().toLowerCase();
                const filtered = campaigns.filter(campaign => {
                    const employees = Array.isArray(campaign.employees) ? campaign.employees : [];
                    const haystack = [
                        campaign.title || '',
                        summarizeWorkflow(campaign.workflow),
                        employees.map(employee => employee.full_name || employee.email || '').join(' ')
                    ].join(' ').toLowerCase();
                    return !query || haystack.includes(query);
                });
                if (!filtered.length) {
                    duplicateCampaignList.innerHTML = '<div class="empty-state">No campaigns matched your search.</div>';
                    return;
                }
                duplicateCampaignList.innerHTML = filtered.map(campaign => {
                    const campaignId = Number(campaign.id || 0);
                    const isSelected = campaignId === Number(selectedDuplicateCampaignId || 0);
                    const employees = Array.isArray(campaign.employees) ? campaign.employees : [];
                    const employeeHtml = employees.length
                        ? employees.map(employee => `<span class="duplicate-employee-pill">${employeeAvatarHtml(employee)}<span>${esc(employee.full_name || employee.email || 'Unknown employee')}</span></span>`).join('')
                        : '<span style="color:var(--muted);font-size:13px;">No employees connected</span>';
                    return `
                        <label class="duplicate-campaign-card ${isSelected ? 'is-selected' : ''}" data-select-duplicate-campaign data-campaign-id="${campaignId}">
                            <div class="duplicate-campaign-card-head">
                                <div>
                                    <h4>${esc(campaign.title || 'Untitled campaign')}</h4>
                                    <p>${esc(summarizeWorkflow(campaign.workflow))}</p>
                                </div>
                                <input class="duplicate-campaign-radio" type="radio" name="duplicate_campaign_id" value="${campaignId}" ${isSelected ? 'checked' : ''}>
                            </div>
                            <div class="duplicate-campaign-meta">
                                <span class="pill">${employees.length} assigned employee${employees.length !== 1 ? 's' : ''}</span>
                                <span class="pill">${getCampaignAttachedCount(campaign)} attached contacts</span>
                            </div>
                            <div class="duplicate-campaign-employees">${employeeHtml}</div>
                        </label>`;
                }).join('');
                duplicateCampaignList.querySelectorAll('[data-select-duplicate-campaign]').forEach(card => {
                    card.addEventListener('click', function(){
                        selectedDuplicateCampaignId = Number(this.dataset.campaignId || 0) || null;
                        renderDuplicateCampaigns();
                    });
                });
            }
            function getPlaybookBySlug(slug){
                return STATIC_PLAYBOOKS.find(item => String(item.slug || '') === String(slug || '')) || null;
            }
            function renderPlaybooks(){
                if (!playbookList) return;
                playbookList.querySelectorAll('[data-select-playbook]').forEach(card => {
                    const isSelected = String(card.dataset.playbookSlug || '') === String(selectedPlaybookSlug || '');
                    card.classList.toggle('is-selected', isSelected);
                    const input = card.querySelector('input[name="playbook_slug"]');
                    if (input) input.checked = isSelected;
                });
            }
            function selectPlaybook(slug){
                selectedPlaybookSlug = String(slug || '');
                const playbook = getPlaybookBySlug(selectedPlaybookSlug);
                if (playbook && !titleInput.value.trim()) {
                    titleInput.value = playbook.heading || playbook.title || '';
                }
                renderPlaybooks();
            }
            function getCampaignIdFromResponse(response){
                const candidates = [
                    response && response.id,
                    response && response.campaign_id,
                    response && response.campaign && response.campaign.id,
                    response && response.data && response.data.id
                ];
                for (const value of candidates) {
                    const id = Number(value || 0);
                    if (id > 0) return id;
                }
                return 0;
            }
            function campaignWorkflowId(prefix){
                return prefix + '_' + Math.random().toString(36).slice(2, 10);
            }
            function buildGuidedCampaignWorkflow(mode, qualification){
                if (mode === 'advanced' || mode === 'duplicate') return null;
                const id = name => campaignWorkflowId(name);
                const opportunity = id('opportunity');
                const connect = id('connect');
                const accepted = id('accepted');
                const notAccepted = id('not_accepted');
                const waitNotAccepted = id('wait_35_days');
                const nodes = [
                    {id: opportunity, type:'opportunity', title:'Opportunity', x:520, y:140, config:{}},
                    {id: connect, type:'connect', title:'Connect', x:520, y:mode === 'sales' ? 800 : 350, config:{connectMessage:'', systemPrompt:'', connectSystemPrompt:''}},
                    {id: accepted, type:'if_accepted', title:'If Accepted', x:340, y:mode === 'sales' ? 1010 : 560, config:{}},
                    {id: notAccepted, type:'not_accepted', title:'Not Accepted', x:700, y:mode === 'sales' ? 1010 : 560, config:{}},
                    {id: waitNotAccepted, type:'wait', title:'Wait 35 days', x:700, y:mode === 'sales' ? 1220 : 770, config:{durationValue:35, durationUnit:'days'}}
                ];
                const edges = [
                    {id:id('edge'), from:connect, to:accepted, label:'If Accepted'},
                    {id:id('edge'), from:connect, to:notAccepted, label:'Not Accepted'},
                    {id:id('edge'), from:notAccepted, to:waitNotAccepted, label:''}
                ];
                if (mode === 'connections') {
                    const deleteRequest = id('delete_connection_request');
                    nodes.push({id:deleteRequest, type:'delete_connection_request', title:'Delete Connection Request', x:700, y:980, config:{}});
                    edges.unshift({id:id('edge'), from:opportunity, to:connect, label:''});
                    edges.push({id:id('edge'), from:waitNotAccepted, to:deleteRequest, label:''});
                    return {version:1, nodes, edges};
                }
                const screening = id('screening');
                const match = id('screening_match');
                const noMatch = id('screening_no_match');
                const waitAccepted = id('wait_1_day');
                const write = id('write');
                const deleteRequest = id('delete_connection_request');
                nodes.push(
                    {id:screening, type:'icp_screening', title:'Automated Screening', x:520, y:350, config:{icpDefinition:String(qualification || '').trim(), matchThreshold:70, scoreThreshold:70}},
                    {id:match, type:'wait', title:'Match', x:350, y:560, config:{durationValue:1, durationUnit:'seconds', icpScreeningId:screening, icpBranch:'match', locked:true}},
                    {id:noMatch, type:'wait', title:'No Match', x:690, y:560, config:{durationValue:1, durationUnit:'seconds', icpScreeningId:screening, icpBranch:'no_match', locked:true}},
                    {id:waitAccepted, type:'wait', title:'Wait 1 day', x:340, y:1220, config:{durationValue:1, durationUnit:'days'}},
                    {id:write, type:'write', title:'Message', x:340, y:1430, config:{template:'Thank you for accepting my connection', systemPrompt:'Translate message into opportunities own language', manualApproval:false}},
                    {id:deleteRequest, type:'delete_connection_request', title:'Delete Connection Request', x:700, y:1430, config:{}}
                );
                edges.unshift(
                    {id:id('edge'), from:opportunity, to:screening, label:''},
                    {id:id('edge'), from:screening, to:match, label:'Match'},
                    {id:id('edge'), from:screening, to:noMatch, label:'No Match'},
                    {id:id('edge'), from:match, to:connect, label:''}
                );
                edges.push(
                    {id:id('edge'), from:accepted, to:waitAccepted, label:''},
                    {id:id('edge'), from:waitAccepted, to:write, label:''},
                    {id:id('edge'), from:waitNotAccepted, to:deleteRequest, label:''}
                );
                return {version:1, nodes, edges};
            }
            function getWizardCountry(){
                if (!campaignAudienceCountry || !campaignAudienceCountry.value) return null;
                const option = campaignAudienceCountry.options[campaignAudienceCountry.selectedIndex];
                return {country_code:campaignAudienceCountry.value, country:String(option?.dataset.country || option?.textContent || '').trim(), geo_urn:String(option?.dataset.geourn || '').trim()};
            }
            async function persistGuidedCampaign(campaignId, mode, workflow){
                if (campaignId > 0 && workflow) {
                    await api.request('/campaigns', {method:'PATCH', body:JSON.stringify({id:campaignId, workflow:JSON.stringify(workflow)})});
                }
                if (campaignId <= 0 || !['connections', 'sales'].includes(mode)) return;
                const country = getWizardCountry();
                await api.request('/campaigns', {method:'POST', body:JSON.stringify({
                    action:'add_leads', campaign_id:campaignId, source_type:'employee_title_location',
                    employee_title:String(campaignDecisionMakerTitle?.value || '').trim(),
                    country_code:country?.country_code || '', country:country?.country || '', geo_urn:country?.geo_urn || ''
                })});
            }
            function normalizePlaybookWorkflowForSave(workflow){
                const fallback = {version: 1, nodes: [], edges: []};
                let normalized = fallback;
                try {
                    normalized = JSON.parse(JSON.stringify(workflow && typeof workflow === 'object' ? workflow : fallback));
                } catch (error) {
                    normalized = fallback;
                }
                if (!normalized || typeof normalized !== 'object') normalized = fallback;
                if (!Array.isArray(normalized.nodes)) normalized.nodes = [];
                if (!Array.isArray(normalized.edges)) normalized.edges = [];
                normalized.nodes = normalized.nodes.map(node => {
                    if (!node || typeof node !== 'object' || node.type !== 'whatsapp_message') return node;
                    const config = node.config && typeof node.config === 'object' && !Array.isArray(node.config) ? Object.assign({}, node.config) : {};
                    const message = String(config.template ?? config.messageTemplate ?? config.message ?? config.Message ?? '');
                    const systemPrompt = String(config.systemPrompt ?? config.whatsappSystemPrompt ?? config.whatsapp_system_prompt ?? config.WhatsAppSystemPrompt ?? '');
                    node.config = Object.assign({}, config, {
                        template: message,
                        messageTemplate: message,
                        message: message,
                        Message: message,
                        systemPrompt: systemPrompt,
                        whatsappSystemPrompt: systemPrompt,
                        whatsapp_system_prompt: systemPrompt,
                        WhatsAppSystemPrompt: systemPrompt,
                        manualApproval: !!config.manualApproval
                    });
                    return node;
                });
                return normalized;
            }
            function buildPlaybookLeadSourcePayload(campaignId, playbook){
                const source = playbook && playbook.lead_source && typeof playbook.lead_source === 'object' ? playbook.lead_source : null;
                if (!source) return null;
                const sourceType = String(source.source_type || source.type || '').trim();
                if (!sourceType) return null;
                const payload = Object.assign({}, source, {
                    action: 'add_leads',
                    campaign_id: Number(campaignId),
                    source_type: sourceType
                });
                delete payload.type;
                if (sourceType === 'like_of_posts') {
                    const mode = normalizeLikePostsMode(payload.like_posts_mode || payload.post_like_mode || payload.posts_mode || 'all_posts');
                    payload.like_posts_mode = mode;
                    payload.post_like_mode = mode;
                    payload.posts_mode = mode;
                }
                return payload;
            }
            function buildPlaybookInstallPayload(title, playbook){
                const workflow = normalizePlaybookWorkflowForSave(playbook.workflow);
                return {
                    mode: 'playbook',
                    title,
                    playbook_slug: playbook.slug,
                    playbook_title: playbook.title || playbook.heading || title,
                    playbook_subject_mode: playbook.subject_mode || '',
                    playbook_has_workflow: playbook.has_workflow ? 1 : 0,
                    workflow: JSON.stringify(workflow),
                    prepared_opportunities: Array.isArray(playbook.prepared_opportunities) ? playbook.prepared_opportunities : []
                };
            }
            async function persistPlaybookDetails(campaignId, playbook, createResponse){
                const workflow = JSON.stringify(normalizePlaybookWorkflowForSave(playbook.workflow));
                try {
                    await api.request('/campaigns', {
                        method: 'PATCH',
                        body: JSON.stringify({
                            id: campaignId,
                            workflow,
                            playbook_slug: playbook.slug,
                            playbook_subject_mode: playbook.subject_mode || ''
                        })
                    });
                } catch (error) {
                    if (!createResponse || !createResponse.campaign) throw error;
                }

                const leadSourcePayload = buildPlaybookLeadSourcePayload(campaignId, playbook);
                if (leadSourcePayload) {
                    await api.request('/campaigns', {
                        method: 'POST',
                        body: JSON.stringify(leadSourcePayload)
                    });
                }

                const prepared = Array.isArray(playbook.prepared_opportunities) ? playbook.prepared_opportunities : [];
                const urls = prepared.map(item => String(item && item.linkedin_url || '').trim()).filter(Boolean);
                if (!urls.length) return;

                await api.request('/campaigns', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'add_leads',
                        campaign_id: campaignId,
                        source_type: 'playbook',
                        playbook_slug: playbook.slug,
                        linkedin_urls: urls,
                        prepared_opportunities: prepared,
                        allow_existing_in_other_campaigns: 1
                    })
                });
            }            async function installPlaybookCampaign(title, playbook){
                const createPayload = buildPlaybookInstallPayload(title, playbook);
                const createResponse = await api.request('/campaigns', {method:'POST', body: JSON.stringify(createPayload)});
                const campaignId = getCampaignIdFromResponse(createResponse);
                if (campaignId > 0) {
                    await persistPlaybookDetails(campaignId, playbook, createResponse);
                }
                return createResponse;
            }
            function getSelectedCountryPayload(){
                if (!leadTitleCountryInput || !leadTitleCountryInput.value) return null;
                const option = leadTitleCountryInput.options[leadTitleCountryInput.selectedIndex];
                return {
                    country_code: leadTitleCountryInput.value,
                    country: option ? String(option.dataset.country || option.textContent || '').trim() : '',
                    geo_urn: option ? String(option.dataset.geourn || '').trim() : ''
                };
            }
            function findCountryOptionValue(countryName, countryCode, geoUrn){
                populateCountrySelect();
                if (!leadTitleCountryInput) return '';
                const normalizedName = String(countryName || '').trim().toLowerCase();
                const normalizedCode = String(countryCode || '').trim().toUpperCase();
                const fallbackCode = /^[a-z]{2}$/i.test(String(countryName || '').trim()) ? String(countryName || '').trim().toUpperCase() : '';
                const activeCode = normalizedCode || fallbackCode;
                const normalizedGeoUrn = String(geoUrn || '').trim();
                if (activeCode && linkedInCountryByCode[activeCode]) return activeCode;
                const options = Array.from(leadTitleCountryInput.options || []);
                const match = options.find(option => {
                    const optionCode = String(option.value || '').trim().toUpperCase();
                    const optionName = String(option.dataset.country || option.textContent || '').trim().toLowerCase();
                    const optionGeoUrn = String(option.dataset.geourn || '').trim();
                    return (normalizedName && optionName === normalizedName)
                        || (normalizedGeoUrn && optionGeoUrn === normalizedGeoUrn)
                        || (activeCode && optionCode === activeCode);
                });
                return match ? match.value : '';
            }
            function removeLinkedinGeoUrnFromUrl(rawUrl){
                try {
                    const cleaned = new URL(String(rawUrl || '').trim());
                    cleaned.searchParams.delete('geoUrn');
                    cleaned.searchParams.delete('geo_urn');
                    return cleaned.toString();
                } catch (error) {
                    return String(rawUrl || '').trim();
                }
            }
            function isLinkedinPostUrl(rawUrl){
                const value = String(rawUrl || '').trim();
                if (!value) return false;
                try {
                    const parsed = new URL(value, 'https://www.linkedin.com');
                    const host = String(parsed.hostname || '').toLowerCase().replace(/^www\./, '');
                    if (host !== 'linkedin.com' && !host.endsWith('.linkedin.com')) return false;
                    const text = decodeURIComponent((parsed.pathname || '') + (parsed.search || '') + (parsed.hash || '')).toLowerCase();
                    return text.startsWith('/feed/update')
                        || text.startsWith('/pulse/')
                        || text.includes('urn:li:activity:')
                        || /activity-\d+/.test(text);
                } catch (error) {
                    return false;
                }
            }
            function isLinkedinActorPostsUrl(rawUrl){
                const value = String(rawUrl || '').trim();
                if (!value) return false;
                try {
                    const parsed = new URL(value, 'https://www.linkedin.com');
                    const host = String(parsed.hostname || '').toLowerCase().replace(/^www\./, '');
                    if (host !== 'linkedin.com' && !host.endsWith('.linkedin.com')) return false;
                    const segments = decodeURIComponent(parsed.pathname || '').toLowerCase().split('/').filter(Boolean);
                    if (!['in', 'pub', 'company', 'school', 'showcase'].includes(segments[0])) return false;
                    if (segments.length === 2) return true;
                    return segments[2] === 'posts' || segments[2] === 'recent-activity';
                } catch (error) {
                    return false;
                }
            }
            function isLinkedinSalesSearchUrl(rawUrl){
                const value = String(rawUrl || '').trim();
                if (!value) return false;
                if (/^\/sales\/search(?:[\/?#]|$)/i.test(value)) return true;
                try {
                    const parsed = new URL(value);
                    const host = String(parsed.hostname || '').toLowerCase().replace(/^www\./, '');
                    const path = String(parsed.pathname || '').replace(/\/+$/, '').toLowerCase();
                    return (host === 'linkedin.com' || host.endsWith('.linkedin.com'))
                        && (path === '/sales/search' || path.startsWith('/sales/search/'));
                } catch (error) {
                    return false;
                }
            }
            function parseLinkedinSearchUrl(rawUrl){
                const value = String(rawUrl || '').trim();
                if (!value) return null;
                const isSalesSearchValue = isLinkedinSalesSearchUrl(value);
                let parsed;
                try {
                    parsed = value.startsWith('/') && isSalesSearchValue
                        ? new URL(value, 'https://www.linkedin.com')
                        : new URL(value);
                } catch (error) {
                    return {error: 'Enter a valid LinkedIn Search URL.'};
                }
                const params = parsed.searchParams;
                const keywords = String(params.get('keywords') || params.get('keyword') || '').trim();
                const geoParam = String(params.get('geoUrn') || params.get('geo_urn') || '').trim();
                const rawGeoUrns = [];
                if (geoParam) {
                    try {
                        const decoded = JSON.parse(geoParam);
                        if (Array.isArray(decoded)) {
                            decoded.forEach(item => {
                                const match = String(item || '').match(/\d+/);
                                if (match) rawGeoUrns.push(match[0]);
                            });
                        }
                    } catch (error) {
                        const matches = geoParam.match(/\d+/g) || [];
                        matches.forEach(item => rawGeoUrns.push(item));
                    }
                }
                const countries = [];
                const geoUrns = [...new Set(rawGeoUrns)];
                [...new Set(rawGeoUrns)].forEach(urn => {
                    const country = linkedInGeoCountryByUrn[String(urn)] || null;
                    if (!country) return;
                    countries.push(country);
                });
                const countryNames = [...new Set(countries.map(country => country.name))];
                const countryCodes = [...new Set(countries.map(country => country.code))];
                const isSalesSearch = isLinkedinSalesSearchUrl(parsed.toString()) || isSalesSearchValue;
                return {
                    is_sales_search: isSalesSearch,
                    keywords,
                    geo_urn: geoUrns[0] || '',
                    geo_urns: geoUrns,
                    country: countryNames[0] || '',
                    countries: countryNames,
                    country_code: countryCodes[0] || '',
                    country_codes: countryCodes,
                    location_label: countryNames.length ? countryNames.join(', ') : ''
                };
            }
            function updateLinkedinUrlPreview(){
                if (!leadLinkedinUrlPreview || !leadLinkedinSearchUrlInput) return;
                const parsed = parseLinkedinSearchUrl(leadLinkedinSearchUrlInput.value);
                if (!parsed) {
                    leadLinkedinUrlPreview.textContent = 'Paste a LinkedIn people or Sales Navigator search URL. Keyword and country are saved when they can be detected.';
                    leadLinkedinUrlPreview.classList.remove('is-valid', 'is-warning');
                    updateAddLeadsButtonState();
                    return;
                }
                if (parsed.error) {
                    leadLinkedinUrlPreview.textContent = parsed.error;
                    leadLinkedinUrlPreview.classList.remove('is-valid');
                    leadLinkedinUrlPreview.classList.add('is-warning');
                    updateAddLeadsButtonState();
                    return;
                }
                const isSalesSearch = !!parsed.is_sales_search;
                const keywordText = parsed.keywords
                    ? 'Keyword: ' + parsed.keywords
                    : (isSalesSearch ? 'Sales Navigator search URL detected' : 'Keyword not found');
                const previewParts = [keywordText];
                if (parsed.location_label) previewParts.push('Location: ' + parsed.location_label);
                if (isSalesSearch && !parsed.location_label) previewParts.push('Filters saved from URL');
                leadLinkedinUrlPreview.textContent = previewParts.join(' · ');
                const hasSearchSource = !!(parsed.keywords || isSalesSearch);
                leadLinkedinUrlPreview.classList.toggle('is-valid', hasSearchSource);
                leadLinkedinUrlPreview.classList.toggle('is-warning', !hasSearchSource);
                updateAddLeadsButtonState();
            }
            function buildLeadSearchPayload(type){
                const campaignId = Number(campaignIdInput.value || 0);
                if (!campaignId) return {error: 'Campaign ID is missing.'};
                const payload = {
                    action: 'add_leads',
                    campaign_id: campaignId,
                    source_type: type
                };
                if (type === 'employee_title_location') {
                    const employeeTitle = String(leadEmployeeTitleInput?.value || '').trim();
                    const country = getSelectedCountryPayload();
                    if (!employeeTitle) return {error: 'Enter an employee title.'};
                    if (!country || !country.country) return {error: 'Select a country.'};
                    payload.employee_title = employeeTitle;
                    payload.keyword = employeeTitle;
                    payload.country = country.country;
                    payload.country_code = country.country_code;
                    if (country.geo_urn) payload.geo_urn = country.geo_urn;
                    return {payload};
                }
                if (type === 'linkedin_search_url') {
                    const url = String(leadLinkedinSearchUrlInput?.value || '').trim();
                    const parsed = parseLinkedinSearchUrl(url);
                    if (!parsed || parsed.error) return {error: parsed && parsed.error ? parsed.error : 'Enter a LinkedIn Search URL.'};
                    if (!parsed.keywords && !parsed.is_sales_search) return {error: 'Keyword not found in the LinkedIn Search URL.'};
                    payload.linkedin_search_url = parsed.is_sales_search
                        ? new URL(url, 'https://www.linkedin.com').toString()
                        : (parsed.geo_urn ? url : removeLinkedinGeoUrnFromUrl(url));
                    payload.linkedin_url = payload.linkedin_search_url;
                    if (parsed.keywords) {
                        payload.keyword = parsed.keywords;
                        payload.keywords = parsed.keywords;
                    }
                    if (parsed.geo_urn) payload.geo_urn = parsed.geo_urn;
                    if (parsed.geo_urns && parsed.geo_urns.length) payload.geo_urns = parsed.geo_urns;
                    if (parsed.country) payload.country = parsed.country;
                    if (parsed.countries && parsed.countries.length) payload.countries = parsed.countries;
                    if (parsed.country_code) payload.country_code = parsed.country_code;
                    if (parsed.country_codes && parsed.country_codes.length) payload.country_codes = parsed.country_codes;
                    if (parsed.location_label) payload.location_label = parsed.location_label;
                    return {payload};
                }
                if (type === 'like_of_posts') {
                    const mode = normalizeLikePostsMode(leadLikePostsModeInput?.value || 'all_posts');
                    payload.like_posts_mode = mode;
                    payload.post_like_mode = mode;
                    payload.posts_mode = mode;
                    if (mode === 'user_posts') {
                        const result = normalizeLinkedinUrl(leadLikePostsUserUrlInput?.value || '', 'Enter a LinkedIn user URL.');
                        if (result.error) return {error: result.error};
                        payload.linkedin_user_url = result.url;
                        payload.user_url = result.url;
                        payload.profile_url = result.url;
                    }
                    if (mode === 'specific_post') {
                        const result = normalizeLinkedinUrl(leadLikePostsPostUrlInput?.value || '', 'Enter a LinkedIn post URL.');
                        if (result.error) return {error: result.error};
                        payload.linkedin_post_url = result.url;
                        payload.post_url = result.url;
                    }
                    return {payload};
                }
                if (type === 'company_followers') {
                    const result = normalizeLinkedinUrl(leadCompanyFollowersUrlInput?.value || '', 'Enter a LinkedIn Company URL.');
                    if (result.error) return {error: result.error};
                    const parsed = new URL(result.url);
                    if (!/^\/company\/[^/]+/i.test(parsed.pathname)) return {error: 'Enter a LinkedIn Company URL.'};
                    payload.company_linkedin_url = result.url;
                    payload.linkedin_company_url = result.url;
                    payload.company_url = result.url;
                    payload.linkedin_url = result.url;
                    return {payload};
                }
                return {error: 'Unknown search type.'};
            }
            function extractSearchId(payload){
                if (!payload || typeof payload !== 'object') return null;
                return payload.search_id || payload.searchId || payload.job_id || payload.jobId || payload.task_id || payload.taskId || payload.id || null;
            }
            function applyLeadSearchValuesToCampaign(campaign, payload, response){
                if (!campaign || !payload) return campaign;
                const responseCampaign = response && response.campaign && typeof response.campaign === 'object' ? response.campaign : null;
                if (responseCampaign) Object.assign(campaign, responseCampaign);
                const source = String(payload.source_type || sourceType.value || '').trim();
                campaign.lead_source = source;
                campaign.lead_source_id = extractSearchId(response) || campaign.lead_source_id || null;
                if (source === 'employee_title_location') {
                    campaign.employee_title = String(payload.employee_title || payload.keyword || '').trim();
                    campaign.country = String(payload.country || '').trim();
                    campaign.country_code = String(payload.country_code || '').trim();
                    campaign.geo_urn = String(payload.geo_urn || '').trim();
                    campaign.linkedin_url = null;
                }
                if (source === 'linkedin_search_url') {
                    campaign.linkedin_url = String(payload.linkedin_url || payload.linkedin_search_url || '').trim();
                    campaign.employee_title = String(payload.keyword || payload.keywords || '').trim();
                    campaign.country = String(payload.country || '').trim();
                    campaign.country_code = String(payload.country_code || '').trim();
                    campaign.geo_urn = String(payload.geo_urn || '').trim();
                }
                if (source === 'like_of_posts') {
                    campaign.like_posts_mode = normalizeLikePostsMode(payload.like_posts_mode || payload.post_like_mode || payload.posts_mode);
                    campaign.post_like_mode = campaign.like_posts_mode;
                    campaign.linkedin_user_url = String(payload.linkedin_user_url || payload.user_url || payload.profile_url || '').trim();
                    campaign.linkedin_post_url = String(payload.linkedin_post_url || payload.post_url || '').trim();
                }
                if (source === 'company_followers') {
                    campaign.company_linkedin_url = String(payload.company_linkedin_url || payload.linkedin_company_url || payload.company_url || '').trim();
                    campaign.linkedin_company_url = campaign.company_linkedin_url;
                    campaign.linkedin_url = campaign.company_linkedin_url;
                }
                return campaign;
            }
            function markLeadSearchSaved(response, payload){
                const campaignId = Number(campaignIdInput.value || 0);
                const existingIndex = campaigns.findIndex(item => Number(item.id) === campaignId);
                if (existingIndex !== -1) {
                    applyLeadSearchValuesToCampaign(campaigns[existingIndex], payload, response);
                    renderCampaigns();
                    updateLeadSourceSummary(campaigns[existingIndex]);
                }
                if (leadSearchProgressSection) leadSearchProgressSection.style.display = '';
                if (leadSearchSaveMessage) leadSearchSaveMessage.style.display = '';
                showToast('The subjects will be added to your campaign within 24 hours.');
                setTimeout(() => {
                    closeModal(addLeadsModal);
                    resetLeadSearchState();
                }, 1200);
            }
            async function startLeadSearch(button){
                const type = sourceType.value;
                const result = buildLeadSearchPayload(type);
                if (result.error) { alert(result.error); return; }
                resetLeadSearchState();
                leadSearchState.isActive = true;
                leadSearchState.payload = result.payload;
                updateAddLeadsButtonState();
                button.classList.add('is-loading');
                try {
                    const campaignId = Number(campaignIdInput.value || 0);
                    const response = await api.request('/campaigns?campaign_id=' + encodeURIComponent(campaignId), {
                        method: 'POST',
                        body: JSON.stringify(result.payload)
                    });
                    leadSearchState.searchId = extractSearchId(response);
                    leadSearchState.isActive = false;
                    markLeadSearchSaved(response, result.payload);
                } catch (error) {
                    leadSearchState.isActive = false;
                    if (leadSearchProgressSection) leadSearchProgressSection.style.display = 'none';
                    alert(error.message);
                    updateAddLeadsButtonState();
                } finally {
                    button.classList.remove('is-loading');
                }
            }
            function hydrateAddLeadsModal(campaign){
                document.getElementById('addLeadsModalTitle').textContent = 'Add new leads to campaign';
                campaignIdInput.value = campaign && campaign.id != null ? String(campaign.id) : '';
                campaignTitleInput.value = campaign && campaign.title ? String(campaign.title) : '';
                populateCountrySelect();
                resetLeadSearchState();
                csvFileInput.value = '';
                csvUrls.value = '';
                csvSummary.textContent = 'Upload a CSV-file and the UI will extract all LinkedIn profile URLs it can find.';
                if (manualUrls) manualUrls.value = '';
                if (manualSummary) manualSummary.textContent = 'Paste LinkedIn profile URLs into the field below.';
                if (leadEmployeeTitleInput) leadEmployeeTitleInput.value = '';
                if (leadTitleCountryInput) leadTitleCountryInput.value = '';
                if (leadLinkedinSearchUrlInput) leadLinkedinSearchUrlInput.value = '';
                if (leadLikePostsModeInput) leadLikePostsModeInput.value = 'all_posts';
                if (leadLikePostsUserUrlInput) leadLikePostsUserUrlInput.value = '';
                if (leadLikePostsPostUrlInput) leadLikePostsPostUrlInput.value = '';
                if (leadCompanyFollowersUrlInput) leadCompanyFollowersUrlInput.value = '';
                if (connectionsModeInput) connectionsModeInput.value = 'not_yet_accepted';

                const meta = getLeadSourceMeta(campaign);
                const searchValues = meta.searchValues || {};
                sourceType.value = meta.source || 'csv';
                pipelineInput.value = meta.source === 'pipeline' && meta.sourceId ? meta.sourceId : '0';
                setSelectedSignalIds(meta.source === 'signal' ? meta.signalIds : []);
                if (connectionsModeInput && meta.source === 'connections') connectionsModeInput.value = meta.connectionsMode || 'not_yet_accepted';

                if (meta.source === 'employee_title_location') {
                    if (leadEmployeeTitleInput) leadEmployeeTitleInput.value = searchValues.employeeTitle || '';
                    if (leadTitleCountryInput) {
                        leadTitleCountryInput.value = findCountryOptionValue(searchValues.country, searchValues.countryCode, searchValues.geoUrn);
                    }
                }
                if (meta.source === 'linkedin_search_url') {
                    if (leadLinkedinSearchUrlInput) leadLinkedinSearchUrlInput.value = searchValues.linkedinUrl || '';
                }
                if (meta.source === 'like_of_posts') {
                    const likePostsValues = meta.likePostsValues || {};
                    if (leadLikePostsModeInput) leadLikePostsModeInput.value = normalizeLikePostsMode(likePostsValues.mode);
                    if (leadLikePostsUserUrlInput) leadLikePostsUserUrlInput.value = likePostsValues.userUrl || '';
                    if (leadLikePostsPostUrlInput) leadLikePostsPostUrlInput.value = likePostsValues.postUrl || '';
                }

                if (meta.source === 'company_followers' && leadCompanyFollowersUrlInput) {
                    leadCompanyFollowersUrlInput.value = meta.companyFollowersUrl || '';
                }

                updateLinkedinUrlPreview();
                syncLikePostsFields();
                updateLeadSourceSummary(campaign);
                syncSourceSections();
            }
            function resetAddLeadsModal(){
                hydrateAddLeadsModal(null);
            }
            function getCampaignById(campaignId){
                return campaigns.find(item => Number(item.id) === Number(campaignId)) || null;
            }
            function getAttachedContactsFromCampaign(campaign){
                if (!campaign || typeof campaign !== 'object') return [];
                const candidates = [
                    campaign.attached_contact_list,
                    campaign.attached_contacts_list,
                    campaign.attached_contacts_data,
                    campaign.contacts,
                    campaign.attached_contacts
                ];
                for (const value of candidates) {
                    if (Array.isArray(value)) return value;
                }
                return [];
            }
            function getConnectedContactsFromCampaign(campaign){
                if (!campaign || typeof campaign !== 'object') return [];
                const candidates = [
                    campaign.connected_contact_list,
                    campaign.connected_contacts_list,
                    campaign.connected_contacts_data,
                    campaign.accepted_contact_list,
                    campaign.accepted_contacts_list,
                    campaign.accepted_contacts,
                    campaign.accepted_connections,
                    campaign.connections
                ];
                for (const value of candidates) {
                    if (Array.isArray(value)) return value;
                }
                return [];
            }
            function asNumber(value){
                if (typeof value === 'number' && Number.isFinite(value)) return value;
                if (typeof value === 'string' && value.trim() !== '' && !Number.isNaN(Number(value))) return Number(value);
                return null;
            }
            function getCampaignAttachedCount(campaign){
                if (!campaign || typeof campaign !== 'object') return 0;
                const candidates = [
                    campaign.attached_count,
                    campaign.attached_contacts_count,
                    campaign.total_attached,
                    campaign.attached_contacts
                ];
                for (const value of candidates) {
                    if (Array.isArray(value)) return value.length;
                    const numeric = asNumber(value);
                    if (numeric !== null) return numeric;
                }
                const contacts = getAttachedContactsFromCampaign(campaign);
                return contacts.length;
            }
            function getCampaignConnectedCount(campaign){
                if (!campaign || typeof campaign !== 'object') return 0;
                const candidates = [
                    campaign.connected_count,
                    campaign.connected_contacts_count,
                    campaign.total_connected,
                    campaign.accepted_contacts_count,
                    campaign.accepted_connections_count,
                    campaign.connected_contacts
                ];
                for (const value of candidates) {
                    if (Array.isArray(value)) return value.length;
                    const numeric = asNumber(value);
                    if (numeric !== null) return numeric;
                }
                const contacts = getConnectedContactsFromCampaign(campaign);
                return contacts.length;
            }
            function getCampaignConnectionRequestCount(campaign){
                if (!campaign || typeof campaign !== 'object') return 0;
                const candidates = [
                    campaign.connection_requests,
                    campaign.connection_requests_count,
                    campaign.connection_request_count,
                    campaign.sent_connection_requests,
                    campaign.sent_connection_requests_count,
                    campaign.connect_requests,
                    campaign.connect_requests_count,
                    campaign.requests_sent,
                    campaign.requested_connections
                ];
                for (const value of candidates) {
                    if (Array.isArray(value)) return value.length;
                    const numeric = asNumber(value);
                    if (numeric !== null) return numeric;
                }
                const fallback = asNumber(campaign.connect);
                return fallback !== null ? fallback : 0;
            }
            function getCampaignAcceptanceRate(campaign){
                if (!campaign || typeof campaign !== 'object') return 0;
                const explicitRate = asNumber(campaign.acceptance_rate ?? campaign.acceptanceRate);
                if (explicitRate !== null) return explicitRate;
                const requests = getCampaignConnectionRequestCount(campaign);
                if (requests <= 0) return 0;
                return (getCampaignConnectedCount(campaign) / requests) * 100;
            }
            function getCampaignReplyRate(campaign){
                if (!campaign || typeof campaign !== 'object') return 0;
                const explicitRate = asNumber(campaign.reply_rate ?? campaign.replyRate);
                if (explicitRate !== null) return explicitRate;
                const messages = Number(campaign.messages || campaign.sent_messages || 0);
                if (messages <= 0) return 0;
                return (Number(campaign.replies || 0) / messages) * 100;
            }
            function getConnectedCountFromPayload(payload, contacts){
                const candidates = [
                    payload && payload.connected_count,
                    payload && payload.connected_contacts_count,
                    payload && payload.total_connected,
                    payload && payload.count,
                    payload && payload.total,
                    payload && payload.data && payload.data.connected_count,
                    payload && payload.data && payload.data.connected_contacts_count,
                    payload && payload.data && payload.data.total_connected,
                    payload && payload.data && payload.data.count,
                    payload && payload.data && payload.data.total
                ];
                for (const value of candidates) {
                    const numeric = asNumber(value);
                    if (numeric !== null) return numeric;
                }
                return Array.isArray(contacts) ? contacts.length : 0;
            }
            function normalizeAttachedContacts(payload){
                if (Array.isArray(payload)) return payload;
                if (payload && Array.isArray(payload.contacts)) return payload.contacts;
                if (payload && Array.isArray(payload.attached_contacts)) return payload.attached_contacts;
                if (payload && payload.data && Array.isArray(payload.data)) return payload.data;
                if (payload && payload.data && Array.isArray(payload.data.contacts)) return payload.data.contacts;
                return [];
            }
            function normalizeConnectedContacts(payload){
                if (Array.isArray(payload)) return payload;
                if (payload && Array.isArray(payload.contacts)) return payload.contacts;
                if (payload && Array.isArray(payload.connected_contacts)) return payload.connected_contacts;
                if (payload && Array.isArray(payload.accepted_contacts)) return payload.accepted_contacts;
                if (payload && Array.isArray(payload.accepted_connections)) return payload.accepted_connections;
                if (payload && Array.isArray(payload.connections)) return payload.connections;
                if (payload && payload.data && Array.isArray(payload.data)) return payload.data;
                if (payload && payload.data && Array.isArray(payload.data.contacts)) return payload.data.contacts;
                if (payload && payload.data && Array.isArray(payload.data.connected_contacts)) return payload.data.connected_contacts;
                if (payload && payload.data && Array.isArray(payload.data.accepted_contacts)) return payload.data.accepted_contacts;
                if (payload && payload.data && Array.isArray(payload.data.accepted_connections)) return payload.data.accepted_connections;
                return [];
            }
            function valueIsTruthy(value){
                if (value === true || value === 1) return true;
                if (typeof value === 'string') {
                    const normalized = value.trim().toLowerCase();
                    return ['1', 'true', 'yes', 'accepted', 'connected'].includes(normalized);
                }
                return false;
            }
            function textIndicatesAccepted(value){
                const normalized = String(value || '').trim().toLowerCase();
                if (!normalized) return false;
                const negativeMatches = ['not_accepted', 'not accepted', 'pending', 'queued', 'removed', 'rejected', 'declined', 'deleted', 'delete_connection_request', 'unconnected'];
                if (negativeMatches.some(match => normalized.includes(match))) return false;
                const positiveMatches = ['accepted', 'connected', 'if_accepted', 'connection_accepted', 'connection accepted'];
                return positiveMatches.some(match => normalized.includes(match));
            }
            function isAcceptedConnection(contact){
                if (!contact || typeof contact !== 'object') return false;
                const truthyFields = [
                    contact.accepted,
                    contact.is_accepted,
                    contact.connection_accepted,
                    contact.connected,
                    contact.is_connected,
                    contact.connection_status_id
                ];
                if (truthyFields.some(valueIsTruthy)) return true;

                const dateFields = [
                    contact.accepted_at,
                    contact.accepted_time,
                    contact.connection_accepted_at,
                    contact.connected_at,
                    contact.connected_time
                ];
                if (dateFields.some(value => value != null && String(value).trim() !== '' && String(value).trim() !== '0000-00-00 00:00:00')) return true;

                const textFields = [
                    contact.connection_status,
                    contact.linkedin_status,
                    contact.status,
                    contact.state,
                    contact.current_state,
                    contact.current_node_type,
                    contact.current_node_title,
                    contact.request_state
                ];
                return textFields.some(textIndicatesAccepted);
            }
            function filterAcceptedContacts(contacts){
                if (!Array.isArray(contacts)) return [];
                return contacts.filter(isAcceptedConnection);
            }
            function setContactsTableHead(type){
                if (!attachedContactsTableHead) return;
                if (type === 'connected') {
                    attachedContactsTableHead.innerHTML = `
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Connection status</th>
                            <th>Accepted at</th>
                            <th>LinkedIn</th>
                        </tr>`;
                    return;
                }
                attachedContactsTableHead.innerHTML = `
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Screening score</th>
                            <th>Current state</th>
                            <th>Next movement</th>
                            <th>LinkedIn</th>
                            <th>Action</th>
                        </tr>`;
            }
            function getContactFullName(contact){
                const name = contact.full_name || contact.name || [contact.first_name, contact.last_name].filter(Boolean).join(' ');
                return String(name || '').trim() || 'Unknown';
            }
            function formatContactName(contact){
                const fullName = getContactFullName(contact);
                if (fullName.toLowerCase() !== 'unknown') {
                    return esc(fullName);
                }
                return `<span class="contact-name-unknown"><span>${esc(fullName)}</span><i>We will identify the name during the first visit or connect request.</i></span>`;
            }
            function getContactTitle(contact){
                return contact.job_title || contact.title || contact.participant_jobtitle || '—';
            }
            function getContactCompany(contact){
                return contact.company_name || contact.company || contact.organization || '—';
            }
            function getContactLinkedin(contact){
                return contact.linkedin_url || contact.profile_url || contact.url || contact.linkedin || '';
            }
            function getOpportunityCompletionCode(contact){
                if (!contact || typeof contact !== 'object') return null;
                const value = [
                    contact.completed,
                    contact.is_completed,
                    contact.campaign_contact_completed
                ].find(item => item !== undefined && item !== null && String(item).trim() !== '');
                if (value === undefined) return null;
                const code = Number(value);
                return Number.isFinite(code) ? code : null;
            }
            function isOpportunityPaused(contact){
                if (!contact || typeof contact !== 'object') return false;
                const completionCode = getOpportunityCompletionCode(contact);
                if (completionCode === 2) return true;
                if (completionCode === 1) return false;
                const values = [
                    contact.paused,
                    contact.is_paused,
                    contact.opportunity_paused,
                    contact.campaign_contact_paused,
                    contact.status,
                    contact.state,
                    contact.current_state
                ];
                return values.some(value => {
                    if (value === true || value === 1) return true;
                    if (typeof value !== 'string') return false;
                    const normalized = value.trim().toLowerCase();
                    return ['1', 'true', 'yes', 'paused', 'pause'].includes(normalized);
                });
            }
            function getContactIdentifier(contact){
                if (!contact || typeof contact !== 'object') return '';
                const candidates = [
                    contact.campaign_contact_id,
                    contact.campaign_contact,
                    contact.contact_id,
                    contact.id,
                    contact.participant_id,
                    contact.lead_id,
                    contact.company_participant_id
                ];
                for (const value of candidates) {
                    if (value !== undefined && value !== null && String(value).trim() !== '') return String(value);
                }
                return '';
            }
            function getFilteredAttachedContacts(contacts){
                const filter = attachedContactsOpportunityFilter ? attachedContactsOpportunityFilter.value : 'all';
                const safeContacts = Array.isArray(contacts) ? contacts : [];
                const getStatusOrder = contact => {
                    if (isOpportunityPaused(contact)) return 2;
                    if (isContactComplete(contact)) return 1;
                    return 0;
                };
                return safeContacts
                    .filter(contact => {
                        if (filter === 'active') return !isOpportunityPaused(contact) && !isContactComplete(contact);
                        if (filter === 'paused') return isOpportunityPaused(contact);
                        if (filter === 'completed') return isContactComplete(contact);
                        return true;
                    })
                    .slice()
                    .sort((a, b) => getStatusOrder(a) - getStatusOrder(b));
            }
            function setAttachedContactsForCampaign(campaignId, contacts){
                attachedContactsCurrentCampaignId = Number(campaignId || attachedContactsCurrentCampaignId || 0) || null;
                attachedContactsCurrentList = Array.isArray(contacts) ? contacts : [];
                const campaign = getCampaignById(attachedContactsCurrentCampaignId);
                if (campaign) {
                    campaign.attached_contacts_data = attachedContactsCurrentList;
                }
            }
            function updateAttachedContactPauseState(contactId, paused){
                attachedContactsCurrentList = attachedContactsCurrentList.map(contact => {
                    if (getContactIdentifier(contact) !== String(contactId)) return contact;
                    return Object.assign({}, contact, {
                        completed: paused ? 2 : 0,
                        paused: paused ? 1 : 0,
                        is_paused: paused ? 1 : 0,
                        opportunity_paused: paused ? 1 : 0
                    });
                });
                const campaign = getCampaignById(attachedContactsCurrentCampaignId);
                if (campaign) {
                    campaign.attached_contacts_data = attachedContactsCurrentList;
                }
            }
            function renderCurrentAttachedContacts(emptyMessage){
                renderAttachedContacts(attachedContactsCurrentList, emptyMessage);
            }
            async function toggleOpportunityPause(campaignId, contact, shouldPause){
                const contactId = getContactIdentifier(contact);
                if (!campaignId || !contactId) {
                    throw new Error('Missing opportunity id.');
                }
                const payload = {
                    action: shouldPause ? 'pause_opportunity' : 'restart_opportunity',
                    campaign_id: Number(campaignId),
                    id: Number(contactId) || contactId,
                    contact_id: Number(contactId) || contactId,
                    opportunity_id: Number(contactId) || contactId,
                    campaign_contact_id: Number(contact.campaign_contact_id || 0) || undefined,
                    participant_id: Number(contact.participant_id || 0) || undefined,
                    completed: shouldPause ? 2 : 0,
                    paused: shouldPause ? 1 : 0
                };
                const attempts = [
                    {path: '/campaigns/attached_contacts', method: 'PATCH'},
                    {path: '/campaigns', method: 'PATCH'},
                    {path: '/campaigns', method: 'POST'}
                ];
                let lastError = null;
                for (const attempt of attempts) {
                    try {
                        return await api.request(attempt.path, {
                            method: attempt.method,
                            body: JSON.stringify(payload)
                        });
                    } catch (error) {
                        lastError = error;
                    }
                }
                throw lastError || new Error('Could not update opportunity.');
            }
            function renderContactScreening(contact){
                const result = window.CohertaScreeningResults.getResult(contact);
                if (!result) return '<span class="campaign-screening-empty">Not screened</span>';
                const score = result.score === null ? 'View result' : result.score + ' / 100';
                const decision = window.CohertaScreeningResults.decisionLabel(result);
                return `<button class="campaign-screening-score" type="button" data-screening-contact-id="${esc(getContactIdentifier(contact))}" data-decision="${esc(result.decision)}" aria-haspopup="dialog" aria-label="${esc('View Automated Screening for ' + getContactFullName(contact) + ': ' + score + ', ' + decision)}"><strong>${esc(score)}</strong><span>${esc(decision)}</span></button>`;
            }
            function renderAttachedContacts(contacts, emptyMessage){
                setContactsTableHead('attached');
                const visibleContacts = getFilteredAttachedContacts(contacts);
                if (!visibleContacts.length) {
                    attachedContactsTableBody.innerHTML = `<tr><td colspan="8"><div class="empty-state">${esc(emptyMessage || 'No attached contacts found for this campaign.')}</div></td></tr>`;
                    return;
                }

                const now = Date.now();

                function parseDate(value){
                    if (!value) return null;
                    const parsed = new Date(value);
                    return Number.isNaN(parsed.getTime()) ? null : parsed;
                }

                function formatMoveState(currentNodeMove){
                    const moveDate = parseDate(currentNodeMove);
                    if (!moveDate) {
                        return '<span style="color:var(--muted);font-size:13px;">—</span>';
                    }

                    if (moveDate.getTime() <= now) {
                        return '<span class="pill">queued for processing</span>';
                    }

                    return `<span style="font-size:13px;">${esc(currentNodeMove)}</span>`;
                }

                function formatCurrentState(contact){
                    const nodeType = contact.current_node_type || '';
                    const nodeId = contact.current_node_id || '';

                    if (!nodeType && !nodeId) {
                        return '<span style="color:var(--muted);font-size:13px;">—</span>';
                    }

                    if (nodeType && nodeId) {
                        return `<div><strong>${esc(nodeType)}</strong></div>`;
                    }

                    if (nodeType) {
                        return `<strong>${esc(nodeType)}</strong>`;
                    }

                    return `<span style="color:var(--muted);font-size:12px;">${esc(nodeId)}</span>`;
                }

                attachedContactsTableBody.innerHTML = visibleContacts.map(contact => {
                    const fullName = formatContactName(contact);
                    const title = getContactTitle(contact);
                    const company = getContactCompany(contact);
                    const linkedin = getContactLinkedin(contact);
                    const contactId = getContactIdentifier(contact);
                    const isPaused = isOpportunityPaused(contact);
                    const isCompleted = isContactComplete(contact);
                    const statusPill = isCompleted
                        ? '<span class="opportunity-status-pill is-completed">Completed</span>'
                        : (isPaused ? '<span class="opportunity-status-pill">Paused</span>' : '');

                    return `
        <tr data-attached-contact-id="${esc(contactId)}" data-opportunity-paused="${isPaused ? '1' : '0'}" data-opportunity-completed="${isCompleted ? '1' : '0'}">
          <td>${fullName}</td>
          <td>${esc(title)}</td>
          <td>${esc(company)}</td>
          <td>${renderContactScreening(contact)}</td>
          <td><div class="opportunity-state-cell">${formatCurrentState(contact)}${statusPill}</div></td>
          <td>${formatMoveState(contact.current_node_move)}</td>
          <td>${linkedin ? `<a class="pill" href="${esc(linkedin)}" target="_blank" rel="noopener">Open profile</a>` : '<span style="color:var(--muted);font-size:13px;">No link</span>'}</td>
          <td><button class="pill opportunity-action-btn" type="button" data-toggle-opportunity-pause data-contact-id="${esc(contactId)}" ${contactId && !isCompleted ? '' : 'disabled'}>${isCompleted ? 'Completed' : (isPaused ? 'Restart Opportunity' : 'Stop Opportunity')}</button></td>
        </tr>`;
                }).join('');
                attachedContactsTableBody.querySelectorAll('[data-toggle-opportunity-pause]').forEach(btn => btn.addEventListener('click', async function(){
                    const contactId = this.dataset.contactId || '';
                    const contact = attachedContactsCurrentList.find(item => getContactIdentifier(item) === contactId);
                    if (!contact) return;
                    const campaignId = attachedContactsCurrentCampaignId || Number(attachedContactsCampaignIdInput.value || 0);
                    const shouldPause = !isOpportunityPaused(contact);
                    this.disabled = true;
                    updateAttachedContactPauseState(contactId, shouldPause);
                    renderCurrentAttachedContacts();
                    try {
                        await toggleOpportunityPause(campaignId, contact, shouldPause);
                        showToast(shouldPause ? 'Opportunity paused' : 'Opportunity restarted');
                    } catch (error) {
                        updateAttachedContactPauseState(contactId, !shouldPause);
                        renderCurrentAttachedContacts();
                        alert(error.message || 'Could not update opportunity.');
                    }
                }));
            }
            function renderConnectedContacts(contacts, emptyMessage){
                setContactsTableHead('connected');
                if (!Array.isArray(contacts) || !contacts.length) {
                    attachedContactsTableBody.innerHTML = `<tr><td colspan="6"><div class="empty-state">${esc(emptyMessage || 'No accepted connections found for this campaign.')}</div></td></tr>`;
                    return;
                }

                function formatConnectionStatus(contact){
                    const status = contact.connection_status || contact.linkedin_status || contact.status || (valueIsTruthy(contact.is_connected) ? 'connected' : '');
                    if (!status) {
                        return '<span style="color:var(--muted);font-size:13px;">—</span>';
                    }
                    const normalized = String(status).replaceAll('_', ' ');
                    return `<span class="pill">${esc(normalized)}</span>`;
                }

                function formatAcceptedAt(contact){
                    const value = contact.accepted_at || contact.accepted_time || contact.connection_accepted_at || contact.connected_at || contact.connected_time || '';
                    if (!value || String(value).trim() === '0000-00-00 00:00:00') {
                        return '<span style="color:var(--muted);font-size:13px;">—</span>';
                    }
                    return `<span style="font-size:13px;">${esc(value)}</span>`;
                }

                attachedContactsTableBody.innerHTML = contacts.map(contact => {
                    const fullName = formatContactName(contact);
                    const title = getContactTitle(contact);
                    const company = getContactCompany(contact);
                    const linkedin = getContactLinkedin(contact);

                    return `
        <tr>
          <td>${fullName}</td>
          <td>${esc(title)}</td>
          <td>${esc(company)}</td>
          <td>${formatConnectionStatus(contact)}</td>
          <td>${formatAcceptedAt(contact)}</td>
          <td>${linkedin ? `<a class="pill" href="${esc(linkedin)}" target="_blank" rel="noopener">Open profile</a>` : '<span style="color:var(--muted);font-size:13px;">No link</span>'}</td>
        </tr>`;
                }).join('');
            }
            async function fetchConnectedContacts(campaignId){
                const payload = await api.request(`/campaigns/connected_contacts?campaign_id=${Number(campaignId)}`);
                const contacts = normalizeConnectedContacts(payload);
                const count = getConnectedCountFromPayload(payload, contacts);
                return {payload, contacts, count};
            }
            function applyConnectedContactsResult(campaign, result){
                if (!campaign || !result) return;
                campaign.connected_contacts_data = Array.isArray(result.contacts) ? result.contacts : [];
                campaign.connected_contacts_loaded = true;
                campaign.connected_count = Number(result.count || 0);
                campaign.connected_contacts_count = Number(result.count || 0);
                campaign.connected_contacts = Number(result.count || 0);
            }
            async function openAttachedContacts(campaignId){
                const campaign = getCampaignById(campaignId);
                const requestId = ++attachedContactsRequestId;
                attachedContactsCurrentCampaignId = Number(campaignId || 0) || null;
                attachedContactsCurrentList = [];
                if (attachedContactsOpportunityFilter) {
                    attachedContactsOpportunityFilter.value = 'all';
                    attachedContactsOpportunityFilter.style.display = '';
                }
                attachedContactsCampaignIdInput.value = String(campaignId || '');
                attachedContactsModalTitle.textContent = `Attached Contacts · ${campaign && campaign.title ? campaign.title : 'Campaign'}`;
                attachedContactsModalSubtitle.textContent = 'Review all contacts currently attached to this campaign.';
                setContactsTableHead('attached');
                attachedContactsTableBody.innerHTML = '<tr><td colspan="8"><div class="empty-state">Loading attached contacts...</div></td></tr>';
                openModal(attachedContactsModal);

                try {
                    const payload = await api.request(`/campaigns/workflow/attached?campaign_id=${Number(campaignId)}&scope=all`);
                    if (requestId !== attachedContactsRequestId) return;
                    setAttachedContactsForCampaign(campaignId, normalizeAttachedContacts(payload));
                    renderCurrentAttachedContacts();
                } catch (error) {
                    if (requestId !== attachedContactsRequestId) return;
                    renderCurrentAttachedContacts('Could not load attached contacts. Please reopen this view to try again.');
                }
            }
            async function openConnectedContacts(campaignId){
                attachedContactsRequestId++;
                const campaign = getCampaignById(campaignId);
                if (attachedContactsOpportunityFilter) {
                    attachedContactsOpportunityFilter.style.display = 'none';
                }
                attachedContactsCampaignIdInput.value = String(campaignId || '');
                attachedContactsModalTitle.textContent = `Accepted Connections · ${campaign && campaign.title ? campaign.title : 'Campaign'}`;
                attachedContactsModalSubtitle.textContent = 'Review the contacts that accepted your connection request in this campaign.';
                setContactsTableHead('connected');
                attachedContactsTableBody.innerHTML = '<tr><td colspan="6"><div class="empty-state">Loading accepted connections...</div></td></tr>';
                openModal(attachedContactsModal);

                const inlineConnectedContacts = getConnectedContactsFromCampaign(campaign);
                if (inlineConnectedContacts.length) {
                    renderConnectedContacts(inlineConnectedContacts, 'No accepted connections found for this campaign.');
                    return;
                }

                if (campaign && campaign.connected_contacts_loaded) {
                    renderConnectedContacts([], 'No accepted connections found for this campaign.');
                    return;
                }

                try {
                    const result = await fetchConnectedContacts(campaignId);
                    if (campaign) {
                        applyConnectedContactsResult(campaign, result);
                        renderCampaigns();
                    }
                    if (result.contacts.length) {
                        renderConnectedContacts(result.contacts, 'No accepted connections found for this campaign.');
                        return;
                    }
                } catch (error) {}

                const fallbackEndpoints = [
                    {url: `/campaigns/accepted_connections?campaign_id=${Number(campaignId)}`, isFiltered: true},
                    {url: `/campaigns/accepted_contacts?campaign_id=${Number(campaignId)}`, isFiltered: true},
                    {url: `/campaigns/attached_contacts?campaign_id=${Number(campaignId)}`, isFiltered: false}
                ];

                for (const endpoint of fallbackEndpoints) {
                    try {
                        const payload = await api.request(endpoint.url);
                        const contacts = endpoint.isFiltered
                            ? normalizeConnectedContacts(payload)
                            : filterAcceptedContacts(normalizeAttachedContacts(payload));
                        if (contacts.length) {
                            if (campaign) {
                                campaign.connected_contacts_data = contacts;
                                campaign.connected_contacts_loaded = true;
                                campaign.connected_count = contacts.length;
                                campaign.connected_contacts_count = contacts.length;
                                campaign.connected_contacts = contacts.length;
                                renderCampaigns();
                            }
                            renderConnectedContacts(contacts, 'No accepted connections found for this campaign.');
                            return;
                        }
                    } catch (error) {}
                }

                const inlineAttachedContacts = filterAcceptedContacts(getAttachedContactsFromCampaign(campaign));
                renderConnectedContacts(inlineAttachedContacts, 'No accepted connections found for this campaign.');
            }

            async function refreshConnectedContactsFromEndpoint(){
                const token = ++connectedContactsRefreshToken;
                const targets = campaigns.filter(campaign => Number(campaign && campaign.id || 0) > 0);
                if (!targets.length) return;

                const results = await Promise.allSettled(targets.map(async campaign => {
                    const result = await fetchConnectedContacts(campaign.id);
                    return {campaignId: Number(campaign.id), result};
                }));

                if (token !== connectedContactsRefreshToken) return;

                results.forEach(item => {
                    if (item.status !== 'fulfilled') return;
                    const campaign = getCampaignById(item.value.campaignId);
                    if (!campaign) return;
                    applyConnectedContactsResult(campaign, item.value.result);
                });

                renderCampaigns();
                renderDuplicateCampaigns();
            }

            async function refreshCampaignTimeslotSettings(){
                const token = ++campaignSettingsRefreshToken;
                const targets = campaigns.filter(campaign => {
                    return Number(campaign && campaign.id || 0) > 0
                        && Number(campaign && campaign.status || 0) === 0
                        && !campaignHasTimeslotData(campaign);
                });
                if (!targets.length) return;

                const results = await Promise.allSettled(targets.map(async campaign => {
                    const payload = await api.request('/campaigns/settings?campaign_id=' + encodeURIComponent(campaign.id));
                    return {campaignId: Number(campaign.id), payload};
                }));

                if (token !== campaignSettingsRefreshToken) return;

                let changed = false;
                results.forEach(item => {
                    if (item.status !== 'fulfilled') return;
                    const index = campaigns.findIndex(campaign => Number(campaign && campaign.id || 0) === item.value.campaignId);
                    if (index === -1) return;

                    const payload = item.value.payload || {};
                    const payloadCampaign = payload.campaign && typeof payload.campaign === 'object' ? payload.campaign : {};
                    const activityPeriod = payload.activity_period || payloadCampaign.activity_period || payloadCampaign.activityPeriod;
                    if (!activityPeriod) return;

                    campaigns[index] = Object.assign({}, campaigns[index], payloadCampaign, {
                        activity_period: activityPeriod
                    });
                    changed = true;
                });

                if (changed) {
                    renderCampaigns();
                    renderDuplicateCampaigns();
                }
            }

            function normalizeDashboardPayload(payload){
                if (Array.isArray(payload)) {
                    return {campaigns: payload, stats: null};
                }

                if (payload && typeof payload === 'object') {
                    return {
                        campaigns: Array.isArray(payload.campaigns) ? payload.campaigns : [],
                        stats: payload.stats && typeof payload.stats === 'object' ? payload.stats : null
                    };
                }

                return {campaigns: [], stats: null};
            }
            function getStatValue(stats, key, fallback){
                if (stats && stats[key] !== undefined && stats[key] !== null && stats[key] !== '') {
                    const value = Number(stats[key]);
                    return Number.isFinite(value) ? value : fallback;
                }
                return fallback;
            }
            function formatStatNumber(value){
                const number = Number(value || 0);
                return Number.isFinite(number) ? number.toLocaleString('en-US') : '0';
            }
            function formatReplyRate(value){
                const number = Number(value || 0);
                if (!Number.isFinite(number)) return '0%';
                const rounded = Number.isInteger(number) ? number : Number(number.toFixed(1));
                return rounded + '%';
            }
            function escapeCsvValue(value){
                const text = String(value ?? '');
                return /[",\r\n]/.test(text) ? '"' + text.replace(/"/g, '""') + '"' : text;
            }
            function getCampaignTableCsvRows(){
                const headers = [
                    'Title',
                    'Opportunities',
                    'Connection Requests',
                    'Accepted',
                    'Acceptance Rate',
                    'Visits',
                    'Likes',
                    'Endorse',
                    'Messages',
                    'Replies',
                    'Reply Rate (Threads)'
                ];
                const rows = (Array.isArray(campaigns) ? campaigns : []).map(campaign => [
                    campaign.title || '',
                    getCampaignAttachedCount(campaign),
                    getCampaignConnectionRequestCount(campaign),
                    getCampaignConnectedCount(campaign),
                    formatReplyRate(getCampaignAcceptanceRate(campaign)),
                    Number(campaign.visits || 0),
                    Number(campaign.likes || 0),
                    Number(campaign.endorse || 0),
                    Number(campaign.messages || 0),
                    Number(campaign.replies || 0),
                    formatReplyRate(getCampaignReplyRate(campaign))
                ]);
                return [headers].concat(rows);
            }
            function downloadCampaignTableCsv(){
                if (!Array.isArray(campaigns) || !campaigns.length) {
                    showToast('No campaigns to download');
                    return;
                }
                const csv = getCampaignTableCsvRows()
                    .map(row => row.map(escapeCsvValue).join(','))
                    .join('\r\n');
                const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0, 10);
                link.href = url;
                link.download = `campaigns-dashboard-${date}.csv`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
                showToast('Campaign table downloaded');
            }
            function updateStats(list, stats = campaignDashboardStats){
                const safeList = Array.isArray(list) ? list : [];
                const fallbackActiveCount = safeList.reduce((sum, item) => sum + (Number(item.status || 0) === 0 ? 1 : 0), 0);
                const fallbackOpportunities = safeList.reduce((sum, item) => sum + getCampaignAttachedCount(item), 0);
                const fallbackConnected = safeList.reduce((sum, item) => sum + getCampaignConnectedCount(item), 0);
                const totalMessages = safeList.reduce(
                    (sum, item) => sum + Number(item.messages || item.sent_messages || 0),
                    0
                );
                const totalReplies = safeList.reduce(
                    (sum, item) => sum + Number(item.replies || 0),
                    0
                );
                const fallbackReplyRate = totalMessages > 0
                    ? (totalReplies / totalMessages) * 100
                    : 0;

                document.getElementById('statCampaigns').textContent = formatStatNumber(getStatValue(stats, 'active_campaigns', fallbackActiveCount));
                document.getElementById('statAttached').textContent = formatStatNumber(getStatValue(stats, 'opportunities', fallbackOpportunities));
                document.getElementById('statConnected').textContent = formatStatNumber(getStatValue(stats, 'connected_contacts', fallbackConnected));
                document.getElementById('statReplies').textContent = formatReplyRate(getStatValue(stats, 'reply_rate', fallbackReplyRate));
                document.getElementById('campaignCountPill').textContent = safeList.length + ' campaigns';
            }
            function formatStatusToggle(campaign){
                const campaignId = Number(campaign && campaign.id ? campaign.id : 0);
                const isOn = Number(campaign && campaign.status || 0) === 0;
                return `<div class="status-toggle" role="group" aria-label="Campaign status">
                    <span class="status-toggle-label">Status:</span>
                    <button class="status-toggle-btn is-on ${isOn ? 'is-active' : ''}" type="button" data-toggle-campaign-status data-status-value="0" data-campaign-id="${campaignId}" aria-pressed="${isOn ? 'true' : 'false'}">On</button>
                    <button class="status-toggle-btn is-off ${!isOn ? 'is-active' : ''}" type="button" data-toggle-campaign-status data-status-value="1" data-campaign-id="${campaignId}" aria-pressed="${!isOn ? 'true' : 'false'}">Off</button>
                </div>`;
            }
            function formatLeadSource(campaign){
                const source = normalizeLeadSourceValue(campaign);
                const sourceId = campaign && campaign.lead_source_id != null && String(campaign.lead_source_id).trim() !== ''
                    ? String(campaign.lead_source_id).trim()
                    : '';

                if (!source && !sourceId) {
                    return '<span style="color:var(--muted);font-size:13px;">No lead source selected</span>';
                }

                const sourceMap = {
                    csv: 'CSV',
                    manual: 'Manual Added',
                    pipeline: 'Lead list',
                    signal: 'Signal',
                    employee_title_location: 'Employee Title + Location',
                    linkedin_search_url: 'LinkedIn Search URL',
                    like_of_posts: 'Like of Posts',
                    company_followers: 'Company Followers',
                    connections: 'Connections'
                };
                const sourceLabel = sourceMap[source] || source;
                const values = getCampaignSearchValues(campaign);
                const details = [];
                if (source === 'pipeline' && getLeadListTitle(sourceId)) {
                    details.push(getLeadListTitle(sourceId));
                }
                if (source === 'employee_title_location' || source === 'linkedin_search_url') {
                    if (values.employeeTitle) details.push(values.employeeTitle);
                    if (values.country) details.push(values.country);
                }
                if (source === 'connections') {
                    const connectionsMode = getLeadSourceMeta(campaign).connectionsMode;
                    details.push(getConnectionsModeLabel(connectionsMode));
                }
                if (source === 'signal') {
                    details.push(formatSignalSelection(getCampaignSignalIds(campaign, sourceId)));
                }
                if (source === 'like_of_posts') {
                    const likePostsValues = getCampaignLikePostsValues(campaign);
                    details.push(getLikePostsModeLabel(likePostsValues.mode));
                    if (likePostsValues.mode === 'user_posts' && likePostsValues.userUrl) details.push('User URL saved');
                    if (likePostsValues.mode === 'specific_post' && likePostsValues.postUrl) details.push('Post URL saved');
                }
                if (source === 'company_followers' && getCampaignCompanyFollowersUrl(campaign)) details.push('Company URL saved');
                const detailLabel = details.length ? ' · ' + esc(details.join(' · ')) : '';
                const idLabel = sourceId && source !== 'signal' ? ' · ID: ' + esc(sourceId) : '';

                return `<span cls="pill">Source: ${esc(sourceLabel)}${detailLabel}${idLabel}</span>`;
            }
            function parseCampaignWorkflow(campaign){
                const raw = campaign && campaign.workflow;
                if (!raw) return null;
                if (typeof raw === 'object') return raw;
                try {
                    return JSON.parse(String(raw));
                } catch (error) {
                    return null;
                }
            }
            function campaignHasWorkflowSteps(campaign){
                const workflow = parseCampaignWorkflow(campaign);
                const nodes = Array.isArray(workflow && workflow.nodes) ? workflow.nodes : [];
                return nodes.some(node => node && node.type && node.type !== 'opportunity');
            }
            function isContactComplete(contact){
                if (!contact || typeof contact !== 'object') return false;
                const completionCode = getOpportunityCompletionCode(contact);
                if (completionCode === 1) return true;
                if (completionCode === 2) return false;
                const status = String(contact.status || contact.state || contact.current_state || contact.current_node_type || '').trim().toLowerCase();
                return ['complete', 'completed', 'finished', 'done', 'ended'].includes(status);
            }
            function getCampaignCompletedCount(campaign){
                const explicit = [
                    campaign && campaign.completed_contacts,
                    campaign && campaign.completed_opportunities,
                    campaign && campaign.completed_count,
                    campaign && campaign.finished_contacts,
                    campaign && campaign.done_contacts
                ].find(value => value !== undefined && value !== null && String(value).trim() !== '');
                if (explicit !== undefined) return Number(explicit || 0);
                const attachedContacts = getAttachedContactsFromCampaign(campaign);
                if (attachedContacts.length) return attachedContacts.filter(isContactComplete).length;
                return Math.max(getCampaignConnectedCount(campaign), Number(campaign && campaign.replies || 0));
            }
            function parseMaybeJson(value){
                if (typeof value !== 'string') return value;
                const trimmed = value.trim();
                if (!trimmed || !['{', '['].includes(trimmed[0])) return value;
                try {
                    return JSON.parse(trimmed);
                } catch (error) {
                    return value;
                }
            }
            function firstDefinedValue(sources, keys){
                for (const source of sources) {
                    if (!source || typeof source !== 'object') continue;
                    for (const key of keys) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) return source[key];
                    }
                }
                return undefined;
            }
            function booleanState(value){
                if (value === undefined || value === null || value === '') return null;
                if (value === true || value === false) return value;
                if (typeof value === 'number') return value > 0;
                const normalized = String(value).trim().toLowerCase();
                if (['1', 'true', 'yes', 'y', 'on', 'active', 'enabled', 'scheduled', 'running'].includes(normalized)) return true;
                if (['0', 'false', 'no', 'n', 'off', 'inactive', 'disabled', 'not_scheduled', 'not scheduled', 'paused'].includes(normalized)) return false;
                return null;
            }
            function parseTimeToMinutes(value){
                const match = String(value || '').trim().match(/^(\d{1,2})(?::(\d{2}))?/);
                if (!match) return null;
                const hours = Number(match[1]);
                const minutes = Number(match[2] || 0);
                if (!Number.isFinite(hours) || !Number.isFinite(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) return null;
                return hours * 60 + minutes;
            }
            function formatTimeValue(value, fallback){
                const minutes = parseTimeToMinutes(value);
                if (minutes === null) return fallback;
                return String(Math.floor(minutes / 60)).padStart(2, '0') + ':' + String(minutes % 60).padStart(2, '0');
            }
            function campaignActivityPeriod(campaign){
                const raw = parseMaybeJson(firstDefinedValue([campaign || {}], [
                    'activity_period',
                    'activityPeriod',
                    'activity_periods',
                    'campaign_activity_period',
                    'schedule',
                    'timeslots',
                    'time_slots'
                ]));
                if (!raw || typeof raw !== 'object') return null;
                if (!Array.isArray(raw)) return raw;

                return raw.reduce((period, row) => {
                    const day = String(row && (row.day || row.weekday || row.name || row.key) || '').trim().toLowerCase();
                    if (day) period[day] = row;
                    return period;
                }, {});
            }
            function campaignTodayActivityRow(campaign, now = new Date()){
                const period = campaignActivityPeriod(campaign);
                if (!period) return null;
                const dayKeys = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const todayKey = dayKeys[now.getDay()];
                const mondayIndex = String((now.getDay() + 6) % 7);
                const row = period[todayKey] || period[todayKey.slice(0, 3)] || period[String(now.getDay())] || period[mondayIndex];
                if (!row || typeof row !== 'object') return null;
                const enabled = booleanState(firstDefinedValue([row], ['enabled', 'active', 'is_active', 'run', 'should_run', 'allowed']));
                return {
                    day: todayKey,
                    label: todayKey.charAt(0).toUpperCase() + todayKey.slice(1),
                    enabled: enabled !== false,
                    start: firstDefinedValue([row], ['start', 'from', 'start_time', 'startTime', 'from_time']),
                    end: firstDefinedValue([row], ['end', 'to', 'end_time', 'endTime', 'to_time'])
                };
            }
            function isWithinMinutesWindow(current, start, end){
                if (start === null || end === null) return true;
                if (start === end) return true;
                if (start < end) return current >= start && current <= end;
                return current >= start || current <= end;
            }
            function getCampaignTimeslotPause(campaign){
                const explicitPause = booleanState(firstDefinedValue([campaign || {}], [
                    'paused_by_timeslot',
                    'timeslot_paused',
                    'outside_timeslot',
                    'outside_activity_period',
                    'is_outside_timeslot'
                ]));
                if (explicitPause === true) {
                    return {
                        copy: 'Campaign is on, but paused by Timeslots outside the current activity window.',
                        footnote: 'Paused by Timeslots'
                    };
                }

                const explicitRunning = booleanState(firstDefinedValue([campaign || {}], [
                    'within_timeslot',
                    'is_within_timeslot',
                    'inside_timeslot',
                    'active_now',
                    'is_active_now',
                    'running_now'
                ]));
                if (explicitRunning === false) {
                    return {
                        copy: 'Campaign is on, but paused by Timeslots outside the current activity window.',
                        footnote: 'Paused by Timeslots'
                    };
                }

                const row = campaignTodayActivityRow(campaign);
                if (row) {
                    if (!row.enabled) {
                        return {
                            copy: `Campaign is on, but paused by Timeslots because ${row.label} is turned off.`,
                            footnote: 'Paused by Timeslots'
                        };
                    }

                    const now = new Date();
                    const currentMinutes = now.getHours() * 60 + now.getMinutes();
                    const startMinutes = parseTimeToMinutes(row.start);
                    const endMinutes = parseTimeToMinutes(row.end);
                    if (!isWithinMinutesWindow(currentMinutes, startMinutes, endMinutes)) {
                        const start = formatTimeValue(row.start, 'the start time');
                        const end = formatTimeValue(row.end, 'the end time');
                        return {
                            copy: `Campaign is on, but paused by Timeslots outside today's ${start}-${end} window.`,
                            footnote: 'Paused by Timeslots'
                        };
                    }
                }

                const activeToday = booleanState(firstDefinedValue([campaign || {}], [
                    'active_today',
                    'scheduled_today',
                    'is_scheduled_today',
                    'campaign_active_today'
                ]));
                if (activeToday === false) {
                    return {
                        copy: 'Campaign is on, but paused by Timeslots because it is not scheduled today.',
                        footnote: 'Paused by Timeslots'
                    };
                }

                return null;
            }
            function campaignHasTimeslotData(campaign){
                const value = firstDefinedValue([campaign || {}], [
                    'activity_period',
                    'activityPeriod',
                    'activity_periods',
                    'campaign_activity_period',
                    'schedule',
                    'timeslots',
                    'time_slots',
                    'active_today',
                    'scheduled_today',
                    'is_scheduled_today',
                    'campaign_active_today',
                    'within_timeslot',
                    'is_within_timeslot',
                    'inside_timeslot',
                    'active_now',
                    'is_active_now',
                    'running_now',
                    'paused_by_timeslot',
                    'timeslot_paused',
                    'outside_timeslot',
                    'outside_activity_period',
                    'is_outside_timeslot'
                ]);
                if (value === undefined || value === null) return false;
                return typeof value !== 'string' || value.trim() !== '';
            }
            function campaignSourceCanCollectOpportunities(campaign){
                const source = normalizeLeadSourceValue(campaign);
                const sourceId = campaign && campaign.lead_source_id != null && String(campaign.lead_source_id).trim() !== ''
                    ? String(campaign.lead_source_id).trim()
                    : '';
                const values = getCampaignSearchValues(campaign);
                if (source === 'pipeline') return Number(sourceId) > 0;
                if (source === 'employee_title_location') {
                    return !!values.employeeTitle && !!(values.country || values.countryCode || values.geoUrn);
                }
                if (source === 'linkedin_search_url') {
                    return !!(values.linkedinUrl || values.employeeTitle || sourceId);
                }
                if (source === 'signal') {
                    return !!sourceId || getCampaignSignalIds(campaign, sourceId).length > 0;
                }
                if (source === 'like_of_posts') {
                    const likePostsValues = getCampaignLikePostsValues(campaign);
                    if (likePostsValues.mode === 'user_posts') return !!likePostsValues.userUrl;
                    if (likePostsValues.mode === 'specific_post') return !!likePostsValues.postUrl;
                    return true;
                }
                if (source === 'company_followers') return !!getCampaignCompanyFollowersUrl(campaign);
                return false;
            }
            function isCampaignEmployeeLinkedInConnected(employee){
                if (!employee || typeof employee !== 'object') return false;
                if (Number(employee.connect) === 1) return true;
                const connector = employee.connectors && employee.connectors.linkedin;
                if (Array.isArray(connector) && connector.some(item => Number(item && item.status) === 1)) return true;
                return Number(employee.linkedin_connected || employee.is_linkedin_connected || employee.linkedin_status) === 1;
            }
            function getCampaignEmployeeDetails(employee){
                if (!employee || typeof employee !== 'object') return employee;
                const employeeId = Number(employee.id || employee.employee_id || 0);
                const fullEmployee = CAMPAIGN_EMPLOYEES.find(item => Number(item && item.id || 0) === employeeId);
                return fullEmployee ? {...employee, ...fullEmployee} : employee;
            }
            function getCampaignHealth(campaign){
                const employees = (Array.isArray(campaign && campaign.employees) ? campaign.employees : []).map(getCampaignEmployeeDetails);
                const disconnectedEmployees = employees.filter(employee => !isCampaignEmployeeLinkedInConnected(employee));
                const hasLinkedInBlocker = employees.length > 0 && disconnectedEmployees.length > 0;
                const attached = getCampaignAttachedCount(campaign);
                const messages = Number(campaign && (campaign.messages || campaign.sent_messages) || 0);
                const replies = Number(campaign && campaign.replies || 0);
                const accepted = getCampaignConnectedCount(campaign);
                const completedOpportunities = getCampaignCompletedCount(campaign);
                const completePercent = attached > 0
                    ? Math.max(0, Math.min(100, Math.round((completedOpportunities / attached) * 100)))
                    : 0;
                const checks = [
                    {key:'employees', label:'employees', done:employees.length > 0},
                    {key:'linkedin', label:'LinkedIn connection', done:employees.length > 0 && !hasLinkedInBlocker},
                    {key:'lead_source', label:'lead source', done:!!normalizeLeadSourceValue(campaign) || !!(campaign && campaign.lead_source_id)},
                    {key:'opportunities', label:'opportunities', done:attached > 0},
                    {key:'workflow', label:'workflow', done:campaignHasWorkflowSteps(campaign)}
                ];
                const completed = checks.filter(item => item.done).length;
                const missingChecks = checks.filter(item => !item.done);
                const missing = missingChecks.map(item => item.label);
                const isOn = Number(campaign && campaign.status || 0) === 0;
                const hasActivity = attached > 0 && (messages > 0 || replies > 0 || accepted > 0 || completedOpportunities > 0 || Number(campaign && campaign.visits || 0) > 0 || Number(campaign && campaign.likes || 0) > 0);
                const timeslotPause = isOn ? getCampaignTimeslotPause(campaign) : null;
                const onlyMissingOpportunities = isOn
                    && attached === 0
                    && missingChecks.length === 1
                    && missingChecks[0].key === 'opportunities';
                const isCollectingOpportunities = onlyMissingOpportunities
                    && campaignSourceCanCollectOpportunities(campaign);
                let state = 'warning';
                let label = 'Needs setup';
                let copy = missing.length
                    ? 'Missing ' + missing.join(', ') + ' before the campaign is ready.'
                    : 'Setup is complete. Waiting for campaign activity.';
                if (!isOn) {
                    state = 'off';
                    label = 'Off';
                    copy = 'Campaign is paused. Turn it on when setup and timing are ready.';
                } else if (hasLinkedInBlocker) {
                    state = 'warning';
                    label = 'Action Required';
                    const disconnectedNames = disconnectedEmployees
                        .map(employee => employee.full_name || employee.email || 'The assigned employee')
                        .join(', ');
                    copy = `LinkedIn is not connected for ${disconnectedNames}. Connect LinkedIn before Coherta can collect opportunities and start the campaign.`;
                } else if (timeslotPause) {
                    state = 'timeslot';
                    label = 'Paused by Timeslots';
                    copy = timeslotPause.copy;
                } else if (hasActivity) {
                    state = 'ready';
                    label = 'Ongoing';
                    copy = `${completePercent}% complete · ${completedOpportunities} of ${attached} opportunities complete.`;
                } else if (onlyMissingOpportunities) {
                    state = 'collecting';
                    label = 'Finalizing Setup';
                    copy = isCollectingOpportunities
                        ? 'Setup is complete. Coherta is collecting opportunities from the saved source.'
                        : 'Setup is almost complete. Coherta is finalizing opportunities. No action is needed.';
                } else if (!missing.length) {
                    state = 'ready';
                    label = 'Ready';
                }
                const displayMissing = onlyMissingOpportunities ? [] : missing;
                const progress = hasActivity && !hasLinkedInBlocker
                    ? completePercent
                    : Math.round((completed / checks.length) * 100);
                return {
                    state,
                    label,
                    copy,
                    missing: displayMissing,
                    completed,
                    total: checks.length,
                    progress,
                    progressLabel: hasActivity && !hasLinkedInBlocker ? `${completePercent}% complete` : `${completed}/${checks.length} setup`,
                    footnote: hasLinkedInBlocker ? 'Campaign blocked by missing LinkedIn connection' : (timeslotPause ? timeslotPause.footnote : (hasActivity ? `Activity: ${messages} messages, ${accepted} accepted, ${replies} replies.` : (onlyMissingOpportunities ? 'Waiting for opportunities' : 'Per-campaign status')))
                };
            }
            function formatCampaignHealth(campaign){
                const health = getCampaignHealth(campaign);
                const campaignId = Number(campaign && campaign.id || 0);
                const missingHtml = health.missing.length
                    ? `<div class="campaign-health-missing">${health.missing.map(item => `<span>${esc(item)}</span>`).join('')}</div>`
                    : '';
                return `<div class="campaign-health-card is-${esc(health.state)}">
                    <div class="campaign-health-top">
                        <span class="campaign-health-label is-${esc(health.state)}">${esc(health.label)}</span>
                        <span class="campaign-health-progress">${esc(health.progressLabel)}</span>
                    </div>
                    <div class="campaign-health-bar" aria-hidden="true"><span style="width:${health.progress}%"></span></div>
                    <p class="campaign-health-copy">${esc(health.copy)}</p>
                    ${missingHtml}
                    <div class="campaign-health-footer">
                        <span class="campaign-health-footnote">${esc(health.footnote)}</span>
                        <a class="campaign-health-link" href="campaigns-statistics.php?campaign_id=${campaignId}">Statistics</a>
                    </div>
                </div>`;
            }
            function renderCampaigns(){
                const campaignList = Array.isArray(campaigns) ? campaigns : [];
                if (!campaignList.length) {
                    campaignTableBody.innerHTML = '<tr><td colspan="6"><div class="empty-state">No campaigns found yet.</div></td></tr>';
                    updateStats(campaignList);
                    renderCampaignRampUpNotice(campaignList);
                    return;
                }
                updateStats(campaignList);
                renderCampaignRampUpNotice(campaignList);
                campaignTableBody.innerHTML = campaignList.map(campaign => {
                    const employees = Array.isArray(campaign.employees) ? campaign.employees : [];
                    const avatarHtml = employees.map(employee => employeeAvatarHtml(employee)).join('');
                    return `
        <tr>
          <td>
            <strong>${esc(campaign.title)}</strong>
            <div class="employee-stack" style="margin-top:8px;">
              <div class="avatar-stack">${avatarHtml || '<span style="color:var(--muted);font-size:13px;">No employees</span>'}</div>
              <span style="color:var(--muted);font-size:13px;">${employees.length} assigned employee${employees.length !== 1 ? 's' : ''}</span>
            </div>
            ${formatCampaignHealth(campaign)}
          </td>
          <td><button class="pill" type="button" data-open-attached-contacts data-campaign-id="${Number(campaign.id)}">${getCampaignAttachedCount(campaign)} attached</button></td>
          <td>${Number(getCampaignConnectionRequestCount(campaign) || 0)}</td>
          <td><button class="pill" type="button" data-open-connected-contacts data-campaign-id="${Number(campaign.id)}">${getCampaignConnectedCount(campaign)} connected</button></td>
          <td>${formatReplyRate(getCampaignAcceptanceRate(campaign))}</td>
          <td>
            ${formatStatusToggle(campaign)}
            ${formatLeadSource(campaign)}
            <span style="margin-left:12px;white-space:nowrap;">Campaign ID: ${esc(campaign.id)}</span>
            <div class="row-actions">
              <a class="pill" href="campaigns-workflow-builder.php?id=${Number(campaign.id)}">Workflow Builder</a>
              <button class="pill" type="button" data-open-add-leads-modal data-campaign-id="${Number(campaign.id)}" data-campaign-title="${esc(campaign.title)}">Add new leads to campaign</button>
              <a class="pill" href="campaigns-settings.php?id=${Number(campaign.id)}">Campaign Settings</a>
              <a class="pill" href="campaigns-statistics.php?campaign_id=${Number(campaign.id)}">Campaign statistics</a>
            </div>
          </td>
        </tr>`;
                }).join('');
                campaignTableBody.querySelectorAll('[data-open-add-leads-modal]').forEach(btn => btn.addEventListener('click', function(){
                    const campaign = getCampaignById(this.dataset.campaignId || '');
                    hydrateAddLeadsModal(campaign || {
                        id: this.dataset.campaignId || '',
                        title: this.dataset.campaignTitle || ''
                    });
                    openModal(addLeadsModal);
                }));
                campaignTableBody.querySelectorAll('[data-open-attached-contacts]').forEach(btn => btn.addEventListener('click', function(){
                    openAttachedContacts(this.dataset.campaignId || '');
                }));
                campaignTableBody.querySelectorAll('[data-open-connected-contacts]').forEach(btn => btn.addEventListener('click', function(){
                    openConnectedContacts(this.dataset.campaignId || '');
                }));
                campaignTableBody.querySelectorAll('[data-toggle-campaign-status]').forEach(btn => btn.addEventListener('click', async function(){
                    const campaignId = Number(this.dataset.campaignId || 0);
                    const nextStatus = Number(this.dataset.statusValue || 0);
                    const existingIndex = campaigns.findIndex(item => Number(item.id) === campaignId);
                    if (existingIndex === -1) return;
                    const previousStatus = Number(campaigns[existingIndex].status || 0);
                    if (previousStatus === nextStatus) return;

                    const buttons = campaignTableBody.querySelectorAll('[data-toggle-campaign-status][data-campaign-id="' + campaignId + '"]');
                    buttons.forEach(button => { button.disabled = true; });

                    campaigns[existingIndex].status = nextStatus;
                    renderCampaigns();

                    try {
                        const response = await api.request('/campaigns', {
                            method: 'PATCH',
                            body: JSON.stringify({id: campaignId, status: nextStatus})
                        });
                        if (response && response.campaign && Number(response.campaign.id || 0) === campaignId) {
                            campaigns[existingIndex] = Object.assign({}, campaigns[existingIndex], response.campaign);
                            renderCampaigns();
                        }
                        showToast('Campaign ' + (nextStatus === 0 ? 'turned on' : 'turned off'));
                    } catch (error) {
                        campaigns[existingIndex].status = previousStatus;
                        renderCampaigns();
                        alert(error.message);
                    }
                }));
            }
            async function loadCampaigns(){
                campaignTableBody.innerHTML = '<tr><td colspan="6"><div class="empty-state">Loading campaigns...</div></td></tr>';
                try {
                    const payload = await api.request('/campaigns');
                    const normalizedPayload = normalizeDashboardPayload(payload);
                    campaigns = normalizedPayload.campaigns;
                    campaignDashboardStats = normalizedPayload.stats;
                    renderCampaigns();
                    renderDuplicateCampaigns();
                    refreshCampaignTimeslotSettings();
                    refreshConnectedContactsFromEndpoint();
                } catch (error) {
                    campaignDashboardStats = null;
                    campaignTableBody.innerHTML = `<tr><td colspan="6"><div class="empty-state">${esc(error.message)}</div></td></tr>`;
                    if (campaignRampUpNotice) campaignRampUpNotice.innerHTML = '';
                    updateStats([]);
                }
            }
            function openLeadModalFromUrl(){
                const params = new URLSearchParams(window.location.search || '');
                const mode = params.get('lead_modal') || '';
                const campaignId = params.get('campaign_id') || '';
                if (!['manual', 'source'].includes(mode) || !campaignId) return;
                const campaign = getCampaignById(campaignId);
                if (!campaign) return;
                hydrateAddLeadsModal(campaign);
                if (mode === 'manual') {
                    sourceType.value = 'manual';
                    syncSourceSections();
                    document.getElementById('addLeadsModalTitle').textContent = 'Add person';
                    manualUrls?.focus();
                } else {
                    document.getElementById('addLeadsModalTitle').textContent = 'Change Source';
                }
                openModal(addLeadsModal);
            }
            function openPlaybookInstallFromUrl(){
                const params = new URLSearchParams(window.location.search || '');
                const slug = params.get('playbook') || '';
                if (!slug) return;
                const playbook = getPlaybookBySlug(slug);
                resetCreateCampaignModal();
                setCreateMode('playbook');
                if (playbook) {
                    selectPlaybook(playbook.slug);
                    titleInput.value = playbook.heading || playbook.title || '';
                }
                openModal(createModal);
            }
            downloadCampaignTableCsvBtn?.addEventListener('click', downloadCampaignTableCsv);
            document.querySelectorAll('[data-open-create-modal]').forEach(btn => btn.addEventListener('click', function(){
                resetCreateCampaignModal();
                openModal(createModal);
            }));
            document.querySelectorAll('[data-close-create-modal]').forEach(btn => btn.addEventListener('click', function(){ closeModal(createModal); }));
            campaignGoalButtons.forEach(button => button.addEventListener('click', function(){ setCreateMode(this.dataset.campaignGoal || ''); }));
            advancedCreateModeButtons.forEach(button => button.addEventListener('click', function(){
                advancedCreateMode = this.dataset.advancedCreateMode || '';
                campaignWizardStep = advancedCreateMode === 'duplicate' ? 4 : 2;
                syncCreateModeUI();
                if (advancedCreateMode === 'duplicate') renderDuplicateCampaigns();
            }));
            document.querySelectorAll('input[name="campaign_employees[]"]').forEach(input => input.addEventListener('change', function(){
                if (!this.checked || campaignWizardStep !== 2 || !campaignCreateMode) return;
                campaignWizardStep = campaignCreateMode === 'advanced' ? 5 : 3;
                syncCreateModeUI();
            }));
            campaignWizardBackBtn?.addEventListener('click', function(){
                if (campaignWizardStep === 5) campaignWizardStep = campaignCreateMode === 'advanced' ? (advancedCreateMode === 'duplicate' ? 4 : 2) : 3;
                else if (campaignWizardStep === 4 || (campaignWizardStep === 2 && campaignCreateMode === 'advanced')) campaignWizardStep = 1;
                else if (campaignWizardStep === 3) campaignWizardStep = 2;
                else if (campaignWizardStep === 2 || campaignWizardStep === 1) campaignWizardStep = 0;
                if (campaignWizardStep === 0) campaignCreateMode = '';
                syncCreateModeUI();
            });
            changeCampaignCreateModeBtn?.addEventListener('click', function(){
                campaignCreateMode = '';
                selectedDuplicateCampaignId = null;
                selectedPlaybookSlug = '';
                syncCreateModeUI();
                renderDuplicateCampaigns();
                renderPlaybooks();
            });
            duplicateCampaignSearch?.addEventListener('input', renderDuplicateCampaigns);
            playbookList?.querySelectorAll('[data-select-playbook]').forEach(card => {
                card.addEventListener('click', function(){
                    selectPlaybook(this.dataset.playbookSlug || '');
                });
            });
            saveCampaignBtn?.addEventListener('click', async function(){
                const employeeIds = Array.from(document.querySelectorAll('input[name="campaign_employees[]"]:checked')).map(el => Number(el.value));
                const title = titleInput.value.trim();
                if (campaignWizardStep === 0) { alert('Choose what you want to achieve.'); return; }
                if (campaignWizardStep === 1) {
                    if (!advancedCreateMode) { alert('Choose Create new Campaign or Duplicate Campaign.'); return; }
                    campaignWizardStep = advancedCreateMode === 'duplicate' ? 4 : 2; syncCreateModeUI(); return;
                }
                if (campaignWizardStep === 2) {
                    if (employeeIds.length !== 1) { alert('Choose one employee for this campaign.'); return; }
                    campaignWizardStep = campaignCreateMode === 'advanced' ? 5 : 3; syncCreateModeUI(); return;
                }
                if (campaignWizardStep === 3) {
                    if (!String(campaignDecisionMakerTitle?.value || '').trim()) { alert('Enter the job title you want to reach.'); return; }
                    if (!campaignAudienceCountry?.value) { alert('Choose a country.'); return; }
                    if (campaignCreateMode === 'sales' && !String(campaignLeadQualification?.value || '').trim()) { alert('Describe what qualifies a lead as good.'); return; }
                    campaignWizardStep = 5; syncCreateModeUI(); titleInput.focus(); return;
                }
                if (campaignWizardStep === 4) {
                    if (!selectedDuplicateCampaignId) { alert('Choose which campaign you want to duplicate.'); return; }
                    const sourceCampaign = campaigns.find(item => Number(item.id) === Number(selectedDuplicateCampaignId));
                    titleInput.value = 'Copy of ' + String(sourceCampaign?.title || 'Campaign');
                    campaignWizardStep = 5; syncCreateModeUI(); return;
                }
                if (!title) { alert('Enter a campaign title.'); return; }
                const selectedMode = campaignCreateMode === 'advanced' && advancedCreateMode === 'duplicate' ? 'duplicate' : campaignCreateMode;
                const loadingText = getCreateCampaignLoadingText(selectedMode);
                const workflow = buildGuidedCampaignWorkflow(selectedMode, campaignLeadQualification?.value || '');
                const payload = selectedMode === 'duplicate' ? {title, mode:'duplicate', duplicate_campaign_id:Number(selectedDuplicateCampaignId)} : {title, mode:'scratch', employee_ids:employeeIds};
                if (workflow) payload.workflow = JSON.stringify(workflow);
                try {
                    setCreateCampaignLoading(true, loadingText);
                    const response = await api.request('/campaigns', {method:'POST', body: JSON.stringify(payload)});
                    if (selectedMode !== 'duplicate') await persistGuidedCampaign(getCampaignIdFromResponse(response), selectedMode, workflow);
                    resetCreateCampaignModal();
                    closeModal(createModal);
                    showToast(selectedMode === 'duplicate' ? 'Campaign duplicated' : 'Campaign created');
                    await loadCampaigns();
                } catch (error) {
                    alert(error.message);
                } finally {
                    setCreateCampaignLoading(false);
                }
            });
            document.querySelectorAll('[data-close-add-leads-modal]').forEach(btn => btn.addEventListener('click', function(){ closeModal(addLeadsModal); }));
            document.querySelectorAll('[data-close-attached-contacts-modal]').forEach(btn => btn.addEventListener('click', function(){ closeModal(attachedContactsModal); }));
            attachedContactsTableBody.addEventListener('click', function(event){
                const button = event.target.closest('[data-screening-contact-id]');
                if (!button) return;
                const contact = attachedContactsCurrentList.find(item => getContactIdentifier(item) === button.dataset.screeningContactId);
                if (contact) window.CohertaScreeningResults.open(contact, getContactFullName(contact));
            });
            attachedContactsOpportunityFilter?.addEventListener('change', function(){
                renderCurrentAttachedContacts();
            });
            sourceType?.addEventListener('change', function(){
                resetLeadSearchState();
                syncSourceSections();
            });
            pipelineInput?.addEventListener('change', updateAddLeadsButtonState);
            signalAllInput?.addEventListener('change', function(){
                signalInputs.forEach(input => {
                    input.checked = this.checked;
                });
                syncSignalSelectionState();
            });
            signalInputs.forEach(input => input.addEventListener('change', syncSignalSelectionState));
            leadLinkedinSearchUrlInput?.addEventListener('input', updateLinkedinUrlPreview);
            leadLikePostsModeInput?.addEventListener('change', syncLikePostsFields);
            leadLikePostsUserUrlInput?.addEventListener('input', updateAddLeadsButtonState);
            leadLikePostsPostUrlInput?.addEventListener('input', updateAddLeadsButtonState);
            leadCompanyFollowersUrlInput?.addEventListener('input', updateAddLeadsButtonState);
            csvFileInput?.addEventListener('change', async function(){
                const file = this.files && this.files[0];
                if (!file) return;
                const text = await file.text();
                const unique = extractLinkedinProfileUrls(text);
                csvUrls.value = unique.join('\n');
                csvSummary.textContent = unique.length ? unique.length + ' LinkedIn profile URL\'s found in the CSV-file.' : 'No LinkedIn profile URL\'s found in the CSV-file.';
            });
            manualUrls?.addEventListener('input', function(){
                const urls = getLeadTextareaUrls(this.value);
                manualSummary.textContent = urls.length ? urls.length + ' LinkedIn profile URL\'s ready to add.' : 'Paste LinkedIn profile URLs into the field below.';
            });
            saveAddLeadsBtn?.addEventListener('click', async function(){
                const type = sourceType.value;
                const selectedSignalIds = type === 'signal' ? getSelectedSignalIds() : [];
                if (isLeadSearchSource(type)) {
                    if (!leadSearchState.isActive) {
                        await startLeadSearch(this);
                    }
                    return;
                }
                const payload = {action:'add_leads', campaign_id:Number(campaignIdInput.value), source_type:type};
                payload.allow_existing_in_other_campaigns = document.getElementById('addMissingCampaignOpportunities')?.checked ? 1 : 0;
                if (type === 'connections') payload.connections_mode = connectionsModeInput?.value || 'not_yet_accepted';
                if (type === 'csv') {
                    const urls = getLeadTextareaUrls(csvUrls.value);
                    if (!urls.length) { alert('Upload a CSV-file with at least one LinkedIn URL.'); return; }
                    payload.linkedin_urls = urls;
                    payload.csv_filename = csvFileInput.files && csvFileInput.files[0] ? csvFileInput.files[0].name : null;
                }
                if (type === 'manual') {
                    const manualText = manualUrls ? manualUrls.value : '';
                    const urls = getLeadTextareaUrls(manualText);
                    if (!urls.length) { alert('Paste at least one LinkedIn profile URL.'); return; }
                    payload.linkedin_urls = urls;
                    payload.manual_leads = manualText.trim();
                }
                if (type === 'pipeline') {
                    const pipelineId = getSelectedLeadListId();
                    if (!pipelineId) { alert('Choose a lead list.'); return; }
                    payload.pipeline_id = pipelineId;
                }
                if (type === 'signal') {
                    if (!selectedSignalIds.length) { alert('Choose at least one signal.'); return; }
                    payload.signal_id = selectedSignalIds.join(',');
                }
                try {
                    this.classList.add('is-loading');
                    const response = await api.request('/campaigns', {method:'POST', body: JSON.stringify(payload)});
                    const campaignId = Number(campaignIdInput.value);
                    const existingIndex = campaigns.findIndex(item => Number(item.id) === campaignId);
                    if (existingIndex !== -1) {
                        const currentCampaign = campaigns[existingIndex] || {};
                        const nextCampaign = Object.assign({}, currentCampaign);
                        const selectedSourceType = String(type || '').trim();
                        const selectedSourceId = selectedSourceType === 'pipeline'
                            ? (pipelineInput.value.trim() || '')
                            : selectedSourceType === 'signal'
                                ? selectedSignalIds.join(',')
                                : '';
                        if (response && response.campaign && Number(response.campaign.id || 0) === campaignId) {
                            campaigns[existingIndex] = Object.assign(nextCampaign, response.campaign);
                        } else {
                            if (response && response.attached_contacts != null) {
                                nextCampaign.attached_contacts = Number(response.attached_contacts);
                            } else if (response && response.attached_count != null) {
                                nextCampaign.attached_contacts = Number(currentCampaign.attached_contacts || 0) + Number(response.attached_count || 0);
                            }
                            if (response && response.removed_contacts != null) {
                                nextCampaign.removed_contacts = Number(response.removed_contacts);
                            } else if (response && response.dismissed_count != null) {
                                nextCampaign.removed_contacts = Number(currentCampaign.removed_contacts || 0) + Number(response.dismissed_count || 0);
                            }
                            nextCampaign.lead_source = selectedSourceType;
                            if (selectedSourceType === 'connections') nextCampaign.connections_mode = payload.connections_mode;
                            nextCampaign.lead_source_id = selectedSourceType === 'signal'
                                ? (selectedSourceId || null)
                                : (selectedSourceId !== '' ? Number(selectedSourceId) : null);
                            if (selectedSourceType === 'signal') {
                                nextCampaign.lead_source_signal_ids = selectedSignalIds;
                                nextCampaign.signal_ids = selectedSignalIds;
                            }
                            campaigns[existingIndex] = nextCampaign;
                        }
                        if (campaigns[existingIndex]) {
                            campaigns[existingIndex].lead_source = selectedSourceType;
                            if (selectedSourceType === 'connections') campaigns[existingIndex].connections_mode = payload.connections_mode;
                            campaigns[existingIndex].lead_source_id = selectedSourceType === 'signal'
                                ? (selectedSourceId || null)
                                : (selectedSourceId !== '' ? Number(selectedSourceId) : null);
                            if (selectedSourceType === 'signal') {
                                campaigns[existingIndex].lead_source_signal_ids = selectedSignalIds;
                                campaigns[existingIndex].signal_ids = selectedSignalIds;
                            }
                        }
                        renderCampaigns();
                        updateLeadSourceSummary(campaigns[existingIndex]);
                    } else {
                        await loadCampaigns();
                    }
                    closeModal(addLeadsModal);
                    const toastAttached = response && response.attached_contacts != null
                        ? Number(response.attached_contacts)
                        : (response && response.attached_count != null ? Number(response.attached_count) : null);
                    const toastRemoved = response && response.removed_contacts != null
                        ? Number(response.removed_contacts)
                        : (response && response.dismissed_count != null ? Number(response.dismissed_count) : null);
                    if (toastAttached != null || toastRemoved != null) {
                        showToast('Campaign updated · Attached: ' + Number(toastAttached ?? 0) + ' · Removed: ' + Number(toastRemoved ?? 0));
                    } else if (type === 'signal' && selectedSignalIds.length > 1) {
                        showToast(selectedSignalIds.length + ' signals added to campaign');
                    } else {
                        showToast('Lead source added');
                    }
                } catch (error) {
                    alert(error.message);
                } finally {
                    this.classList.remove('is-loading');
                }
            });
            [addLeadsModal, attachedContactsModal].forEach(modal => modal?.addEventListener('click', function(event){ if (event.target === modal) closeModal(modal); }));
            populateCountrySelect();
            updateLinkedinUrlPreview();
            syncSourceSections();
            syncCreateModeUI();
            loadCampaigns().then(openLeadModalFromUrl);
            openPlaybookInstallFromUrl();
        })();
    </script>
    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 2147483647 !important;
            isolation: isolate;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.45);
            overflow-y: auto;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-card {
            width: min(720px, calc(100vw - 32px));
            max-height: calc(100vh - 48px);
            margin: auto 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #fff;
            border-radius: 28px;
        }

        .modal-card.modal-card-lg {
            width: min(1100px, calc(100vw - 32px));
        }

        .modal-head,
        .modal-actions {
            flex: 0 0 auto;
        }

        .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding-right: 4px;
        }

        .modal-body .table-shell {
            overflow-x: auto;
        }
    </style>
<?php include('../partial/footer.php'); include('../partial/scripts.php'); include('../partial/footer-end.php'); ?>
