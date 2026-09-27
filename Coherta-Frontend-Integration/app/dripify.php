<?php
require_once "./base.php";
$sidebar_close = true;
$PageTitle = "Dripify Campaign Mapping";
/**
 * NOTE:
 * The two write endpoints below are inferred placeholders because only GET endpoints
 * were provided in the request. Update them if your API uses different paths.
 */
const DRIPIFY_CONNECT_ENDPOINT = 'plugins/dripify/accounts';
const DRIPIFY_MAPPING_ENDPOINT = 'plugins/dripify/list';

$rawInput = file_get_contents('php://input');
$jsonInput = [];
if (is_string($rawInput) && trim($rawInput) !== '') {
    $decodedJsonInput = json_decode($rawInput, true);
    if (is_array($decodedJsonInput)) {
        $jsonInput = $decodedJsonInput;
    }
}
$requestData = array_merge($_POST ?? [], $jsonInput);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($requestData['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $action = trim((string)($requestData['ajax_action'] ?? ''));

        if ($action === 'connect_dripify') {
            $username = trim((string)($requestData['username'] ?? ''));
            $password = (string)($requestData['password'] ?? '');
            if ($username === '' || $password === '') {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Username and password are required.']);
                exit;
            }

            $payload = [
                'username' => $username,
                'password' => $password,
            ];
            $result = $client->post(DRIPIFY_CONNECT_ENDPOINT, $payload);
            $freshAccounts = $client->get('company/plugins/dripify/accounts');
            $freshCampaigns = $client->get('company/plugins/dripify/list');

            echo json_encode([
                'ok' => true,
                'message' => 'Dripify account connected.',
                'result' => $result,
                'accounts' => is_array($freshAccounts) ? count($freshAccounts) : 0,
                'campaigns' => is_array($freshCampaigns) ? count($freshCampaigns) : 0,
            ]);
            exit;
        }

        if ($action === 'save_mapping') {
            $pipelineId = (int)($requestData['pipeline_id'] ?? 0);
            $campaignId = trim((string)($requestData['campaign_id'] ?? ''));
            $campaignTitle = trim((string)($requestData['campaign_title'] ?? ''));

            $rowId = (int)($requestData['id'] ?? 0);
            $connectId = (int)($requestData['connect_id'] ?? 0);

            if ($pipelineId <= 0) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'message' => 'Missing pipeline_id.']);
                exit;
            }

            $payload = [
                'pipeline_id' => $pipelineId,
                'campaign_id' => $campaignId,
                'campaign_title' => $campaignTitle,
                'id' => $rowId,
                'connect_id' => $connectId,
            ];
            $result = $client->post(DRIPIFY_MAPPING_ENDPOINT, $payload);
            echo json_encode([
                'ok' => true,
                'message' => 'Mapping saved.',
                'result' => $result,
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Unknown action.']);
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
    exit;
}
$dripify_accounts = $client->get('plugins/dripify/accounts');


$dripify_campaigns = $client->get('plugins/dripify/list');
$dripify_pipelines = $client->getCurrentPipelines();
$users = $client->get('company/users');

$dripify_accounts = is_array($dripify_accounts) ? array_values(array_filter($dripify_accounts, 'is_array')) : [];
$dripify_campaigns = is_array($dripify_campaigns) ? array_values(array_filter($dripify_campaigns, 'is_array')) : [];
$dripify_pipelines = is_array($dripify_pipelines) ? array_values(array_filter($dripify_pipelines, 'is_array')) : [];
$users = is_array($users) ? array_values(array_filter($users, 'is_array')) : [];

$userLookup = [];
foreach ($users as $companyUser) {
    if (!isset($companyUser['id'])) {
        continue;
    }
    $userLookup[(string)$companyUser['id']] = $companyUser;
}

function dripify_first_value(array $row, array $keys, $default = null) {
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return $row[$key];
        }
    }
    return $default;
}

function dripify_campaign_title(array $campaign): string {
    return (string)dripify_first_value($campaign, ['campaign_title', 'title', 'name'], 'Untitled campaign');
}

function dripify_campaign_id(array $campaign): string {
    return (string)dripify_first_value($campaign, ['campaign_id', 'id', 'uuid'], '');
}

function dripify_campaign_account_key(array $campaign): string {
    return (string)dripify_first_value($campaign, ['connect_id', 'account_id', 'dripify_account_id', 'user_id', 'owner_id'], 'unassigned');
}

$campaignsByAccount = [];
foreach ($dripify_campaigns as $campaign) {
    $accountKey = dripify_campaign_account_key($campaign);
    if (!isset($campaignsByAccount[$accountKey])) {
        $campaignsByAccount[$accountKey] = [];
    }
    $campaignsByAccount[$accountKey][] = $campaign;
}

$totalAccounts = count($dripify_accounts);
$totalCampaigns = count($dripify_campaigns);
$hasAccounts = $totalAccounts > 0;
$hasCampaigns = $totalCampaigns > 0;

include('partial/header.php');
?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<style>
    :root {
        --bg: #f5f7fb;
        --panel: #ffffff;
        --panel-2: #f9fbff;
        --line: #e7ebf3;
        --line-strong: #d7deeb;
        --text: #101828;
        --muted: #667085;
        --primary: #4f46e5;
        --primary-2: #7c3aed;
        --primary-soft: rgba(79, 70, 229, 0.08);
        --success: #12b76a;
        --danger: #f04438;
        --shadow-lg: 0 20px 50px rgba(15, 23, 42, 0.08);
        --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.06);
        --radius-xl: 28px;
        --radius-lg: 20px;
        --radius-md: 16px;
    }
    body {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: radial-gradient(circle at top right, rgba(124,58,237,0.10), transparent 24%), linear-gradient(180deg, #f8faff 0%, var(--bg) 100%);
        color: var(--text);
    }
    .page-body.no-top-margin { margin-top: 0 !important; }
    .app-shell { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
    .content { min-width: 0; padding: 24px; }
    .main-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 22px; align-items: start; }
    .main-column { display: grid; gap: 22px; }
    .panel {
        border-radius: var(--radius-xl);
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(255,255,255,0.75);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }
    .panel-inner { padding: 24px; }
    .hero {
        position: relative;
        overflow: hidden;
        background: radial-gradient(circle at 0% 0%, rgba(255,255,255,0.36), transparent 30%), linear-gradient(135deg, #1f2a6e 0%, #3547c8 45%, #6d48e5 100%);
        color: white;
    }
    .hero-grid { display: grid; grid-template-columns: minmax(0,1.4fr) minmax(260px,.9fr); gap: 18px; align-items: end; }
    .hero h2 { margin: 0; font-size: 36px; line-height: 1.05; letter-spacing: -0.05em; max-width: 12ch; }
    .hero p { margin: 14px 0 0; color: rgba(255,255,255,0.78); line-height: 1.6; max-width: 60ch; }
    .hero-actions { margin-top: 22px; display: flex; gap: 12px; flex-wrap: wrap; }
    .hero-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .hero-stat {
        padding: 18px;
        border-radius: 22px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.14);
        backdrop-filter: blur(14px);
    }
    .hero-stat strong { display: block; font-size: 30px; line-height: 1; letter-spacing: -0.05em; margin-bottom: 6px; }
    .hero-stat span { display: block; color: rgba(255,255,255,0.8); font-size: 13px; line-height: 1.4; }
    .section-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
    .section-head h3 { margin: 0; font-size: 22px; letter-spacing: -0.03em; }
    .section-head p { margin: 6px 0 0; color: var(--muted); line-height: 1.6; max-width: 70ch; }
    .count-pill {
        white-space: nowrap;
        border-radius: 999px;
        padding: 10px 14px;
        background: var(--primary-soft);
        color: #3447c7;
        border: 1px solid rgba(79,70,229,0.10);
        font-weight: 700;
        font-size: 13px;
    }
    .btn {
        appearance: none; border: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        min-height: 48px; padding: 0 18px; border-radius: 16px; font-weight: 700; transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn-primary { color: white; background: linear-gradient(135deg, #111827, #344054); box-shadow: 0 12px 24px rgba(15,23,42,0.18); }
    .btn-secondary { color: white; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.18); }
    .btn-outline { color: #344054; background: transparent; border: 1px solid var(--line-strong); }
    .btn-success { color: white; background: linear-gradient(135deg, #12b76a, #039855); box-shadow: 0 12px 24px rgba(18,183,106,0.24); }
    .subpanel { border: 1px solid var(--line); border-radius: 22px; background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,251,255,0.72)); padding: 18px; }
    .subpanel h4 { margin: 0 0 6px; font-size: 18px; letter-spacing: -0.03em; }
    .subpanel p { margin: 0 0 16px; color: var(--muted); line-height: 1.55; font-size: 14px; }
    .input, .select {
        width: 100%; min-height: 48px; border-radius: 16px; border: 1px solid var(--line); background: white; color: var(--text); padding: 0 14px; outline: 0;
    }
    .input:focus, .select:focus { border-color: rgba(79,70,229,0.25); box-shadow: 0 0 0 4px rgba(79,70,229,0.08); }
    .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .field { display: grid; gap: 8px; }
    .field.full { grid-column: 1 / -1; }
    .field label { font-size: 13px; font-weight: 700; color: #344054; }
    .summary-column { position: sticky; top: 24px; display: grid; gap: 18px; }
    .summary-card { border-radius: var(--radius-xl); background: rgba(255,255,255,0.88); border: 1px solid rgba(255,255,255,0.75); box-shadow: var(--shadow-lg); padding: 22px; }
    .summary-card h3 { margin: 0; font-size: 18px; letter-spacing: -0.03em; }
    .summary-card p { margin: 8px 0 0; font-size: 14px; color: var(--muted); line-height: 1.6; }
    .metric-stack { display: grid; gap: 12px; margin-top: 18px; }
    .metric-box { padding: 16px; border-radius: 20px; background: linear-gradient(180deg, #fff, #f8fbff); border: 1px solid var(--line); }
    .metric-box strong { display: block; font-size: 30px; line-height: 1; letter-spacing: -0.05em; margin-bottom: 6px; }
    .metric-box span { color: var(--muted); font-size: 13px; }
    .mapping-grid { display: grid; gap: 14px; }
    .mapping-card {
        border: 1px solid var(--line); border-radius: 20px; background: white; padding: 18px;
        display: grid; grid-template-columns: minmax(0,1.1fr) minmax(220px,.9fr); gap: 16px; align-items: center;
        box-shadow: 0 8px 28px rgba(15,23,42,0.04);
    }
    .mapping-title strong { display: block; font-size: 18px; letter-spacing: -0.03em; }
    .mapping-title span { display: block; margin-top: 6px; color: var(--muted); font-size: 13px; }
    .mapping-actions { display: grid; gap: 10px; }
    .helper-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .meta-pill {
        display: inline-flex; align-items: center; min-height: 30px; padding: 0 10px; border-radius: 999px; background: rgba(79,70,229,0.08);
        color: #3447c7; font-size: 12px; font-weight: 700; width: fit-content;
    }
    .tiny-note { font-size: 12px; color: var(--muted); }
    .toast-wrap { position: fixed; right: 22px; bottom: 22px; z-index: 2000; display: grid; gap: 10px; }
    .toast {
        min-width: 280px; max-width: 360px; padding: 14px 16px; border-radius: 16px; color: white;
        box-shadow: 0 18px 40px rgba(15,23,42,0.16); animation: fadeUp .22s ease;
    }
    .toast.success { background: linear-gradient(135deg, #12b76a, #039855); }
    .toast.error { background: linear-gradient(135deg, #f04438, #b42318); }
    .empty-state {
        padding: 24px; border-radius: 22px; border: 1px dashed var(--line-strong); background: #fbfcfe; color: var(--muted); text-align: center;
    }
    .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.56); display: flex; align-items: center; justify-content: center; padding: 24px; z-index: 1200; }
    .modal-overlay[hidden] { display: none; }
    .modal-card { width: min(680px, 100%); background: #ffffff; border-radius: 28px; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 30px 80px rgba(15, 23, 42, 0.18); overflow: hidden; }
    .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 28px 32px 20px; border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
    .modal-head h3 { margin: 0 0 6px; font-size: 24px; line-height: 1.2; }
    .modal-head p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
    .modal-close { width: 42px; height: 42px; border-radius: 999px; border: 1px solid rgba(15, 23, 42, 0.12); background: #f8fafc; color: #0f172a; font-size: 24px; line-height: 1; cursor: pointer; }
    .modal-body { padding: 28px 32px 32px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
    .status-box { margin-top: 16px; border-radius: 16px; padding: 14px 16px; display: none; }
    .status-box.info { display: block; background: rgba(79,70,229,0.08); color: #3447c7; }
    .status-box.error { display: block; background: rgba(240,68,56,0.08); color: #b42318; }
    .section-stack { display: grid; gap: 22px; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 1180px) {
        .main-grid { grid-template-columns: 1fr; }
        .summary-column { position: static; }
    }
    @media (max-width: 860px) {
        .hero-grid, .form-grid, .mapping-card { grid-template-columns: 1fr; }
        .content { padding: 16px; }
        .panel-inner, .summary-card, .modal-head, .modal-body { padding-left: 20px; padding-right: 20px; }
    }
</style>
<?php include('partial/loader.php'); ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="app-shell">
                <main class="content">
                    <div class="main-grid">
                        <div class="main-column section-stack">
                            <section class="panel hero">
                                <div class="panel-inner">
                                    <div class="hero-grid">
                                        <div>
                                            <h2><?php echo $hasAccounts ? 'Map Coherta lead lists to Dripify campaigns' : 'Connect your first Dripify account'; ?></h2>
                                            <p>
                                                <?php if ($hasAccounts): ?>
                                                    Your connected Dripify accounts and campaigns are ready. Add another account whenever needed, and map each Coherta lead list to the right campaign.
                                                <?php else: ?>
                                                    Start the onboarding flow by entering your Dripify login. When the account connects, Coherta can fetch the available campaigns and let you link them to lead lists.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="hero-stats">
                                            <div class="hero-stat">
                                                <strong><?php echo (int)$totalAccounts; ?></strong>
                                                <span>Connected Dripify accounts</span>
                                            </div>
                                            <div class="hero-stat">
                                                <strong><?php echo (int)$totalCampaigns; ?></strong>
                                                <span>Available campaigns</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="panel">
                                <div class="panel-inner">
                                    <div class="section-head">
                                        <div>
                                            <h3>Connected Dripify accounts</h3>
                                            <p>Each connected account can contribute one or more campaign titles to the mapping dropdowns below.</p>
                                        </div>
                                        <div class="count-pill"><?php echo (int)$totalAccounts; ?> accounts</div>
                                    </div>

                                    <?php if (!$hasAccounts): ?>
                                        <div class="empty-state">
                                            No Dripify accounts are connected yet. Use the onboarding flow above to connect the first account and fetch campaigns.
                                        </div>
                                    <?php else: ?>
                                        <div class="mapping-grid">
                                            <?php foreach ($dripify_accounts as $account): ?>
                                                <?php
                                                $accountId = (string)dripify_first_value($account, ['id', 'account_id', 'user_id'], '');
                                                $displayName = (string)dripify_first_value($account, ['username', 'full_name', 'name', 'email'], 'Connected account');
                                                $accountCampaigns = $campaignsByAccount[$accountId] ?? [];
                                                ?>
                                                <div class="mapping-card">
                                                    <div class="mapping-title">
                                                        <strong><?php echo htmlspecialchars($displayName); ?></strong>
                                                        <span>Last validated: <?php echo htmlspecialchars((string)dripify_first_value($account, ['last_validated'], 'Not available')); ?></span>
                                                    </div>
                                                    <div class="mapping-actions">
                                                        <div class="helper-row">
                                                            <span class="meta-pill"><?php echo count($accountCampaigns); ?> campaigns</span>
                                                            <span class="tiny-note">Account ID: <?php echo htmlspecialchars($accountId !== '' ? $accountId : 'n/a'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <section class="panel" id="leadListMappings">
                                <div class="panel-inner">
                                    <div class="section-head">
                                        <div>
                                            <h3>Coherta lead lists → Dripify campaigns</h3>
                                            <p>Choosing a campaign saves immediately and shows a small confirmation toast.</p>
                                        </div>
                                        <div class="count-pill"><?php echo count($dripify_pipelines); ?> lead lists</div>
                                    </div>

                                    <?php if (empty($dripify_pipelines)): ?>
                                        <div class="empty-state">No lead lists were found for this company.</div>
                                    <?php elseif (!$hasCampaigns): ?>
                                        <div class="empty-state">Campaigns are not available yet. Connect a Dripify account first to fetch campaigns.</div>
                                    <?php else: ?>
                                        <div class="mapping-grid">
                                            <?php foreach ($dripify_pipelines as $pipeline): ?>
                                            <?php $id = (int)($pipeline['id'] ?? 0); ?>
                                                <div class="mapping-card">
                                                    <div class="mapping-title">
                                                        <strong><?php echo htmlspecialchars((string)($pipeline['title'] ?? 'Untitled lead list')); ?></strong>
                                                        <span>
                                                            Pipeline ID: <?php echo (int)($id ?? 0); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mapping-actions">
                                                        <select
                                                            class="select js-campaign-map"
                                                            data-pipeline-id="<?php echo (int)($pipeline['id'] ?? 0); ?>"
                                                            data-original-value="0"
                                                        >
                                                            <option value="">Choose Dripify campaign</option>
                                                            <?php foreach ($dripify_accounts as $account): ?>
                                                                <?php
                                                                $accountId = (string)dripify_first_value($account, ['id', 'account_id', 'user_id'], '');
                                                                $displayName = (string)dripify_first_value($account, ['username', 'full_name', 'name', 'email'], 'Connected account');
                                                                $accountCampaigns = $campaignsByAccount[$accountId] ?? [];
                                                                if (empty($accountCampaigns)) {
                                                                    continue;
                                                                }
                                                                ?>
                                                                <optgroup label="<?php echo htmlspecialchars($displayName); ?>">
                                                                    <?php foreach ($accountCampaigns as $campaign): ?>
                                                                        <?php $campaignId = dripify_campaign_id($campaign); ?>
                                                                        <option
                                                                            value="<?php echo htmlspecialchars($campaignId); ?>"
                                                                            data-id="<?php echo (int)($campaign['id'] ?? 0); ?>"
                                                                            data-connect-id="<?php echo (int)($campaign['connect_id'] ?? 0); ?>"
                                                                            data-campaign-title="<?php echo htmlspecialchars(dripify_campaign_title($campaign)); ?>"
                                                                            <?php
                                                                            if (
                                                                                in_array((string)$pipeline['id'], explode(';', (string)$campaign['pipeline_ids']), true) &&
                                                                                in_array((string)$campaign['id'], explode(';', (string)$campaign['dripify_ids']), true)
                                                                            ) {
                                                                                echo "selected";
                                                                            }
                                                                            ?>
                                                                        >
                                                                            <?php echo htmlspecialchars(dripify_campaign_title($campaign)); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </optgroup>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="helper-row">
                                                            <span class="tiny-note js-save-state">Saved automatically on change</span>
                                                            <span class="meta-pill">Dynamic save</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </section>
                        </div>

                        <aside class="summary-column">
                            <section class="summary-card">
                                <h3>Overview</h3>
                                <p>Status for connected accounts, campaigns, and available Coherta lead lists.</p>
                                <div class="metric-stack">
                                    <div class="metric-box">
                                        <strong><?php echo (int)$totalAccounts; ?></strong>
                                        <span>Dripify accounts</span>
                                    </div>
                                    <div class="metric-box">
                                        <strong><?php echo (int)$totalCampaigns; ?></strong>
                                        <span>Campaign titles</span>
                                    </div>
                                    <div class="metric-box">
                                        <strong><?php echo (int)count($dripify_pipelines); ?></strong>
                                        <span>Coherta lead lists</span>
                                    </div>
                                </div>
                                <div style="margin-top:18px; display:grid; gap:10px;">
                                    <button class="btn btn-primary" type="button" id="openConnectModalAside">Add Dripify account</button>
                                </div>
                            </section>
                        </aside>
                    </div>
                </main>
            </div>
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>

<div class="modal-overlay" id="dripifyConnectModal" hidden>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="dripifyConnectModalTitle">
        <div class="modal-head">
            <div>
                <h3 id="dripifyConnectModalTitle">Connect a Dripify account</h3>
                <p>Enter the Dripify login. Once connected, Coherta can fetch the available campaigns and make them available for lead list mapping.</p>
            </div>
            <button class="modal-close" id="closeDripifyConnectModal" type="button" aria-label="Close modal">×</button>
        </div>
        <div class="modal-body">
            <form id="dripifyConnectForm">
                <div class="form-grid">
                    <div class="field full">
                        <label for="dripifyUsername">Dripify username</label>
                        <input class="input" id="dripifyUsername" name="username" type="text" placeholder="name@company.com" required>
                    </div>
                    <div class="field full">
                        <label for="dripifyPassword">Dripify password</label>
                        <input class="input" id="dripifyPassword" name="password" type="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="status-box" id="dripifyConnectStatus"></div>
                <div class="modal-actions">
                    <button class="btn btn-outline" id="cancelDripifyConnectModal" type="button">Cancel</button>
                    <button class="btn btn-success" id="submitDripifyConnectModal" type="submit">Connect account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<?php include('partial/scripts.php'); ?>
<script>
    (function () {
        const connectModal = document.getElementById('dripifyConnectModal');
        const connectButtons = [
            document.getElementById('openConnectModal'),
            document.getElementById('openConnectModalAside'),
            ...document.querySelectorAll('.open-connect-modal-inline')
        ].filter(Boolean);
        const closeConnectModalBtn = document.getElementById('closeDripifyConnectModal');
        const cancelConnectModalBtn = document.getElementById('cancelDripifyConnectModal');
        const connectForm = document.getElementById('dripifyConnectForm');
        const connectStatus = document.getElementById('dripifyConnectStatus');
        const connectSubmit = document.getElementById('submitDripifyConnectModal');
        const toastWrap = document.getElementById('toastWrap');

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.textContent = message;
            toastWrap.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(8px)';
                setTimeout(() => toast.remove(), 200);
            }, 2200);
        }

        function openModal() {
            connectModal.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            connectModal.hidden = true;
            document.body.style.overflow = '';
            connectStatus.className = 'status-box';
            connectStatus.textContent = '';
            connectForm.reset();
            connectSubmit.disabled = false;
        }

        connectButtons.forEach(btn => btn.addEventListener('click', openModal));
        if (closeConnectModalBtn) closeConnectModalBtn.addEventListener('click', closeModal);
        if (cancelConnectModalBtn) cancelConnectModalBtn.addEventListener('click', closeModal);
        if (connectModal) {
            connectModal.addEventListener('click', function (event) {
                if (event.target === connectModal) closeModal();
            });
        }

        if (connectForm) {
            connectForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                connectSubmit.disabled = true;
                connectStatus.className = 'status-box info';
                connectStatus.textContent = 'Connecting to Dripify and fetching campaigns...';

                const payload = {
                    ajax_action: 'connect_dripify',
                    username: document.getElementById('dripifyUsername').value,
                    password: document.getElementById('dripifyPassword').value
                };

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: JSON.stringify(payload),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Could not connect Dripify.');
                    }
                    showToast('Dripify account connected and campaigns refreshed.');
                    connectStatus.className = 'status-box info';
                    connectStatus.textContent = 'Connected successfully. Reloading...';
                    //window.location.reload();
                } catch (error) {
                    connectSubmit.disabled = false;
                    connectStatus.className = 'status-box error';
                    connectStatus.textContent = error.message || 'Could not connect Dripify.';
                }
            });
        }

        document.querySelectorAll('.js-campaign-map').forEach(function (select) {
            select.addEventListener('change', async function () {
                const pipelineId = select.dataset.pipelineId;
                const campaignId = select.value || '';
                const selectedOption = select.options[select.selectedIndex] || null;
                const campaignTitle = selectedOption ? (selectedOption.dataset.campaignTitle || selectedOption.text || '') : '';
                const rowId = selectedOption ? (selectedOption.dataset.id || '0') : '0';
                const connectId = selectedOption ? (selectedOption.dataset.connectId || '0') : '0';
                const statusNode = select.closest('.mapping-actions').querySelector('.js-save-state');
                const previousValue = select.dataset.originalValue || '';

                select.disabled = true;
                if (statusNode) statusNode.textContent = 'Saving...';

                const payload = {
                    ajax_action: 'save_mapping',
                    pipeline_id: pipelineId,
                    campaign_id: campaignId,
                    campaign_title: campaignTitle,
                    id: rowId,
                    connect_id: connectId
                };

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: JSON.stringify(payload),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Save failed.');
                    }

                    select.dataset.originalValue = campaignId;
                    if (statusNode) statusNode.textContent = 'Saved just now';
                    showToast('Mapping saved');
                } catch (error) {
                    select.value = previousValue;
                    if (statusNode) statusNode.textContent = 'Save failed';
                    showToast(error.message || 'Save failed', 'error');
                } finally {
                    select.disabled = false;
                }
            });
        });
    })();
</script>
<?php include('partial/footer-end.php'); ?>
