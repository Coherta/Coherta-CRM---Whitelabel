<?php
require_once "../base.php";
require_once "./recruiters-data.php";

$campaignId = isset($_GET["id"]) ? (string)$_GET["id"] : "";
$campaign = $campaignId !== "" ? recruiter_api_campaign($client ?? null, $campaignId) : null;
$settingsStep = isset($_GET["step"]) && ctype_digit((string)$_GET["step"]) ? (int)$_GET["step"] : 0;
if (!in_array($settingsStep, [0, 1, 2, 3], true)) $settingsStep = 0;
$settingsStage = $settingsStep > 0 ? ($recruiterStages[$settingsStep] ?? null) : null;

$PageTitle = $campaign ? ($settingsStage ? $settingsStage["short"] . " Settings" : $campaign["title"] . " Settings") : "Recruiter Settings";
$sidebar_close = true;

include('../partial/header.php');
$ownerOptions = recruiter_owner_options($user ?? null, $campaign);
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
                                <p>There is no campaign settings page to show yet.</p>
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
                                    <h2><?php echo $settingsStage ? recruiter_h($settingsStage["short"]) . " settings" : "Campaign settings"; ?></h2>
                                    <p><?php echo recruiter_h($campaign["title"]); ?> · <?php echo recruiter_h($campaign["owner"]); ?> · Created <?php echo recruiter_h($campaign["created"]); ?></p>
                                    <div class="hero-actions">
                                        <a class="btn btn-light" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>">Back to campaign</a>
                                        <a class="btn btn-success" href="recruiters-create.php">New campaign</a>
                                    </div>
                                </div>
                                <div class="hero-stats">
                                    <div class="hero-stat"><strong><?php echo $settingsStep > 0 ? (int)$settingsStep : 3; ?></strong><span><?php echo $settingsStep > 0 ? "Current step" : "Settings steps"; ?></span></div>
                                    <div class="hero-stat"><strong><?php echo (int)$campaign["progress"]; ?>%</strong><span>Progress</span></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="panel" id="campaignSettings">
                        <div class="panel-inner">
                            <div class="section-head">
                                <div>
                                    <h3 id="campaignSettingsTitle"><?php echo $settingsStage ? "Step " . (int)$settingsStep . " · " . recruiter_h($settingsStage["title"]) : "Campaign settings"; ?></h3>
                                    <p data-settings-full-copy><?php echo $settingsStage ? "Edit only this step." : "Edit campaign-level settings."; ?></p>
                                </div>
                            </div>
                            <div>
                                <form class="settings-form" id="recruiterSettingsForm" data-campaign-id="<?php echo recruiter_h($campaign["campaign_id"] ?? ""); ?>" data-recruiter-campaign-id="<?php echo recruiter_h($campaign["recruiter_campaign_id"] ?? $campaign["id"]); ?>">
                                    <?php if ($settingsStep === 0): ?>
                                    <div class="settings-grid js-settings-general">
                                        <div class="field">
                                            <label for="settingsTitle">Campaign title</label>
                                            <input class="input" id="settingsTitle" name="title" type="text" value="<?php echo recruiter_h($campaign["title"]); ?>">
                                        </div>
                                        <div class="field">
                                            <label for="settingsOwner">Owner</label>
                                            <select class="select" id="settingsOwner" name="owner_id">
                                                <option value="">Choose owner</option>
                                                <?php foreach ($ownerOptions as $ownerOption): ?>
                                                    <option value="<?php echo recruiter_h($ownerOption["id"]); ?>"<?php echo (string)($campaign["owner_id"] ?? "") === (string)$ownerOption["id"] ? " selected" : ""; ?>>
                                                        <?php echo recruiter_h($ownerOption["name"]); ?><?php echo $ownerOption["email"] !== "" ? " · " . recruiter_h($ownerOption["email"]) : ""; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="field field-wide">
                                            <label for="settingsTerms">Target candidate titles</label>
                                            <textarea class="textarea" id="settingsTerms" name="terms"><?php echo recruiter_h(implode(", ", $campaign["terms"])); ?></textarea>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php foreach ($recruiterStages as $stageNumber => $stage): ?>
                                        <?php if ((int)$stageNumber === 4) continue; ?>
                                        <?php if ($settingsStep > 0 && (int)$stageNumber !== $settingsStep) continue; ?>
                                        <?php if ($settingsStep === 0) continue; ?>
                                        <?php $settings = $campaign["steps"][$stageNumber]["settings"] ?? []; ?>
                                        <?php $step = $campaign["steps"][$stageNumber] ?? []; ?>
                                        <?php $isStepRunning = ($step["status"] ?? "") === "active"; ?>
                                        <div class="settings-step" id="settingsStep<?php echo (int)$stageNumber; ?>" data-settings-step="<?php echo (int)$stageNumber; ?>">
                                            <div class="settings-step-head">
                                                <div>
                                                    <span>Step <?php echo (int)$stageNumber; ?> · <?php echo recruiter_h($settings["campaign_action_label"] ?? $stage["short"]); ?></span>
                                                    <strong><?php echo recruiter_h($stage["title"]); ?></strong>
                                                </div>
                                                <div class="settings-step-controls" data-step-card="<?php echo (int)$stageNumber; ?>">
                                                    <span class="stage-started" data-started-label><?php echo $isStepRunning ? "Started: " . recruiter_h($step["started_at"] ?? "") : "Status: " . recruiter_h(ucfirst(str_replace("_", " ", (string)($step["status"] ?? "not_started")))); ?></span>
                                                    <?php if ($isStepRunning): ?>
                                                        <button class="btn btn-light btn-sm js-step-toggle" type="button" data-action="stop" data-stage="<?php echo (int)$stageNumber; ?>">Stop step</button>
                                                    <?php else: ?>
                                                        <button class="btn btn-success btn-sm js-step-toggle" type="button" data-action="start" data-stage="<?php echo (int)$stageNumber; ?>">Start step</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="settings-grid">
                                                <div class="field js-filter-field">
                                                    <label for="stage<?php echo (int)$stageNumber; ?>Max">Max candidates</label>
                                                    <input class="input" id="stage<?php echo (int)$stageNumber; ?>Max" name="steps[<?php echo (int)$stageNumber; ?>][max_candidates]" type="number" min="1" value="<?php echo (int)($settings["max_candidates"] ?? 0); ?>">
                                                </div>
                                                <?php if ((int)$stageNumber === 2): ?>
                                                    <div class="field js-filter-field">
                                                        <label for="stage<?php echo (int)$stageNumber; ?>Threshold">Minimum match</label>
                                                        <input class="input" id="stage<?php echo (int)$stageNumber; ?>Threshold" name="steps[<?php echo (int)$stageNumber; ?>][match_threshold]" type="number" min="0" max="100" value="<?php echo (int)($settings["match_threshold"] ?? 0); ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($stageNumber === 1): ?>
                                                    <div class="field field-wide js-filter-field"><label for="stage1Titles">Titles</label><textarea class="textarea" id="stage1Titles" name="steps[1][titles]"><?php echo recruiter_h($settings["titles"] ?? ""); ?></textarea></div>
                                                    <div class="field js-filter-field"><label for="stage1Locations">Geography</label><input class="input" id="stage1Locations" name="steps[1][locations]" type="text" value="<?php echo recruiter_h($settings["locations"] ?? ""); ?>"></div>
                                                    <div class="field js-filter-field"><label for="stage1Exclude">Exclude companies</label><input class="input" id="stage1Exclude" name="steps[1][exclude_companies]" type="text" value="<?php echo recruiter_h($settings["exclude_companies"] ?? ""); ?>"></div>
                                                <?php elseif ($stageNumber === 2): ?>
                                                    <?php $skillCriteria = recruiter_skill_criteria($settings); ?>
                                                    <div class="field field-wide js-filter-field">
                                                        <label>Skills / experience</label>
                                                        <div class="skill-criteria-list" data-skill-criteria-list>
                                                            <?php foreach ($skillCriteria as $skillIndex => $skillCriterion): ?>
                                                                <div class="skill-criteria-row" data-skill-criteria-row>
                                                                    <div class="skill-criteria-field">
                                                                        <label>Skill</label>
                                                                        <input class="input" name="steps[2][skill_criteria][<?php echo (int)$skillIndex; ?>][skill]" type="text" placeholder="Skill" value="<?php echo recruiter_h($skillCriterion["skill"] ?? ""); ?>">
                                                                    </div>
                                                                    <div class="skill-criteria-field">
                                                                        <label>Min. endorsements</label>
                                                                        <input class="input" name="steps[2][skill_criteria][<?php echo (int)$skillIndex; ?>][endorsements]" type="number" min="0" value="<?php echo isset($skillCriterion["endorsements"]) && $skillCriterion["endorsements"] !== null ? (int)$skillCriterion["endorsements"] : ""; ?>">
                                                                    </div>
                                                                    <div class="skill-criteria-field">
                                                                        <label>Type</label>
                                                                        <select class="select" name="steps[2][skill_criteria][<?php echo (int)$skillIndex; ?>][priority]">
                                                                            <option value="requirement"<?php echo ($skillCriterion["priority"] ?? "requirement") === "requirement" ? " selected" : ""; ?>>Requirement</option>
                                                                            <option value="preferred"<?php echo ($skillCriterion["priority"] ?? "") === "preferred" ? " selected" : ""; ?>>Preferred</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="skill-criteria-field skill-value-field">
                                                                        <label>Value</label>
                                                                        <span><?php echo recruiter_h(number_format((float)($skillCriterion["value"] ?? 0), 2, ",", ".")); ?>%</span>
                                                                    </div>
                                                                    <button class="btn btn-light btn-sm js-remove-skill-criterion" type="button" aria-label="Remove skill">×</button>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <button class="btn btn-light btn-sm skill-add-button js-add-skill-criterion" type="button">Add skill</button>
                                                    </div>
                                                    <div class="field field-wide js-filter-field"><label for="stage2ConnectionNote">Connection note</label><textarea class="textarea" id="stage2ConnectionNote" name="steps[2][connection_note]"><?php echo recruiter_h($settings["connection_note"] ?? ""); ?></textarea></div>
                                                <?php elseif ($stageNumber === 3): ?>
                                                    <div class="field field-wide js-filter-field">
                                                        <label for="stage3Body">Automatic message</label>
                                                        <div class="template-field">
                                                            <textarea class="textarea js-template-autocomplete" id="stage3Body" name="steps[3][message_body]" data-template-autocomplete="campaign"><?php echo recruiter_h($settings["message_body"] ?? ""); ?></textarea>
                                                            <div class="template-autocomplete" hidden><div class="template-autocomplete-list"></div></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="field field-wide">
                                                        <p class="field-note">Step 4 shows candidates who replied after the automatic message.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="form-actions">
                                        <?php if ($settingsStep === 0): ?>
                                        <button class="btn btn-danger js-delete-campaign" type="button">Delete campaign</button>
                                        <span class="form-actions-spacer"></span>
                                        <?php endif; ?>
                                        <a class="btn btn-light" href="recruiters-campaign.php?id=<?php echo urlencode($campaign["id"]); ?>">Cancel</a>
                                        <button class="btn btn-success" type="submit">Save settings</button>
                                    </div>
                                </form>
                            </div>
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
