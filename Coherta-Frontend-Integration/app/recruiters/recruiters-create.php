<?php
require_once "../base.php";
require_once "./recruiters-data.php";

$PageTitle = "Create Recruiter Campaign";
$sidebar_close = true;

include('../partial/header.php');
$ownerOptions = recruiter_owner_options($user ?? null);
?>
<link rel="stylesheet" type="text/css" href="/app/assets/css/recruiters.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/recruiters.css'); ?>">
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
                            <div class="hero-grid create-hero-grid">
                                <div>
                                    <h2>New Recruiter Campaign</h2>
                                    <p>Create the recruiter journey across Identify, Connect, Message, and Conclude.</p>
                                    <div class="hero-actions">
                                        <a class="btn btn-light" href="recruiters.php">Back to overview</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="create-layout">
                        <section class="panel">
                            <div class="panel-inner">
                                <div class="section-head">
                                    <div>
                                        <h3>Campaign setup</h3>
                                        <p>Candidates start in Identify. The journey narrows the field before outreach and final reply review.</p>
                                    </div>
                                </div>

                                <aside class="summary-card create-preview-card">
                                    <h3>Preview</h3>
                                    <p>The preview updates as you fill out the campaign.</p>
                                    <div class="metric-stack" id="createPreview">
                                        <div class="metric-box"><strong data-preview-title>New campaign</strong><span data-preview-meta>Campaign</span></div>
                                        <div class="metric-box"><strong data-preview-identify>Identify · 0 titles</strong><span>Step 1 · Build field</span></div>
                                        <div class="metric-box"><strong data-preview-connect>Connect · 0 skills</strong><span>Step 2 · Narrow field</span></div>
                                        <div class="metric-box"><strong data-preview-message>Message · Not written</strong><span>Step 3 · Outreach</span></div>
                                        <div class="metric-box"><strong>Conclude</strong><span>Step 4 · Replies are collected automatically</span></div>
                                    </div>
                                </aside>

                                <form class="create-form" id="recruiterCreateForm" method="post" action="https://api.coherta.com/recruiters">
                                    <div class="settings-grid">
                                        <div class="field">
                                            <label for="campaignTitle">Campaign title</label>
                                            <input class="input" id="campaignTitle" name="title" type="text" value="">
                                        </div>
                                        <div class="field">
                                            <label for="campaignOwner">Owner</label>
                                            <select class="select" id="campaignOwner" name="owner_id">
                                                <option value="">Choose owner</option>
                                                <?php foreach ($ownerOptions as $ownerOption): ?>
                                                    <option value="<?php echo recruiter_h($ownerOption["id"]); ?>">
                                                        <?php echo recruiter_h($ownerOption["name"]); ?><?php echo $ownerOption["email"] !== "" ? " · " . recruiter_h($ownerOption["email"]) : ""; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for="campaignCountry">Country</label>
                                            <input class="input" id="campaignCountry" name="country" type="text" value="Denmark">
                                        </div>
                                    </div>

                                    <div class="create-step-card is-active">
                                        <div class="create-step-head">
                                            <span>Step 1 · Identify</span>
                                            <div>
                                                <strong>Identify candidates</strong>
                                                <small>Find profiles and build the first candidate field.</small>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label for="campaignTerms">Employee Title</label>
                                            <textarea class="textarea" id="campaignTerms" name="steps[1][titles]"></textarea>
                                        </div>
                                        <div class="settings-grid">
                                            <div class="field">
                                                <label for="campaignLocations">Geografi</label>
                                                <input class="input" id="campaignLocations" name="steps[1][locations]" type="text" value="Denmark, Remote">
                                            </div>
                                            <div class="field">
                                                <label for="campaignStep1Max">Max candidates</label>
                                                <input class="input" id="campaignStep1Max" name="steps[1][max_candidates]" type="number" value="500" min="1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="create-step-card">
                                        <div class="create-step-head">
                                            <span>Step 2 · Connect</span>
                                            <div>
                                                <strong>Connect with candidates</strong>
                                                <small>Narrow the field by match and skills before sending connection requests.</small>
                                            </div>
                                        </div>
                                        <div class="field field-wide">
                                            <label>Candidate skills</label>
                                            <div class="skill-criteria-list" data-skill-criteria-list>
                                                <div class="skill-criteria-row" data-skill-criteria-row>
                                                    <div class="skill-criteria-field">
                                                        <label>Skill</label>
                                                        <input class="input" name="steps[2][skill_criteria][0][skill]" type="text" placeholder="Skill">
                                                    </div>
                                                    <div class="skill-criteria-field">
                                                        <label>Min. endorsements</label>
                                                        <input class="input" name="steps[2][skill_criteria][0][endorsements]" type="number" min="0" value="">
                                                    </div>
                                                    <div class="skill-criteria-field">
                                                        <label>Type</label>
                                                        <select class="select" name="steps[2][skill_criteria][0][priority]">
                                                            <option value="requirement">Requirement</option>
                                                            <option value="preferred">Preferred</option>
                                                        </select>
                                                    </div>
                                                    <div class="skill-criteria-field skill-value-field">
                                                        <label>Value</label>
                                                        <span>Calculated</span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm js-remove-skill-criterion" type="button" aria-label="Remove skill">×</button>
                                                </div>
                                            </div>
                                            <button class="btn btn-light btn-sm skill-add-button js-add-skill-criterion" type="button">Add skill</button>
                                        </div>
                                        <div class="settings-grid">
                                            <div class="field">
                                                <label for="campaignStep2Threshold">Minimum match</label>
                                                <input class="input" id="campaignStep2Threshold" name="steps[2][match_threshold]" type="number" value="72" min="0" max="100">
                                            </div>
                                            <div class="field">
                                                <label for="campaignStep2Max">Max candidates</label>
                                                <input class="input" id="campaignStep2Max" name="steps[2][max_candidates]" type="number" value="150" min="1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="create-step-card">
                                        <div class="create-step-head">
                                            <span>Step 3 · Message</span>
                                            <div>
                                                <strong>Message candidates</strong>
                                                <small>Send the automatic message to the smallest relevant field.</small>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label for="campaignMessage">Automatic message</label>
                                            <div class="template-field">
                                                <textarea class="textarea js-template-autocomplete" id="campaignMessage" name="steps[3][message_body]" data-template-autocomplete="campaign"></textarea>
                                                <div class="template-autocomplete" hidden><div class="template-autocomplete-list"></div></div>
                                            </div>
                                        </div>
                                        <div class="settings-grid">
                                            <div class="field">
                                                <label for="campaignStep3Max">Max candidates</label>
                                                <input class="input" id="campaignStep3Max" name="steps[3][max_candidates]" type="number" value="60" min="1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="create-step-card">
                                        <div class="create-step-head">
                                            <span>Step 4 · Conclude</span>
                                            <div>
                                                <strong>Conclude replies</strong>
                                                <small>Review replies and decide which candidates should move forward.</small>
                                            </div>
                                        </div>
                                        <div class="field field-wide">
                                            <p class="field-note">Conclude is populated automatically from candidates who reply after the message step.</p>
                                        </div>
                                    </div>

                                    <button class="btn btn-success" type="submit">Create campaign</button>
                                </form>
                            </div>
                        </section>
                    </div>
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
