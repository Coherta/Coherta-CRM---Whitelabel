<?php
require_once "./base.php";
$PageTitle = "Connect your data with LinkedIn";
$cohertaFreeIntroductionPage = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$chromeExtensionComingSoon = true;
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
        :root { --linkedin-blue:#0a66c2; --linkedin-blue-hover:#004182; --linkedin-ink:#191919; --linkedin-muted:#666; --linkedin-line:#d9d9d9; --linkedin-canvas:#f3f2ef; }
        .linkedin-page-shell { min-height:calc(100vh - 165px); padding:clamp(28px,6vh,72px) 16px; background:var(--linkedin-canvas); }
        .linkedin-connect-card { width:100%; max-width:456px; margin:0 auto; overflow:hidden; border:1px solid rgba(0,0,0,.12); border-radius:8px; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,.08); }
        .linkedin-connect-card > .card-header { padding:28px 28px 10px; border:0; background:#fff; }
        .linkedin-connect-card > .card-header h5 { margin:0; color:var(--linkedin-ink); font-size:24px; font-weight:600; line-height:1.25; }
        .linkedin-connect-card > .card-header p { margin:7px 0 0; color:var(--linkedin-muted); font-size:14px; line-height:1.45; }
        .linkedin-connect-card > .card-body { padding:14px 28px 30px; }
        .linkedin-connect-options { grid-template-columns:1fr !important; gap:14px !important; }
        .linkedin-method-card { min-height:0 !important; padding:18px !important; gap:14px !important; border-color:var(--linkedin-line) !important; border-radius:4px !important; box-shadow:none !important; }
        .linkedin-method-card.is-coming-soon { background:#f7f7f7; border-color:#e4e4e4 !important; }
        .coming-soon-ribbon { top:14px; right:14px; width:auto; padding:4px 8px; transform:none; color:#666; background:#e8e8e8; box-shadow:none; border-radius:999px; font-size:9px; font-weight:700; letter-spacing:.05em; line-height:1.2; }
        .linkedin-method-icon { width:36px; height:36px; border-radius:4px; font-size:19px; }
        .linkedin-method-card h6 { color:var(--linkedin-ink); font-size:15px; font-weight:600; }
        .linkedin-method-card p { color:var(--linkedin-muted); }
        .linkedin-method-actions .btn, #linkedinModal .modal-footer .btn, .linkedin-auth-action { width:100%; min-height:44px; border:0; border-radius:999px; background:var(--linkedin-blue); font-size:14px; font-weight:600; }
        .linkedin-method-actions .btn:hover, .linkedin-method-actions .btn:focus, #linkedinModal .modal-footer .btn:hover, #linkedinModal .modal-footer .btn:focus, .linkedin-auth-action:hover, .linkedin-auth-action:focus { background:var(--linkedin-blue-hover); }
        #linkedinModal .modal-dialog { max-width:376px; }
        #linkedinModal .modal-content { overflow:hidden; border:1px solid rgba(0,0,0,.12); border-radius:8px; box-shadow:0 8px 28px rgba(0,0,0,.18); }
        #linkedinModal .modal-header { align-items:flex-start; padding:26px 26px 8px; border:0; }
        #linkedinModal .modal-title { color:var(--linkedin-ink); font-size:24px; font-weight:600; }
        #linkedinModal .modal-body { padding:14px 26px 8px; }
        #linkedinModal .modal-footer { padding:14px 26px 26px; border:0; }
        .linkedin-auth-mark { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; margin-bottom:18px; border-radius:3px; color:#fff; background:var(--linkedin-blue); font-size:22px; }
        .linkedin-auth-intro { margin:-3px 0 18px; color:var(--linkedin-muted); font-size:13px; line-height:1.5; }
        #linkedinModal .form-label { margin-bottom:5px; color:var(--linkedin-ink); font-size:12px; }
        #linkedinModal .form-control { min-height:44px; border:1px solid #666; border-radius:3px; color:var(--linkedin-ink); font-size:15px; }
        #linkedinModal .form-control:focus { border-color:var(--linkedin-blue); box-shadow:inset 0 0 0 1px var(--linkedin-blue); }
        @media (max-width:767px) { .linkedin-page-shell { padding:18px 10px; } .linkedin-connect-card > .card-header, .linkedin-connect-card > .card-body { padding-left:20px; padding-right:20px; } }
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

        /* Full-screen LinkedIn sign-in treatment */
        #linkedinModal { padding:0 !important; background:#fff; }
        #linkedinModal .modal-dialog { width:100%; max-width:none; min-height:100vh; margin:0; }
        #linkedinModal .modal-content { position:relative; min-height:100vh; overflow:auto; border:0; border-radius:0; background:#fff; box-shadow:none; }
        #linkedinModal form { position:relative; width:464px; max-width:calc(100% - 32px); margin:150px auto 80px; border:0; border-radius:8px; background:#fff; box-shadow:0 0 0 1px rgba(140,140,140,.2); overflow:hidden; }
        #linkedinModal .modal-header { padding:32px 32px 8px; }
        #linkedinModal .modal-title { font-size:24px; font-weight:600; line-height:1.25; }
        #linkedinModal .linkedin-auth-mark { display:none; }
        #linkedinModal .linkedin-auth-subtitle { margin:8px 0 0; color:#191919; font-size:12px; font-weight:400; }
        #linkedinModal .linkedin-auth-subtitle a, #linkedinModal .linkedin-forgot-link { color:#0a66c2; font-weight:600; text-decoration:none; }
        #linkedinModal .linkedin-auth-subtitle a:hover, #linkedinModal .linkedin-forgot-link:hover { text-decoration:underline; }
        #linkedinModal .btn-close { position:absolute; top:14px; right:14px; width:18px; height:18px; padding:6px; opacity:.65; }
        #linkedinModal .modal-body { padding:12px 32px 4px; }
        #linkedinModal .linkedin-auth-intro { display:none; }
        #linkedinModal .mb-3 { margin-bottom:12px !important; }
        #linkedinModal .form-label { display:block; margin-bottom:5px; color:#191919; font-size:12px; font-weight:400; }
        #linkedinModal .form-control { width:100%; min-height:52px; padding:12px 16px; border:1px solid #404040; border-radius:4px; background:#fff; color:#191919; font-size:16px; line-height:1.25; transition:border-color .16s ease, box-shadow .16s ease; }
        #linkedinModal .linkedin-forgot-link { display:inline-block; margin:1px 0 14px; font-size:12px; }
        #linkedinModal .linkedin-remember { display:flex; align-items:center; gap:8px; margin:0; color:#191919; font-size:12px; }
        #linkedinModal .linkedin-remember input { width:18px; height:18px; margin:0; accent-color:#057642; }
        #linkedinModal .modal-footer { display:block; padding:16px 32px 32px; }
        #linkedinModal .modal-footer .btn { min-height:52px; margin:0; border-radius:999px; font-size:16px; line-height:1.25; }
        #linkedinModal #linkedinConnectSMSForm { margin-top:180px; }
        @media (max-width:600px) {
            #linkedinModal form { width:calc(100% - 32px); margin-top:100px; }
        }

        /* LinkedIn guest login page — matched to supplied reference */
        #linkedinModal form { width:464px; max-width:calc(100% - 32px); margin-top:80px; }
        #linkedinModal .modal-header { padding:32px 32px 10px; }
        #linkedinModal .modal-title { font-family:Arial,sans-serif; font-size:24px; font-weight:600; }
        #linkedinModal .linkedin-auth-subtitle { margin-top:6px; font-size:12px; }
        #linkedinModal .modal-body { padding:10px 32px 4px; }
        #linkedinModal .form-control { min-height:52px; padding:12px 16px; border-radius:4px; font-size:16px; background:#fff !important; }
        #linkedinModal .linkedin-password-wrap { position:relative; }
        #linkedinModal .linkedin-password-wrap .form-control { padding-right:42px; }
        #linkedinModal .linkedin-password-toggle { position:absolute; right:10px; bottom:10px; width:32px; height:32px; padding:0; border:0; color:#404040; background:transparent; }
        #linkedinModal .linkedin-forgot-link { margin:0 0 14px; }
        #linkedinModal .modal-footer { padding:16px 32px 32px; }
        #linkedinModal .modal-footer .btn { min-height:52px; color:#fff !important; background:#0a66c2 !important; background-color:#0a66c2 !important; border-color:#0a66c2 !important; font-size:13px; }
        #linkedinModal .modal-footer .btn:hover { background:#004182 !important; background-color:#004182 !important; }
        @media (max-width:600px) { #linkedinModal form { margin-top:78px; margin-bottom:70px; } }

        /* Exact geometry and typography from LinkedIn's guest sign-in surface */
        #linkedinModal .modal-content,
        #linkedinModal form,
        #linkedinModal input,
        #linkedinModal button,
        #linkedinModal label,
        #linkedinModal a {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
        }
        #linkedinModal form { width:464px; margin-top:80px; }
        #linkedinModal .modal-title { font-weight:600 !important; letter-spacing:0 !important; }
        #linkedinModal .modal-footer .btn {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
            font-size:16px !important;
            font-weight:600 !important;
            font-style:normal !important;
            letter-spacing:0 !important;
            line-height:1.25 !important;
            text-transform:none !important;
            text-shadow:none !important;
        }

        /* Pixel-matched typography from the equal-size LinkedIn reference */
        #linkedinModal .modal-title {
            font-size:32px !important;
            font-weight:400 !important;
            line-height:40px !important;
        }
        #linkedinModal .linkedin-auth-subtitle,
        #linkedinModal .form-label,
        #linkedinModal .linkedin-forgot-link,
        #linkedinModal .linkedin-remember {
            font-size:14px !important;
            line-height:20px !important;
        }
        #linkedinModal .form-label { font-weight:400 !important; }
        #linkedinModal .form-control {
            min-height:48px !important;
            font-size:16px !important;
            font-weight:400 !important;
            line-height:24px !important;
        }
        #linkedinModal .linkedin-password-toggle { bottom:8px; }
        #linkedinModal .modal-footer .btn {
            min-height:48px !important;
            font-size:16px !important;
            font-weight:600 !important;
            line-height:20px !important;
        }

        #linkedinModal .modal-footer .linkedin-close-signin {
            margin-top:12px !important;
            color:#404040 !important;
            background:#fff !important;
            background-color:#fff !important;
            border:1px solid #8c8c8c !important;
            font-size:16px !important;
            font-weight:600 !important;
            box-shadow:none !important;
        }
        #linkedinModal .modal-footer .linkedin-close-signin:hover,
        #linkedinModal .modal-footer .linkedin-close-signin:focus {
            color:#191919 !important;
            background:#f3f3f3 !important;
            background-color:#f3f3f3 !important;
            border-color:#666 !important;
        }

        #linkedinModal .linkedin-success-close {
            width:100%;
            min-height:48px;
            padding:12px 20px;
            color:#fff !important;
            background:#0a66c2 !important;
            border:1px solid #0a66c2 !important;
            border-radius:999px;
            font-size:16px;
            font-weight:600;
        }
        #linkedinModal .linkedin-success-close:hover,
        #linkedinModal .linkedin-success-close:focus {
            background:#004182 !important;
            border-color:#004182 !important;
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
                <div class="container-fluid linkedin-page-shell">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card linkedin-connect-card<?php echo $linkedinConnected ? ' is-connected' : ''; ?>">
                                <div class="card-header">
                                    <h5>Connect your LinkedIn</h5>
                                    <p>Sign in securely to connect your account with Coherta.</p>
                                </div>
                                <div class="card-body">
                                    <form class="form-wizard" id="regForm" action="#" method="POST" autocomplete="off">
                                        <div class="linkedin-connect-options">
                                            <section class="linkedin-method-card<?php echo $linkedinConnected ? ' is-connected' : ''; ?>" id="linkedinCredentialCard">
                                                <div class="linkedin-method-header">
                                                    <span class="linkedin-method-icon"><i class="fab fa-linkedin"></i></span>
                                                    <div>
                                                        <h6>Connect with email and password</h6>
                                                        <p>Use your LinkedIn email and password to connect directly from Coherta.</p>
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
                                                            Sign in and connect
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
          <div>
            <span class="linkedin-auth-mark" aria-hidden="true"><i class="fab fa-linkedin-in"></i></span>
            <h5 class="modal-title" id="linkedinModalLabel">Sign in with LinkedIn</h5>
            <p class="linkedin-auth-subtitle">New to LinkedIn? <a href="https://www.linkedin.com/signup" target="_blank" rel="noopener">Join now</a></p>
          </div>
        </div>
        <div class="modal-body" id="linkedinModalBody">
          <div id="linkedinLoginError" class="alert alert-danger" role="alert" hidden></div>
          <div class="mb-3">
            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
            <input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="username" inputmode="email" required>
          </div>
          <div class="mb-3 linkedin-password-wrap">
            <label for="linkedinPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="current-password" required>
            <button type="button" class="linkedin-password-toggle" aria-label="Show password" onclick="toggleLinkedInPassword()"><i class="fas fa-eye"></i></button>
          </div>
          <a class="linkedin-forgot-link" href="https://www.linkedin.com/checkpoint/rp/request-password-reset" target="_blank" rel="noopener">Forgot password?</a>
          <label class="linkedin-remember"><input type="checkbox" checked> Keep me logged in</label>
        </div>
        <div class="modal-footer" id="linkedinModalFooter">
          <button type="submit" class="btn btn-primary linkedin-auth-action">
            Sign in
          </button>
          <button type="button" class="btn linkedin-close-signin" data-bs-dismiss="modal" onclick="resetLinkedInModal()">Close sign in</button>
        </div>
      </form>
        <form id="linkedinConnectSMSForm" autocomplete="off" style="display: none">
            <div class="modal-header">
                <div><span class="linkedin-auth-mark" aria-hidden="true"><i class="fas fa-shield-halved"></i></span><h5 class="modal-title">Enter verification code</h5></div>
                  </div>
            <div class="modal-body" id="linkedinSMSModalBody">
                <div class="mb-3">
                    <p class="linkedin-auth-intro">Enter the code LinkedIn sent by SMS or email.</p>
                    <label for="linkedinSMS" class="form-label">Verification code</label>
                    <input type="text" class="form-control" id="linkedinSMS" name="sms_code" inputmode="numeric" autocomplete="one-time-code" required>
                </div>
            </div>
            <div class="modal-footer" id="linkedinModalFooter">
                <button type="submit" class="btn btn-primary linkedin-auth-action">
                    Verify and connect
                </button>
            </div>
        </form>
        <?php require_once __DIR__ . '/partial/linkedin-approval.php'; ?>
    </div>
  </div>
</div>
<script>
let linkedinModal = null;
let linkedinAttemptVersion = 0;
const linkedinSMSInitialMarkup = document.getElementById('linkedinConnectSMSForm').innerHTML;
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
const chromeExtensionComingSoon = true;
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
    document.getElementById('linkedinModal').addEventListener('hide.bs.modal', () => {
        linkedinAttemptVersion++;
        window.CohertaLinkedInApproval.update(false);
    });

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
        const usernameInput = document.getElementById('linkedinUsername');
        const username = usernameInput.value.trim();
        usernameInput.value = username;
        if (!usernameInput.checkValidity()) {
            usernameInput.reportValidity();
            return;
        }

        const password = document.getElementById('linkedinPassword').value;
        const attemptVersion = ++linkedinAttemptVersion;
        window.CohertaLinkedInApproval.update(false);
        document.getElementById('linkedinModalBody').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>We are currently connecting to your LinkedIn Account.<br>
                You might receive a notification in your LinkedIn App, to approve our connection request.</p>
            </div>
        `;
        document.getElementById('linkedinModalFooter').innerHTML = '';
        try {
            const connectResponse = await fetch(LINKEDIN_CONNECT_ENDPOINT, {
                method: 'POST',
                headers: headersJson(),
                body: JSON.stringify({username, password})
            });
            if (attemptVersion !== linkedinAttemptVersion) return;
            if (!connectResponse.ok) throw new Error('LinkedIn could not be connected. Please try again.');

            let connected = false;
            for (let i = 0; i < 120; i++) {
                await new Promise(res => setTimeout(res, 2000));
                if (attemptVersion !== linkedinAttemptVersion) return;
                try {
                    const res = await fetch(LINKEDIN_CONNECT_ENDPOINT, {
                        method: 'GET',
                        headers: headersJson()
                    });
                    const data = await res.json();
                    if (attemptVersion !== linkedinAttemptVersion) return;
                    if (!res.ok) throw new Error('LinkedIn status could not be checked. Please try again.');
                    const status = Number(data.status);
                    window.CohertaLinkedInApproval.update(status === 7);
                    if (status === 1) {
                        $("#linkedinConnectForm").css("display","block");
                        $("#linkedinConnectSMSForm").css("display","none");
                        connected = true;
                        markLinkedInConnected('Your LinkedIn account is now connected.');
                        break;
                    }
                    else if (status === 2 || status === 3) {
                        const messages = window.CohertaLinkedInApproval.messages;
                        resetLinkedInModal(status === 2 ? messages.invalidCredentials : messages.approvalTimeout, username);
                        return;
                    }
                    else if (status === 8 || status === 9) {
                        $("#linkedinConnectForm").css("display","none");
                        $("#linkedinConnectSMSForm").css("display","block");
                    }
                    else if (status === 7 || status === 10) {
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
                    if (attemptVersion !== linkedinAttemptVersion) return;
                    resetLinkedInModal('LinkedIn could not be connected. Please try again.', username);
                    return;
                }
            }
            if (connected) {
                document.getElementById('linkedinModalBody').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                        <h5 class="mt-3">Your LinkedIn account is now connected</h5>
                        <button type="button" class="btn linkedin-success-close mt-4" onclick="closeLinkedInSuccess()">Close and continue in Coherta</button>
                    </div>
                `;
            } else {
                resetLinkedInModal(window.CohertaLinkedInApproval.messages.approvalTimeout, username);
            }
        } catch (err) {
            if (attemptVersion === linkedinAttemptVersion) resetLinkedInModal('LinkedIn could not be connected. Please try again.', username);
        } finally {
            if (attemptVersion === linkedinAttemptVersion) window.CohertaLinkedInApproval.update(false);
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
function closeLinkedInSuccess() {
    if (linkedinModal) linkedinModal.hide();
    resetLinkedInModal();
}
function toggleLinkedInPassword() {
    const input = document.getElementById('linkedinPassword');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}function openLinkedInModal() {
    resetLinkedInModal();
    linkedinModal.show();
}
function resetLinkedInModal(message = '', username = '') {
    linkedinAttemptVersion++;
    window.CohertaLinkedInApproval.update(false);
    $("#linkedinConnectForm").css("display","block");
    $("#linkedinConnectSMSForm").css("display","none");
    document.getElementById('linkedinConnectSMSForm').innerHTML = linkedinSMSInitialMarkup;
    document.getElementById('linkedinModalBody').innerHTML = `
      <div id="linkedinLoginError" class="alert alert-danger" role="alert" hidden></div>
      <div class="mb-3"><label for="linkedinUsername" class="form-label">LinkedIn Email</label><input type="email" class="form-control" id="linkedinUsername" name="username" autocomplete="username" inputmode="email" required></div>
      <div class="mb-3 linkedin-password-wrap"><label for="linkedinPassword" class="form-label">Password</label><input type="password" class="form-control" id="linkedinPassword" name="password" autocomplete="current-password" required><button type="button" class="linkedin-password-toggle" aria-label="Show password" onclick="toggleLinkedInPassword()"><i class="fas fa-eye"></i></button></div>
      <a class="linkedin-forgot-link" href="https://www.linkedin.com/checkpoint/rp/request-password-reset" target="_blank" rel="noopener">Forgot password?</a>
      <label class="linkedin-remember"><input type="checkbox" checked> Keep me logged in</label>
    `;
    document.getElementById('linkedinUsername').value = username;
    const loginError = document.getElementById('linkedinLoginError');
    loginError.textContent = message;
    loginError.hidden = !message;
    document.getElementById('linkedinModalFooter').innerHTML = `
      <button type="submit" class="btn btn-primary">
        Sign in
      </button>
          <button type="button" class="btn linkedin-close-signin" data-bs-dismiss="modal" onclick="resetLinkedInModal()">Close sign in</button>
    `;
}
</script>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
