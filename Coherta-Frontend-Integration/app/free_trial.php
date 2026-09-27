<?php
require_once __DIR__ . '/../pricing.php';

$supportedFreeTrialLocales = ['da', 'uk', 'no', 'se', 'de', 'es', 'fi', 'fr', 'hr', 'is', 'pl'];
$requestLocale = isset($_GET['locale']) ? strtolower(trim((string) $_GET['locale'])) : '';
$requestCountryCode = isset($_GET['country_code']) ? strtolower(trim((string) $_GET['country_code'])) : '';
$referrerLocale = '';
$redirectLocale = '';
$countryCodeLocaleMap = ['dk' => 'da', 'gb' => 'uk', 'sv' => 'se', 'nb' => 'no'];

if ($requestLocale === '' && $requestCountryCode !== '') {
    if (isset($countryCodeLocaleMap[$requestCountryCode])) {
        $redirectLocale = $countryCodeLocaleMap[$requestCountryCode];
    } elseif (in_array($requestCountryCode, $supportedFreeTrialLocales, true)) {
        $redirectLocale = $requestCountryCode;
    }
}

if ($redirectLocale === '' && $requestLocale === '' && $requestCountryCode === '' && !empty($_SERVER['HTTP_REFERER'])) {
    $referrerPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    $firstPathPart = strtolower(trim(explode('/', trim((string) $referrerPath, '/'))[0] ?? ''));
    if (in_array($firstPathPart, $supportedFreeTrialLocales, true)) {
        $referrerLocale = $firstPathPart;
    }
}

if ($redirectLocale === '' && $referrerLocale !== '') {
    $redirectLocale = $referrerLocale;
}

if ($redirectLocale !== '') {
    $params = $_GET;
    $params['locale'] = $redirectLocale;
    if ($requestCountryCode === '') {
        $params['country_code'] = $redirectLocale;
    }
    $query = http_build_query($params);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($query ? '?' . $query : ''), true, 302);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Start your Coherta free trial and set up a guided LinkedIn test with your target audience, account details, activation and next steps in one flow." />
    <title>Free Test Period | Coherta</title>
    <link rel="icon" href="/app/assets/images/favicon.png" type="image/png" />
    <link rel="shortcut icon" href="/app/assets/images/favicon.png" type="image/png" />
    <link rel="apple-touch-icon" href="/app/assets/images/favicon.png" />
    <style>
        :root { --bg:#f6f8fb; --card:#fff; --ink:#101828; --muted:#667085; --line:#e4e7ec; --blue:#2563eb; --blue-dark:#1d4ed8; --blue-soft:#eff6ff; --blue-line:#bfdbfe; --green:#10b981; --green-dark:#047857; --green-soft:#ecfdf5; --shadow:0 18px 45px rgba(16,24,40,.07); --shadow-soft:0 10px 24px rgba(16,24,40,.055); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        *{box-sizing:border-box} body{margin:0;min-height:100vh;background:var(--bg);color:var(--ink)} button,input,select{font:inherit} button{cursor:pointer} button:disabled{cursor:not-allowed} svg{display:block} p{margin:0} strong{color:var(--ink);font-weight:680}.low-word-count-section{max-width:1120px;margin:24px auto;padding:28px;border:1px solid var(--line);border-radius:22px;background:#fff;box-shadow:var(--shadow-soft)}.low-word-count-section .product-section-head{max-width:900px}.low-word-count-section h2{margin:0 0 12px;font-size:24px;line-height:1.18;letter-spacing:-.025em}.low-word-count-section p{margin:0 0 14px;color:#52637a;font-size:14px;line-height:1.62}.low-word-count-section .product-card-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:18px 0}.low-word-count-section .product-card{border:1px solid var(--line);border-radius:16px;padding:18px;background:#fff}.low-word-count-section .product-card-icon{display:block;margin-bottom:10px;color:var(--blue-dark);font-weight:760}.low-word-count-section .product-card h3{margin:0 0 8px;font-size:16px}
        .page{min-height:100vh;padding:18px 28px 22px}.shell{max-width:1320px;margin:0 auto}.topbar{min-height:64px;display:flex;align-items:center;justify-content:space-between;gap:18px;position:sticky;top:0;z-index:20;padding:6px 0;background:rgba(246,248,251,.92);backdrop-filter:blur(14px)}.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit}.brand img{width:36px;height:36px;object-fit:contain}.brand-fallback{width:36px;height:36px;display:grid;place-items:center;border-radius:50%;background:var(--blue);color:#fff;font-weight:760}.brand-name{font-size:23px;line-height:1;font-weight:680;letter-spacing:-.03em}.proof{display:flex;align-items:center;gap:12px}.membership-card{width:238px;min-height:52px;border:1px solid #dbe3ee;border-radius:15px;background:#fff;display:flex;align-items:center;gap:12px;padding:0 16px;box-shadow:0 7px 16px rgba(16,24,40,.035)}.membership-logo{width:32px;height:32px;display:grid;place-items:center;flex:0 0 auto}.membership-logo img{display:block;width:32px;height:auto;max-height:32px;object-fit:contain}.membership-logo.de-logo img{width:38px;border-radius:3px}.membership-card span{font-size:13.5px;line-height:1.12;font-weight:650;letter-spacing:-.015em;color:#203a5f}
        .layout{display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start}.main{border:1px solid var(--line);border-radius:24px;background:var(--card);box-shadow:var(--shadow);padding:24px 30px 30px}.main-content{padding-top:24px}.right-rail{position:sticky;top:86px;display:grid;gap:14px}.journey{border:1px solid #dbeafe;border-radius:18px;background:#f8fbff;padding:13px 14px 14px}.journey-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.j-step{width:100%;min-height:54px;display:grid;grid-template-columns:26px 1fr;gap:8px;align-items:center;text-align:left;border:1px solid transparent;border-radius:15px;background:#fff;padding:10px;color:inherit}.j-step:hover:not(:disabled){background:#f8fafc}.j-step.active{border-color:var(--blue-line);background:var(--blue-soft)}.j-step.done{border-color:#d1fae5}.j-step:disabled{opacity:.55}.dot{width:24px;height:24px;border-radius:50%;display:grid;place-items:center;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:700}.active .dot{background:var(--blue);color:#fff}.done .dot{background:var(--green);color:#fff}.j-title{font-size:12.5px;line-height:1.18;font-weight:650;letter-spacing:-.012em}.j-sub{margin-top:2px;font-size:11px;line-height:1.18;color:var(--muted);font-weight:450}
        .contact-card{border:1px solid var(--line);border-radius:22px;background:#fff;box-shadow:var(--shadow-soft);padding:26px}.contact-label{font-size:14px;font-weight:500;color:var(--blue-dark);margin-bottom:20px;letter-spacing:-.012em}.contact-person{display:grid;grid-template-columns:72px 1fr;gap:16px;align-items:center}.contact-photo{width:72px;height:72px;border-radius:50%;display:block;object-fit:cover;border:3px solid #fff;box-shadow:0 12px 28px rgba(16,24,40,.12);background:#fff}.contact-name{font-size:15.5px;font-weight:560;letter-spacing:-.018em;color:var(--ink);line-height:1.18}.contact-role{margin-top:6px;font-size:12.5px;color:var(--muted);font-weight:430}.contact-links{margin-top:24px;display:grid;gap:14px}.contact-link{display:flex;align-items:center;gap:12px;color:var(--blue-dark);font-size:13.2px;font-weight:430;text-decoration:none;line-height:1.28;word-break:break-word}.contact-link:hover{text-decoration:underline;text-underline-offset:3px}.contact-note{margin-top:24px;padding-top:18px;border-top:1px solid var(--line);font-size:12.8px;line-height:1.48;color:#475569;font-weight:390}.rail-language{display:flex;justify-content:flex-end}.language-switch{display:flex;gap:8px}.language-btn{width:36px;height:28px;border:1px solid var(--line);border-radius:8px;background:#fff;display:grid;place-items:center;padding:0;box-shadow:0 4px 10px rgba(16,24,40,.04);overflow:hidden}.language-btn svg{width:24px;height:18px;border-radius:3px;box-shadow:0 0 0 1px rgba(16,24,40,.08)}.language-btn.active{border-color:var(--blue);background:var(--blue-soft);box-shadow:0 0 0 3px #dbeafe}.language-btn:hover{border-color:var(--blue-line)}.customer-type{display:flex;gap:8px;align-items:center;grid-column:1/-1}.type-btn{min-height:34px;border:1px solid var(--line);border-radius:10px;background:#fff;color:#334155;padding:0 13px;font-size:13px;font-weight:700;box-shadow:0 4px 10px rgba(16,24,40,.035)}.type-btn.active{border-color:var(--blue);background:var(--blue-soft);color:var(--blue-dark);box-shadow:0 0 0 3px #dbeafe}.type-btn:hover{border-color:var(--blue-line)}
        h1{margin:0;font-size:38px;line-height:1.03;font-weight:720;letter-spacing:-.045em;color:var(--ink)}.lead{margin-top:12px;max-width:760px;font-size:15.5px;line-height:1.55;color:var(--muted);font-weight:430}.step1-refined{max-width:940px;margin:0 auto}.personal-note{max-width:850px;margin-bottom:20px}.personal-note p{margin:0;font-size:16px;line-height:1.38;color:#111827;font-weight:450;letter-spacing:-.018em}.personal-note p+p{margin-top:7px}.personal-note .lead{margin-top:10px;color:#111827}.step1-intro{margin-top:22px;display:grid;grid-template-columns:1.08fr .92fr;gap:22px;align-items:start}.test-card{border:1px solid #cfe3ff;background:linear-gradient(180deg,#f4f9ff 0%,#f8fbff 100%);border-radius:20px;padding:24px}.company-card{border-left:1px solid var(--line);padding:4px 0 4px 24px}.card-kicker{font-size:15px;font-weight:720;color:var(--blue-dark);margin-bottom:13px;letter-spacing:-.025em}.agreement-list{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.agreement-check{display:grid;grid-template-columns:38px 1fr;gap:11px;align-items:center;font-size:13px;line-height:1.24;color:#334155;font-weight:450}.agreement-check .check{width:38px;height:38px;color:var(--blue);background:#fff;border:1px solid #dbeafe;box-shadow:0 8px 18px rgba(37,99,235,.08)}.agreement-check strong{display:block;font-size:15px;line-height:1.14;color:var(--ink);font-weight:560;white-space:nowrap;margin-top:3px}.agreement-check .text{display:block;color:#52637a;font-size:11px;font-weight:620;letter-spacing:.035em;text-transform:uppercase}.company-card p{margin:0;color:#52637a;font-size:14px;line-height:1.55;font-weight:430}.trust-list,.quiet-list,.soft-list{margin-top:16px;display:grid;gap:11px}.trust-item,.quiet-item{display:grid;grid-template-columns:22px 1fr;gap:10px;align-items:start;font-size:13.5px;line-height:1.42;color:#334155;font-weight:450}.quiet-item.quiet-note{grid-template-columns:1fr;padding:12px 14px;border-radius:12px;background:#f8fafc;color:#475569}.check,.check-circle{width:22px;height:22px;border-radius:50%;background:var(--green);color:#fff;display:grid;place-items:center;flex:0 0 auto}.linkedin-icon,.inmark{display:grid;place-items:center;border-radius:6px;background:#0a66c2;color:#fff;font-size:13px;font-weight:800;letter-spacing:-.02em}.linkedin-icon,.inmark{width:22px;height:22px}
        .panel{margin-top:24px;border:1px solid var(--line);background:#fff;border-radius:18px;padding:22px 24px}.step-helper-grid{display:grid;grid-template-columns:.86fr 1.14fr;gap:22px;align-items:start}.quiet-card,.result-card{border:1px solid var(--line);background:#fff;border-radius:18px;padding:20px}.quiet-title{font-size:15px;font-weight:650;letter-spacing:-.02em;color:var(--ink);margin-bottom:12px}.quiet-copy{font-size:13.5px;line-height:1.5;color:#52637a;font-weight:420;margin-bottom:12px}.form{max-width:520px;margin:0 auto}label{display:block;font-size:13px;font-weight:740;margin:14px 0 7px;color:var(--ink)}input,select{width:100%;height:44px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;padding:0 13px;outline:none;color:#334155;font-size:14px;font-weight:500}input:focus,select:focus{border-color:var(--blue);box-shadow:0 0 0 4px #dbeafe}.password-wrap{position:relative}.password-wrap input{padding-right:48px}.icon-button{position:absolute;top:50%;right:10px;width:30px;height:30px;transform:translateY(-50%);border:0;background:transparent;color:var(--muted);display:grid;place-items:center}.status,.alert-bar,.success-bar,.info-bar{margin-top:12px;min-height:40px;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:740}.status.ok,.success-bar{background:var(--green-soft);border:1px solid #bbf7d0;color:var(--green-dark)}.status.info,.info-bar{background:#fff;border:1px solid var(--blue-line);color:var(--blue-dark)}button.status.info,button.info-bar{width:100%}.alert-bar{justify-content:flex-start}.alert-bar.error{border:1px solid #fecaca;background:#fef2f2;color:#b91c1c}.alert-bar.warning{border:1px solid #fde68a;background:#fffbeb;color:#92400e}.status-stack,.code-box{margin-top:16px}.code-row{display:grid;grid-template-columns:1fr auto;gap:12px}.trial-length{font-size:34px;line-height:1.05;font-weight:620;letter-spacing:-.04em;color:var(--ink);margin-top:8px}.trial-detail{margin-top:8px;font-size:13.5px;line-height:1.45;color:#52637a}.result-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.result-card h3{margin:0 0 10px;font-size:17px;font-weight:650;letter-spacing:-.025em}.result-card p{color:#52637a;font-size:13.8px;line-height:1.5}.status-actions{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-top:18px;flex-wrap:wrap}.status-note{font-size:12.5px;color:#64748b;line-height:1.4}.summary{margin-top:20px;display:grid;grid-template-columns:repeat(4,1fr);border:1px solid var(--line);border-radius:18px;overflow:hidden;background:#fff}.summary.three{grid-template-columns:repeat(3,1fr)}.sum-item{padding:16px 18px;display:flex;gap:12px;align-items:center;border-right:1px solid var(--line)}.sum-item:last-child{border-right:0}.sum-ico{width:34px;height:34px;border-radius:50%;background:var(--blue-soft);color:var(--blue-dark);display:grid;place-items:center;flex:0 0 auto}.sum-label{font-size:12px;color:var(--muted);font-weight:650}.sum-val{margin-top:1px;font-size:16px;font-weight:740;letter-spacing:-.025em}.actions{margin-top:24px;display:flex;gap:12px;align-items:center;justify-content:center;flex-wrap:wrap}.actions.between{justify-content:space-between}.btn,.primary-btn,.secondary-btn{min-height:46px;border-radius:13px;padding:0 22px;display:inline-flex;align-items:center;justify-content:center;gap:9px;font-size:14px;font-weight:750;border:0;text-decoration:none;transition:.15s ease}.btn.primary,.primary-btn{background:var(--blue);color:#fff;box-shadow:0 12px 26px rgba(37,99,235,.18)}.btn.primary:hover:not(:disabled),.primary-btn:hover:not(:disabled){background:var(--blue-dark);transform:translateY(-1px)}.btn.secondary,.secondary-btn{background:#fff;color:var(--blue-dark);border:1px solid var(--line);box-shadow:none}.btn:disabled,.primary-btn:disabled,.secondary-btn:disabled{background:#e2e8f0;color:#94a3b8;border-color:transparent;box-shadow:none;transform:none}.footer{margin-top:12px;text-align:center;font-size:12px;color:#64748b}.cta-note,.disabled-note,.safe-note{margin-top:10px;text-align:center;font-size:12px;color:#64748b;font-weight:600}.setup-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.loading-card{min-height:520px;display:grid;place-items:center;text-align:center}.spin-wrap{width:58px;height:58px;display:grid;place-items:center;margin:0 auto 18px;border-radius:999px;background:var(--blue-soft);color:var(--blue-dark)}.spin{animation:spin 1s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}@media(max-width:1100px){.layout{grid-template-columns:1fr}.right-rail{position:static}.main{min-height:auto}}@media(max-width:900px){.step1-intro,.step-helper-grid,.result-grid{grid-template-columns:1fr}.agreement-list{grid-template-columns:1fr}.company-card{border-left:0;border-top:1px solid var(--line);padding:22px 0 0}}@media(max-width:760px){.page{padding:14px}.topbar{align-items:flex-start;flex-direction:column}.proof{width:100%;flex-direction:column;align-items:stretch}.membership-card{width:100%}.main{padding:20px}h1{font-size:30px}.journey-list,.summary,.summary.three,.setup-grid,.code-row{grid-template-columns:1fr}.sum-item{border-right:0;border-bottom:1px solid var(--line)}.sum-item:last-child{border-bottom:0}.actions.between{justify-content:center}}
        .self-service-grid{display:grid;grid-template-columns:.92fr 1.08fr;gap:28px;align-items:stretch}.self-service-form-card{border:1px solid var(--line);background:#fff;border-radius:19px;padding:24px}.self-service-title{font-size:16px;font-weight:540;letter-spacing:-.025em;margin-bottom:12px;color:#0f172a}.self-service-toggle{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:8px 0 2px}.self-service-toggle button{height:44px;border:1px solid #dbeafe;background:#fff;border-radius:12px;color:#334155;font-size:13.5px;font-weight:520}.self-service-toggle button.active{background:#eff6ff;border-color:#2563eb;color:#1d4ed8;box-shadow:0 8px 18px rgba(37,99,235,.07)}.form.full-width{max-width:100%;margin:0}.help{font-size:12px;color:var(--muted);margin-top:6px}.manual-actions{justify-content:flex-end}.manual-actions .cta-note-wrap{text-align:center}.connect-panel{background:#f4f9ff;border-color:#cfe3ff}.input-error{font-size:12px;color:#b91c1c;margin-top:6px;font-weight:520}.input-invalid{border-color:#fca5a5!important;box-shadow:0 0 0 4px #fee2e2!important}
        @media(max-width:900px){.self-service-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div id="app"></div>

<?php
$low_word_count_locale = "en";
$low_word_count_topic = "trial";
include(__DIR__ . "/../includes/low-word-count-content.php");
?>

<script>
    const OFFER_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/view/';
    const FREE_TRIAL_START_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/start/';
    const FREE_TRIAL_CAMPAIGN_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/campaign/';
    const CAMPAIGNS_DASHBOARD_URL = 'https://www.coherta.com/app/campaign/campaigns-dashboard';
    const MANUAL_SETUP_STORAGE_KEY = 'coherta_free_test_manual_setup_v2';
    const CURRENT_USER_STORAGE_KEY = 'coherta_current_user';
    const SESSION_STORAGE_KEY = 'session_id';
    const JOBSEEKER_PRODUCT_ID = 'prod_UTgr8MlNvkYiwU';
    const signupSearchParams = new URLSearchParams(window.location.search);
    const signupLocale = signupSearchParams.get('locale') || '';
    const signupCountryCode = signupSearchParams.get('country_code') || signupLocale || '';
    const signupTargetContactType = signupSearchParams.get('target_contact_type') || signupSearchParams.get('employee_title') || signupSearchParams.get('job_title') || '';
    const signupTargetCountry = signupSearchParams.get('target_country') || signupSearchParams.get('employee_country') || signupSearchParams.get('country') || '';
    const signupCustomerType = signupSearchParams.get('customer_type') || signupSearchParams.get('customerType') || signupSearchParams.get('type') || '';
    const normalizedCountryCode = signupCountryCode.trim().toLowerCase();
    const normalizedSignupLocale = normalizeSignupLocale(signupLocale || normalizedCountryCode);
    const normalizedCustomerType = signupCustomerType.trim().toLowerCase();
    const COMPANY_COUNTRY_PRICING = <?php echo json_encode(coherta_market_pricing_js_map(), JSON_UNESCAPED_SLASHES); ?>;

    function getBrowserLanguage() {
        const languages = Array.isArray(navigator.languages) && navigator.languages.length ? navigator.languages : [navigator.language || ''];
        return languages.some((language) => String(language || '').toLowerCase().startsWith('da')) ? 'da' : 'en';
    }

    function normalizeSignupLocale(value) {
        const key = String(value || '').trim().toLowerCase().replace(/[\s_]+/g, '-');
        const map = { dk: 'da', gb: 'uk', sv: 'se', nb: 'no' };
        return map[key] || key;
    }

    function getDefaultLanguage() {
        if (!normalizedCountryCode) return getBrowserLanguage();
        return ['dk', 'da', 'denmark', 'danmark'].includes(normalizedCountryCode) ? 'da' : 'en';
    }

    const DEFAULT_LANGUAGE = getDefaultLanguage();

    const COMPANY_SUBSCRIPTION_PLAN = {
        plan: 'growth',
        plan_label: 'Standard',
        billing_cycle: 'monthly',
        monthly_price: 999,
        currency: 'DKK',
        stripe_price_id: '',
        stripe_product_id: ''
    };

    const PRIVATE_SUBSCRIPTION_PLAN = {
        plan: 'growth',
        plan_label: 'Recruiter',
        billing_cycle: 'monthly',
        monthly_price: 299,
        currency: 'DKK',
        stripe_price_id: '',
        stripe_product_id: JOBSEEKER_PRODUCT_ID
    };

    function getDefaultCustomerType() {
        return ['private', 'person', 'privat', 'privatperson', 'individual', 'jobseeker', 'job_seekers', 'job-seeker'].includes(normalizedCustomerType) ? 'private' : 'company';
    }

    const DEFAULT_CUSTOMER_TYPE = getDefaultCustomerType();

    const DEFAULT_SUBSCRIPTION_PLAN = {
        ...(DEFAULT_CUSTOMER_TYPE === 'private' ? PRIVATE_SUBSCRIPTION_PLAN : COMPANY_SUBSCRIPTION_PLAN)
    };

    const defaultCountryPricing = getCompanyCountryPricingOverride(normalizedCountryCode);
    const defaultProductPricing = getMarketProductPricing(DEFAULT_CUSTOMER_TYPE, 'standard', normalizedCountryCode);
    if (defaultCountryPricing && defaultProductPricing) {
        DEFAULT_SUBSCRIPTION_PLAN.monthly_price = defaultProductPricing.monthly_price;
        DEFAULT_SUBSCRIPTION_PLAN.currency = defaultCountryPricing.currency;
        if (defaultProductPricing.product_id) DEFAULT_SUBSCRIPTION_PLAN.stripe_product_id = defaultProductPricing.product_id;
    }

    function getDefaultTargetCountry() {
        return signupTargetCountry || getCompanyCountryPricingOverride(normalizedCountryCode)?.label || 'Denmark';
    }

    const DEFAULT_TRIAL = {
        title: signupTargetContactType || decodeURIComponent(window.location.hash.match(/title=([^&]+)/)?.[1] || '') || (DEFAULT_CUSTOMER_TYPE === 'private' ? 'recruiters' : ''),
        connections: DEFAULT_CUSTOMER_TYPE === 'private' ? 25 : 10,
        period: DEFAULT_CUSTOMER_TYPE === 'private' ? '30 days' : '24 hours',
        price: priceFormat(DEFAULT_SUBSCRIPTION_PLAN.monthly_price, DEFAULT_SUBSCRIPTION_PLAN.currency),
        monthlyPrice: DEFAULT_SUBSCRIPTION_PLAN.monthly_price,
        currency: DEFAULT_SUBSCRIPTION_PLAN.currency,
        trialDays: DEFAULT_CUSTOMER_TYPE === 'private' ? 30 : 7,
        organizationLabel: DEFAULT_CUSTOMER_TYPE === 'private' ? 'person' : 'organization',
        logoSrc: '/app/assets/images/logo/coherta-logo.png?w=32&h=32',
        targetCountry: getDefaultTargetCountry(),
        targetSentence: 'We will connect you with ' + (signupTargetContactType || (DEFAULT_CUSTOMER_TYPE === 'private' ? 'recruiters' : 'CEOs')) + ' in ' + getDefaultTargetCountry() + '.',
        testSummary: 'The test will happen over ' + (DEFAULT_CUSTOMER_TYPE === 'private' ? '30 days' : '24 hours') + '.',
        customerName: '',
        customerEmail: '',
        companyName: '',
        companyUid: '',
        website: '',
        searchNotes: '',
        userId: null,
        freeTrialId: null,
        activeSubscription: false,
        subscriptionStatus: '',
        subscriptionId: '',
        startedAt: null
    };

    const STEPS = [
        { id: 1, label: 'Agreed test', defaultSub: 'Confirm setup' },
        { id: 2, label: 'Connect LinkedIn', defaultSub: 'Prepare the test' },
        { id: 3, label: 'Activation', defaultSub: 'Automatic trial' },
        { id: 4, label: 'Result & next steps', defaultSub: 'Test is active' }
    ];



    const COPY = {
        en: {
            steps: [
                ['Agreed test', 'Confirm setup'],
                ['Connect LinkedIn', 'Prepare the test'],
                ['Activation', 'Automatic trial'],
                ['Result & next steps', 'Test is active']
            ],
            setupSaved: 'Setup saved', connected: 'Connected', activated: 'Activated', nextStep: 'Next step', blocked: 'Complete the previous step first',
            contactLabel: 'Your contact person', contactNote: 'Questions about the test or LinkedIn?<br>Write or call directly, and we will help you move forward.',
            memberDI: 'Member of Danish Industry', memberDE: 'Member of Danish Chamber of Commerce', homeAria: 'Coherta front page',
            startTitle: 'Start using Coherta', manualLead: 'Tell Coherta who you want to find. We summarize the test in the next step before you connect LinkedIn.',
            setupHowTitle: 'How the test works', setupHowCopy: 'The test uses LinkedIn to connect you with the target audience you choose here.', setupFormTitle: 'Your test setup', setupTypeLabel: 'Are you testing as a private person or company?', targetHelp: 'Enter the LinkedIn title of the people you want the test to find.', continueSummary: 'Continue to summary', incompleteNote: 'Fill in all fields and use a valid email to continue.',
            privatePerson: 'Private person', company: 'Company',
            yourName: 'Your name', workEmail: 'Work email', privateEmail: 'Private email', whoFind: 'Who should we find?', market: 'Market',
            marketNames: { Denmark: 'Denmark', Sweden: 'Sweden', Norway: 'Norway', 'United Kingdom': 'United Kingdom', Germany: 'Germany', Netherlands: 'Netherlands', Europe: 'Europe', Other: 'Other' },
            namePh: 'John Smith', emailPh: 'name@company.com', targetPh: 'Example: Architects', continueLinkedIn: 'Continue and connect LinkedIn',
            dear: 'Dear', setupReady: 'Your setup is ready.', intro1: 'As agreed, we connect your LinkedIn profile to Coherta during the test period and connect you with the target audience we discussed.',
            intro2: 'The goal is to show how automation can expand your professional network and make networking easier to scale.',
            introStrong: '', intro3: 'After the test, the same setup can be expanded with messages, likes, waiting times, variants, and follow-up flows. We help you with that and share our recommendations.',
            agreementTitle: 'What we agreed', audience: 'Audience', testPeriod: 'Test period', channel: 'Channel', measuringTitle: 'How we measure the test', measuringCopy: 'We use LinkedIn connections as the primary test action and track whether the agreed target audience accepts the requests.',
            connectOnly: 'Connection requests only during the test period.', noMessages: 'No messages are sent as part of the test.', resultAfter: 'You receive the result and recommended next steps after the period.',
            connectTitle: 'Connect LinkedIn', connectLead: 'We prepare your LinkedIn profile for the test. The profile is connected to Coherta automation, so we can connect you with the agreed audience once the test is activated.',
            linkedinUseTitle: 'How LinkedIn is used', linkedinUseCopy: 'In this test, LinkedIn is only used for connection requests and result measurement.',
            connections: 'Connections', connectionsCopy: 'We connect you with the agreed target audience.', noMessagesTitle: 'No messages', noMessagesCopy: 'We do not send messages in the test.', controlTitle: 'You stay in control', controlCopy: 'When the test is active, you can follow the status from your account.',
            liUser: 'LinkedIn email or username', password: 'Password', passwordPh: 'Enter password', passwordAria: 'Show or hide password', liConnected: 'LinkedIn is connected', authCode: 'Microsoft Authenticator code', smsCode: 'SMS or email code', codePh: 'Enter code', approveCode: 'Approve code', checkStatus: 'Check LinkedIn status', connecting: 'Connecting...', connectLinkedIn: 'Connect LinkedIn', back: 'Back', continueActivation: 'Continue to activation', lockedNote: 'The button activates when LinkedIn is connected.',
            activation: 'Activation', activationLead: (days) => `Your ${days === 1 ? '1-day' : `${days}-day`} trial is ready. Activate the account to create the subscription automatically and start the test.`, trialPeriod: 'Trial period', trialLength: (days) => days === 1 ? '1 day' : `${days} days`, trialSetupCopy: 'Your Coherta account and subscription are created automatically when you activate.', automaticSetup: 'Automatic setup', automaticSetupCopy: 'The trial starts as soon as the account and subscription have been created.', followAccount: 'You can follow the test and manage the process from your account.', resultMail: 'You receive the result and our recommended next steps by email.', activateAccount: 'Activate account', activationNote: 'The trial length comes from the agreement you received.',
            activeTitle: 'The test is active', activeLead: 'The test is now active. We run the agreed LinkedIn test and send the result by email after the test period.', whatNow: 'What happens now?', now1: 'We connect you with the agreed target audience.', now2: 'You can follow status and manage the process from your account.', now3: 'After the test period, you receive the result and our recommended next steps.', account: 'Your account', accountCopy: 'Here you can follow the test, see status, and manage your account.', accountNote: 'The account page is your fixed overview throughout the test.', openAccount: 'Open my account',
            loadingTitle: 'Loading your Coherta test', loadingLead: 'We are getting the latest setup details from Coherta.', errorTitle: 'We could not load the test', errorFallback: 'Check the link and try again.', createManual: 'Create a test manually', fillRequired: 'Please fill in the required fields before continuing.'
        },
        da: {
            steps: [
                ['Aftalt test', 'Bekræftet test'],
                ['Forbind LinkedIn', 'Gør klar til test'],
                ['Aktivering', 'Automatisk prøveperiode'],
                ['Resultat & næste skridt', 'Testen er aktiv']
            ],
            setupSaved: 'Opsætning gemt', connected: 'Forbundet', activated: 'Aktiveret', nextStep: 'Næste skridt', blocked: 'Gennemfør forrige trin først',
            contactLabel: 'Din kontaktperson', contactNote: 'Spørgsmål til testen eller LinkedIn?<br>Skriv eller ring direkte, så hjælper vi dig videre.',
            memberDI: 'Medlem af Dansk Industri', memberDE: 'Medlem af Dansk Erhverv', homeAria: 'Coherta forside',
            startTitle: 'Start med Coherta', manualLead: 'Udfyld hvem du ønsker at finde, så opsummerer vi testen i næste step, inden du forbinder LinkedIn.',
            setupHowTitle: 'Sådan fungerer testen', setupHowCopy: 'Testen bruger LinkedIn til at forbinde dig med den målgruppe, du vælger her.', setupFormTitle: 'Din testopsætning', setupTypeLabel: 'Tester du som privatperson eller virksomhed?', targetHelp: 'Indtast den LinkedIn-titel på de personer, du ønsker testen skal finde.', continueSummary: 'Fortsæt til opsummering', incompleteNote: 'Udfyld alle felter og brug en gyldig email for at fortsætte.',
            privatePerson: 'Privatperson', company: 'Virksomhed',
            yourName: 'Dit navn', workEmail: 'Arbejdsmail', privateEmail: 'Privat e-mail', whoFind: 'Hvem skal vi finde?', market: 'Marked',
            marketNames: { Denmark: 'Danmark', Sweden: 'Sverige', Norway: 'Norge', 'United Kingdom': 'Storbritannien', Germany: 'Tyskland', Netherlands: 'Nederlandene', Europe: 'Europa', Other: 'Andet' },
            namePh: 'Jens Andersen', emailPh: 'navn@virksomhed.dk', targetPh: 'Eksempel: Arkitekter', continueLinkedIn: 'Fortsæt og forbind LinkedIn',
            dear: 'Kære', setupReady: 'Nu er din opsætning klar.', intro1: 'Som aftalt kobler vi din LinkedIn-profil på Coherta i testperioden og forbinder dig med den målgruppe, vi har talt om.',
            intro2: 'Formålet er at vise, hvordan automatisering kan udvide dit professionelle netværk og gøre netværksarbejdet lettere at skalere.',
            introStrong: '', intro3: 'Efter testen kan samme opsætning bygges videre med beskeder, likes, ventetider, varianter og opfølgningsflows. Det hjælper vi dig med, og kommer med vores anbefalinger.',
            agreementTitle: 'Det har vi aftalt', audience: 'Målgruppe', testPeriod: 'Testperiode', channel: 'Kanal', measuringTitle: 'Sådan måler vi testen', measuringCopy: 'Vi bruger LinkedIn-forbindelser som testens primære handling og følger, om den aftalte målgruppe accepterer anmodningerne.',
            connectOnly: 'Kun forbindelsesanmodninger i testperioden.', noMessages: 'Ingen beskeder sendes som del af testen.', resultAfter: 'Du får resultatet og anbefalede næste skridt efter perioden.',
            connectTitle: 'Forbind LinkedIn', connectLead: 'Vi gør din LinkedIn-profil klar til testen. Profilen kobles på Cohertas automatisering, så vi kan forbinde dig med den aftalte målgruppe, når testen aktiveres.',
            linkedinUseTitle: 'Sådan bruges LinkedIn', linkedinUseCopy: 'I denne test bruges LinkedIn kun til forbindelsesanmodninger og resultatmåling.',
            connections: 'Forbindelser', connectionsCopy: 'Vi forbinder dig med den aftalte målgruppe.', noMessagesTitle: 'Ingen beskeder', noMessagesCopy: 'Vi sender ikke beskeder i testen.', controlTitle: 'Du har kontrol', controlCopy: 'Når testen er aktiv, kan du følge status fra din konto.',
            liUser: 'LinkedIn e-mail eller brugernavn', password: 'Adgangskode', passwordPh: 'Indtast adgangskode', passwordAria: 'Vis eller skjul adgangskode', liConnected: 'LinkedIn er forbundet', authCode: 'Microsoft Authenticator-kode', smsCode: 'SMS- eller e-mailkode', codePh: 'Indtast kode', approveCode: 'Godkend kode', checkStatus: 'Tjek LinkedIn-status', connecting: 'Forbinder...', connectLinkedIn: 'Forbind LinkedIn', back: 'Tilbage', continueActivation: 'Fortsæt til aktivering', lockedNote: 'Knappen aktiveres, når LinkedIn er forbundet.',
            activation: 'Aktivering', activationLead: (days) => `Din prøveperiode på ${days === 1 ? '1 dag' : `${days} dage`} er klar. Aktivér kontoen for automatisk at oprette abonnementet og starte testen.`, trialPeriod: 'Prøveperiode', trialLength: (days) => days === 1 ? '1 dag' : `${days} dage`, trialSetupCopy: 'Din Coherta-konto og dit abonnement oprettes automatisk, når du aktiverer.', automaticSetup: 'Automatisk opsætning', automaticSetupCopy: 'Prøveperioden starter, så snart kontoen og abonnementet er oprettet.', followAccount: 'Du kan følge testen og administrere forløbet fra din konto.', resultMail: 'Du får resultatet og vores anbefalede næste skridt på mail.', activateAccount: 'Aktivér konto', activationNote: 'Prøveperiodens længde kommer fra den aftale, du har modtaget.',
            activeTitle: 'Testen er aktiv', activeLead: 'Testen er nu aktiv. Vi gennemfører den aftalte LinkedIn-test og sender resultatet på mail efter testperioden.', whatNow: 'Hvad sker der nu?', now1: 'Vi forbinder dig med den aftalte målgruppe.', now2: 'Du kan følge status og administrere forløbet fra din konto.', now3: 'Efter testperioden får du resultatet og vores anbefalede næste skridt.', account: 'Din konto', accountCopy: 'Her kan du følge testen, se status og administrere din konto.', accountNote: 'Kontosiden er dit faste overblik under hele testen.', openAccount: 'Tilgå min konto',
            loadingTitle: 'Indlæser din Coherta-test', loadingLead: 'Vi henter de seneste opsætningsdetaljer fra Coherta.', errorTitle: 'Vi kunne ikke indlæse testen', errorFallback: 'Tjek linket og prøv igen.', createManual: 'Opret en test manuelt', fillRequired: 'Udfyld de påkrævede felter før du fortsætter.'
        }
    };

    function copy(key, ...args) {
        const value = (COPY[state.language] || COPY.en)[key] ?? COPY.en[key] ?? key;
        return typeof value === 'function' ? value(...args) : value;
    }

    function stepMeta() {
        return (COPY[state.language] || COPY.en).steps.map(([label, defaultSub], index) => ({ id: index + 1, label, defaultSub }));
    }
    const state = {
        currentStep: 1,
        language: DEFAULT_LANGUAGE,
        customerType: DEFAULT_CUSTOMER_TYPE,
        linkedinConnected: false,
        accountActivated: false,
        linkedInStatus: 'idle',
        linkedinError: '',
        linkedinMessage: '',
        twoFactorType: '',
        username: '',
        password: '',
        twoFactorCode: '',
        showPassword: false,
        selectedTime: '11:00',
        bookingStatus: 'idle',
        activationStatus: 'idle',
        activationError: '',
        loading: true,
        loadError: '',
        companyMarket: '',
        trial: { ...DEFAULT_TRIAL }
    };

    let bearerToken = localStorage.getItem(SESSION_STORAGE_KEY) || '';
    let offerId = '';
    let testId = '';
    let manualSetupMode = false;
    let storageKey = '';
    let linkedInStorageKey = '';

    function wait(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function normalizeIdentifier(value) {
        return String(value || '').trim().replace(/[^A-Za-z0-9_]/g, '');
    }

    function getOfferIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const ignoredQueryFlags = ['TalentAttract'];
        const keys = ['currentOfferId', 'offer_id', 'offerId', 'offer', 'id'];
        for (const key of keys) {
            const value = normalizeIdentifier(params.get(key));
            if (value) return value;
        }

        for (const [key, value] of params.entries()) {
            const normalizedKey = normalizeIdentifier(key);
            if (!value && /^offer_[A-Za-z0-9_]+$/.test(normalizedKey)) return normalizedKey;
        }

        const rawQuery = window.location.search.replace(/^\?/, '').trim();
        if (/^[A-Za-z0-9_]+$/.test(rawQuery) && !ignoredQueryFlags.includes(rawQuery)) return normalizeIdentifier(rawQuery);

        const ignoredPathParts = ['free_trial', 'free-trial', 'trial', 'app', 'index'];
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        const lastPart = (pathParts[pathParts.length - 1] || '').replace(/\.php$/i, '');
        if (lastPart && !ignoredPathParts.includes(lastPart.toLowerCase()) && /^[A-Za-z0-9_]+$/.test(lastPart)) return normalizeIdentifier(lastPart);
        return '';
    }

    function unwrapOfferPayload(payload) {
        if (!payload || typeof payload !== 'object') return null;
        if (payload.offer && typeof payload.offer === 'object') return payload.offer;
        if (payload.data && typeof payload.data === 'object') return payload.data;
        if (payload.result && typeof payload.result === 'object') return payload.result;
        return payload;
    }

    function firstValue(source, keys, fallback = null) {
        source = source || {};
        for (const key of keys) {
            if (Object.prototype.hasOwnProperty.call(source, key) && source[key] !== null && source[key] !== '') return source[key];
        }
        return fallback;
    }

    function intValue(source, keys, fallback) {
        source = source || {};
        for (const key of keys) {
            const value = Number(source[key]);
            if (Number.isFinite(value) && value > 0) return Math.floor(value);
        }
        return fallback;
    }

    function currencyCodeFromOffer(currency) {
        const map = { 208: 'DKK', 826: 'GBP', 978: 'EUR', 840: 'USD' };
        if (typeof currency === 'string' && /^[A-Za-z]{3}$/.test(currency)) return currency.toUpperCase();
        return map[Number(currency || 208)] || 'DKK';
    }

    function normalizePricingValue(pricing) {
        if (pricing === null || pricing === undefined || pricing === '') return 0;
        if (typeof pricing === 'number') return pricing;
        if (typeof pricing === 'string') {
            try { return normalizePricingValue(JSON.parse(pricing)); } catch (e) {}
            let cleaned = pricing.replace(/[^0-9,.]/g, '');
            if (!cleaned) return 0;
            if (cleaned.includes(',') && cleaned.includes('.')) cleaned = cleaned.replace(/\./g, '').replace(',', '.');
            else if (cleaned.includes(',')) cleaned = cleaned.replace(',', '.');
            return Number(cleaned) || 0;
        }
        if (Array.isArray(pricing)) {
            for (const item of pricing) {
                const value = normalizePricingValue(item);
                if (value > 0) return value;
            }
            return 0;
        }
        if (typeof pricing === 'object') {
            const keys = ['monthly_price', 'price', 'amount', 'value', 'total', 'unit_amount', 'unit_amount_decimal', 'amount_cents', 'price_cents'];
            for (const key of keys) {
                if (!Object.prototype.hasOwnProperty.call(pricing, key)) continue;
                const value = normalizePricingValue(pricing[key]);
                if (value > 0) return key.includes('cents') || key.includes('unit_amount') ? value / 100 : value;
            }
        }
        return 0;
    }

    function simpleHash(value) {
        let hash = 0;
        const text = String(value || '');
        for (let i = 0; i < text.length; i++) {
            hash = ((hash << 5) - hash) + text.charCodeAt(i);
            hash |= 0;
        }
        return Math.abs(hash);
    }

    function splitName(fullName) {
        const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
        return { first_name: parts.shift() || '', last_name: parts.join(' ') };
    }

    function emailLocalName(email) {
        email = String(email || '').trim();
        if (!email || !email.includes('@')) return '';
        const local = email.split('@')[0].replace(/[^A-Za-z]+/g, ' ').trim();
        return local ? local.toLowerCase().replace(/[a-z]/g, (letter) => letter.toUpperCase()) : '';
    }

    function numberFormat(value) {
        return new Intl.NumberFormat('da-DK').format(Number(value || 0));
    }

    function priceFormat(value, currency) {
        const amount = Number(value || 0);
        if (!amount) return priceFormat(DEFAULT_SUBSCRIPTION_PLAN.monthly_price, DEFAULT_SUBSCRIPTION_PLAN.currency);
        const code = String(currency || 'DKK').toUpperCase();
        const formatted = new Intl.NumberFormat('da-DK', { maximumFractionDigits: 0 }).format(amount);
        const symbolsBefore = { EUR: '€', GBP: '£', USD: '$' };
        const symbolsAfter = { DKK: 'kr.', SEK: 'kr.', NOK: 'kr.', ISK: 'kr.', PLN: 'zł' };
        if (symbolsBefore[code]) return symbolsBefore[code] + formatted;
        if (symbolsAfter[code]) return formatted + ' ' + symbolsAfter[code];
        return code + ' ' + formatted;
    }

    function hoursBetweenDates(startDate, endDate) {
        if (!startDate || !endDate) return null;
        const start = new Date(startDate).getTime();
        const end = new Date(endDate).getTime();
        if (!Number.isFinite(start) || !Number.isFinite(end) || end <= start) return null;
        return Math.max(1, Math.ceil((end - start) / 3600000));
    }

    function addDaysToDate(startDate, days) {
        if (!startDate || !Number(days)) return null;
        const start = new Date(startDate);
        if (Number.isNaN(start.getTime())) return null;
        start.setDate(start.getDate() + Number(days));
        return start.toISOString();
    }

    function buildTrialFromOffer(offer) {
        offer = offer || {};
        const customerType = offer.customer_type === 'private' ? 'private' : 'company';
        const customerPlan = getPlanForCustomerType(customerType);
        const countryPricingOverride = getCountryPricingOverride();
        const productPricingOverride = getMarketProductPricing(customerType, 'standard');
        const currency = countryPricingOverride?.currency || currencyCodeFromOffer(offer.currency ?? customerPlan.currency);
        const trialDays = intValue(offer, ['number_of_days', 'period_days', 'days', 'trial_days'], 7);
        const endDate = String(firstValue(offer, ['end_date'], addDaysToDate(offer.start_date, trialDays)) || '');
        const hoursFromOffer = intValue(offer, ['test_hours', 'hours', 'period_hours', 'trial_hours'], 0);
        const hoursFromDates = hoursBetweenDates(offer.start_date, endDate || null);
        const testHours = hoursFromOffer > 0 ? hoursFromOffer : (hoursFromDates || trialDays * 24 || 24);
        const targetTitle = String(firstValue(offer, ['employee_title', 'target_contact_type', 'target_contact_title', 'target_title', 'segment', 'audience', 'profession', 'job_title'], DEFAULT_TRIAL.title));
        const targetCountry = String(firstValue(offer, ['employee_coutry', 'employee_country', 'target_country', 'country', 'market', 'geography'], DEFAULT_TRIAL.targetCountry));
        const connections = intValue(offer, ['contacts_to_identify', 'connections_to_succeed', 'connection_goal', 'approved_connections_to_succeed', 'contacts_to_succeed', 'contact_goal', 'target_contacts', 'success_contacts'], DEFAULT_TRIAL.connections);
        const monthlyPrice = productPricingOverride?.monthly_price || normalizePricingValue(offer.pricing ?? offer.monthly_price ?? customerPlan.monthly_price);
        const email = String(offer.email || '');
        const customerName = String(firstValue(offer, ['receiver_name', 'name', 'contact_name'], emailLocalName(email)) || '');
        const startedRaw = offer.started;
        let startedAt = null;
        if (startedRaw && startedRaw !== '0' && startedRaw !== 0) {
            const startedDate = new Date(startedRaw === true ? Date.now() : startedRaw);
            if (!Number.isNaN(startedDate.getTime())) startedAt = startedDate.toISOString();
        }
        if (!startedAt && offer.start_date && startedRaw) {
            const startDate = new Date(offer.start_date);
            if (!Number.isNaN(startDate.getTime())) startedAt = startDate.toISOString();
        }

        return {
            ...DEFAULT_TRIAL,
            title: targetTitle,
            connections,
            period: testHours === 24 ? '24 hours' : (testHours % 24 === 0 ? (testHours / 24) + ' days' : testHours + ' hours'),
            price: priceFormat(monthlyPrice, currency),
            monthlyPrice,
            currency,
            trialDays,
            organizationLabel: 'organization',
            targetCountry,
            targetSentence: String(firstValue(offer, ['target_sentence'], 'We will connect you with ' + targetTitle + ' in ' + targetCountry + '.')),
            testSummary: String(firstValue(offer, ['test_summary'], 'The test will happen over ' + testHours + ' hours.')),
            customerName,
            customerEmail: email,
            companyName: offer.company_name || '',
            companyUid: offer.company_uid || '',
            website: offer.website || '',
            searchNotes: offer.search_notes || '',
            userId: offer.user_id || null,
            freeTrialId: offer.id || null,
            activeSubscription: firstValue(offer, ['active_subscription', 'has_active_subscription', 'activeSubscription', 'hasActiveSubscription', 'subscription_active', 'subscriptionActive', 'is_subscribed', 'isSubscribed'], false),
            subscriptionStatus: String(firstValue(offer, ['subscription_status', 'subscriptionStatus', 'stripe_subscription_status', 'stripeSubscriptionStatus', 'status_subscription'], '')),
            subscriptionId: String(firstValue(offer, ['subscription_id', 'subscriptionId', 'stripe_subscription_id', 'stripeSubscriptionId'], '')),
            startedAt,
            testHours,
            mailTitle: offer.mail_title || '',
            mailBody: offer.mail_body || '',
            customerType
        };
    }

    function readManualSetupDraft() {
        try {
            const draft = JSON.parse(localStorage.getItem(MANUAL_SETUP_STORAGE_KEY) || 'null');
            return draft && typeof draft === 'object' ? draft : null;
        } catch (e) {
            return null;
        }
    }

    function saveManualSetupDraft(draft) {
        localStorage.setItem(MANUAL_SETUP_STORAGE_KEY, JSON.stringify(draft));
    }

    function manualDraftToOffer(draft) {
        draft = draft || {};
        const plan = getPlanForCustomerType(draft.customer_type === 'private' ? 'private' : 'company');
        return {
            id: draft.free_trial_id || null,
            user_id: draft.user_id || null,
            receiver_name: draft.customer_name || '',
            email: draft.email || '',
            company_name: draft.company_name || '',
            website: draft.website || '',
            employee_title: draft.target_contact_type || DEFAULT_TRIAL.title,
            employee_country: draft.target_country || DEFAULT_TRIAL.targetCountry,
            contacts_to_identify: draft.contacts_to_identify || DEFAULT_TRIAL.connections,
            number_of_days: draft.number_of_days || DEFAULT_TRIAL.trialDays,
            pricing: draft.pricing || plan.monthly_price,
            currency: draft.currency || plan.currency,
            billing_cycle: draft.billing_cycle || plan.billing_cycle,
            plan: draft.plan || plan.plan,
            plan_label: draft.plan_label || plan.plan_label,
            customer_type: draft.customer_type || 'company',
            search_notes: '',
            started: draft.started || null
        };
    }

    function applyManualSetup(draft) {
        const seed = String((draft.email || '') + '|' + (draft.company_name || '') + '|' + (draft.target_contact_type || '') + '|' + (draft.target_country || ''));
        testId = draft.test_id || ('manual_' + simpleHash(seed + '|' + Date.now()));
        draft.test_id = testId;
        saveManualSetupDraft(draft);
        state.customerType = draft.customer_type === 'private' ? 'private' : 'company';
        state.trial = buildTrialFromOffer(manualDraftToOffer(draft));
        applySubscriptionPlan(state.customerType);
        state.username = state.username || state.trial.customerEmail || '';
        storageKey = 'coherta_free_test_' + testId;
        linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
    }

    function canVisitStep(targetStep, currentStep, linkedinConnected, accountActivated) {
        if (targetStep === 1 || targetStep === 2) return true;
        if (targetStep === 3) return linkedinConnected;
        if (targetStep === 4) return linkedinConnected && accountActivated;
        return false;
    }

    function getStepSubLabel(stepId, { currentStep, linkedinConnected, accountActivated }) {
        if (stepId === 1 && manualSetupMode && state.trial.customerEmail) return copy('setupSaved');
        if (stepId === 2 && linkedinConnected) return copy('connected');
        if (stepId === 3 && accountActivated) return copy('activated');
        if (stepId === 4 && currentStep === 4) return copy('nextStep');
        return stepMeta()[stepId - 1]?.defaultSub || '';
    }


    function isTruthyFlag(value) {
        if (value === true || value === 1) return true;
        if (value === false || value === 0 || value === null || value === undefined) return false;
        if (typeof value === 'string') {
            const normalized = value.trim().toLowerCase();
            if (!normalized || ['0', 'false', 'no', 'inactive', 'cancelled', 'canceled', 'expired', 'none', 'null'].includes(normalized)) return false;
            if (['1', 'true', 'yes', 'active', 'trialing', 'paid', 'current'].includes(normalized)) return true;
        }
        if (typeof value === 'object') return Object.keys(value).length > 0;
        return Boolean(value);
    }

    function customerHasActiveSubscription() {
        const trial = state.trial || {};
        const status = String(trial.subscriptionStatus || '').trim().toLowerCase();
        const inactiveStatuses = ['canceled', 'cancelled', 'expired', 'incomplete_expired', 'unpaid'];
        if (inactiveStatuses.includes(status)) return false;
        return isTruthyFlag(trial.activeSubscription)
            || ['active', 'trialing', 'paid', 'current'].includes(status)
            || Boolean(trial.subscriptionId);
    }
    function hasTalentAttractParam() {
        return new URLSearchParams(window.location.search).has('TalentAttract');
    }

    function normalizeMarketKey(value) {
        return String(value || '').trim().toLowerCase().replace(/[\s_]+/g, '-').replace(/[^a-z0-9-]/g, '');
    }

    function getCompanyCountryPricingOverride(countryCode = normalizedCountryCode) {
        const key = normalizeMarketKey(countryCode);
        return COMPANY_COUNTRY_PRICING[key] || null;
    }

    function getActiveMarketCode() {
        return normalizedCountryCode || state.companyMarket || state.trial?.targetCountry || '';
    }

    function getMarketProductPricing(type = state.customerType, plan = 'standard', countryCode = getActiveMarketCode()) {
        const marketPricing = getCompanyCountryPricingOverride(countryCode);
        if (!marketPricing) return null;
        const customerType = type === 'private' ? 'private' : 'company';
        const planKey = plan === 'extended' ? 'extended' : 'standard';
        return marketPricing?.[customerType]?.[planKey] || marketPricing?.[customerType]?.standard || null;
    }

    function isDanishSignupLocale() {
        const key = String(normalizedSignupLocale || '').trim().toLowerCase().replace(/[\s_]+/g, '-');
        return ['da', 'dk', 'denmark', 'danmark'].includes(key);
    }

    function isUkCountryCode() {
        const key = normalizeMarketKey(getActiveMarketCode());
        return ['uk', 'gb', 'unitedkingdom', 'united-kingdom'].includes(key);
    }

    function getPlanForCustomerType(type) {
        const plan = type === 'private' ? PRIVATE_SUBSCRIPTION_PLAN : COMPANY_SUBSCRIPTION_PLAN;
        const resolved = { ...plan };
        const countryPricingOverride = getCompanyCountryPricingOverride(getActiveMarketCode());
        const productPricing = getMarketProductPricing(type, 'standard');
        if (countryPricingOverride && productPricing) {
            resolved.monthly_price = productPricing.monthly_price;
            resolved.currency = countryPricingOverride.currency;
            if (productPricing.product_id) resolved.stripe_product_id = productPricing.product_id;
        }
        return resolved;
    }

    function applySubscriptionPlan(type) {
        const plan = getPlanForCustomerType(type);
        Object.assign(DEFAULT_SUBSCRIPTION_PLAN, plan);
        state.trial.monthlyPrice = plan.monthly_price;
        state.trial.currency = plan.currency;
        state.trial.price = priceFormat(plan.monthly_price, plan.currency);
        state.trial.organizationLabel = type === 'private' ? 'person' : 'organization';
        if (manualSetupMode) {
            state.trial.trialDays = type === 'private' ? 30 : 7;
            state.trial.connections = type === 'private' ? 25 : 10;
            state.trial.period = type === 'private' ? '30 days' : '24 hours';
            if (type === 'private' && (!state.trial.title || state.trial.title === 'CEOs')) state.trial.title = 'recruiters';
        }
    }

    function getCountryPricingOverride() {
        return getCompanyCountryPricingOverride(getActiveMarketCode());
    }

    function readStoredTest() {
        try {
            const stored = JSON.parse(localStorage.getItem(storageKey) || 'null');
            return stored && stored.started_at ? stored : null;
        } catch (e) {
            return null;
        }
    }

    function storeStartedTest(startedAt) {
        localStorage.setItem(storageKey, JSON.stringify({
            test_id: testId,
            offer_id: offerId,
            manual_setup: manualSetupMode,
            started_at: startedAt,
            created_from: window.location.pathname
        }));
    }

    function readSessionId() {
        return localStorage.getItem(SESSION_STORAGE_KEY) || '';
    }

    function extractSessionId(payload) {
        if (!payload || typeof payload !== 'object') return '';
        const directKeys = ['session_id', 'sessionId', 'token', 'access_token', 'accessToken', 'bearer_token', 'bearerToken'];
        for (const key of directKeys) {
            if (payload[key]) return String(payload[key]);
        }
        for (const key of ['data', 'user', 'session', 'auth']) {
            const nested = extractSessionId(payload[key]);
            if (nested) return nested;
        }
        return '';
    }

    function persistSessionId(sessionId) {
        const cleanSessionId = String(sessionId || '').trim();
        if (!cleanSessionId) return '';
        bearerToken = cleanSessionId;
        localStorage.setItem(SESSION_STORAGE_KEY, cleanSessionId);
        return cleanSessionId;
    }

    function clearStoredSession() {
        bearerToken = '';
        state.trial.userId = null;
        localStorage.removeItem(SESSION_STORAGE_KEY);
        localStorage.removeItem(CURRENT_USER_STORAGE_KEY);
        localStorage.removeItem('user');
    }

    function getCurrentSessionId() {
        if (!bearerToken) bearerToken = readSessionId();
        return bearerToken;
    }

    function unwrapCurrentUser(payload) {
        if (!payload || typeof payload !== 'object') return null;
        if (payload.user && typeof payload.user === 'object') return payload.user;
        if (payload.data && typeof payload.data === 'object') {
            if (payload.data.user && typeof payload.data.user === 'object') return payload.data.user;
            if (payload.data.id || payload.data.user_id) return payload.data;
        }
        if (payload.id || payload.user_id || payload.email || payload.username) return payload;
        return null;
    }

    function getUserIdFromUser(user) {
        if (!user || typeof user !== 'object') return null;
        return user.user_id || user.id || user.uid || null;
    }

    function readStoredCurrentUser() {
        try {
            return JSON.parse(localStorage.getItem(CURRENT_USER_STORAGE_KEY) || localStorage.getItem('user') || 'null');
        } catch (e) {
            localStorage.removeItem(CURRENT_USER_STORAGE_KEY);
            localStorage.removeItem('user');
            return null;
        }
    }

    function persistCurrentUser(user) {
        if (!user || typeof user !== 'object') return null;
        localStorage.setItem(CURRENT_USER_STORAGE_KEY, JSON.stringify(user));
        localStorage.setItem('user', JSON.stringify(user));
        const userId = getUserIdFromUser(user);
        if (userId) state.trial.userId = userId;
        return user;
    }

    function unwrapCompanyPayload(payload) {
        if (!payload || typeof payload !== 'object') return null;
        if (Array.isArray(payload)) return payload[0] || null;
        if (payload.company && typeof payload.company === 'object') return payload.company;
        if (payload.data && typeof payload.data === 'object') return unwrapCompanyPayload(payload.data);
        if (payload.id || payload.uid || payload.market || payload.country || payload.country_code || payload.company_name) return payload;
        return null;
    }

    function extractCompanyMarket(company) {
        if (!company || typeof company !== 'object') return '';
        return String(firstValue(company, [
            'market',
            'company_market',
            'billing_market',
            'country',
            'country_code',
            'countryCode',
            'company_country',
            'geography'
        ], '') || '');
    }

    function applyCompanyMarketPricing(company) {
        if (state.customerType === 'private' || customerHasActiveSubscription()) return false;
        const market = extractCompanyMarket(company);
        if (market) state.companyMarket = market;
        if (normalizedCountryCode) return false;

        const pricing = getCompanyCountryPricingOverride(market);
        const productPricing = getMarketProductPricing(state.customerType, 'standard', market);
        if (!pricing || !productPricing) return false;

        DEFAULT_SUBSCRIPTION_PLAN.monthly_price = productPricing.monthly_price;
        DEFAULT_SUBSCRIPTION_PLAN.currency = pricing.currency;
        if (productPricing?.product_id) DEFAULT_SUBSCRIPTION_PLAN.stripe_product_id = productPricing.product_id;
        state.trial.monthlyPrice = productPricing.monthly_price;
        state.trial.currency = pricing.currency;
        state.trial.price = priceFormat(productPricing.monthly_price, pricing.currency);
        return true;
    }

    async function hydrateCurrentCompanyFromSession() {
        const sessionId = getCurrentSessionId();
        if (!sessionId) return null;

        const endpoints = [
            'https://api.coherta.com/users/me/company',
            'https://api.coherta.com/users/me/companies'
        ];

        for (const endpoint of endpoints) {
            try {
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        Authorization: 'Bearer ' + sessionId
                    }
                });

                if (response.status === 401 || response.status === 403) {
                    clearStoredSession();
                    return null;
                }

                if (!response.ok) continue;

                const payload = await response.json().catch(() => null);
                const company = unwrapCompanyPayload(payload);
                if (company) {
                    applyCompanyMarketPricing(company);
                    return company;
                }
            } catch (e) {}
        }

        return null;
    }

    function syncUserIdFromLocalStorage() {
        const user = readStoredCurrentUser();
        const userId = getUserIdFromUser(user);
        if (userId) state.trial.userId = userId;
        return userId;
    }

    async function hydrateCurrentUserFromSession() {
        const sessionId = getCurrentSessionId();
        if (!sessionId) {
            localStorage.removeItem(CURRENT_USER_STORAGE_KEY);
            return null;
        }

        try {
            const response = await fetch('https://api.coherta.com/users/me', {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    Authorization: 'Bearer ' + sessionId
                }
            });

            if (response.status === 401 || response.status === 403) {
                clearStoredSession();
                return null;
            }

            if (!response.ok) return readStoredCurrentUser();

            const payload = await response.json().catch(() => null);
            const user = unwrapCurrentUser(payload);
            return user ? persistCurrentUser(user) : null;
        } catch (e) {
            return readStoredCurrentUser();
        }
    }

    function headersJson(useAuth = true) {
        const headers = { 'Content-Type': 'application/json' };
        const sessionId = getCurrentSessionId();
        if (useAuth && sessionId) headers.Authorization = 'Bearer ' + sessionId;
        return headers;
    }

    async function fetchOfferData(currentOfferId) {
        const headers = { Accept: 'application/json' };
        if (bearerToken) headers.Authorization = 'Bearer ' + bearerToken;
        const response = await fetch(OFFER_ENDPOINT_BASE + encodeURIComponent(currentOfferId), { method: 'GET', headers });
        if (!response.ok) throw new Error('The offer could not be loaded from Coherta. HTTP ' + response.status + '.');
        const payload = await response.json();
        const offer = unwrapOfferPayload(payload);
        if (!offer || typeof offer !== 'object') throw new Error('The offer endpoint did not return a valid offer object.');
        return offer;
    }

    async function hydrateLinkedInState() {
        if (linkedInStorageKey && localStorage.getItem(linkedInStorageKey) === '1') {
            state.linkedinConnected = true;
            state.linkedInStatus = 'connected';
            return;
        }
        if (!bearerToken) return;
        try {
            const response = await fetch('https://api.coherta.com/apps/linkedin/connect', { method: 'GET', headers: headersJson(true) });
            const data = await response.json().catch(() => ({}));
            if (Number(data.status) === 1) markLinkedInConnected(false);
        } catch (e) {}
    }

    function markLinkedInConnected(shouldRender = true) {
        state.linkedinConnected = true;
        state.linkedInStatus = 'connected';
        state.linkedinError = '';
        state.linkedinMessage = 'LinkedIn is connected.';
        if (linkedInStorageKey) localStorage.setItem(linkedInStorageKey, '1');
        if (shouldRender) render();
    }

    function getStoredPaymentContext() {
        const context = {};
        try {
            const paid = localStorage.getItem('paid');
            const pacid = localStorage.getItem('pacid');
            if (paid !== null && paid !== '') context.paid = paid;
            if (pacid !== null && pacid !== '') context.pacid = pacid;
        } catch (e) {}
        return context;
    }

    async function createCohertaUserForLinkedIn() {
        const existingSessionId = getCurrentSessionId();
        if (existingSessionId) {
            const user = await hydrateCurrentUserFromSession();
            return { session_id: existingSessionId, token: existingSessionId, user, reused: true };
        }

        const nameParts = splitName(state.trial.customerName || state.trial.customerEmail || '');
        const userPayload = {
            phone: '',
            username: state.trial.customerEmail || state.username,
            password: true,
            email: state.trial.customerEmail || state.username,
            first_name: nameParts.first_name,
            last_name: nameParts.last_name,
            company_name: state.trial.companyName,
            free_trial_id: state.trial.freeTrialId || null,
            offer_id: offerId || '',
            use_case: (manualSetupMode ? 'Manual Free Trial: ' : 'Free Trial offer: ') + (state.trial.title || state.trial.freeTrialId || ''),
            token: true,
            session_id: true,
            ...getStoredPaymentContext()
        };

        const response = await fetch('https://api.coherta.com/users/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userPayload)
        });
        const data = await response.json().catch(() => ({}));
        const sessionId = extractSessionId(data);

        if (!response.ok && !sessionId) {
            throw new Error(data.message || data.error || 'Coherta could not create a user session for the LinkedIn connection.');
        }

        if (sessionId) persistSessionId(sessionId);

        if (data.user_id || data.id || data?.user?.id || data?.data?.user_id || data?.data?.id) {
            state.trial.userId = data.user_id || data.id || data?.user?.id || data?.data?.user_id || data?.data?.id;
            const draft = readManualSetupDraft();
            if (manualSetupMode && draft) {
                draft.user_id = state.trial.userId;
                saveManualSetupDraft(draft);
            }
        }

        if (!getCurrentSessionId()) throw new Error('Coherta could not create a session for the LinkedIn connection.');
        return data;
    }

    async function connectLinkedIn() {
        if (state.linkedinConnected || state.linkedInStatus === 'submitting') return;
        if (state.linkedInStatus === 'app-approval') {
            state.linkedinError = '';
            await pollLinkedInStatus(30);
            return;
        }
        state.linkedinError = '';
        state.linkedinMessage = '';
        state.linkedInStatus = 'submitting';
        render();

        try {
            await createCohertaUserForLinkedIn();
            const sessionId = getCurrentSessionId();
            if (!sessionId) throw new Error('Coherta could not create a session for the LinkedIn connection.');

            const response = await fetch('https://api.coherta.com/apps/linkedin/connect', {
                method: 'POST',
                headers: headersJson(true),
                body: JSON.stringify({ username: state.username, password: state.password })
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || result.error || 'LinkedIn could not be connected. Please try again.');
            state.linkedInStatus = 'waiting';
            state.linkedinMessage = 'We are checking the LinkedIn login status.';
            render();
            await pollLinkedInStatus(90);
        } catch (error) {
            state.linkedInStatus = 'idle';
            state.linkedinMessage = '';
            state.linkedinError = error.message || 'Something went wrong while connecting LinkedIn. Please try again.';
            render();
        }
    }

    async function submitTwoFactorCode() {
        if (!state.twoFactorCode.trim()) {
            state.linkedinError = 'Enter the code before continuing.';
            render();
            return;
        }

        state.linkedinError = '';
        state.linkedinMessage = 'We are validating the code.';
        state.linkedInStatus = 'waiting';
        render();

        try {
            await fetch('https://api.coherta.com/apps/linkedin/connect/sms', {
                method: 'PATCH',
                headers: headersJson(true),
                body: JSON.stringify({ sms_code: state.twoFactorCode.trim() })
            });
            await pollLinkedInStatus(90);
        } catch (error) {
            state.linkedInStatus = state.twoFactorType || 'sms';
            state.linkedinError = error.message || 'The code could not be validated. Please try again.';
            render();
        }
    }

    async function pollLinkedInStatus(maxAttempts = 3000) {
        for (let i = 0; i < maxAttempts; i++) {
            const response = await fetch('https://api.coherta.com/apps/linkedin/connect', { method: 'GET', headers: headersJson(true) });
            const data = await response.json().catch(() => ({}));
            const status = Number(data.status);

            if (status === 1) {
                markLinkedInConnected(false);
                render();
                return true;
            }

            if (status === 2) {
                state.linkedInStatus = 'idle';
                state.linkedinMessage = '';
                state.twoFactorType = '';
                state.linkedinError = 'LinkedIn could not validate the email and/or password. Update the password and try again. We keep the same Coherta session.';
                render();
                return false;
            }

            if (status === 8) {
                state.twoFactorType = 'authenticator';
                state.linkedInStatus = 'authenticator';
                state.linkedinMessage = data.code === null ? 'Enter the code from Microsoft Authenticator.' : 'We are validating your authenticator approval.';
                render();
                return false;
            }

            if (status === 9) {
                state.twoFactorType = 'sms';
                state.linkedInStatus = 'sms';
                state.linkedinMessage = 'Enter the SMS or email code you just received.';
                render();
                return false;
            }

            if (status === 10) {
                state.twoFactorType = 'app';
                state.linkedInStatus = 'app-approval';
                state.linkedinMessage = 'Approve the login request in your LinkedIn app.';
                render();
            } else {
                state.linkedInStatus = 'waiting';
                state.linkedinMessage = 'Waiting for LinkedIn approval.';
                render();
            }

            await wait(2000);
        }

        state.linkedinError = 'LinkedIn is still waiting for approval. You can continue checking the status.';
        render();
        return false;
    }

    async function startOfferOnServer() {
        if (!offerId) return null;
        const response = await fetch(FREE_TRIAL_START_ENDPOINT_BASE + encodeURIComponent(offerId), { method: 'POST', headers: headersJson(true) });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.detail || result.message || 'The account could not be activated. Please try again.');
        if (result.stripe_subscription_id) {
            state.trial.activeSubscription = true;
            state.trial.subscriptionId = String(result.stripe_subscription_id);
            state.trial.subscriptionStatus = String(result.stripe_subscription_status || result.subscription_status || 'trialing');
        }
        const responseTrialDays = Number(result.trial_days);
        if (Number.isFinite(responseTrialDays) && responseTrialDays > 0) state.trial.trialDays = responseTrialDays;
        return result;
    }

    async function startCampaign() {
        if (offerId) return null;
        const response = await fetch(FREE_TRIAL_CAMPAIGN_ENDPOINT_BASE, { method: 'POST', headers: headersJson(true), body: JSON.stringify({ employee_title: state.trial.title, country: state.trial.targetCountry }) });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.detail || result.message || 'The test could not be started. Please try again.');
        return result;
    }

    async function completeStartTest() {
        if (state.activationStatus === 'submitting') return;
        state.activationStatus = 'submitting'; state.activationError = ''; render();
        try {
            await startOfferOnServer(); await startCampaign();
            const startedAt = new Date().toISOString();
            storeStartedTest(startedAt); state.trial.startedAt = startedAt; state.accountActivated = true; state.currentStep = 4; state.activationStatus = 'idle'; render();
        } catch (error) {
            state.activationStatus = 'idle'; state.activationError = error.message || 'The account could not be activated. Please try again.'; render();
        }
    }
    function icon(name, size = 22, strokeWidth = 2.2, className = '') {
        const common = `width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-linejoin="round" class="${className}" aria-hidden="true"`;
        const icons = {
            check: `<svg ${common}><path d="M20 6 9 17l-5-5"></path></svg>`,
            arrowRight: `<svg ${common}><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>`,
            arrowLeft: `<svg ${common}><path d="M19 12H5"></path><path d="m12 19-7-7 7-7"></path></svg>`,
            eye: `<svg ${common}><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>`,
            users: `<svg ${common}><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
            clock: `<svg ${common}><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>`,
            briefcase: `<svg ${common}><rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M3 12h18"></path></svg>`,
            calendar: `<svg ${common}><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path><path d="M8 2v4"></path><path d="M3 10h18"></path></svg>`,
            smartphone: `<svg ${common}><rect x="7" y="2" width="10" height="20" rx="2"></rect><path d="M11 18h2"></path></svg>`,
            loader: `<svg ${common} class="${className} spin"><path d="M21 12a9 9 0 0 1-9 9"></path><path d="M3 12a9 9 0 0 1 9-9"></path></svg>`,
            alert: `<svg ${common}><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>`,
            phone: `<svg ${common}><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.89.32 1.76.59 2.6a2 2 0 0 1-.45 2.11L8 9.7a16 16 0 0 0 6.3 6.3l1.27-1.27a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.6.59A2 2 0 0 1 22 16.92Z"></path></svg>`,
            mail: `<svg ${common}><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>`
        };
        return icons[name] || '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buttonPrimary(action, content, disabled = false, extraClass = '') {
        return `<button type="button" class="btn primary ${extraClass}" data-action="${action}" ${disabled ? 'disabled' : ''}>${content}</button>`;
    }

    function buttonSecondary(action, content, disabled = false, extraClass = '') {
        return `<button type="button" class="btn secondary ${extraClass}" data-action="${action}" ${disabled ? 'disabled' : ''}>${content}</button>`;
    }

    function linkedInMark() { return '<span style="font-family:Arial, Helvetica, sans-serif; font-weight:800; line-height:1">in</span>'; }

    function marketName(country) {
        const names = (COPY[state.language] || COPY.en).marketNames || COPY.en.marketNames;
        return names[country] || country;
    }

    function summaryStrip(trial) {
        const items = [{ icon: 'briefcase', label: copy('audience'), value: trial.title }, { icon: 'users', label: copy('connections'), value: String(trial.connections) }, { icon: 'clock', label: copy('testPeriod'), value: trial.period }];
        return `<div class="summary three">${items.map((item) => `<div class="sum-item"><div class="sum-ico">${icon(item.icon, 18, 2.2)}</div><div><div class="sum-label">${escapeHtml(item.label)}</div><div class="sum-val">${escapeHtml(item.value)}</div></div></div>`).join('')}</div>`;
    }
    function testJourney() {
        const { currentStep, linkedinConnected, accountActivated } = state;
        return `<aside class="journey"><div class="journey-list">${stepMeta().map((item) => {
            const isComplete = item.id < currentStep || (item.id === 2 && linkedinConnected && currentStep > 2) || (item.id === 3 && accountActivated && currentStep > 3);
            const isActive = item.id === currentStep;
            const isDisabled = !canVisitStep(item.id, currentStep, linkedinConnected, accountActivated);
            const sub = getStepSubLabel(item.id, { currentStep, linkedinConnected, accountActivated });
            return `<button type="button" class="j-step ${isActive ? 'active' : ''} ${isComplete ? 'done' : ''}" data-action="jump" data-step="${item.id}" ${isDisabled ? `disabled title="${escapeHtml(copy('blocked'))}"` : ''}><div class="dot">${isComplete ? icon('check', 15, 3) : item.id}</div><div><div class="j-title">${escapeHtml(item.label)}</div><div class="j-sub">${escapeHtml(sub)}</div></div></button>`;
        }).join('')}</div></aside>`;
    }

    function flagIcon(lang) {
        if (lang === 'da') {
            return '<svg viewBox="0 0 28 20" aria-hidden="true"><rect width="28" height="20" fill="#c8102e"></rect><rect x="8" width="3" height="20" fill="#fff"></rect><rect y="8.5" width="28" height="3" fill="#fff"></rect></svg>';
        }
        return '<svg viewBox="0 0 28 20" aria-hidden="true"><rect width="28" height="20" fill="#012169"></rect><path d="M0 0l28 20M28 0L0 20" stroke="#fff" stroke-width="4"></path><path d="M0 0l28 20M28 0L0 20" stroke="#c8102e" stroke-width="2.4"></path><path d="M14 0v20M0 10h28" stroke="#fff" stroke-width="6"></path><path d="M14 0v20M0 10h28" stroke="#c8102e" stroke-width="3.4"></path></svg>';
    }

    function languageButton(lang) {
        return `<button type="button" class="language-btn ${state.language === lang ? 'active' : ''}" data-action="set-language" data-language="${lang}" aria-label="${lang === 'en' ? 'Switch to English' : 'Skift til dansk'}">${flagIcon(lang)}</button>`;
    }

    function languageSwitcher() {
        return `<div class="rail-language"><div class="language-switch">${languageButton('da')}${languageButton('en')}</div></div>`;
    }

    function contactCard() {
        return `<aside class="contact-card"><div class="contact-label">${escapeHtml(copy('contactLabel'))}</div><div class="contact-person"><img class="contact-photo" src="https://api.coherta.com/images/img_68ed7e19c4ed8.jpg" alt="Valdemar Bilecka" width="100" height="100"><div><div class="contact-name">Valdemar Bilecka</div><div class="contact-role">Co-founder</div></div></div><div class="contact-links"><a class="contact-link" href="https://www.linkedin.com/in/valdemarbilecka" target="_blank" rel="noreferrer"><span class="inmark">${linkedInMark()}</span> linkedin.com/in/valdemarbilecka</a><a class="contact-link" href="mailto:valdemar@coherta.com">${icon('mail', 19)} valdemar@coherta.com</a><a class="contact-link" href="tel:+4570505666">${icon('phone', 19)} +45 70 50 56 66</a></div><p class="contact-note">${copy('contactNote')}</p></aside>`;
    }

    function customerTypeToggle() {
        return `<div class="self-service-toggle" role="group" aria-label="${escapeHtml(copy('setupTypeLabel'))}"><button type="button" class="${state.customerType === 'private' ? 'active' : ''}" data-action="set-customer-type" data-customer-type="private">${escapeHtml(copy('privatePerson'))}</button><button type="button" class="${state.customerType === 'company' ? 'active' : ''}" data-action="set-customer-type" data-customer-type="company">${escapeHtml(copy('company'))}</button></div>`;
    }

    function manualSetupStep() {
        const trial = state.trial;
        const isTalentAttract = hasTalentAttractParam();
        const targetContactType = isTalentAttract ? 'Executive Search' : trial.title;
        return `<div class="step-page"><h1>${escapeHtml(copy('startTitle'))}</h1><p class="lead">${escapeHtml(copy('manualLead'))}</p><form class="panel connect-panel" id="manualSetupForm" novalidate><div class="self-service-grid"><section class="quiet-card"><div class="quiet-title">${escapeHtml(copy('setupHowTitle'))}</div><p class="quiet-copy">${escapeHtml(copy('setupHowCopy'))}</p><div class="quiet-list"><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span><strong>${escapeHtml(copy('connections'))}:</strong> ${escapeHtml(copy('connectionsCopy'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span><strong>${escapeHtml(copy('controlTitle'))}:</strong> ${escapeHtml(copy('controlCopy'))}</span></div></div></section><section class="self-service-form-card"><div class="self-service-title">${escapeHtml(copy('setupFormTitle'))}</div><div class="form full-width"><label for="manualCustomerName">${escapeHtml(copy('yourName'))}</label><input id="manualCustomerName" name="customer_name" value="${escapeHtml(trial.customerName)}" placeholder="${escapeHtml(copy('namePh'))}" autocomplete="name" required><label for="manualEmail">${escapeHtml(state.customerType === 'private' || isTalentAttract ? copy('privateEmail') : copy('workEmail'))}</label><input id="manualEmail" name="email" type="email" value="${escapeHtml(trial.customerEmail)}" placeholder="${escapeHtml(copy('emailPh'))}" autocomplete="email" required><label>${escapeHtml(copy('setupTypeLabel'))}</label>${customerTypeToggle()}<label for="manualTargetType">${escapeHtml(copy('whoFind'))}</label><input id="manualTargetType" name="target_contact_type" value="${escapeHtml(targetContactType)}" placeholder="${escapeHtml(copy('targetPh'))}" ${isTalentAttract ? 'readonly' : ''} required><div class="help">${escapeHtml(copy('targetHelp'))}</div><label for="manualTargetCountry">${escapeHtml(copy('market'))}</label><select id="manualTargetCountry" name="target_country" required>${['Denmark','Sweden','Norway','United Kingdom','Germany','Netherlands','Europe','Other'].map((country) => `<option value="${escapeHtml(country)}" ${country === trial.targetCountry ? 'selected' : ''}>${escapeHtml(marketName(country))}</option>`).join('')}</select></div></section></div></form><div class="actions between manual-actions"><div></div><div class="cta-note-wrap">${buttonPrimary('save-manual-setup', `${escapeHtml(copy('continueSummary'))} ${icon('arrowRight', 17)}`)}</div></div></div>`;
    }

    function step1() {
        if (manualSetupMode && !state.trial.customerEmail) return manualSetupStep();
        const trial = state.trial;
        const name = trial.customerName || (state.language === 'da' ? 'Claus' : 'there');
        return `<div class="step1-refined"><div class="personal-note"><p>${escapeHtml(copy('dear'))} ${escapeHtml(name)},</p><p>${escapeHtml(copy('setupReady'))}</p><p class="lead">${escapeHtml(copy('intro1'))}</p><p class="lead">${escapeHtml(copy('intro2'))}</p><p class="lead"><strong>${escapeHtml(copy('introStrong'))}</strong></p><p class="lead">${escapeHtml(copy('intro3'))}</p></div><div class="step1-intro"><section class="test-card"><div class="card-kicker">${escapeHtml(copy('agreementTitle'))}</div><div class="agreement-list"><div class="agreement-check"><span class="check">${icon('users', 18, 2.2)}</span><div><span class="text">${escapeHtml(copy('audience'))}</span><strong>${escapeHtml(trial.connections)} ${escapeHtml(trial.title)}s</strong></div></div><div class="agreement-check"><span class="check">${icon('clock', 18, 2.2)}</span><div><span class="text">${escapeHtml(copy('testPeriod'))}</span><strong>${escapeHtml(trial.period)}</strong></div></div><div class="agreement-check"><span class="check"><span class="linkedin-icon">${linkedInMark()}</span></span><div><span class="text">${escapeHtml(copy('channel'))}</span><strong>LinkedIn</strong></div></div></div></section><section class="company-card"><div class="card-kicker">${escapeHtml(copy('measuringTitle'))}</div><p>${escapeHtml(copy('measuringCopy'))}</p><div class="trust-list"><div class="trust-item"><span class="check">${icon('check', 13, 3)}</span></div><div class="trust-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('noMessages'))}</span></div><div class="trust-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('resultAfter'))}</span></div></div></section></div><div class="actions">${buttonPrimary('next-linkedin', `${escapeHtml(copy('continueLinkedIn'))} ${icon('arrowRight', 18)}`)}</div></div>`;
    }

    function linkedinStep() {
        const status = state.linkedinConnected ? 'connected' : state.linkedInStatus;
        const isWaiting = ['submitting', 'waiting', 'app-approval'].includes(status);
        const isConnected = status === 'connected' || state.linkedinConnected;
        const needsCode = status === 'sms' || status === 'authenticator';
        const codeLabel = status === 'authenticator' ? copy('authCode') : copy('smsCode');
        return `<div class="step-page"><h1>${escapeHtml(copy('connectTitle'))}</h1><p class="lead">${escapeHtml(copy('connectLead'))}</p><div class="panel connect-panel"><div class="step-helper-grid"><section class="quiet-card"><div class="quiet-title">${escapeHtml(copy('linkedinUseTitle'))}</div><p class="quiet-copy">${escapeHtml(copy('linkedinUseCopy'))}</p><div class="quiet-list"><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span><strong>${escapeHtml(copy('connections'))}:</strong> ${escapeHtml(copy('connectionsCopy'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span><strong>${escapeHtml(copy('controlTitle'))}:</strong> ${escapeHtml(copy('controlCopy'))}</span></div></div></section><section class="quiet-card"><div class="form">${!isConnected ? `<label for="linkedin-username">${escapeHtml(copy('liUser'))}</label><input id="linkedin-username" data-field="username" type="email" value="${escapeHtml(state.username)}" placeholder="${escapeHtml(copy('emailPh'))}" autocomplete="username"><label for="linkedin-password">${escapeHtml(copy('password'))}</label><div class="password-wrap"><input id="linkedin-password" data-field="password" value="${escapeHtml(state.password)}" type="${state.showPassword ? 'text' : 'password'}" autocomplete="current-password" placeholder="${escapeHtml(copy('passwordPh'))}"><button type="button" class="icon-button" data-action="toggle-password" aria-label="${escapeHtml(copy('passwordAria'))}">${icon('eye', 19)}</button></div>` : ''}${state.linkedinError ? `<div class="alert-bar error">${icon('alert', 18)} ${escapeHtml(state.linkedinError)}</div>` : ''}${state.linkedinMessage && !state.linkedinError && !isConnected ? `<div class="alert-bar warning">${status === 'app-approval' ? icon('smartphone', 18) : icon('loader', 18, 2.2, 'spin')} ${escapeHtml(state.linkedinMessage)}</div>` : ''}${isConnected ? `<div class="status-stack"><div class="success-bar">${icon('check', 18)} ${escapeHtml(copy('liConnected'))}</div></div>` : ''}${needsCode ? `<div class="code-box"><label for="linkedin-code">${escapeHtml(codeLabel)}</label><div class="code-row"><input id="linkedin-code" data-field="twoFactorCode" value="${escapeHtml(state.twoFactorCode)}" placeholder="${escapeHtml(copy('codePh'))}" autocomplete="one-time-code">${buttonPrimary('submit-2fa', escapeHtml(copy('approveCode')))}</div></div>` : ''}${!isConnected && !needsCode ? `<button type="button" class="btn primary" style="width:100%;margin-top:16px" data-action="connect-linkedin" ${isWaiting ? 'disabled' : ''}>${isWaiting ? icon('loader', 18, 2.2, 'spin') : `<span class="inmark">${linkedInMark()}</span>`}${status === 'app-approval' ? escapeHtml(copy('checkStatus')) : status === 'submitting' ? escapeHtml(copy('connecting')) : escapeHtml(copy('connectLinkedIn'))}</button>` : ''}</div></section></div></div><div class="actions between">${buttonSecondary('back-step-1', escapeHtml(copy('back')))}<div class="cta-note-wrap">${buttonPrimary('next-activation', `${escapeHtml(copy('continueActivation'))} ${icon('arrowRight', 17)}`, !isConnected)}${!isConnected ? `<div class="cta-note">${escapeHtml(copy('lockedNote'))}</div>` : ''}</div></div></div>`;
    }

    function activationStep() {
        const trial = state.trial;
        const submitting = state.activationStatus === 'submitting';
        return `<div class="step-page"><h1>${escapeHtml(copy('activation'))}</h1><p class="lead">${escapeHtml(copy('activationLead', trial.trialDays))}</p><div class="panel"><div class="step-helper-grid"><section class="quiet-card"><div class="quiet-title">${escapeHtml(copy('trialPeriod'))}</div><div class="trial-length">${escapeHtml(copy('trialLength', trial.trialDays))}</div><div class="trial-detail">${escapeHtml(copy('trialSetupCopy'))}</div></section><section class="quiet-card"><div class="quiet-title">${escapeHtml(copy('automaticSetup'))}</div><div class="soft-list"><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('automaticSetupCopy'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('followAccount'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('resultMail'))}</span></div></div></section></div></div>${state.activationError ? `<div class="alert-bar error">${icon('alert', 18)} ${escapeHtml(state.activationError)}</div>` : ''}<div class="actions between">${buttonSecondary('back-linkedin', escapeHtml(copy('back')))}${buttonPrimary('activate-account', `${submitting ? icon('loader', 18, 2.2, 'spin') : ''} ${escapeHtml(copy('activateAccount'))} ${icon('arrowRight', 17)}`, submitting)}</div><div class="footer">${escapeHtml(copy('activationNote'))}</div></div>`;
    }
    function finalStep() {
        const trial = state.trial;
        return `<div class="step-page"><h1>${escapeHtml(copy('activeTitle'))}</h1><p class="lead">${escapeHtml(copy('activeLead'))}</p><div class="panel result-panel"><div class="result-grid"><section class="result-card"><h3>${escapeHtml(copy('whatNow'))}</h3><div class="quiet-list"><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('now1'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('now2'))}</span></div><div class="quiet-item"><span class="check">${icon('check', 13, 3)}</span><span>${escapeHtml(copy('now3'))}</span></div></div></section><section class="result-card"><h3>${escapeHtml(copy('account'))}</h3><p>${escapeHtml(copy('accountCopy'))}</p><div class="status-actions"><div class="status-note">${escapeHtml(copy('accountNote'))}</div><a class="btn primary" href="${CAMPAIGNS_DASHBOARD_URL}">${escapeHtml(copy('openAccount'))} ${icon('arrowRight', 17)}</a></div></section></div></div>${summaryStrip(trial)}</div>`;
    }

    function loadingContent() { return `<div class="loading-card"><div><div class="spin-wrap">${icon('loader', 34, 2.2, 'spin')}</div><h1>${escapeHtml(copy('loadingTitle'))}</h1><p class="lead">${escapeHtml(copy('loadingLead'))}</p></div></div>`; }

    function errorContent() { return `<div class="loading-card"><div><div class="spin-wrap">${icon('alert', 34)}</div><h1>${escapeHtml(copy('errorTitle'))}</h1><p class="lead">${escapeHtml(state.loadError || copy('errorFallback'))}</p><div class="actions">${buttonPrimary('create-manual-test', escapeHtml(copy('createManual')))}</div></div></div>`; }

    function content() {
        if (state.loading) return loadingContent();
        if (state.loadError) return errorContent();
        if (state.currentStep === 1) return step1();
        if (state.currentStep === 2) return linkedinStep();
        if (state.currentStep === 3) return activationStep();
        return finalStep();
    }
    function render() {
        document.documentElement.lang = state.language;
        document.getElementById('app').innerHTML = `<div class="page"><div class="shell"><div class="topbar"><a class="brand" href="/da/" aria-label="${escapeHtml(copy('homeAria'))}"><img src="${escapeHtml(state.trial.logoSrc)}" alt="Coherta logo" width="32" height="32" onerror="this.outerHTML='<span class=&quot;brand-fallback&quot;>C</span>'"><span class="brand-name">Coherta</span></a><div class="proof"><div class="membership-card"><span class="membership-logo"><img src="/da/assets/img/dansk-industri-logo.png" alt="Dansk Industri logo" width="176" height="128"></span><span>${escapeHtml(copy('memberDI'))}</span></div><div class="membership-card"><span class="membership-logo de-logo"><img src="/da/assets/img/dansk-erhverv-logo.svg" alt="Dansk Erhverv logo" width="512" height="512"></span><span>${escapeHtml(copy('memberDE'))}</span></div></div></div><div class="layout"><main class="main">${state.loading || state.loadError ? '' : testJourney()}<div class="main-content">${content()}</div></main>${state.loading || state.loadError ? '' : `<div class="right-rail">${languageSwitcher()}${contactCard()}</div>`}</div><div class="footer">© 2026 Coherta ApS</div></div></div>`;
        bindEvents();
    }

    function goToStep(targetStep) {
        if (!canVisitStep(targetStep, state.currentStep, state.linkedinConnected, state.accountActivated)) return;
        state.currentStep = targetStep;
        render();
    }

    function saveManualSetupFromForm() {
        const form = document.getElementById('manualSetupForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        const formData = new FormData(form);
        const isTalentAttract = hasTalentAttractParam();
        const plan = getPlanForCustomerType(state.customerType);
        const draft = {
            ...(readManualSetupDraft() || {}),
            customer_type: state.customerType,
            customer_name: String(formData.get('customer_name') || '').trim(),
            email: String(formData.get('email') || '').trim(),
            company_name: isTalentAttract ? '' : String(formData.get('company_name') || '').trim(),
            website: isTalentAttract ? '' : String(formData.get('website') || '').trim(),
            target_contact_type: isTalentAttract ? 'Executive Search' : String(formData.get('target_contact_type') || '').trim(),
            target_country: String(formData.get('target_country') || '').trim(),
            contacts_to_identify: state.customerType === 'private' ? 25 : 10,
            number_of_days: state.customerType === 'private' ? 30 : 7,
            plan: plan.plan,
            plan_label: plan.plan_label,
            billing_cycle: plan.billing_cycle,
            currency: plan.currency,
            price_id: plan.stripe_price_id,
            stripe_price_id: plan.stripe_price_id,
            products: plan.stripe_product_id ? [plan.stripe_product_id] : [],
            pricing: plan.monthly_price
        };
        applyManualSetup(draft);
        state.currentStep = 2;
        render();
    }

    function setCustomerType(type) {
        state.customerType = type === 'private' ? 'private' : 'company';
        const form = document.getElementById('manualSetupForm');
        if (form) {
            const formData = new FormData(form);
            state.trial.customerName = String(formData.get('customer_name') || '').trim();
            state.trial.customerEmail = String(formData.get('email') || '').trim();
            state.trial.title = String(formData.get('target_contact_type') || '').trim() || state.trial.title;
            state.trial.targetCountry = String(formData.get('target_country') || '').trim() || state.trial.targetCountry;
        }
        if (state.customerType === 'private' && (!state.trial.title || state.trial.title === 'CEOs')) state.trial.title = 'recruiters';
        applySubscriptionPlan(state.customerType);
        render();
    }

    function bindEvents() {
        document.querySelectorAll('[data-field]').forEach((field) => {
            field.addEventListener('input', (event) => {
                state[event.target.dataset.field] = event.target.value;
            });
        });

        const manualForm = document.getElementById('manualSetupForm');
        if (manualForm) {
            manualForm.addEventListener('submit', (event) => {
                event.preventDefault();
                saveManualSetupFromForm();
            });
        }

        document.querySelectorAll('[data-action]').forEach((element) => {
            element.addEventListener('click', async (event) => {
                const action = event.currentTarget.dataset.action;
                if (action === 'jump') return goToStep(Number(event.currentTarget.dataset.step));
                if (action === 'set-language') { state.language = event.currentTarget.dataset.language === 'da' ? 'da' : 'en'; return render(); }
                if (action === 'set-customer-type') return setCustomerType(event.currentTarget.dataset.customerType);
                if (action === 'create-manual-test') {
                    manualSetupMode = true;
                    state.loadError = '';
                    state.loading = false;
                    testId = 'manual_' + simpleHash('manual|' + Date.now());
                    storageKey = 'coherta_free_test_' + testId;
                    linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
                    return render();
                }
                if (action === 'save-manual-setup') return saveManualSetupFromForm();
                if (action === 'next-linkedin') { state.currentStep = 2; return render(); }
                if (action === 'back-step-1') { state.currentStep = 1; return render(); }
                if (action === 'next-activation') { if (state.linkedinConnected) state.currentStep = 3; return render(); }
                if (action === 'back-linkedin') { state.currentStep = 2; return render(); }
                if (action === 'toggle-password') { state.showPassword = !state.showPassword; return render(); }
                if (action === 'connect-linkedin') return connectLinkedIn();
                if (action === 'submit-2fa') return submitTwoFactorCode();
                if (action === 'activate-account') return completeStartTest();
            });
        });
    }

    async function init() {
        render();
        try {
            await hydrateCurrentUserFromSession();
            const params = new URLSearchParams(window.location.search);
            offerId = getOfferIdFromUrl();
            manualSetupMode = !offerId || params.get('manual_trial') === '1';

            if (manualSetupMode) {
                const draft = readManualSetupDraft();
                if (draft) {
                    if (DEFAULT_CUSTOMER_TYPE === 'private') draft.customer_type = 'private';
                    applyManualSetup(draft);
                }
                else {
                    applySubscriptionPlan(state.customerType);
                    testId = 'manual_' + simpleHash('manual|' + Date.now());
                    storageKey = 'coherta_free_test_' + testId;
                    linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
                }
            } else {
                const offer = await fetchOfferData(offerId);
                state.trial = buildTrialFromOffer(offer);
                state.customerType = state.trial.customerType || state.customerType;
                Object.assign(DEFAULT_SUBSCRIPTION_PLAN, getPlanForCustomerType(state.customerType));
                state.username = state.trial.customerEmail || '';
                testId = normalizeIdentifier(firstValue(offer, ['test_id', 'trial_id'], '')) || ('test_' + offerId);
                storageKey = 'coherta_free_test_' + testId;
                linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
            }

            await hydrateLinkedInState();

            const stored = readStoredTest();
            if (stored && stored.started_at) {
                state.trial.startedAt = stored.started_at;
                state.accountActivated = true;
                state.currentStep = 4;
            }


            state.loading = false;
            render();
        } catch (error) {
            state.loading = false;
            state.loadError = error.message || 'The test could not be loaded.';
            render();
        }
    }

    document.addEventListener('DOMContentLoaded', init);

</script>
</body>
</html>
