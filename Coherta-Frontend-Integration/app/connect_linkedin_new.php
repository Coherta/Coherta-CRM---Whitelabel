<?php
require_once "./base.php";
$PageTitle = "Connect your data with LinkedIn";
if (!isset($publicPage)) {
    $currentUser = $client->getCurrentUser();
    if (is_array($currentUser)) {
        $user = $currentUser;
    }
}
$connectors = isset($user["connectors"]) && is_array($user["connectors"]) ? $user["connectors"] : [];

function coherta_connect_page_normalize_key($value): string
{
    return preg_replace('/[^a-z0-9]/', '', strtolower((string)$value));
}

function coherta_connect_page_truthy($value): bool
{
    if (is_bool($value)) return $value;
    if (is_numeric($value)) return (int)$value === 1;

    $clean = strtolower(trim((string)$value));
    return in_array($clean, ['1', 'true', 'yes', 'connected', 'healthy', 'active'], true);
}

function coherta_connect_page_falsy($value): bool
{
    if (is_bool($value)) return !$value;
    if (is_numeric($value)) return (int)$value === 0;

    $clean = strtolower(trim((string)$value));
    return in_array($clean, ['0', 'false', 'no', 'disconnected', 'inactive', 'expired', 'missing'], true);
}

function coherta_connect_page_has_value($value): bool
{
    if (is_array($value)) return !empty($value);
    if (is_bool($value)) return $value;

    $clean = strtolower(trim((string)$value));
    return $clean !== '' && !in_array($clean, ['0', 'false', 'null', 'undefined', 'none', '[]'], true);
}

function coherta_connect_page_is_linkedin_connector($key, $connector): bool
{
    if (coherta_connect_page_normalize_key($key) === 'linkedin') return true;
    if (!is_array($connector)) return false;

    foreach (['key', 'name', 'type', 'slug', 'provider', 'connector', 'label'] as $field) {
        if (isset($connector[$field]) && coherta_connect_page_normalize_key($connector[$field]) === 'linkedin') return true;
    }

    return false;
}

function coherta_connect_page_has_connector_fields($connector): bool
{
    if (!is_array($connector)) return false;

    foreach (['status', 'connect', 'connected', 'linkedin_status', 'linkedin_cookie', 'linkedinCookie'] as $field) {
        if (array_key_exists($field, $connector)) return true;
    }

    return false;
}

function coherta_connect_page_add_linkedin_source(array &$sources, $source): void
{
    if (!is_array($source)) return;
    if (coherta_connect_page_has_connector_fields($source)) $sources[] = $source;

    foreach ($source as $item) {
        if (is_array($item) && coherta_connect_page_has_connector_fields($item)) $sources[] = $item;
    }
}

function coherta_connect_page_linkedin_sources(array $user, array $connectors): array
{
    $sources = [$user, $connectors];

    if (array_key_exists('linkedin', $connectors)) {
        coherta_connect_page_add_linkedin_source($sources, $connectors['linkedin']);
    }

    foreach ($connectors as $key => $connector) {
        if (coherta_connect_page_is_linkedin_connector($key, $connector)) {
            coherta_connect_page_add_linkedin_source($sources, $connector);
        }
    }

    foreach (['linkedin', 'linkedin_connector', 'crm_user_linkedin', 'user_linkedin'] as $key) {
        if (isset($user[$key]) && is_array($user[$key])) {
            coherta_connect_page_add_linkedin_source($sources, $user[$key]);
        }
    }

    return $sources;
}

function coherta_connect_page_linkedin_status(array $sources): ?int
{
    $fallbackStatus = null;
    $statusFields = ['status', 'linkedin_status', 'connect', 'connected', 'is_connected', 'linkedin_connected', 'is_linkedin_connected'];

    foreach ($sources as $source) {
        if (!is_array($source)) continue;

        foreach ($statusFields as $field) {
            if (!array_key_exists($field, $source)) continue;

            $value = $source[$field];
            if (coherta_connect_page_truthy($value)) return 1;
            if ($fallbackStatus === null && coherta_connect_page_falsy($value)) $fallbackStatus = 0;
        }
    }

    return $fallbackStatus;
}

function coherta_connect_page_has_linkedin_cookie(array $sources): bool
{
    $cookieFields = [
        'linkedin_cookie',
        'linkedinCookie',
        'linkedin_cookies',
        'linkedinCookies',
        'cookie',
        'cookies',
        'has_linkedin_cookie',
        'hasLinkedinCookie',
        'linkedin_cookie_present',
        'linkedinCookiePresent',
        'cookie_present',
        'cookiePresent',
    ];

    foreach ($sources as $source) {
        if (!is_array($source)) continue;

        foreach ($cookieFields as $field) {
            if (array_key_exists($field, $source) && coherta_connect_page_has_value($source[$field])) return true;
        }
    }

    return false;
}

$linkedinSources = coherta_connect_page_linkedin_sources(is_array($user ?? null) ? $user : [], $connectors);
$linkedinStatus = coherta_connect_page_linkedin_status($linkedinSources);
$linkedinCookiePresent = coherta_connect_page_has_linkedin_cookie($linkedinSources);
$linkedinConnected = (int)$linkedinStatus === 1 || ($linkedinStatus === null && $linkedinCookiePresent);
$chromeExtensionValue = $connectors["chrome_extension"] ?? false;
$chromeExtensionConnected = $chromeExtensionValue === true
        || $chromeExtensionValue === 1
        || $chromeExtensionValue === "1"
        || $chromeExtensionValue === "true";
$chromeExtensionComingSoon = false;
$chromeExtensionCardConnected = !$chromeExtensionComingSoon && ($linkedinConnected || $chromeExtensionConnected);
$chromePluginUrl = "https://chromewebstore.google.com/detail/coherta/npcgakepgigijgkhidhopkmbignnkmgc";
include('partial/header.php');
?>
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
    <style>
        .linkedin-connect-card.is-connected {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border-color: rgba(34, 197, 94, 0.34);
            box-shadow: 0 18px 44px rgba(22, 163, 74, 0.14);
        }

        .linkedin-connect-card.is-connected .card-header {
            background: transparent;
            border-bottom-color: rgba(34, 197, 94, 0.18);
        }

        .linkedin-connect-card.is-connected h5 {
            color: #14532d;
        }

        .linkedin-connected-state {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            padding: 16px 18px;
            border-radius: 18px;
            color: #166534;
            background: rgba(34, 197, 94, 0.14);
            border: 1px solid rgba(34, 197, 94, 0.22);
        }

        .linkedin-connected-state i {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #22c55e;
            flex: 0 0 auto;
        }

        .linkedin-connected-state strong,
        .linkedin-connected-state span {
            display: block;
        }

        .linkedin-connected-state strong {
            color: #14532d;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .linkedin-connected-state span {
            color: #166534;
            font-size: 13px;
            line-height: 1.5;
        }

        .linkedin-connect-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .linkedin-method-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 260px;
            padding: 22px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 8px;
            background: #fff;
            text-align: left;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }

        .linkedin-method-card.is-connected {
            border-color: rgba(34, 197, 94, 0.28);
            background: rgba(240, 253, 244, 0.82);
        }

        .linkedin-method-card.disabled {
            opacity: 0.66;
        }
        .linkedin-method-card.is-coming-soon {
            position: relative;
            overflow: hidden;
            background: #f8fafc;
            border-color: rgba(100, 116, 139, 0.22);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04);
        }

        .linkedin-method-card.is-coming-soon .linkedin-method-icon {
            color: #64748b;
            background: rgba(100, 116, 139, 0.12);
        }

        .linkedin-method-card.is-coming-soon h6 {
            color: #475569;
        }

        .linkedin-method-card.is-coming-soon p {
            color: #64748b;
        }

        .coming-soon-ribbon {
            position: absolute;
            top: 20px;
            right: -44px;
            z-index: 2;
            width: 168px;
            padding: 7px 0;
            transform: rotate(45deg);
            color: #fff;
            background: #94a3b8;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1;
            text-align: center;
            text-transform: uppercase;
            pointer-events: none;
        }

        .linkedin-method-header {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .linkedin-method-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            color: #0a66c2;
            background: rgba(10, 102, 194, 0.1);
            font-size: 20px;
        }

        .linkedin-method-card h6 {
            margin: 0 0 5px;
            color: #111827;
            font-size: 16px;
            font-weight: 700;
        }

        .linkedin-method-card p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
        }

        .linkedin-method-copy {
            flex: 1 1 auto;
        }

        .linkedin-extension-actions,
        .linkedin-method-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .linkedin-plugin-status {
            min-height: 20px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .linkedin-plugin-status.success {
            color: #166534;
        }

        .linkedin-plugin-status.error {
            color: #b91c1c;
        }

        @media (max-width: 767px) {
            .linkedin-connect-options {
                grid-template-columns: 1fr;
            }

            .linkedin-method-card {
                min-height: auto;
            }
        }
    </style>
<?php include('partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php');?>
        <!-- Page Header Ends -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php');?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card linkedin-connect-card<?php echo $linkedinConnected ? ' is-connected' : ''; ?>">
                                <div class="card-header">
                                    <h5>Connect your LinkedIn</h5>
                                </div>
                                <div class="card-body">
                                    <form class="form-wizard" id="regForm" action="#" method="POST" autocomplete="off">
                                        <div class="linkedin-connect-options">
                                            <section class="linkedin-method-card<?php echo $linkedinConnected ? ' is-connected' : ''; ?>" id="linkedinCredentialCard">
                                                <div class="linkedin-method-header">
                                                    <span class="linkedin-method-icon"><i class="fab fa-linkedin"></i></span>
                                                    <div>
                                                        <h6>Connect with LinkedIn email and password</h6>
                                                        <p>Use your LinkedIn email with your password to connect directly from Coherta.</p>
                                                    </div>
                                                </div>
                                                <div class="linkedin-connected-state" id="linkedinCredentialConnectedState" <?php echo $linkedinConnected ? '' : 'hidden'; ?>>
                                                    <i class="fas fa-check"></i>
                                                    <div>
                                                        <strong>LinkedIn is connected</strong>
                                                        <span>Coherta can now use your LinkedIn connection for outreach and campaigns.</span>
                                                    </div>
                                                </div>
                                                <?php if(!$linkedinConnected) { ?>
                                                    <p class="linkedin-method-copy">This method may require SMS, email, authenticator, or app approval from LinkedIn.</p>
                                                    <div class="linkedin-method-actions">
                                                        <button class="btn btn-primary" id="nextBtn" type="button" onclick="openLinkedInModal()">
                                                            <i class="fab fa-linkedin"></i> Connect now
                                                        </button>
                                                    </div>
                                                <?php } ?>
                                            </section>

                                            <section class="linkedin-method-card<?php echo $chromeExtensionCardConnected ? ' is-connected' : ''; ?>" id="setupChromePluginCard">
                                                <div class="linkedin-method-header">
                                                    <span class="linkedin-method-icon"><i class="fa-brands fa-chrome"></i></span>
                                                    <div>
                                                        <h6>Connect with Chrome Extension</h6>
                                                        <p>Use the Coherta Chrome Extension to connect through your existing LinkedIn browser session.</p>
                                                    </div>
                                                </div>
                                                <div class="linkedin-connected-state" id="setupChromePluginConnectedState" <?php echo $chromeExtensionCardConnected ? '' : 'hidden'; ?>>
                                                    <i class="fas fa-check"></i>
                                                    <div>
                                                        <strong>LinkedIn is connected</strong>
                                                        <span>The Coherta Chrome Extension is ready for LinkedIn outreach and campaigns.</span>
                                                    </div>
                                                </div>
                                                <p class="linkedin-method-copy" <?php echo $chromeExtensionCardConnected ? 'hidden' : ''; ?>>Install the extension, log it in to Coherta, and connect your current LinkedIn browser session.</p>
                                                <div class="linkedin-extension-actions" <?php echo $chromeExtensionCardConnected ? 'hidden' : ''; ?>>
                                                    <button class="btn btn-primary" id="setupChromePluginBtn" type="button">
                                                        <i class="fa-brands fa-chrome"></i> Install extension
                                                    </button>
                                                    <button class="btn btn-primary" id="cohertaPluginLogin" type="button" hidden>Log extension in</button>
                                                    <button class="btn btn-primary" id="cohertaLinkedInConnect" type="button" hidden>Connect LinkedIn</button>
                                                </div>
                                                <p class="linkedin-plugin-status" id="cohertaPluginStatus" role="status" aria-live="polite" hidden></p>
                                            </section>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php');?>
        </div>
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
            <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="off"  required>
          </div>
        </div>
        <div class="modal-footer" id="linkedinModalFooter">
          <button type="submit" class="btn btn-primary">
            <i class="fab fa-linkedin"></i> Connect now
          </button>
        </div>
      </form>
        <form id="linkedinConnectSMSForm" autocomplete="off" style="display: none">
            <div class="modal-header">
                <h5 class="modal-title" id="linkedinModalLabel">Enter SMS/Email Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetLinkedInModal()"></button>
            </div>
            <div class="modal-body" id="linkedinModalBody">
                <div class="mb-3">
                    <label for="linkedinUsername" class="form-label">Enter the SMS or Email code you have just received</label>
                    <input type="text" class="form-control" id="linkedinSMS" name="username" autocomplete="off" required>
                </div>
            </div>
            <div class="modal-footer" id="linkedinModalFooter">
                <button type="submit" class="btn btn-primary">
                    <i class="fab fa-linkedin"></i> Enter code and connect
                </button>
            </div>
        </form>
    </div>
  </div>
</div>
<script>
let linkedinModal = null;
let bearerToken = document.body.getAttribute('session_id') || '';

if (bearerToken) {
    try {
        window.localStorage.setItem('session_id', bearerToken);
        window.localStorage.setItem('access_token', bearerToken);
        window.localStorage.setItem('coherta_auth_session_id', bearerToken);
        window.sessionStorage.setItem('access_token', bearerToken);
    } catch (error) {}
}

const LINKEDIN_CONNECT_ENDPOINT = 'https://api.coherta.com/apps/linkedin/connect';
const LINKEDIN_SMS_ENDPOINT = 'https://api.coherta.com/apps/linkedin/connect/sms';
const EXTENSION_BRIDGE_CHANNEL = 'COHERTA_EXTENSION_BRIDGE';
const EXTENSION_PAGE_SOURCE = 'coherta-website';
const EXTENSION_SOURCE = 'coherta-extension';
const CHROME_PLUGIN_URL = <?php echo json_encode($chromePluginUrl); ?>;
const chromeExtensionComingSoon = <?php echo $chromeExtensionComingSoon ? 'true' : 'false'; ?>;
const linkedInInitiallyConnected = <?php echo $linkedinConnected ? 'true' : 'false'; ?>;
const chromePluginInitiallyAuthenticated = <?php echo $chromeExtensionConnected ? 'true' : 'false'; ?>;
const connectLinkedInInitialState = {linkedIn: linkedInInitiallyConnected, extension: chromePluginInitiallyAuthenticated};
window.CohertaLinkedInConnectState = connectLinkedInInitialState;
const chromePluginInitiallyInstalled = connectLinkedInInitialState.extension;
const directLinkedInCard = document.getElementById('linkedinCredentialCard');
const chromePluginCard = document.getElementById('setupChromePluginCard');
const directConnectBtn = document.getElementById('nextBtn');
const credentialConnectedState = document.getElementById('linkedinCredentialConnectedState');
const chromePluginConnectedState = document.getElementById('setupChromePluginConnectedState');
const pluginLoginBtn = document.getElementById('cohertaPluginLogin');
const extensionLinkedInConnectBtn = document.getElementById('cohertaLinkedInConnect');
const chromePluginBtn = document.getElementById('setupChromePluginBtn');
const pluginStatus = document.getElementById('cohertaPluginStatus');
const extensionBridgePending = new Map();
let extensionBridgeSeq = 0;
let chromePluginCheckInProgress = false;
const extensionState = {
    linkedinConnected: connectLinkedInInitialState.linkedIn,
    linkedinStatus: connectLinkedInInitialState.linkedIn ? 'connected' : 'idle',
    extension: connectLinkedInInitialState.extension,
    chromePluginInstalled: chromePluginInitiallyInstalled,
    chromePluginAuthenticated: connectLinkedInInitialState.extension
};
const CHROME_PLUGIN_READY_TYPES = new Set([
    'COHERTA_PLUGIN_READY',
    'COHERTA_PLUGIN_PONG',
    'COHERTA_CHROME_PLUGIN_READY',
    'COHERTA_CHROME_PLUGIN_PONG',
    'coherta-plugin-ready',
    'coherta-plugin-pong',
    'coherta:plugin:ready',
    'coherta:plugin:pong'
]);

function headersJson() {
    return {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + bearerToken};
}

function setPluginStatus(message, type) {
    if (!pluginStatus) return;
    pluginStatus.textContent = message || '';
    pluginStatus.hidden = !message;
    pluginStatus.classList.toggle('success', type === 'success');
    pluginStatus.classList.toggle('error', type === 'error');
}

function setPluginLoginBusy(isBusy) {
    if (!pluginLoginBtn) return;
    pluginLoginBtn.disabled = isBusy;
    pluginLoginBtn.textContent = isBusy ? 'Logging extension in...' : 'Log extension in';
}

function setExtensionLinkedInBusy(isBusy, label) {
    if (!extensionLinkedInConnectBtn) return;
    extensionLinkedInConnectBtn.disabled = isBusy;
    extensionLinkedInConnectBtn.textContent = isBusy ? (label || 'Connecting...') : 'Connect LinkedIn';
}

function isChromeBrowser() {
    const ua = navigator.userAgent || '';
    return /(Chrome|CriOS)\//.test(ua) && !/(Edg|OPR|Opera|Firefox|SamsungBrowser)\//.test(ua);
}

function isStoredUserIdSession(value) {
    const cleanValue = String(value || '').trim();
    return !cleanValue || /^[0-9]+$/.test(cleanValue);
}

function getSessionId() {
    try {
        const candidates = [
            bearerToken,
            document.body.getAttribute('session_id'),
            window.localStorage.getItem('access_token'),
            window.sessionStorage.getItem('access_token'),
            window.localStorage.getItem('coherta_auth_session_id'),
            window.sessionStorage.getItem('coherta_auth_session_id'),
            window.sessionStorage.getItem('session_id'),
            window.localStorage.getItem('session_id')
        ];
        for (const candidate of candidates) {
            const clean = String(candidate || '').trim();
            if (clean && !isStoredUserIdSession(clean)) return clean;
        }
        return '';
    } catch (error) {
        const fallback = bearerToken || document.body.getAttribute('session_id') || '';
        return isStoredUserIdSession(fallback) ? '' : fallback;
    }
}

function requestCohertaExtension(action, payload, timeoutMs) {
    const requestId = 'coherta-' + Date.now() + '-' + (++extensionBridgeSeq);
    const requestPayload = payload && typeof payload === 'object' ? payload : {};
    const waitMs = Number(timeoutMs) > 0 ? Number(timeoutMs) : 15000;

    return new Promise((resolve, reject) => {
        const timer = window.setTimeout(() => {
            extensionBridgePending.delete(requestId);
            reject(new Error('Coherta extension did not respond. Is it installed?'));
        }, waitMs);

        extensionBridgePending.set(requestId, {resolve: resolve, reject: reject, timer: timer});

        window.postMessage({
            channel: EXTENSION_BRIDGE_CHANNEL,
            source: EXTENSION_PAGE_SOURCE,
            requestId: requestId,
            action: action,
            payload: requestPayload
        }, window.location.origin);
    });
}

function handleCohertaExtensionMessage(event) {
    if (event.source !== window || event.origin !== window.location.origin) return;

    const message = event.data;
    if (!message || message.channel !== EXTENSION_BRIDGE_CHANNEL || message.source !== EXTENSION_SOURCE) return;

    const waiter = extensionBridgePending.get(message.requestId);
    if (!waiter) return;

    window.clearTimeout(waiter.timer);
    extensionBridgePending.delete(message.requestId);

    if (message.ok) waiter.resolve(message);
    else waiter.reject(new Error(message.error || 'Coherta extension error.'));
}

function isLinkedInConnectedFromExtensionStatus(status) {
    return !!(
        status?.linkedIn?.connected
        || status?.linkedin?.connected
        || status?.linkedinConnected
        || status?.linkedInConnected
    );
}

function markLinkedInConnected(message) {
    extensionState.linkedinConnected = true;
    extensionState.linkedinStatus = 'connected';
    setPluginLoginBusy(false);
    setExtensionLinkedInBusy(false);

    document.querySelector('.linkedin-connect-card')?.classList.add('is-connected');
    directLinkedInCard?.classList.add('is-connected');
    if (!chromeExtensionComingSoon) {
        chromePluginCard?.classList.add('is-connected');
    }
    if (credentialConnectedState) credentialConnectedState.hidden = false;
    if (!chromeExtensionComingSoon && chromePluginConnectedState) chromePluginConnectedState.hidden = false;

    const directCopy = directLinkedInCard ? directLinkedInCard.querySelector('.linkedin-method-copy') : null;
    const chromeCopy = chromePluginCard ? chromePluginCard.querySelector('.linkedin-method-copy') : null;
    if (directCopy) directCopy.hidden = true;
    if (!chromeExtensionComingSoon && chromeCopy) chromeCopy.hidden = true;

    if (directConnectBtn) {
        directConnectBtn.hidden = true;
        directConnectBtn.disabled = true;
    }
    if (pluginLoginBtn) {
        pluginLoginBtn.hidden = true;
        pluginLoginBtn.disabled = true;
    }
    if (extensionLinkedInConnectBtn) {
        extensionLinkedInConnectBtn.hidden = true;
        extensionLinkedInConnectBtn.disabled = true;
    }
    if (chromePluginBtn) chromePluginBtn.hidden = true;

    if (message) setPluginStatus(message, 'success');
}

function syncExtensionStatus(status) {
    extensionState.chromePluginInstalled = true;
    const authenticated = !!status?.authenticated;
    const linkedInConnected = isLinkedInConnectedFromExtensionStatus(status);
    extensionState.chromePluginAuthenticated = authenticated;

    if (chromePluginCard) chromePluginCard.classList.remove('disabled');
    if (chromePluginBtn) chromePluginBtn.hidden = true;

    if (linkedInConnected) {
        markLinkedInConnected('LinkedIn is connected through the Coherta extension.');
        return;
    }

    if (chromePluginCard) chromePluginCard.classList.remove('is-connected');
    if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
    if (pluginLoginBtn) {
        pluginLoginBtn.hidden = authenticated;
        pluginLoginBtn.disabled = false;
    }
    if (extensionLinkedInConnectBtn) {
        extensionLinkedInConnectBtn.hidden = !authenticated;
        extensionLinkedInConnectBtn.disabled = false;
    }

    setPluginStatus(
        authenticated
            ? 'Extension is logged in. You can now connect LinkedIn.'
            : 'Extension is installed, but not logged in.',
        authenticated ? 'success' : ''
    );
}

async function refreshPluginStatus(options) {
    const settings = options || {};
    if (chromeExtensionComingSoon) return false;

    if (extensionState.chromePluginAuthenticated && !settings.forceBridge) {
        syncExtensionStatus({
            authenticated: true,
            linkedIn: {connected: extensionState.linkedinConnected}
        });
        return true;
    }

    if (!isChromeBrowser()) {
        if (extensionState.linkedinConnected) return false;
        extensionState.chromePluginInstalled = false;
        if (pluginLoginBtn) pluginLoginBtn.hidden = true;
        if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.hidden = true;
        if (chromePluginBtn) {
            chromePluginBtn.hidden = false;
            chromePluginBtn.disabled = true;
        }
        if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
        if (chromePluginCard) chromePluginCard.classList.add('disabled');
        setPluginStatus('This option requires Chrome and is disabled in the current browser.', 'error');
        return false;
    }

    try {
        await requestCohertaExtension('PING', {}, 1200);
        const status = await requestCohertaExtension('GET_STATUS');
        syncExtensionStatus(status);
        return true;
    } catch (error) {
        if (extensionState.linkedinConnected) return false;
        extensionState.chromePluginInstalled = false;
        if (pluginLoginBtn) pluginLoginBtn.hidden = true;
        if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.hidden = true;
        if (chromePluginBtn) {
            chromePluginBtn.hidden = false;
            chromePluginBtn.disabled = false;
            chromePluginBtn.textContent = 'Install extension';
        }
        if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
        if (chromePluginCard) chromePluginCard.classList.remove('is-connected', 'disabled');
        if (!settings.silent) setPluginStatus('Coherta extension is not installed or not active.', 'error');
        return false;
    }
}

async function setupChromePluginAvailability(options) {
    if (chromePluginCheckInProgress) return false;
    chromePluginCheckInProgress = true;

    try {
        return await refreshPluginStatus(options);
    } finally {
        chromePluginCheckInProgress = false;
    }
}

function hasInstalledPluginDataset(element) {
    if (!element || !element.dataset) return false;
    return element.dataset.cohertaPlugin === 'installed'
        || element.dataset.cohertaPluginInstalled === 'true'
        || element.dataset.cohertaChromePluginInstalled === 'true';
}

function hasCohertaPluginSignal() {
    const globals = [
        'CohertaPlugin',
        'CohertaChromePlugin',
        'cohertaPlugin',
        'cohertaChromePlugin',
        '__COHERTA_PLUGIN_INSTALLED__',
        '__COHERTA_CHROME_PLUGIN_INSTALLED__'
    ];

    if (globals.some((key) => !!window[key])) return true;
    if (window.Coherta && (window.Coherta.pluginInstalled || window.Coherta.chromePluginInstalled)) return true;
    if (hasInstalledPluginDataset(document.documentElement) || hasInstalledPluginDataset(document.body)) return true;
    if (document.querySelector('[data-coherta-plugin-installed="true"], [data-coherta-chrome-plugin-installed="true"], [data-coherta-plugin="installed"]')) return true;

    const installedMeta = document.querySelector('meta[name="coherta-plugin-installed"], meta[name="coherta-chrome-plugin-installed"]');
    return !!installedMeta && /^(true|installed|1)$/i.test(installedMeta.getAttribute('content') || '');
}

function isCohertaPluginMessage(data) {
    if (!data) return false;
    if (typeof data === 'string') return CHROME_PLUGIN_READY_TYPES.has(data);
    if (typeof data !== 'object') return false;

    const messageKeys = [data.type, data.event, data.action, data.name]
        .filter(Boolean)
        .map((value) => String(value));
    if (messageKeys.some((value) => CHROME_PLUGIN_READY_TYPES.has(value))) return true;
    if (data.cohertaPluginInstalled === true || data.cohertaChromePluginInstalled === true || data.cohertaPlugin === 'installed') return true;

    const source = String(data.source || data.origin || '').toLowerCase();
    const status = String(data.status || '').toLowerCase();
    return (source === 'coherta-plugin' || source === 'coherta-extension' || source === 'coherta-chrome-plugin')
        && (data.installed === true || data.ready === true || status === 'ready' || status === 'installed');
}

function markChromePluginInstalled() {
    extensionState.chromePluginInstalled = true;
    if (chromePluginBtn) {
        chromePluginBtn.disabled = true;
        chromePluginBtn.textContent = 'Extension installed';
    }
    if (chromePluginCard) chromePluginCard.classList.remove('disabled');
    setPluginStatus('Chrome extension is already installed.', 'success');
}

function handleChromePluginReady() {
    if (chromeExtensionComingSoon) return;
    markChromePluginInstalled();
    setupChromePluginAvailability({forceBridge: true, silent: true});
}

function handleChromePluginMessage(event) {
    if (event.source && event.source !== window) return;
    if (isCohertaPluginMessage(event.data)) handleChromePluginReady();
}

function recheckChromePluginAfterFocus() {
    if (document.hidden || !isChromeBrowser() || extensionState.chromePluginInstalled) return;
    setupChromePluginAvailability({silent: true});
}

async function loginPluginFromConnectPage() {
    if (!pluginLoginBtn || pluginLoginBtn.hidden) return;

    const sessionId = getSessionId();
    if (!sessionId) {
        setPluginStatus('Coherta session is missing. Reload the page and try again.', 'error');
        return;
    }

    setPluginLoginBusy(true);
    setPluginStatus('Logging extension in...', '');

    try {
        await requestCohertaExtension('LOGIN_WITH_SESSION', {sessionId: sessionId});
        await refreshPluginStatus({silent: true, forceBridge: true});
    } catch (error) {
        setPluginStatus(error.message || 'The extension could not be logged in.', 'error');
    } finally {
        setPluginLoginBusy(false);
    }
}

async function connectLinkedInWithExtensionFromConnectPage() {
    if (extensionState.linkedinConnected || extensionState.linkedinStatus === 'extension-submitting') return;

    extensionState.linkedinStatus = 'extension-submitting';
    setExtensionLinkedInBusy(true, 'Connecting...');
    setPluginStatus('Connecting LinkedIn through the Coherta extension...', '');

    try {
        await requestCohertaExtension('CONNECT_LINKEDIN', {active: true, closeTab: true}, 45000);
        await refreshPluginStatus({silent: true, forceBridge: true});
        markLinkedInConnected('LinkedIn is connected through the Coherta extension.');
    } catch (error) {
        extensionState.linkedinStatus = 'idle';
        setPluginStatus(error.message || 'LinkedIn could not be connected. Try again.', 'error');
    } finally {
        if (!extensionState.linkedinConnected) setExtensionLinkedInBusy(false);
    }
}

function openChromePlugin() {
    if (chromeExtensionComingSoon || !chromePluginBtn || chromePluginBtn.disabled) return;
    window.open(CHROME_PLUGIN_URL, '_blank', 'noopener');
    setPluginStatus('Chrome extension opened in a new tab. We will check again when you return.', 'success');
    window.setTimeout(() => setupChromePluginAvailability({silent: true}), 1200);
}

document.addEventListener('DOMContentLoaded', function() {
    linkedinModal = new bootstrap.Modal(document.getElementById('linkedinModal'));

    document.getElementById('linkedinConnectSMSForm').onsubmit = async function(e) {
        e.preventDefault();
        const sms_code = document.getElementById('linkedinSMS').value;
        fetch(LINKEDIN_SMS_ENDPOINT, {
            method: 'PATCH',
            headers: headersJson(),
            body: JSON.stringify({"sms_code": sms_code})
        });
            document.getElementById('linkedinConnectSMSForm').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>We are validating the SMS code you entered.<br>
                    Please wait a moment...</p>
                </div>
            `;
    }


    document.getElementById('linkedinConnectForm').onsubmit = async function(e) {
        e.preventDefault();
        const username = document.getElementById('linkedinUsername').value;
        const password = document.getElementById('linkedinPassword').value;
        document.getElementById('linkedinModalBody').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>We are currently connecting to your LinkedIn Account.<br>
                You might receive a notification in your LinkedIn App, to approve our connection request.</p>
            </div>
        `;
        document.getElementById('linkedinModalFooter').innerHTML = '';
        try {
            await fetch(LINKEDIN_CONNECT_ENDPOINT, {
                method: 'POST',
                headers: headersJson(),
                body: JSON.stringify({username, password})
            });

            let connected = false;
            for (let i = 0; i < 120; i++) {
                await new Promise(res => setTimeout(res, 2000));
                try {
                    const res = await fetch(LINKEDIN_CONNECT_ENDPOINT, {
                        method: 'GET',
                        headers: headersJson()
                    });
                    const data = await res.json();
                    const status = Number(data.status);
                    if (status === 1) {
                        $("#linkedinConnectForm").css("display","block");
                        $("#linkedinConnectSMSForm").css("display","none");
                        connected = true;
                        markLinkedInConnected('Your LinkedIn account is now connected.');
                        break;
                    }
                    else if (status === 2) {
                        $("#linkedinConnectForm").css("display","block");
                        $("#linkedinConnectSMSForm").css("display","none");
                        document.getElementById('linkedinModalBody').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            LinkedIn could not validate your email and/or password. Please try again.
                        </div>
                        <div class="mb-3">
                            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
                            <input type="email" class="form-control" id="linkedinUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
                            <input type="password" class="form-control" id="linkedinPassword" name="password" required>
                        </div>
                    `;
                    document.getElementById('linkedinModalFooter').innerHTML = `
                        <button type="submit" class="btn btn-primary">
                            <i class="fab fa-linkedin"></i> Connect now
                        </button>
                    `;
                                        break;
                    }
                    else if (status === 9) {
                        $("#linkedinConnectForm").css("display","none");
                        $("#linkedinConnectSMSForm").css("display","block");
                    }
                    else if (status === 10) {
                        $("#linkedinConnectForm").css("display","block");
                        $("#linkedinConnectSMSForm").css("display","none");
                        document.getElementById('linkedinModalBody').innerHTML = `
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>You have received a notification in your LinkedIn App. Please open LinkedIn on your mobile and approve our login request</p>
                            </div>
                        `;
                        document.getElementById('linkedinModalFooter').innerHTML = '';
                    }
                } catch (err) {
                    // Keep polling while LinkedIn is still processing the connection.
                }
            }
            if (connected) {
                document.getElementById('linkedinModalBody').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                        <h5 class="mt-3">Your LinkedIn account is now connected</h5>
                    </div>
                `;
            }
        } catch (err) {
            document.getElementById('linkedinModalBody').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    LinkedIn could not be connected. Please try again.
                </div>
            `;
        }
    };

    if (!chromeExtensionComingSoon) {
        window.CohertaExtension = {
            request: requestCohertaExtension,
            refresh: refreshPluginStatus,
            login: () => requestCohertaExtension('LOGIN_WITH_SESSION', {sessionId: getSessionId()}),
            connectLinkedIn: () => requestCohertaExtension('CONNECT_LINKEDIN', {active: true, closeTab: true}, 45000)
        };
        if (pluginLoginBtn) pluginLoginBtn.addEventListener('click', loginPluginFromConnectPage);
        if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.addEventListener('click', connectLinkedInWithExtensionFromConnectPage);
        if (chromePluginBtn) chromePluginBtn.addEventListener('click', openChromePlugin);
        window.addEventListener('message', handleCohertaExtensionMessage);
        window.addEventListener('message', handleChromePluginMessage);
        window.addEventListener('focus', recheckChromePluginAfterFocus);
        document.addEventListener('visibilitychange', recheckChromePluginAfterFocus);
        document.addEventListener('coherta:plugin:ready', handleChromePluginReady);
        document.addEventListener('coherta:plugin:pong', handleChromePluginReady);
        document.addEventListener('coherta-plugin-ready', handleChromePluginReady);
        document.addEventListener('coherta-plugin-pong', handleChromePluginReady);

        if (hasCohertaPluginSignal()) handleChromePluginReady();
        else if (!extensionState.linkedinConnected) setupChromePluginAvailability({silent: true});
    }
});
function openLinkedInModal() {
    resetLinkedInModal();
    linkedinModal.show();
}
function resetLinkedInModal() {
    $("#linkedinConnectForm").css("display","block");
    $("#linkedinConnectSMSForm").css("display","none");
    document.getElementById('linkedinModalBody').innerHTML = `
      <div class="mb-3">
        <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
        <input type="email" class="form-control" id="linkedinUsername" name="username" required>
      </div>
      <div class="mb-3">
        <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
        <input type="password" class="form-control" id="linkedinPassword" name="password" required>
      </div>
    `;
    document.getElementById('linkedinModalFooter').innerHTML = `
      <button type="submit" class="btn btn-primary">
        <i class="fab fa-linkedin"></i> Connect now
      </button>
    `;
}
</script>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
