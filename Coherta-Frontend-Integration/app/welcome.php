<?php
require_once "./base.php";
$sessionToken = $_SESSION["token"] ?? "";

$PageTitle = "Welcome to Coherta";
$user = $client->getCurrentUser();
$linkedinConnected = isset($user['connectors']['linkedin'][0]["status"]) && (int)$user['connectors']['linkedin'][0]["status"] == 1;
$showWelcomeFlow = !isset($user['welcome_screen']) || $user['welcome_screen'] === null || (int)$user['welcome_screen'] === 0;

if ($showWelcomeFlow === true) {
    $ch = curl_init('https://api.coherta.com/users/me');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION["token"]
    ]);
    // Replace 'yourNewPasswordHere' with the actual new password variable/value
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['welcome_screen' => 1]));
    $response = curl_exec($ch);
    curl_close($ch);
    // Handle $response as needed
}

/*
|--------------------------------------------------------------------------
| AJAX: Create lead list directly from selected GTM template
|--------------------------------------------------------------------------
| NOTE:
| I do not have your exact pipeline-create endpoint from advanced-wizard.js,
| so replace $client->post("pipeline/create", ...) below with the exact same
| endpoint you already use there.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_from_template') {
    header('Content-Type: application/json');

    try {
        $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : 0;
        if ($templateId <= 0) {
            echo json_encode(['success' => false, 'detail' => 'Missing template_id']);
            exit;
        }

        $templateRows = $client->get("gtm/templates?id=" . $templateId);
        if (empty($templateRows) || !isset($templateRows[0])) {
            echo json_encode(['success' => false, 'detail' => 'Template not found']);
            exit;
        }

        $template = $templateRows[0];
        $templateTitle = trim((string)($template['gtm_title'] ?? 'Lead list'));
        $templateFilters = json_decode($template['filters'] ?? '[]', true);
        if (!is_array($templateFilters)) {
            $templateFilters = [];
        }
        $company = $client->getCurrentCompanies();
        $filters = [];
        $filters['filters'] = $templateFilters;

        $payload = [
                'title' => $templateTitle,
                'crm_company' => $company[0]['id'],
                'pipeline_filter' => $filters,
                'origin' => 'dk',
                'owner' => $user['id'],
                'description' => $template['gtm_description'] ?? '',
                'priority' => 'random',
                'websiteInfo' => false,
                'subjects' => 0,
                "jobtitles" => ["CEO","CTO","CFO","COO","Founder","Co-Founder","Owner","Managing Director","Director","Adm. Dir"],
                "prefetch_companies" => false
        ];
        $created = $client->post("pipeline", $payload);

        $newPipelineId = null;
        if (is_array($created)) {
            if (isset($created['id'])) {
                $newPipelineId = (int)$created['id'];
            } elseif (isset($created[0]['id'])) {
                $newPipelineId = (int)$created[0]['id'];
            }
        }
        echo json_encode([
                'success' => true,
                'pipeline_id' => $newPipelineId,
                'title' => $templateTitle
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
                'success' => false,
                'detail' => $e->getMessage()
        ]);
        exit;
    }
}
include('partial/header.php');

?>
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.body.setAttribute('session_id', <?php echo json_encode($sessionToken); ?>);
        });
    </script>

    <style>
        :root {
            --bg: #f5f7fb;
            --text: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, 0.08);
            --line-strong: rgba(99, 102, 241, 0.28);
            --primary: #6366f1;
            --primary-soft: rgba(99, 102, 241, 0.08);
            --success: #22c55e;
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            background: var(--bg);
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
        }

        .welcome-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 340px 1fr;
            background: var(--bg);
        }

        .welcome-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border-right: 1px solid var(--line);
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .welcome-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .welcome-brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(99, 102, 241, 0.25);
        }

        .welcome-sidebar h2 {
            margin: 0 0 10px;
            font-size: 28px;
            line-height: 1.1;
        }

        .welcome-sidebar p {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .welcome-progress {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #e9edf5;
            overflow: hidden;
            margin-bottom: 22px;
        }

        .welcome-progress span {
            display: block;
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            transition: width 0.25s ease;
        }

        .welcome-step-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .welcome-step-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid transparent;
            color: var(--muted);
            transition: 0.2s ease;
        }

        .welcome-step-item.active {
            background: #fff;
            color: var(--text);
            border-color: var(--line-strong);
            box-shadow: 0 12px 28px rgba(99, 102, 241, 0.08);
        }

        .welcome-step-item.completed .welcome-step-bullet {
            background: var(--success);
            color: #fff;
            border-color: var(--success);
        }

        .welcome-step-item.completed {
            background: rgba(34, 197, 94, 0.12);
            color: #166534;
            border-color: rgba(34, 197, 94, 0.24);
            box-shadow: 0 12px 28px rgba(34, 197, 94, 0.12);
        }

        .welcome-step-item.completed .welcome-step-copy span {
            color: #15803d;
        }

        .welcome-step-bullet {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex: 0 0 auto;
        }

        .welcome-step-item.active .welcome-step-bullet {
            color: #000;
            border-color: #000;
            box-shadow: 0 0 0 6px rgba(99, 102, 241, 0.08);
        }

        .welcome-step-copy strong {
            display: block;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .welcome-step-copy span {
            display: block;
            font-size: 12px;
            color: var(--muted);
        }

        .welcome-sidebar-help {
            margin-top: 22px;
            padding: 16px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .welcome-main {
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .welcome-main-inner {
            width: 100%;
            max-width: 860px;
        }

        .welcome-card {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 30px;
            padding: 34px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .welcome-card.linkedin-connected {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border-color: rgba(34, 197, 94, 0.34);
            box-shadow: 0 22px 54px rgba(22, 163, 74, 0.16);
        }

        .welcome-card.linkedin-connected .welcome-topline {
            background: rgba(34, 197, 94, 0.14);
            color: #166534;
        }

        .welcome-card.linkedin-connected h1,
        .welcome-card.linkedin-connected > p {
            color: #14532d;
        }

        #linkedinActivationCard.linkedin-connected .welcome-actions {
            display: none;
        }

        .welcome-topline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: #000;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .welcome-card h1 {
            color: var(--text);
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1.08;
            letter-spacing: -0.03em;
        }

        .welcome-card > p {
            margin: 0 0 26px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
            max-width: 620px;
        }

        .welcome-card strong {
            color: var(--text);
        }

        .welcome-connection-note {
            padding: 16px 18px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid rgba(15,23,42,0.08);
            max-width: 620px;
        }

        .welcome-connection-note strong {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .welcome-connection-note div {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        .welcome-connection-note.is-connected {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: rgba(34, 197, 94, 0.14);
            border-color: rgba(34, 197, 94, 0.24);
        }

        .welcome-connection-note.is-connected .welcome-connection-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: var(--success);
            flex: 0 0 auto;
        }

        .welcome-connection-note.is-connected .welcome-connection-copy strong {
            color: #14532d;
            font-size: 15px;
        }

        .welcome-connection-note.is-connected .welcome-connection-copy span {
            display: block;
            color: #166534;
            font-size: 13px;
            line-height: 1.6;
        }

        .welcome-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .welcome-btn {
            appearance: none;
            border: 0;
            min-height: 54px;
            padding: 0 22px;
            border-radius: 18px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.18s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-btn:hover { transform: translateY(-1px); }
        .welcome-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

        .welcome-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
            color: #fff;
            box-shadow: 0 18px 32px rgba(99, 102, 241, 0.25);
        }

        .welcome-btn-secondary {
            background: #fff;
            color: #334155;
            border: 1px solid var(--line);
        }

        .welcome-btn-ghost {
            background: transparent;
            color: var(--muted);
        }

        .welcome-search { margin-bottom: 18px; }

        .welcome-search input {
            width: 100%;
            height: 54px;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 0 16px;
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        .welcome-gmt-cards {
            display: grid;
            gap: 14px;
        }

        .welcome-gtm-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: #fff;
            cursor: pointer;
            transition: 0.18s ease;
            color: #000;
        }

        .welcome-gtm-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
        }

        .welcome-gtm-card.active {
            border-color: #000;
            box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.08);
            background: linear-gradient(180deg, rgba(99,102,241,0.05) 0%, rgba(255,255,255,1) 100%);
        }

        .welcome-gtm-card h3 {
            margin: 0 0 6px;
            font-size: 17px;
        }

        .welcome-gtm-card p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .welcome-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .welcome-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid var(--line);
            color: #334155;
            font-size: 12px;
            font-weight: 600;
        }

        .welcome-pill.recommended {
            background: rgba(34, 197, 94, 0.08);
            color: #166534;
            border-color: rgba(34, 197, 94, 0.16);
        }

        .welcome-mini-steps {
            display: grid;
            gap: 12px;
        }

        .welcome-mini-step {
            display: flex;
            gap: 14px;
            padding: 16px;
            border-radius: 20px;
            background: #fff;
            border: 1px solid var(--line);
        }

        .welcome-mini-number {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--primary-soft);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            flex: 0 0 auto;
        }

        .welcome-mini-copy strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .welcome-mini-copy p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
        }

        .welcome-empty {
            padding: 22px;
            border-radius: 24px;
            border: 1px dashed rgba(15, 23, 42, 0.16);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .welcome-loading {
            padding: 30px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--line);
            text-align: center;
        }

        @media (max-width: 1024px) {
            .welcome-page {
                grid-template-columns: 1fr;
            }

            .welcome-sidebar {
                position: relative;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .welcome-main {
                padding: 24px;
            }
        }
    </style>
    <body session_id="<?php echo htmlspecialchars($_SESSION["token"] ?? "", ENT_QUOTES, "UTF-8"); ?>">
    <div class="welcome-page">
        <aside class="welcome-sidebar">
            <div>
                <div class="welcome-brand">
                    <div class="welcome-brand-mark">C</div>
                    <div>
                        <strong>Coherta</strong><br>
                        <span style="font-size:13px;color:#64748b;">Welcome setup</span>
                    </div>
                </div>

                <h2>Welcome to Coherta</h2>
                <p>We will guide you through the essentials so you can get started with leads, outreach, and campaigns right away.</p>

                <div class="welcome-progress">
                    <span id="welcomeProgressBar"></span>
                </div>

                <div class="welcome-step-list">
                    <div class="welcome-step-item <?php echo $linkedinConnected ? 'completed' : 'active'; ?>" data-step="1">
                        <div class="welcome-step-bullet"><?php echo $linkedinConnected ? '✓' : '1'; ?></div>
                        <div class="welcome-step-copy">
                            <strong>Connect LinkedIn</strong>
                            <span>Optional but recommended</span>
                        </div>
                    </div>

                    <div class="welcome-step-item<?php echo ($linkedinConnected && $showWelcomeFlow) ? ' active' : ''; ?>" data-step="2">
                        <div class="welcome-step-bullet">2</div>
                        <div class="welcome-step-copy">
                            <strong>Choose GTM List</strong>
                            <span>Select your starting point</span>
                        </div>
                    </div>

                    <div class="welcome-step-item" data-step="3">
                        <div class="welcome-step-bullet">3</div>
                        <div class="welcome-step-copy">
                            <strong>Campaigns Intro</strong>
                            <span>Optional</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="welcome-sidebar-help">
                Setup usually takes about 2 minutes. You can skip any step and continue later.
            </div>
        </aside>

        <main class="welcome-main">
            <div class="welcome-main-inner">
                <?php if (!$linkedinConnected): ?>
                    <div class="welcome-card" id="linkedinActivationCard">
                        <div class="welcome-topline">Step 1 · Connect LinkedIn</div>
                        <h1>Connect your LinkedIn account</h1>
                        <p>Coherta uses your LinkedIn connection for outreach, campaigns, and automation. This is the most important activation step.</p>

                        <div class="welcome-connection-note" id="linkedinActivationNote">
                            <strong>Secure activation</strong>
                            <div>
                                This connection is only used to activate your account and enable outreach, campaigns, and automation inside Coherta.
                            </div>
                        </div>

                        <div class="welcome-actions">
                            <button class="welcome-btn welcome-btn-primary" type="button" onclick="openLinkedInModal()">
                                <i class="fab fa-linkedin"></i>&nbsp; Connect and continue
                            </button>
                            <button class="welcome-btn welcome-btn-ghost" type="button" onclick="openSkipLinkedInModal()">
                                Skip this step
                            </button>
                        </div>
                    </div>

                <?php elseif ($showWelcomeFlow): ?>
                    <div class="welcome-card" id="welcomeStepContainer"></div>

                <?php else: ?>
                    <div class="welcome-card linkedin-connected">
                        <div class="welcome-topline">Welcome completed</div>
                        <h1>You are already set up</h1>
                        <p>Your LinkedIn is already connected and your welcome flow has been completed.</p>

                        <div class="welcome-connection-note is-connected">
                            <span class="welcome-connection-icon"><i class="fas fa-check"></i></span>
                            <span class="welcome-connection-copy">
                                <strong>LinkedIn is connected</strong>
                                <span>Coherta is ready to use your LinkedIn connection for outreach and campaigns.</span>
                            </span>
                        </div>

                        <div class="welcome-actions">
                            <a class="welcome-btn welcome-btn-primary" href="/app/index">Go to dashboard</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="modal fade" id="linkedinModal" tabindex="-1" aria-labelledby="linkedinModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="linkedinConnectForm" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="linkedinModalLabel">Connect your LinkedIn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetLinkedInModal()"></button>
                    </div>
                    <div class="modal-body" id="linkedinModalBody">
                        <div class="mb-3">
                            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
                            <input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
                            <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="modal-footer" id="linkedinModalFooter">
                        <button type="submit" class="btn btn-primary">
                            <i class="fab fa-linkedin"></i> Connect now
                        </button>
                    </div>
                </form>

                <form id="linkedinConnectSMSForm" autocomplete="off" style="display:none;">
                    <div class="modal-header">
                        <h5 class="modal-title">Enter SMS/Email Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetLinkedInModal()"></button>
                    </div>
                    <div class="modal-body" id="linkedinSMSBody">
                        <div class="mb-3">
                            <label for="linkedinSMS" class="form-label">Enter the SMS or Email code you have just received</label>
                            <input type="text" class="form-control" id="linkedinSMS" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="modal-footer" id="linkedinSMSFooter">
                        <button type="submit" class="btn btn-primary">
                            <i class="fab fa-linkedin"></i> Enter code and connect
                        </button>
                    </div>
                </form>
                <form id="linkedinConnectAuthenticatorForm" autocomplete="off" style="display:none;">
                    <div class="modal-header">
                        <h5 class="modal-title">Enter Microsoft Authenticator Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetLinkedInModal()"></button>
                    </div>
                    <div class="modal-body" id="linkedinAuthBody">
                        <div class="mb-3" id="linkedInAuthBodyPin">
                            <label for="linkedinAuth" class="form-label">Enter the code you can see in Microsoft Authenticator</label>
                            <input type="text" class="form-control" id="linkedinAuth" autocomplete="off" required>
                        </div>
                        <div class="text-center" id="linkedInAuthBodyPinValidation" style="display:none;">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>We are validating the code you entered.<br>Please wait a moment...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fab fa-linkedin"></i> Enter code and connect
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="skipLinkedInModal" tabindex="-1" aria-labelledby="skipLinkedInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="skipLinkedInModalLabel">Skip LinkedIn connection?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you wish to continue without LinkedIn. This will have severe impact on your experience of Coherta
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Go back</button>
                    <button type="button" class="btn btn-primary" id="confirmSkipLinkedInBtn">Continue without LinkedIn</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const bearerToken = document.body.getAttribute('session_id');
        const linkedinInitiallyConnected = <?php echo $linkedinConnected ? 'true' : 'false'; ?>;
        let linkedinModal = null;
        let skipLinkedInModal = null;
        let gtmSearchTimeout = null;

        const state = {
            currentStep: 2,
            selected_gtm_list_id: null,
            selected_template_name: '',
            gtmLists: [],
            gtmSearch: '',
            gtmLoading: false,
            gtmSaving: false
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function updateSidebar(step) {
            const progressBar = document.getElementById('welcomeProgressBar');
            if (progressBar) {
                const totalSteps = 3;
                const percent = ((step - 1) / (totalSteps - 1)) * 100;
                progressBar.style.width = percent + '%';
            }

            document.querySelectorAll('.welcome-step-item').forEach((item) => {
                const itemStep = Number(item.dataset.step);
                const bullet = item.querySelector('.welcome-step-bullet');

                item.classList.remove('active', 'completed');

                if (itemStep < step) {
                    item.classList.add('completed');
                    bullet.textContent = '✓';
                } else if (itemStep === step) {
                    item.classList.add('active');
                    bullet.textContent = itemStep;
                } else {
                    bullet.textContent = itemStep;
                }
            });
        }

        function showLinkedInConnectedState() {
            const activationCard = document.getElementById('linkedinActivationCard');
            if (activationCard) {
                activationCard.classList.add('linkedin-connected');
            }

            const activationNote = document.getElementById('linkedinActivationNote');
            if (activationNote) {
                activationNote.classList.add('is-connected');
                activationNote.innerHTML = `
                    <span class="welcome-connection-icon"><i class="fas fa-check"></i></span>
                    <span class="welcome-connection-copy">
                        <strong>LinkedIn is connected</strong>
                        <span>We are taking you to the next setup step.</span>
                    </span>
                `;
            }

            const linkedinStep = document.querySelector('.welcome-step-item[data-step="1"]');
            if (linkedinStep) {
                linkedinStep.classList.remove('active');
                linkedinStep.classList.add('completed');
                const bullet = linkedinStep.querySelector('.welcome-step-bullet');
                if (bullet) {
                    bullet.textContent = '✓';
                }
            }
        }

        function normalizeTemplate(template) {
            return {
                id: template.id || template.template_id || template.gtm_id || null,
                title: template.gtm_title || template.title || 'Untitled template',
                description: template.gtm_description || template.description || 'No description available',
                leads: template.leads || template.total_leads || template.count || template.results_found || 0,
                market: template.country || template.market || 'Unknown market',
                icp: template.icp || template.segment || template.category || 'GTM Template',
                recommended: Number(template.featured || 0) === 1
            };
        }

        async function fetchGtmTemplates(search = '') {
            state.gtmLoading = true;
            renderWelcomeStep();

            try {
                const url = search && search.trim() !== ''
                    ? 'https://api.coherta.com/gtm/templates?search=' + encodeURIComponent(search.trim())
                    : 'https://api.coherta.com/gtm/templates';

                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + bearerToken
                    }
                });

                const data = await res.json();
                let rows = [];

                if (Array.isArray(data)) {
                    rows = data;
                } else if (data && Array.isArray(data.data)) {
                    rows = data.data;
                }

                // Only show 3 GTM lists
                state.gtmLists = rows.slice(0, 3);

                if (state.selected_gtm_list_id !== null) {
                    const stillExists = state.gtmLists.some((item) => Number(normalizeTemplate(item).id) === Number(state.selected_gtm_list_id));
                    if (!stillExists && search.trim() === '') {
                        state.selected_gtm_list_id = null;
                        state.selected_template_name = '';
                    }
                }
            } catch (err) {
                state.gtmLists = [];
            }

            state.gtmLoading = false;
            renderWelcomeStep();
        }

        function debounceTemplateSearch(value) {
            clearTimeout(gtmSearchTimeout);
            gtmSearchTimeout = setTimeout(() => {
                state.gtmSearch = value;
                fetchGtmTemplates(value);
            }, 350);
        }

        async function continueWithSelectedTemplate() {
            if (!state.selected_gtm_list_id || state.gtmSaving) return;

            state.gtmSaving = true;
            renderWelcomeStep();

            try {
                const formData = new FormData();
                formData.append('action', 'create_from_template');
                formData.append('template_id', state.selected_gtm_list_id);

                const res = await fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Authorization': 'Bearer ' + bearerToken
                    }
                });

                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.detail || 'Could not create lead list');
                }

                if (data.pipeline_id) {
                    window.location.href = '/app/leads?pipeline_id=' + encodeURIComponent(data.pipeline_id);
                    return;
                }

                window.location.href = '/app/leads';
            } catch (err) {
                alert(err.message || 'Could not create lead list from template.');
                state.gtmSaving = false;
                renderWelcomeStep();
            }
        }

        function renderWelcomeStep() {
            const container = document.getElementById('welcomeStepContainer');
            if (!container) return;

            updateSidebar(state.currentStep);

            if (state.currentStep === 2) {
                const cardsHtml = state.gtmLists.map((rawItem) => {
                    const item = normalizeTemplate(rawItem);

                    return `
                <div class="welcome-gtm-card ${Number(state.selected_gtm_list_id) === Number(item.id) ? 'active' : ''}" data-id="${escapeHtml(item.id)}" data-title="${escapeHtml(item.title)}">
                    <h3>${escapeHtml(item.title)}</h3>
                    <p>${escapeHtml(item.description)}</p>
                    <div class="welcome-pills">
                        <span class="welcome-pill">${escapeHtml(item.leads)} leads</span>
                        <span class="welcome-pill">${escapeHtml(item.market)}</span>
                        <span class="welcome-pill">${escapeHtml(item.icp)}</span>
                    </div>
                </div>
            `;
                }).join('');

                container.innerHTML = `
            <div class="welcome-topline">Step 2 · Choose GTM List</div>
            <h1>Choose your GTM starting point</h1>
            <p>Start with a GTM list that matches the kind of companies you want to work with.</p>

            <div class="welcome-search">
                <input
                    type="text"
                    id="gtmTemplateSearch"
                    placeholder="Search GTM templates..."
                    value="${escapeHtml(state.gtmSearch)}"
                >
            </div>

            ${
                    state.gtmLoading
                        ? `
                        <div class="welcome-loading">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <div style="font-size:14px;color:#64748b;">Loading GTM templates...</div>
                        </div>
                    `
                        : state.gtmLists.length > 0
                            ? `<div class="welcome-gmt-cards">${cardsHtml}</div>`
                            : `
                            <div class="welcome-empty">
                                <strong>No GTM templates found</strong>
                                <p>Try another search term or clear your search to see all templates.</p>
                            </div>
                        `
                }

            <div class="welcome-actions">
                <button class="welcome-btn welcome-btn-primary" id="continueStep2" ${state.selected_gtm_list_id && !state.gtmSaving ? '' : 'disabled'}>
                    ${state.gtmSaving ? 'Creating lead list...' : 'Continue'}
                </button>
                <a class="welcome-btn welcome-btn-ghost" href="/app/campaign/campaigns-dashboard">Skip this step</a>
            </div>
        `;

                const searchInput = document.getElementById('gtmTemplateSearch');
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        debounceTemplateSearch(this.value);
                    });
                }

                document.querySelectorAll('.welcome-gtm-card').forEach((card) => {
                    card.addEventListener('click', function () {
                        state.selected_gtm_list_id = Number(this.dataset.id);
                        state.selected_template_name = this.dataset.title || '';
                        renderWelcomeStep();
                    });
                });

                const continueBtn = document.getElementById('continueStep2');
                if (continueBtn) {
                    continueBtn.addEventListener('click', continueWithSelectedTemplate);
                }

                return;
            }

            if (state.currentStep === 3) {
                container.innerHTML = `
            <div class="welcome-topline">Step 3 · Optional</div>
            <h1>Ready for outreach?</h1>
            <p>Here is the quickest path from your GTM list to an active campaign inside Coherta Campaigns.</p>

            <div class="welcome-mini-steps">
                <div class="welcome-mini-step">
                    <div class="welcome-mini-number">1</div>
                    <div class="welcome-mini-copy">
                        <strong>Select leads from your GTM list</strong>
                        <p>Use the list you just chose as your starting point so you begin with relevant companies.</p>
                    </div>
                </div>

                <div class="welcome-mini-step">
                    <div class="welcome-mini-number">2</div>
                    <div class="welcome-mini-copy">
                        <strong>Build your campaign</strong>
                        <p>Set up your flow and messaging so your outreach feels sharp, personal, and ready to launch.</p>
                    </div>
                </div>

                <div class="welcome-mini-step">
                    <div class="welcome-mini-number">3</div>
                    <div class="welcome-mini-copy">
                        <strong>Start outreach</strong>
                        <p>Activate your campaign and let Coherta begin working on the leads you prepared.</p>
                    </div>
                </div>
            </div>

            <div class="welcome-actions">
                <button class="welcome-btn welcome-btn-secondary" id="backStep3">Back</button>
                <a class="welcome-btn welcome-btn-primary" href="/app/campaign/campaigns-dashboard">Go to Campaigns</a>
                <a class="welcome-btn welcome-btn-ghost" href="/app/index">Skip this step</a>
            </div>
        `;

                document.getElementById('backStep3').addEventListener('click', function () {
                    state.currentStep = 2;
                    renderWelcomeStep();
                });

                return;
            }
        }

        function openLinkedInModal() {
            resetLinkedInModal();
            linkedinModal.show();
        }

        function openSkipLinkedInModal() {
            if (skipLinkedInModal) {
                skipLinkedInModal.show();
            }
        }

        function resetLinkedInModal() {
            const connectForm = document.getElementById('linkedinConnectForm');
            const smsForm = document.getElementById('linkedinConnectSMSForm');

            connectForm.style.display = 'block';
            smsForm.style.display = 'none';

            document.getElementById('linkedinModalBody').innerHTML = `
        <div class="mb-3">
            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
            <input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="off" required>
        </div>
        <div class="mb-3">
            <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
            <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="off" required>
        </div>
    `;

            document.getElementById('linkedinModalFooter').innerHTML = `
        <button type="submit" class="btn btn-primary">
            <i class="fab fa-linkedin"></i> Connect now
        </button>
    `;

            document.getElementById('linkedinSMSBody').innerHTML = `
        <div class="mb-3">
            <label for="linkedinSMS" class="form-label">Enter the SMS or Email code you have just received</label>
            <input type="text" class="form-control" id="linkedinSMS" autocomplete="off" required>
        </div>
    `;
            document.getElementById('linkedInAuthBodyPin').style.display = 'block';
            document.getElementById('linkedInAuthBodyPinValidation').style.display = 'none';
            document.getElementById('linkedinSMSFooter').innerHTML = `
        <button type="submit" class="btn btn-primary">
            <i class="fab fa-linkedin"></i> Enter code and connect
        </button>
    `;
        }

        document.addEventListener('DOMContentLoaded', function () {
            linkedinModal = new bootstrap.Modal(document.getElementById('linkedinModal'));
            skipLinkedInModal = new bootstrap.Modal(document.getElementById('skipLinkedInModal'));

            const welcomeContainer = document.getElementById('welcomeStepContainer');
            if (welcomeContainer) {
                fetchGtmTemplates();
            } else {
                updateSidebar(linkedinInitiallyConnected ? 2 : 1);
            }

            const confirmSkipLinkedInBtn = document.getElementById('confirmSkipLinkedInBtn');
            if (confirmSkipLinkedInBtn) {
                confirmSkipLinkedInBtn.addEventListener('click', function () {
                    window.location.href = '/app/index';
                });
            }

            document.getElementById('linkedinConnectSMSForm').onsubmit = async function(e) {
                e.preventDefault();
                const sms_code = document.getElementById('linkedinSMS').value;

                await fetch('https://api.coherta.com/apps/linkedin/connect/sms', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + bearerToken
                    },
                    body: JSON.stringify({ sms_code })
                });

                document.getElementById('linkedinSMSBody').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>We are validating the SMS code you entered.<br>Please wait a moment...</p>
            </div>
        `;
                document.getElementById('linkedinSMSFooter').innerHTML = '';
            };

            document.getElementById('linkedinConnectAuthenticatorForm').onsubmit = async function(e) {
                e.preventDefault();
                const sms_code = document.getElementById('linkedinAuth').value;

                await fetch('https://api.coherta.com/apps/linkedin/connect/sms', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + bearerToken
                    },
                    body: JSON.stringify({ sms_code })
                });
                document.getElementById('linkedInAuthBodyPin').style.display = 'none';
                document.getElementById('linkedInAuthBodyPinValidation').style.display = 'block';
                document.getElementById('linkedinSMSFooter').innerHTML = '';
            };

            document.getElementById('linkedinConnectForm').onsubmit = async function(e) {
                e.preventDefault();

                const username = document.getElementById('linkedinUsername').value;
                const password = document.getElementById('linkedinPassword').value;

                document.getElementById('linkedinModalBody').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>We are currently connecting to your LinkedIn account.<br>You might receive a notification in your LinkedIn app to approve our connection request.</p>
            </div>
        `;
                document.getElementById('linkedinModalFooter').innerHTML = '';

                try {
                    await fetch('https://api.coherta.com/apps/linkedin/connect', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + bearerToken
                        },
                        body: JSON.stringify({ username, password })
                    });

                    let connected = false;

                    for (let i = 0; i < 120; i++) {
                        await new Promise(res => setTimeout(res, 2000));

                        try {
                            const res = await fetch('https://api.coherta.com/apps/linkedin/connect', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + bearerToken
                                }
                            });

                            const data = await res.json();

                            if (data.status === 1) {
                                connected = true;
                                break;
                            } else if (data.status === 2) {
                                document.getElementById('linkedinConnectForm').style.display = 'block';
                                document.getElementById('linkedinConnectSMSForm').style.display = 'none';
                                document.getElementById('linkedinModalBody').innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                LinkedIn could not validate your email and/or password. Please try again.
                            </div>
                            <div class="mb-3">
                                <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
                                <input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="off" required>
                            </div>
                            <div class="mb-3">
                                <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
                                <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="off" required>
                            </div>
                        `;
                                document.getElementById('linkedinModalFooter').innerHTML = `
                            <button type="submit" class="btn btn-primary">
                                <i class="fab fa-linkedin"></i> Connect now
                            </button>
                        `;
                                return;
                            } else if (data.status === 8) {
                                document.getElementById('linkedinConnectForm').style.display = 'none';
                                document.getElementById('linkedinConnectAuthenticatorForm').style.display = 'block';
                                if(data.code === null) {
                                    document.getElementById('linkedInAuthBodyPin').style.display = 'block';
                                    document.getElementById('linkedInAuthBodyPinValidation').style.display = 'none';
                                } else {
                                    document.getElementById('linkedInAuthBodyPin').style.display = 'none';
                                    document.getElementById('linkedInAuthBodyPinValidation').style.display = 'block';
                                }
                            } else if (data.status === 9) {
                                document.getElementById('linkedinConnectForm').style.display = 'none';
                                document.getElementById('linkedinConnectSMSForm').style.display = 'block';
                                document.getElementById('linkedinConnectAuthenticatorForm').style.display = 'none';
                            } else if (data.status === 10) {
                                document.getElementById('linkedinConnectForm').style.display = 'block';
                                document.getElementById('linkedinConnectSMSForm').style.display = 'none';
                                document.getElementById('linkedinConnectAuthenticatorForm').style.display = 'none';
                                document.getElementById('linkedinModalBody').innerHTML = `
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>You have received a notification in your LinkedIn app. Please open LinkedIn on your mobile and approve our login request.</p>
                            </div>
                        `;
                                document.getElementById('linkedinModalFooter').innerHTML = '';
                            }
                        } catch (err) {}
                    }

                    if (connected) {
                        document.getElementById('linkedinModalBody').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                        <h5 class="mt-3">Your LinkedIn account is now connected</h5>
                    </div>
                `;

                        showLinkedInConnectedState();

                        setTimeout(() => {
                            window.location.reload();
                        }, 900);
                    }
                } catch (err) {
                    document.getElementById('linkedinModalBody').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    Something went wrong while connecting LinkedIn. Please try again.
                </div>
                <div class="mb-3">
                    <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
                    <input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="off" required>
                </div>
                <div class="mb-3">
                    <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
                    <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="off" required>
                </div>
            `;
                    document.getElementById('linkedinModalFooter').innerHTML = `
                <button type="submit" class="btn btn-primary">
                    <i class="fab fa-linkedin"></i> Connect now
                </button>
            `;
                }
            };

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;

                const welcomeContainer = document.getElementById('welcomeStepContainer');
                if (!welcomeContainer) return;

                if (state.currentStep === 2) {
                    const btn = document.getElementById('continueStep2');
                    if (btn && !btn.disabled) btn.click();
                } else if (state.currentStep === 3) {
                    window.location.href = '/app/index';
                }
            });
        });
    </script>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
