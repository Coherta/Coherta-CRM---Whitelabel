<?php
require_once "../base.php";
require_once "./recruiters-data.php";

$recruiterCampaigns = recruiter_api_campaigns($client ?? null);
$PageTitle = "Recruiter Campaigns";
$sidebar_close = true;

$totalCandidates = array_sum(array_map(fn($campaign) => (int)($campaign["_active_candidates"] ?? count(recruiter_active_candidates($campaign))), $recruiterCampaigns));
$answeredCandidates = array_sum(array_map(fn($campaign) => recruiter_stage_counts($campaign)[4], $recruiterCampaigns));

include('../partial/header.php');
?>
<link rel="stylesheet" type="text/css" href="/app/assets/css/recruiters.css">
<?php include(__DIR__ . '/recruiters-theme.php'); ?>
<?php include('../partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('../partial/sidebar.php'); ?>
        <div class="page-body recruiters-page-body no-top-margin">
            <div class="app-shell recruiters-app-shell">
                <main class="content recruiters-content">
                    <section class="panel hero recruiters-hero-panel">
                        <div class="panel-inner">
                            <div class="hero-grid">
                                <div>
                                    <h2>Recruiter Campaigns</h2>
                                    <p>Manage recruiter campaigns across Identify, Connect, Message, and Conclude.</p>
                                    <div class="hero-actions">
                                        <a class="btn btn-success" href="recruiters-create.php">New Recruiter Campaign</a>
                                    </div>
                                </div>
                                <div class="hero-stats">
                                    <div class="hero-stat"><strong><?php echo count($recruiterCampaigns); ?></strong><span>Campaigns</span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$totalCandidates; ?></strong><span>Active candidates</span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$answeredCandidates; ?></strong><span>Concluded</span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div>
                                    <h3>Active campaigns</h3>
                                    <p>Scan progress, candidate volume, and the current stage of every recruiter campaign.</p>
                                </div>
                                <div class="count-pill"><?php echo count($recruiterCampaigns); ?> active campaigns</div>
                            </div>

                            <div class="campaign-grid campaign-grid-thirds">
                                <?php if (empty($recruiterCampaigns)): ?>
                                    <div class="empty-state">
                                        <strong>No recruiter campaigns yet</strong>
                                        <p>Create the first campaign to start building the candidate journey.</p>
                                        <a class="btn btn-success" href="recruiters-create.php">New Recruiter Campaign</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recruiterCampaigns as $campaign): ?>
                                        <?php $counts = recruiter_stage_counts($campaign); ?>
                                        <article class="campaign-card">
                                            <a class="campaign-card-main" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>">
                                                <div class="campaign-card-top">
                                                    <div>
                                                        <strong><?php echo recruiter_h($campaign["title"]); ?></strong>
                                                        <span><?php echo recruiter_h($campaign["owner"]); ?> · Created <?php echo recruiter_h($campaign["created"]); ?></span>
                                                    </div>
                                                    <span class="pill"><?php echo recruiter_h($campaign["status"]); ?></span>
                                                </div>
                                                <div class="recruiters-progress"><span style="width: <?php echo (int)$campaign["progress"]; ?>%"></span></div>
                                                <div class="stage-mini-grid stage-mini-grid-horizontal campaign-stage-grid">
                                                    <?php foreach ($recruiterStages as $stageNumber => $stage): ?>
                                                        <div class="stage-mini <?php echo (int)$campaign["primary_stage"] === (int)$stageNumber ? "is-primary-mini" : ""; ?>">
                                                            <strong><?php echo (int)$counts[$stageNumber]; ?></strong>
                                                            <span><?php echo (int)$stageNumber; ?>. <?php echo recruiter_h($stage["short"]); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </a>
                                            <div class="campaign-card-actions">
                                                <a class="btn btn-light btn-sm" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>">Open</a>
                                                <a class="btn btn-light btn-sm" href="recruiters-settings.php?id=<?php echo urlencode($campaign["id"]); ?>">Settings</a>
                                            </div>
                                            <div class="recruiters-tags">
                                                <?php foreach ($campaign["terms"] as $term): ?>
                                                    <span class="meta-pill"><?php echo recruiter_h($term); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>
    </div>
</div>

<?php include('../partial/footer.php'); ?>
<?php include('../partial/scripts.php'); ?>
<?php include('../partial/footer-end.php'); ?>
