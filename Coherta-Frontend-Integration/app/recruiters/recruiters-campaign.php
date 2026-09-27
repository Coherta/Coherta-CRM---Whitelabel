<?php
require_once "../base.php";
require_once "./recruiters-data.php";

$campaignId = isset($_GET["id"]) ? (string)$_GET["id"] : "";
$campaign = $campaignId !== "" ? recruiter_api_campaign($client ?? null, $campaignId) : null;
$selectedStage = isset($_GET["stage"]) && ctype_digit((string)$_GET["stage"]) ? (int)$_GET["stage"] : 0;
if (!isset($recruiterStages[$selectedStage]) && $selectedStage !== 0) $selectedStage = 0;
$view = isset($_GET["view"]) && in_array($_GET["view"], ["top5", "top10", "all"], true) ? (string)$_GET["view"] : "top5";
$stageCounts = $campaign ? recruiter_stage_counts($campaign) : [1 => 0, 2 => 0, 3 => 0, 4 => 0];
$activeCandidates = $campaign ? recruiter_active_candidates($campaign) : [];
$visibleStageCounts = $stageCounts;
if ($campaign) {
    $visibleStageCounts[2] = count(recruiter_filter_narrowing_candidates(
        array_values(array_filter($activeCandidates, fn($candidate) => (int)$candidate["stage"] === 2)),
        $campaign
    ));
}
$allDataCandidates = $campaign ? array_merge(
    recruiter_candidates_for_stage($campaign, 1),
    recruiter_candidates_for_stage($campaign, 2),
    recruiter_candidates_for_stage($campaign, 3),
    recruiter_candidates_for_stage($campaign, 4)
) : [];
$allCandidates = $campaign ? recruiter_candidates_for_stage($campaign, $selectedStage) : [];
$candidates = $campaign ? array_slice($allCandidates, 0, $view === "top5" ? 5 : ($view === "top10" ? 10 : count($allCandidates))) : [];

$PageTitle = $campaign ? $campaign["title"] : "Recruiter Campaign";
$sidebar_close = true;

include('../partial/header.php');
?>
<link rel="stylesheet" type="text/css" href="/app/assets/css/recruiters.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/recruiters.css'); ?>">
<?php include(__DIR__ . '/recruiters-theme.php'); ?>
<?php include('../partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('../partial/sidebar.php'); ?>
        <div class="page-body recruiters-page-body no-top-margin">
            <div class="app-shell recruiters-app-shell">
                <main class="content recruiters-content"<?php if ($campaign): ?> data-campaign-id="<?php echo recruiter_h($campaign["campaign_id"] ?? ""); ?>" data-recruiter-campaign-id="<?php echo recruiter_h($campaign["recruiter_campaign_id"] ?? $campaign["id"]); ?>"<?php endif; ?>>
                    <?php if (!$campaign): ?>
                    <section class="panel">
                        <div class="panel-inner">
                            <div class="empty-state">
                                <strong>No recruiter campaign selected</strong>
                                <p>There is no campaign to show yet.</p>
                                <div class="hero-actions">
                                    <a class="btn btn-light" href="recruiters.php">Back to overview</a>
                                    <a class="btn btn-success" href="recruiters-create.php">New campaign</a>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php else: ?>
                    <section class="panel hero recruiters-hero-panel">
                        <div class="panel-inner">
                            <div class="hero-grid">
                                <div>
                                    <h2><?php echo recruiter_h($campaign["title"]); ?></h2>
                                    <p><?php echo recruiter_h($campaign["owner"]); ?> · Created <?php echo recruiter_h($campaign["created"]); ?> · <?php echo recruiter_h(implode(", ", $campaign["terms"])); ?></p>
                                    <div class="hero-actions">
                                        <a class="btn btn-light" href="recruiters.php">Back to overview</a>
                                        <a class="btn btn-light" href="recruiters-settings.php?id=<?php echo urlencode($campaign["id"]); ?>">Settings</a>
                                        <a class="btn btn-success" href="recruiters-create.php">New campaign</a>
                                    </div>
                                </div>
                                <div class="hero-stats">
                                    <div class="hero-stat"><strong><?php echo count($activeCandidates); ?></strong><span>Active candidates</span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$stageCounts[3]; ?></strong><span>Messaged</span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$stageCounts[4]; ?></strong><span>Concluded</span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$campaign["progress"]; ?>%</strong><span>Progress</span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div>
                                    <h3>Candidate journey</h3>
                                    <p>Candidates move through Identify, Connect, Message, and Conclude. Each active stage is tied to its own campaign action.</p>
                                </div>
                                <div class="count-pill"><?php echo count($candidates); ?> candidates shown</div>
                            </div>

                            <div class="recruiter-flow" aria-label="Recruiter campaign flow">
                                <?php $flowIndex = 0; foreach ($recruiterStages as $stageNumber => $stage): $flowIndex++; ?>
                                    <?php $isPrimary = (int)$campaign["primary_stage"] === (int)$stageNumber; ?>
                                    <div class="flow-step <?php echo $isPrimary ? "is-current" : ""; ?>">
                                        <span class="flow-number"><?php echo (int)$stageNumber; ?></span>
                                        <strong><?php echo recruiter_h($stage["title"]); ?></strong>
                                        <small><?php echo recruiter_h($stage["short"]); ?> · <?php echo (int)$visibleStageCounts[$stageNumber]; ?> candidates</small>
                                    </div>
                                    <?php if ($flowIndex < count($recruiterStages)): ?><span class="flow-arrow">→</span><?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <div class="stage-overview compact-stage-overview">
                                <?php foreach ($recruiterStages as $stageNumber => $stage): ?>
                                    <?php
                                    $step = $campaign["steps"][$stageNumber] ?? ["started_at" => null, "status" => "not_started", "settings" => []];
                                    $settings = $step["settings"] ?? [];
                                    $isStepRunning = ($step["status"] ?? "") === "active";
                                    $isPrimary = (int)$campaign["primary_stage"] === (int)$stageNumber;
                                    $stageCandidates = array_values(array_filter($activeCandidates, fn($candidate) => (int)$candidate["stage"] === (int)$stageNumber));
                                    if ((int)$stageNumber === 2) {
                                        $stageCandidates = recruiter_filter_narrowing_candidates($stageCandidates, $campaign);
                                    }
                                    $stageCandidates = recruiter_rank_candidates($stageCandidates);
                                    ?>
                                    <div class="stage-mini stage-card <?php echo $isPrimary ? "is-primary" : ""; ?>" data-step-card="<?php echo (int)$stageNumber; ?>">
                                        <div class="stage-card-head">
                                            <div>
                                                <strong><?php echo (int)$visibleStageCounts[$stageNumber]; ?></strong>
                                                <span><?php echo (int)$stageNumber; ?>. <?php echo recruiter_h($stage["title"]); ?></span>
                                            </div>
                                            <em><?php echo recruiter_h((int)$stageNumber === 4 ? "Review" : ($settings["campaign_action_label"] ?? $stage["short"])); ?></em>
                                        </div>
                                        <p class="stage-description"><?php echo recruiter_h($stage["description"]); ?></p>
                                        <div class="stage-filter-summary">
                                            <span><?php echo recruiter_h($settings["filter_label"] ?? "Filtering"); ?></span>
                                            <?php if ((int)$stageNumber === 2): ?>
                                                <strong data-stage-match-threshold="<?php echo (int)$stageNumber; ?>"><?php echo (int)($settings["match_threshold"] ?? 0); ?>% match</strong>
                                            <?php endif; ?>
                                            <strong>Max <?php echo (int)($settings["max_candidates"] ?? 0); ?></strong>
                                        </div>
                                        <div class="stage-started" data-started-label>
                                            <?php echo $isStepRunning ? "Started: " . recruiter_h($step["started_at"]) : "Status: " . recruiter_h(ucfirst(str_replace("_", " ", (string)($step["status"] ?? "not_started")))); ?>
                                        </div>
                                        <div class="stage-actions">
                                            <?php if ($stageNumber === 4): ?>
                                                <button class="btn btn-light btn-sm" type="button" disabled>Review replies</button>
                                            <?php elseif ($isStepRunning): ?>
                                                <button class="btn btn-light btn-sm js-step-toggle" type="button" data-action="stop" data-stage="<?php echo (int)$stageNumber; ?>">Stop step</button>
                                            <?php elseif (in_array((int)$stageNumber, [1, 2, 3], true)): ?>
                                                <button class="btn btn-success btn-sm js-step-toggle" type="button" data-action="start" data-stage="<?php echo (int)$stageNumber; ?>">Start step</button>
                                            <?php else: ?>
                                                <button class="btn btn-light btn-sm" type="button" disabled>Not started</button>
                                            <?php endif; ?>
                                            <?php if ((int)$stageNumber !== 4): ?>
                                                <a class="btn btn-light btn-sm" href="recruiters-settings.php?id=<?php echo urlencode($campaign["id"]); ?>&step=<?php echo (int)$stageNumber; ?>">Edit step</a>
                                            <?php endif; ?>
                                            <button class="btn btn-light btn-sm js-open-stage-modal" type="button" data-stage="<?php echo (int)$stageNumber; ?>"><?php echo (int)$stageNumber === 4 ? "Review candidates" : "View candidates"; ?></button>
                                        </div>
                                    </div>

                                    <div class="recruiter-modal" id="stageModal<?php echo (int)$stageNumber; ?>" aria-hidden="true">
                                        <div class="recruiter-modal-card" role="dialog" aria-modal="true" aria-labelledby="stageModalTitle<?php echo (int)$stageNumber; ?>">
                                            <div class="recruiter-modal-head">
                                                <div>
                                                    <h3 id="stageModalTitle<?php echo (int)$stageNumber; ?>"><?php echo (int)$stageNumber; ?>. <?php echo recruiter_h($stage["title"]); ?></h3>
                                                    <p><?php echo count($stageCandidates); ?> active candidates are in this step.</p>
                                                </div>
                                                <button class="modal-close js-close-stage-modal" type="button" data-stage="<?php echo (int)$stageNumber; ?>">×</button>
                                            </div>
                                            <div class="recruiter-modal-body">
                                                <div class="table-shell">
                                                    <table class="data-table recruiters-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Candidate</th>
                                                                <th>Title</th>
                                                                <th>Company</th>
                                                                <th>Match</th>
                                                                <th>Time in step</th>
                                                                <th>LinkedIn</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($stageCandidates as $candidate): ?>
                                                                <tr data-candidate-row="<?php echo recruiter_h($candidate["id"]); ?>">
                                                                    <td>
                                                                        <div class="candidate-table-person">
                                                                            <img class="candidate-avatar candidate-avatar-sm" src="<?php echo recruiter_h($candidate["image"] ?: "/app/assets/images/user/user.png"); ?>" alt="<?php echo recruiter_h($candidate["name"]); ?>">
                                                                            <div>
                                                                                <strong><?php echo recruiter_h($candidate["name"]); ?></strong>
                                                                                <div class="recruiters-muted"><?php echo recruiter_h($candidate["location"]); ?></div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td><?php echo recruiter_h($candidate["title"]); ?></td>
                                                                    <td><?php echo recruiter_h($candidate["company"]); ?></td>
                                                                    <td><?php echo (int)$candidate["fit"]; ?>%</td>
                                                                    <td><?php echo recruiter_h($candidate["time"]); ?></td>
                                                                    <td>
                                                                        <a class="linkedin-banner" href="<?php echo recruiter_h($candidate["linkedin"]); ?>" target="_blank" rel="noopener">LinkedIn</a>
                                                                    </td>
                                                                    <td class="table-actions">
                                                                        <button class="btn btn-light btn-xs js-deactivate-candidate" type="button" data-candidate-id="<?php echo recruiter_h($candidate["id"]); ?>" data-stage="<?php echo (int)$stageNumber; ?>">Withdraw candidate</button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div>
                                    <h3>Candidate overview</h3>
                                    <p>The strongest matches are shown first. Use Top 5, Top 10, or all candidates to work with the right slice.</p>
                                </div>
                                <div class="count-pill" data-candidate-count><?php echo count($candidates); ?> candidates shown</div>
                            </div>

                            <div class="candidate-toolbar">
                                <div class="stage-filter" data-stage-filter>
                                    <a class="<?php echo $selectedStage === 0 ? "is-active" : ""; ?>" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>&view=<?php echo urlencode($view); ?>" data-stage-value="0">All steps</a>
                                    <?php foreach ($recruiterStages as $stageNumber => $stage): ?>
                                        <a class="<?php echo $selectedStage === $stageNumber ? "is-active" : ""; ?>" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>&stage=<?php echo (int)$stageNumber; ?>&view=<?php echo urlencode($view); ?>" data-stage-value="<?php echo (int)$stageNumber; ?>">
                                            <?php echo (int)$stageNumber; ?>. <?php echo recruiter_h($stage["short"]); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="view-tabs" data-candidate-view-tabs>
                                    <a class="<?php echo $view === "top5" ? "is-active" : ""; ?>" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>&stage=<?php echo (int)$selectedStage; ?>&view=top5" data-view-value="top5" data-view-limit="5">Top 5</a>
                                    <a class="<?php echo $view === "top10" ? "is-active" : ""; ?>" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>&stage=<?php echo (int)$selectedStage; ?>&view=top10" data-view-value="top10" data-view-limit="10">Top 10</a>
                                    <a class="<?php echo $view === "all" ? "is-active" : ""; ?>" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>&stage=<?php echo (int)$selectedStage; ?>&view=all" data-view-value="all" data-view-limit="<?php echo count($allCandidates); ?>">All candidates</a>
                                </div>
                            </div>

                            <div class="candidate-card-grid <?php echo $view === "all" ? "is-all" : ""; ?>" data-candidate-grid data-selected-stage="<?php echo (int)$selectedStage; ?>" data-current-view="<?php echo recruiter_h($view); ?>">
                                <?php foreach ($candidates as $candidate): ?>
                                    <?php $candidateStage = (int)$candidate["stage"]; ?>
                                    <article class="candidate-card" data-candidate-card="<?php echo recruiter_h($candidate["id"]); ?>">
                                        <div class="candidate-card-top">
                                            <img class="candidate-avatar" src="<?php echo recruiter_h($candidate["image"] ?: "/app/assets/images/user/user.png"); ?>" alt="<?php echo recruiter_h($candidate["name"]); ?>">
                                            <div class="candidate-heading">
                                                <div class="candidate-name-line">
                                                    <strong><?php echo recruiter_h($candidate["name"]); ?></strong>
                                                    <span class="pill"><?php echo $candidateStage; ?>. <?php echo recruiter_h($recruiterStages[$candidateStage]["short"]); ?></span>
                                                </div>
                                                <span><?php echo recruiter_h($candidate["title"]); ?></span>
                                            </div>
                                            <div class="match-badge"><?php echo (int)$candidate["fit"]; ?>%</div>
                                        </div>
                                        <div class="candidate-meta-line"><?php echo recruiter_h($candidate["company"]); ?> · <?php echo recruiter_h($candidate["location"]); ?></div>
                                        <p><?php echo recruiter_h($candidate["note"]); ?></p>
                                        <div class="candidate-actions">
                                            <a class="btn btn-light btn-xs candidate-action-button" href="<?php echo recruiter_h($candidate["linkedin"]); ?>" target="_blank" rel="noopener">LinkedIn</a>
                                            <?php if (!empty($candidate["phone"])): ?>
                                                <a class="btn btn-light btn-xs candidate-action-button" href="tel:<?php echo recruiter_h(preg_replace('/[^\d+]/', '', (string)$candidate["phone"])); ?>"><?php echo recruiter_h($candidate["phone"]); ?></a>
                                            <?php else: ?>
                                                <button class="btn btn-light btn-xs js-reveal-phone" type="button" data-candidate-id="<?php echo recruiter_h($candidate["id"]); ?>" data-participant-id="<?php echo recruiter_h($candidate["participant_id"] ?? ""); ?>" data-participant-linkedin-id="<?php echo recruiter_h($candidate["participant_linkedin_id"] ?? ""); ?>" data-name="<?php echo recruiter_h($candidate["name"]); ?>" data-title="<?php echo recruiter_h($candidate["title"]); ?>" data-company="<?php echo recruiter_h($candidate["company"]); ?>" data-linkedin="<?php echo recruiter_h($candidate["linkedin"]); ?>" data-phone="">Get phone</button>
                                            <?php endif; ?>
                                            <?php $cvEnabled = $candidateStage === 4 && !empty($candidate["cv_available"]); ?>
                                            <a class="btn btn-light btn-xs <?php echo $cvEnabled ? "" : "is-disabled"; ?>" href="<?php echo $cvEnabled ? recruiter_h($candidate["cv"]) : "#"; ?>" <?php echo $cvEnabled ? 'target="_blank" rel="noopener"' : 'aria-disabled="true" tabindex="-1"'; ?>>CV</a>
                                            <button class="btn btn-light btn-xs js-deactivate-candidate" type="button" data-candidate-id="<?php echo recruiter_h($candidate["id"]); ?>" data-stage="<?php echo $candidateStage; ?>">Withdraw candidate</button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <script type="application/json" id="recruiterCandidateData"><?php echo json_encode([
                                "stages" => array_map(fn($stage) => ["title" => $stage["title"], "short" => $stage["short"]], $recruiterStages),
                                "candidates" => $allDataCandidates,
                            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
                        </div>
                    </section>

                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>
</div>

<?php include('../partial/footer.php'); ?>
<?php include('../partial/scripts.php'); ?>
<script>
window.RECRUITER_API_ENDPOINT = 'https://api.coherta.com/recruiters';
window.RECRUITER_BACKEND_ENDPOINT = 'https://api.coherta.com/recruiters';
window.fetch = (function (nativeFetch) {
    return function (input, init) {
        var url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
        if (url && /\/?recruiters-backend\.php/i.test(url)) {
            input = 'https://api.coherta.com/recruiters';
        }
        return nativeFetch.call(this, input, init);
    };
})(window.fetch);
</script>
<script src="/app/assets/js/recruiters.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/recruiters.js'); ?>"></script>
<?php include('../partial/footer-end.php'); ?>
