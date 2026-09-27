<?php
$params = $_GET;
$params['manual_trial'] = '1';
$params['customer_type'] = 'private';

header('Location: /app/free_trial.php?' . http_build_query($params), true, 302);
exit;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Find your next job | Coherta</title>
    <style>
        :root {
            --bg: #f8fafc;
            --white: #ffffff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-950: #020617;
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-300: #93c5fd;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-200: #a7f3d0;
            --emerald-500: #10b981;
            --emerald-700: #047857;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.12), 0 4px 6px -4px rgba(15, 23, 42, 0.12);
            --shadow-xl: 0 20px 25px -5px rgba(15, 23, 42, 0.10), 0 8px 10px -6px rgba(15, 23, 42, 0.10);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: var(--slate-50); color: var(--slate-700); }
        button, input { font: inherit; }
        button { cursor: pointer; }
        button:disabled { cursor: not-allowed; }

        .page { min-height: 100vh; padding: 32px 24px; }
        .wrap { max-width: 1540px; margin: 0 auto; }
        .logo-row { display: flex; align-items: center; gap: 12px; }
        .logo-row img { height: 44px; width: auto; object-fit: contain; }
        .logo-fallback { font-size: 30px; line-height: 1; font-weight: 800; letter-spacing: -0.025em; color: var(--slate-950); }
        .layout { display: grid; gap: 32px; margin-top: 32px; }
        .main-card { min-height: 740px; border: 1px solid var(--slate-200); border-radius: 24px; background: var(--white); padding: 48px; box-shadow: var(--shadow-xl); }
        .journey-card { border: 1px solid var(--slate-200); border-radius: 24px; background: var(--white); padding: 32px; box-shadow: var(--shadow-xl); align-self: start; }
        h1 { margin: 12px 0 0; font-size: clamp(44px, 5vw, 60px); line-height: 1; letter-spacing: -0.035em; color: var(--slate-950); font-weight: 800; }
        h2 { margin: 0 0 32px; font-size: 24px; line-height: 1.2; letter-spacing: -0.025em; color: var(--slate-950); font-weight: 800; }
        p { margin: 0; }
        .lead { margin-top: 24px; font-size: 20px; line-height: 1.65; color: var(--slate-500); font-weight: 500; }
        .max-3xl { max-width: 768px; }

        .primary-btn, .secondary-btn {
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border-radius: 12px;
            padding: 0 36px;
            font-size: 16px;
            font-weight: 800;
            transition: transform .18s ease, background-color .18s ease, border-color .18s ease, color .18s ease;
            border: 0;
            text-decoration: none;
        }
        .primary-btn { background: var(--blue-600); color: white; box-shadow: var(--shadow-xl); }
        .primary-btn:hover:not(:disabled) { transform: translateY(-2px); background: var(--blue-700); }
        .primary-btn:disabled { background: var(--slate-200); color: var(--slate-400); box-shadow: none; }
        .secondary-btn { background: white; color: var(--blue-600); border: 1px solid var(--slate-200); }
        .secondary-btn:hover:not(:disabled) { transform: translateY(-2px); border-color: var(--blue-200); }

        .hero-box { margin-top: 36px; display: flex; flex-direction: column; gap: 32px; border: 1px solid var(--blue-100); border-radius: 24px; background: var(--blue-50); padding: 40px 32px; }
        .hero-icon { width: 112px; height: 112px; display: grid; place-items: center; flex-shrink: 0; border-radius: 999px; background: white; color: var(--blue-700); box-shadow: var(--shadow-sm); }
        .hero-title { font-size: clamp(34px, 4vw, 48px); line-height: 1.12; letter-spacing: -0.035em; color: var(--slate-950); font-weight: 800; }
        .hero-sub { margin-top: 20px; max-width: 576px; font-size: 20px; line-height: 1.65; font-weight: 500; color: var(--slate-500); }

        .summary-strip { display: grid; overflow: hidden; border: 1px solid var(--slate-200); border-radius: 16px; background: white; box-shadow: var(--shadow-sm); }
        .summary-item { display: flex; align-items: center; gap: 16px; border-bottom: 1px solid var(--slate-200); padding: 20px 24px; }
        .summary-item:last-child { border-bottom: 0; }
        .summary-icon { width: 44px; height: 44px; display: grid; place-items: center; flex-shrink: 0; border-radius: 999px; background: var(--blue-50); color: var(--blue-700); }
        .summary-label { font-size: 14px; font-weight: 600; color: var(--slate-500); }
        .summary-value { margin-top: 2px; font-size: 18px; line-height: 1.2; font-weight: 800; letter-spacing: -0.025em; color: var(--slate-950); }

        .center { display: flex; justify-content: center; }
        .mt-3 { margin-top: 12px; }
        .mt-6 { margin-top: 24px; }
        .mt-7 { margin-top: 28px; }
        .mt-8 { margin-top: 32px; }
        .mt-9 { margin-top: 36px; }

        .journey-list { display: flex; flex-direction: column; gap: 8px; }
        .journey-step { display: grid; width: 100%; grid-template-columns: 44px 1fr; gap: 16px; border: 0; border-radius: 16px; background: transparent; padding: 12px; text-align: left; transition: background-color .18s ease; }
        .journey-step.active { background: var(--blue-50); }
        .journey-step:not(:disabled):hover { background: var(--slate-50); }
        .journey-step:disabled { opacity: .9; cursor: not-allowed; }
        .dot-wrap { position: relative; display: flex; width: 44px; justify-content: center; }
        .dot-line { position: absolute; left: 50%; top: 36px; height: 48px; transform: translateX(-50%); border-left: 2px dashed var(--slate-200); }
        .dot-line.complete { border-left-style: solid; border-color: var(--emerald-500); }
        .dot-line.active { border-left-style: solid; border-color: var(--blue-600); }
        .dot-bg { position: relative; z-index: 1; border-radius: 999px; background: white; }
        .step-dot { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 999px; background: var(--slate-100); color: var(--slate-500); font-size: 14px; font-weight: 800; transition: all .18s ease; }
        .step-dot.active { background: var(--blue-600); color: white; box-shadow: var(--shadow-sm); }
        .step-dot.complete { background: var(--emerald-500); color: white; box-shadow: var(--shadow-sm); }
        .step-text { min-width: 0; padding-top: 4px; }
        .step-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 16px; font-weight: 800; color: var(--slate-950); }
        .step-label.active { color: var(--blue-700); }
        .step-sub { margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 14px; font-weight: 500; color: var(--slate-500); }
        .step-sub.active { color: var(--blue-700); }
        .step-sub.disabled { color: var(--slate-400); }

        .linkedin-box, .price-box { margin-top: 32px; border: 1px solid var(--blue-100); border-radius: 24px; background: var(--blue-50); padding: 36px 48px; }
        .mini-steps { max-width: 768px; margin: 0 auto 32px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; align-items: start; }
        .mini-step { display: flex; flex-direction: column; align-items: center; text-align: center; }
        .mini-dot { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 999px; background: var(--slate-100); color: var(--slate-500); font-size: 14px; font-weight: 800; }
        .mini-dot.active { background: var(--blue-600); color: white; }
        .mini-dot.complete { background: var(--emerald-500); color: white; }
        .mini-label { margin-top: 8px; font-size: 14px; font-weight: 800; color: var(--slate-400); }
        .mini-label.active { color: var(--blue-700); }
        .mini-label.complete { color: var(--slate-500); }
        .form-box { max-width: 576px; margin: 0 auto; }
        label { display: block; font-size: 14px; font-weight: 800; color: var(--slate-950); }
        input { width: 100%; height: 48px; margin-top: 8px; border: 1px solid var(--slate-200); border-radius: 8px; background: white; padding: 0 16px; color: var(--slate-700); font-size: 16px; font-weight: 600; outline: 0; transition: border-color .18s ease, box-shadow .18s ease; }
        input:focus { border-color: var(--blue-600); box-shadow: 0 0 0 4px var(--blue-100); }
        .hint { margin-top: 8px; font-size: 14px; font-weight: 500; color: var(--slate-500); }
        .password-wrap { position: relative; margin-top: 8px; }
        .password-wrap input { margin-top: 0; padding-right: 48px; }
        .icon-button { position: absolute; top: 50%; right: 12px; display: grid; place-items: center; width: 28px; height: 28px; transform: translateY(-50%); border: 0; background: transparent; color: var(--slate-500); }
        .linkedin-submit { margin-top: 20px; width: 100%; height: 48px; display: flex; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 8px; background: var(--blue-600); color: white; font-size: 16px; font-weight: 800; box-shadow: var(--shadow-lg); transition: background-color .18s ease; }
        .linkedin-submit:hover:not(:disabled) { background: var(--blue-700); }
        .linkedin-submit:disabled { background: var(--slate-200); color: var(--slate-400); box-shadow: none; }
        .in-badge { width: 20px; height: 20px; display: grid; place-items: center; border-radius: 4px; background: white; color: var(--blue-700); font-size: 12px; font-weight: 900; }
        .status-stack { margin-top: 20px; display: flex; flex-direction: column; gap: 8px; }
        .success-bar, .info-bar { min-height: 44px; display: flex; align-items: center; justify-content: center; gap: 12px; border-radius: 8px; padding: 12px 16px; font-size: 14px; font-weight: 800; }
        .success-bar { border: 1px solid var(--emerald-200); background: var(--emerald-50); color: var(--emerald-700); }
        .info-bar { border: 1px solid var(--blue-100); background: var(--blue-50); color: var(--blue-700); }
        button.info-bar { width: 100%; }
        button.info-bar:hover { background: var(--blue-100); }
        .below-actions { margin-top: 32px; display: flex; flex-direction: column; gap: 16px; }
        .disabled-note { margin-top: 12px; text-align: center; font-size: 14px; font-weight: 600; color: var(--slate-400); }

        .badge-free { display: inline-flex; border-radius: 999px; background: var(--emerald-100); padding: 6px 16px; font-size: 14px; font-weight: 800; color: var(--emerald-700); }
        .price-grid { margin-top: 28px; display: grid; gap: 32px; }
        .price-kicker { font-size: 20px; font-weight: 600; color: var(--slate-500); }
        .price-big { margin-top: 12px; font-size: clamp(60px, 7vw, 72px); line-height: 1; letter-spacing: -0.035em; color: var(--slate-950); font-weight: 800; }
        .price-desc { margin-top: 16px; font-size: 20px; font-weight: 500; color: var(--slate-500); }
        .check-list { margin-top: 32px; display: flex; flex-direction: column; gap: 16px; }
        .check-line { display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 600; color: var(--slate-800); }
        .check-circle { width: 28px; height: 28px; display: grid; place-items: center; flex-shrink: 0; border-radius: 999px; background: var(--emerald-500); color: white; }
        .credit-illustration { width: 176px; height: 176px; display: grid; place-items: center; justify-self: center; border-radius: 999px; background: white; color: var(--blue-700); box-shadow: var(--shadow-sm); }
        .safe-note { margin-top: 12px; text-align: center; font-size: 14px; font-weight: 600; color: var(--slate-400); }

        .final-head { display: flex; flex-direction: column; gap: 16px; }
        .added-pill { display: inline-flex; width: fit-content; align-items: center; gap: 8px; border: 1px solid var(--emerald-200); border-radius: 12px; background: var(--emerald-50); padding: 12px 16px; color: var(--emerald-700); font-size: 14px; font-weight: 800; }
        .what-now { margin-top: 24px; border: 1px solid var(--slate-200); border-radius: 16px; background: white; padding: 20px 28px; }
        .what-title { margin-bottom: 16px; font-size: 20px; font-weight: 800; letter-spacing: -0.025em; color: var(--slate-950); }
        .what-grid { display: grid; gap: 16px; }
        .what-item { display: flex; align-items: center; gap: 12px; color: var(--slate-700); font-size: 16px; font-weight: 600; }
        .booking { margin-top: 24px; border: 1px solid var(--slate-200); border-radius: 16px; background: white; padding: 24px 32px; box-shadow: var(--shadow-sm); }
        .booking-title { font-size: 20px; font-weight: 800; letter-spacing: -0.025em; color: var(--slate-950); }
        .booking-sub { margin-top: 4px; font-size: 14px; font-weight: 500; color: var(--slate-500); }
        .booking-grid { margin-top: 20px; display: grid; gap: 32px; }
        .calendar-head { margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; color: var(--slate-700); font-size: 14px; font-weight: 800; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 12px 0; text-align: center; }
        .weekday { color: var(--slate-500); font-size: 12px; font-weight: 800; }
        .day-btn { width: 28px; height: 28px; display: grid; place-items: center; margin: 0 auto; border: 0; border-radius: 999px; background: transparent; color: var(--slate-700); font-size: 14px; font-weight: 600; }
        .day-btn:hover { background: var(--blue-50); }
        .day-btn.selected { background: var(--blue-600); color: white; }
        .divider { display: none; background: var(--slate-200); }
        .time-title { margin-bottom: 16px; text-align: center; font-size: 14px; font-weight: 800; color: var(--slate-950); }
        .times { max-width: 190px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; }
        .time-btn { width: 100%; height: 36px; border: 1px solid var(--blue-200); border-radius: 6px; background: white; color: var(--blue-700); font-size: 14px; font-weight: 800; transition: background-color .18s ease, border-color .18s ease; }
        .time-btn:hover { background: var(--blue-50); }
        .time-btn.selected { border-color: var(--blue-600); background: var(--blue-50); }
        .timezone { margin-top: 16px; text-align: center; font-size: 12px; font-weight: 600; color: var(--slate-500); }
        .book-btn { margin-top: 20px; width: 100%; height: 40px; border: 1px solid var(--blue-200); border-radius: 8px; background: white; color: var(--blue-700); font-size: 14px; font-weight: 800; transition: background-color .18s ease, border-color .18s ease; }
        .book-btn:hover:not(:disabled) { border-color: var(--blue-300); background: var(--blue-50); }
        .book-btn:disabled { border-color: var(--emerald-200); background: var(--emerald-50); color: var(--emerald-700); }
        .calendly { margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--slate-400); font-size: 14px; font-weight: 600; }

        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (min-width: 640px) {
            .below-actions { flex-direction: row; align-items: flex-start; justify-content: space-between; }
            .card-actions { flex-direction: row !important; gap: 24px !important; }
        }

        @media (min-width: 768px) {
            .summary-strip { grid-template-columns: repeat(var(--summary-columns), minmax(0, 1fr)); }
            .summary-item { border-bottom: 0; border-right: 1px solid var(--slate-200); }
            .summary-item:last-child { border-right: 0; }
        }

        @media (min-width: 1024px) {
            .page { padding-left: 32px; padding-right: 32px; }
            .hero-box { flex-direction: row; align-items: center; gap: 48px; padding: 56px 64px; }
            .hero-icon { width: 128px; height: 128px; }
            .price-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: center; }
            .what-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .what-item { padding-left: 8px; border-left: 1px solid var(--slate-200); }
            .what-item:first-child { padding-left: 0; border-left: 0; }
            .final-head { flex-direction: row; align-items: flex-start; justify-content: space-between; }
            .final-head .added-pill { margin-top: 48px; }
            .booking-grid { grid-template-columns: 1fr 1px 1fr; gap: 40px; }
            .divider { display: block; }
        }

        @media (min-width: 1280px) {
            .layout { grid-template-columns: 1fr 360px; }
        }

        @media (max-width: 767px) {
            .page { padding: 20px 14px; }
            .main-card, .journey-card { padding: 24px; border-radius: 22px; }
            .linkedin-box, .price-box { padding: 28px 20px; }
            .mini-steps { gap: 8px; }
            .mini-label { font-size: 12px; }
        }


        .setup-grid { margin-top: 32px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field-full { grid-column: 1 / -1; }
        .select-input { width: 100%; height: 48px; margin-top: 8px; border: 1px solid var(--slate-200); border-radius: 8px; background: white; padding: 0 16px; color: var(--slate-700); font-size: 16px; font-weight: 600; outline: 0; }
        .alert-bar { margin-top: 18px; min-height: 44px; display: none; align-items: center; gap: 12px; border-radius: 10px; padding: 12px 16px; font-size: 14px; font-weight: 800; }
        .alert-bar.show { display: flex; }
        .alert-bar.error { border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; }
        .alert-bar.success { border: 1px solid var(--emerald-200); background: var(--emerald-50); color: var(--emerald-700); }
        .alert-bar.warning { border: 1px solid #fde68a; background: #fffbeb; color: #92400e; }
        .code-box { margin-top: 18px; }
        .code-row { display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-top: 10px; }
        .small-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; color: var(--blue-700); font-size: 14px; font-weight: 800; text-decoration: none; }
        .dashboard-box { margin-top: 24px; border: 1px solid var(--blue-100); border-radius: 20px; background: var(--blue-50); padding: 28px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; }
        .dashboard-title { font-size: 22px; font-weight: 800; color: var(--slate-950); letter-spacing: -0.025em; }
        .dashboard-sub { margin-top: 6px; color: var(--slate-500); font-size: 16px; line-height: 1.5; font-weight: 500; }
        .loading-card { min-height: 520px; display: grid; place-items: center; text-align: center; }
        .loading-card .spin-wrap { width: 58px; height: 58px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 999px; background: var(--blue-50); color: var(--blue-700); }
        .primary-btn.full, .secondary-btn.full { width: 100%; }
        @media (max-width: 767px) { .setup-grid { grid-template-columns: 1fr; } .code-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div id="app"></div>

<script>
    const OFFER_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/view/';
    const FREE_TRIAL_START_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/start/';
    const FREE_TRIAL_CAMPAIGN_ENDPOINT_BASE = 'https://api.coherta.com/coherta/trial/free/campaign/';
    const STRIPE_CHECKOUT_ENDPOINT = 'https://api.coherta.com/billing/checkout';
    const CAMPAIGNS_DASHBOARD_URL = 'https://www.coherta.com/app/campaign/campaigns-dashboard';
    const MANUAL_SETUP_STORAGE_KEY = 'coherta_free_test_manual_setup_v2';
    const PENDING_BILLING_KEY = 'coherta_free_test_pending_billing_v2';
    const SESSION_STORAGE_KEY = 'session_id';

    const DEFAULT_SUBSCRIPTION_PLAN = {
        plan: 'growth',
        plan_label: 'Recruiter (30 days free trial)',
        billing_cycle: 'monthly',
        monthly_price: 299,
        currency: 'DKK',
        stripe_price_id: 'price_1TUjUELliR0gAZeqzAK1V2E5',
        stripe_product_id: 'prod_UTgr8MlNvkYiwU'
    };



    const DEFAULT_TRIAL = {
        title: 'recruiter',
        connections: 25,
        period: '30 days',
        price: '299 DKK',
        monthlyPrice: 299,
        currency: 'DKK',
        trialDays: 30,
        organizationLabel: 'organization',
        logoSrc: '/assets/img/coherta-logo.png',
        targetCountry: 'Denmark',
        targetSentence: 'We will connect you with recruiters in Denmark.',
        testSummary: 'The test will happen over 30 days.',
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
        { id: 2, label: 'Connect LinkedIn', defaultSub: 'Give access to the test' },
        { id: 3, label: 'Card details', defaultSub: 'Activate the test' },
        { id: 4, label: 'Result & follow-up', defaultSub: 'Next step' }
    ];

    const state = {
        currentStep: 1,
        linkedinConnected: false,
        cardAdded: false,
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
        cardStatus: 'idle',
        cardError: '',
        loading: true,
        loadError: '',
        trial: { ...DEFAULT_TRIAL }
    };

    let bearerToken = localStorage.getItem(SESSION_STORAGE_KEY) || '';
    let offerId = '';
    let testId = '';
    let manualSetupMode = true;
    let storageKey = '';
    let linkedInStorageKey = '';

    function wait(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function normalizeIdentifier(value) {
        return String(value || '').trim().replace(/[^A-Za-z0-9_]/g, '');
    }

    function isJobSeekersRoute() {
        return /(^|\/)job[-_]seekers(?:\.php)?\/?$/i.test(window.location.pathname);
    }

    function getOfferIdFromUrl() {
        if (isJobSeekersRoute()) return '';
        const params = new URLSearchParams(window.location.search);
        const keys = ['currentOfferId', 'offer_id', 'offerId', 'offer', 'id'];
        for (const key of keys) {
            const value = normalizeIdentifier(params.get(key));
            if (value) return value;
        }

        const rawQuery = window.location.search.replace(/^\?/, '').trim();
        if (/^[A-Za-z0-9_]+$/.test(rawQuery)) return normalizeIdentifier(rawQuery);

        const ignoredPathParts = ['free_trial', 'free-trial', 'job_seekers', 'job-seekers', 'trial', 'app', 'index'];
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
        const map = { 208: 'DKK', 978: 'EUR', 840: 'USD' };
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
        if (!amount) return '999 DKK';
        if ((currency || 'DKK') === 'DKK') return new Intl.NumberFormat('da-DK', { maximumFractionDigits: 0 }).format(amount) + ' DKK';
        return new Intl.NumberFormat('da-DK', { style: 'currency', currency: currency || 'DKK', maximumFractionDigits: 0 }).format(amount);
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
        const currency = currencyCodeFromOffer(offer.currency ?? DEFAULT_SUBSCRIPTION_PLAN.currency);
        const trialDays = DEFAULT_TRIAL.trialDays;
        const testHours = trialDays * 24;
        const targetTitle = DEFAULT_TRIAL.title;
        const targetCountry = String(firstValue(offer, ['employee_coutry', 'employee_country', 'target_country', 'country', 'market', 'geography'], DEFAULT_TRIAL.targetCountry));
        const connections = DEFAULT_TRIAL.connections;
        const monthlyPrice = DEFAULT_SUBSCRIPTION_PLAN.monthly_price;
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
            targetSentence: 'We will connect you with ' + targetTitle + 's in ' + targetCountry + '.',
            testSummary: 'The test will happen over ' + trialDays + ' days.',
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
            mailBody: offer.mail_body || ''
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
        return {
            id: draft.free_trial_id || null,
            user_id: draft.user_id || null,
            receiver_name: draft.customer_name || '',
            email: draft.email || '',
            company_name: draft.company_name || '',
            website: draft.website || '',
            employee_title: DEFAULT_TRIAL.title,
            employee_country: draft.target_country || DEFAULT_TRIAL.targetCountry,
            contacts_to_identify: DEFAULT_TRIAL.connections,
            number_of_days: DEFAULT_TRIAL.trialDays,
            pricing: DEFAULT_SUBSCRIPTION_PLAN.monthly_price,
            currency: DEFAULT_SUBSCRIPTION_PLAN.currency,
            billing_cycle: DEFAULT_SUBSCRIPTION_PLAN.billing_cycle,
            price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
            stripe_price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
            plan: DEFAULT_SUBSCRIPTION_PLAN.plan,
            plan_label: DEFAULT_SUBSCRIPTION_PLAN.plan_label,
            search_notes: '',
            started: draft.started || null
        };
    }

    function applyManualSetup(draft) {
        const seed = String((draft.email || '') + '|' + DEFAULT_TRIAL.title + '|' + (draft.target_country || ''));
        testId = draft.test_id || ('manual_' + simpleHash(seed + '|' + Date.now()));
        draft.test_id = testId;
        saveManualSetupDraft(draft);
        state.trial = buildTrialFromOffer(manualDraftToOffer(draft));
        state.username = state.username || state.trial.customerEmail || '';
        storageKey = 'coherta_free_test_' + testId;
        linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
    }

    function canVisitStep(targetStep, currentStep, linkedinConnected, cardAdded) {
        if (targetStep === 1 || targetStep === 2) return true;
        if (targetStep === 3) return linkedinConnected;
        if (targetStep === 4) return linkedinConnected && cardAdded;
        return false;
    }

    function getStepSubLabel(stepId, { currentStep, linkedinConnected, cardAdded }) {
        if (stepId === 1 && manualSetupMode && state.trial.customerEmail) return 'Setup saved';
        if (stepId === 2 && linkedinConnected) return 'Connected';
        if (stepId === 3 && cardAdded) return 'Added';
        if (stepId === 4 && currentStep === 4) return 'Next step';
        return STEPS[stepId - 1]?.defaultSub || '';
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
        return isTruthyFlag(trial.activeSubscription)
            || Boolean(trial.subscriptionId)
            || ['active', 'trialing', 'paid', 'current'].includes(status);
    }

    function isActiveSubscriptionConflict(status, result) {
        const message = String(result?.message || result?.error || '').trim();
        return Number(status) === 409 && message === 'Customer already has an active subscription';
    }

    function requiresStripeCardDetails() {
        if (customerHasActiveSubscription()) return false;
        if (!offerId) return true;
        return !state.trial.companyUid;
    }

    function buildBillingReturnUrl(status) {
        const url = new URL(window.location.origin + window.location.pathname);
        if (offerId) url.searchParams.set('offerId', offerId);
        else url.searchParams.set('manual_trial', '1');
        url.searchParams.set('billing', status);
        return url.toString();
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

    function getCurrentSessionId() {
        if (!bearerToken) bearerToken = readSessionId();
        return bearerToken;
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

    async function createCohertaUserForLinkedIn() {
        const existingSessionId = getCurrentSessionId();
        if (existingSessionId) return { session_id: existingSessionId, token: existingSessionId, reused: true };

        const nameParts = splitName(state.trial.customerName || state.trial.customerEmail || '');
        const response = await fetch('https://api.coherta.com/users/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
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
                session_id: true
            })
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

    async function pollLinkedInStatus(maxAttempts = 30) {
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
        if (!offerId) return;
        await fetch(FREE_TRIAL_START_ENDPOINT_BASE + encodeURIComponent(offerId), { method: 'POST', headers: headersJson(true) });
    }

    async function startCampaign() {
        if (offerId) return;
        await fetch(FREE_TRIAL_CAMPAIGN_ENDPOINT_BASE, {
            method: 'POST',
            headers: headersJson(true),
            body: JSON.stringify({
                employee_title: state.trial.title,
                country: state.trial.targetCountry
            })
        });
    }

    async function completeStartTest() {
        state.cardStatus = 'submitting';
        state.cardError = '';
        render();
        try {
            await startOfferOnServer();
            await startCampaign();
            const startedAt = new Date().toISOString();
            storeStartedTest(startedAt);
            state.trial.startedAt = startedAt;
            state.cardAdded = true;
            state.currentStep = 4;
            state.cardStatus = 'idle';
            render();
        } catch (error) {
            state.cardStatus = 'idle';
            state.cardError = error.message || 'The test could not be started. Please try again.';
            render();
        }
    }

    async function createStripeCheckout(payload) {
        const response = await fetch(STRIPE_CHECKOUT_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
            const error = new Error(result.message || 'Unable to open secure checkout.');
            error.status = response.status;
            error.result = result;
            throw error;
        }
        return result;
    }

    async function addCard() {
        if (state.cardStatus === 'submitting') return;
        state.cardError = '';

        if (!requiresStripeCardDetails()) {
            await completeStartTest();
            return;
        }

        state.cardStatus = 'submitting';
        render();

        try {
            localStorage.setItem(PENDING_BILLING_KEY, JSON.stringify({
                test_id: testId,
                offer_id: offerId || '',
                created_at: new Date().toISOString()
            }));

            const checkout = await createStripeCheckout({
                user_id: state.trial.userId || null,
                offer_id: offerId || '',
                company_uid: state.trial.companyUid || '',
                email: state.trial.customerEmail || state.username,
                plan: DEFAULT_SUBSCRIPTION_PLAN.plan,
                plan_label: DEFAULT_SUBSCRIPTION_PLAN.plan_label,
                billing_cycle: DEFAULT_SUBSCRIPTION_PLAN.billing_cycle,
                price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
                stripe_price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
                priceId: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
                products: [DEFAULT_SUBSCRIPTION_PLAN.stripe_product_id],
                trial_days: DEFAULT_TRIAL.trialDays,
                trial_period_days: DEFAULT_TRIAL.trialDays,
                company_name: state.trial.companyName,
                country: state.trial.targetCountry,
                vat_code: '',
                success_url: buildBillingReturnUrl('success'),
                cancel_url: buildBillingReturnUrl('cancelled'),
                trial_setup: {
                    target_contact_type: state.trial.title,
                    target_country: state.trial.targetCountry,
                    contacts_to_identify: state.trial.connections,
                    number_of_days: state.trial.trialDays,
                    test_hours: state.trial.testHours || 24,
                    search_notes: state.trial.searchNotes || ''
                }
            });

            if (checkout.url) {
                window.location.href = checkout.url;
                return;
            }

            throw new Error('Revolut checkout URL was not returned.');
        } catch (error) {
            if (isActiveSubscriptionConflict(error.status, error.result)) {
                localStorage.removeItem(PENDING_BILLING_KEY);
                state.trial.activeSubscription = true;
                await completeStartTest();
                return;
            }
            state.cardStatus = 'idle';
            state.cardError = error.message || 'Unable to open card details. Please try again.';
            render();
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
            creditCard: `<svg ${common}><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path><path d="M7 15h3"></path><path d="M15 15h2"></path></svg>`,
            calendar: `<svg ${common}><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path><path d="M8 2v4"></path><path d="M3 10h18"></path></svg>`,
            smartphone: `<svg ${common}><rect x="7" y="2" width="10" height="20" rx="2"></rect><path d="M11 18h2"></path></svg>`,
            loader: `<svg ${common} class="${className} spin"><path d="M21 12a9 9 0 0 1-9 9"></path><path d="M3 12a9 9 0 0 1 9-9"></path></svg>`,
            alert: `<svg ${common}><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>`
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
        return `<button type="button" class="primary-btn ${extraClass}" data-action="${action}" ${disabled ? 'disabled' : ''}>${content}</button>`;
    }

    function buttonSecondary(action, content, disabled = false, extraClass = '') {
        return `<button type="button" class="secondary-btn ${extraClass}" data-action="${action}" ${disabled ? 'disabled' : ''}>${content}</button>`;
    }

    function summaryStrip(trial, showPrice = false) {
        const items = [
            { icon: 'briefcase', label: 'Target', value: trial.title },
            { icon: 'users', label: 'Amount', value: String(trial.connections) },
            { icon: 'clock', label: 'Period', value: trial.period }
        ];
        if (showPrice) items.push({ icon: 'creditCard', label: 'Price', value: `${trial.price}/mo.` });
        return `
        <div class="summary-strip" style="--summary-columns:${items.length}">
          ${items.map((item) => `
            <div class="summary-item">
              <div class="summary-icon">${icon(item.icon, 23, 2.3)}</div>
              <div>
                <div class="summary-label">${escapeHtml(item.label)}</div>
                <div class="summary-value">${escapeHtml(item.value)}</div>
              </div>
            </div>
          `).join('')}
        </div>`;
    }

    function stepDot(index, active, complete, disabled) {
        if (complete) return `<div class="step-dot complete">${icon('check', 19, 3)}</div>`;
        return `<div class="step-dot ${active ? 'active' : ''}" style="${disabled ? 'opacity:.6' : ''}">${index}</div>`;
    }

    function testJourney() {
        const { currentStep, linkedinConnected, cardAdded } = state;
        return `
        <aside class="journey-card">
          <h2>Test journey</h2>
          <div class="journey-list">
            ${STEPS.map((item, index) => {
            const isComplete = item.id < currentStep || (item.id === 2 && linkedinConnected && currentStep > 2) || (item.id === 3 && cardAdded && currentStep > 3);
            const isActive = item.id === currentStep;
            const isDisabled = !canVisitStep(item.id, currentStep, linkedinConnected, cardAdded);
            const sub = getStepSubLabel(item.id, { currentStep, linkedinConnected, cardAdded });
            return `
                <button type="button" class="journey-step ${isActive ? 'active' : ''}" data-action="jump" data-step="${item.id}" ${isDisabled ? 'disabled title="Complete the previous step first"' : ''}>
                  <div class="dot-wrap">
                    ${index < STEPS.length - 1 ? `<span class="dot-line ${isComplete ? 'complete' : isActive ? 'active' : ''}"></span>` : ''}
                    <div class="dot-bg">${stepDot(item.id, isActive, isComplete, isDisabled)}</div>
                  </div>
                  <div class="step-text">
                    <div class="step-label ${isActive ? 'active' : ''}">${escapeHtml(item.label)}</div>
                    <div class="step-sub ${isActive ? 'active' : ''} ${isDisabled ? 'disabled' : ''}">${escapeHtml(sub)}</div>
                  </div>
                </button>`;
        }).join('')}
          </div>
        </aside>`;
    }

    function manualSetupStep() {
        const trial = state.trial;
        return `
        
        <h1>Find your next job</h1>
        <p class="lead max-3xl">Tell Coherta where we should find recruiters. The test length is always ${DEFAULT_TRIAL.trialDays} days, and card details are added at the end.</p>
        <form class="linkedin-box" id="manualSetupForm" novalidate>
          <div class="setup-grid">
            <div>
              <label for="manualCustomerName">Your name</label>
              <input id="manualCustomerName" name="customer_name" value="${escapeHtml(trial.customerName)}" placeholder="Jens Andersen" autocomplete="name" required />
            </div>
            <div>
              <label for="manualEmail">Private email</label>
              <input id="manualEmail" name="email" type="email" value="${escapeHtml(trial.customerEmail)}" placeholder="name@company.com" autocomplete="email" required />
            </div>
            <div>
              <label for="manualTargetCountry">Market</label>
              <select class="select-input" id="manualTargetCountry" name="target_country" required>
                ${['Denmark', 'Sweden', 'Norway', 'United Kingdom', 'Germany', 'Netherlands', 'Europe', 'Other'].map((country) => `<option value="${escapeHtml(country)}" ${country === trial.targetCountry ? 'selected' : ''}>${escapeHtml(country)}</option>`).join('')}
              </select>
            </div>
          </div>
          <div class="alert-bar error" id="manualSetupAlert"></div>
          <div class="mt-8 center">${buttonPrimary('save-manual-setup', `Continue to LinkedIn ${icon('arrowRight', 21)}`)}</div>
        </form>`;
    }

    function step1() {
        if (manualSetupMode) return manualSetupStep();
        const trial = state.trial;
        return `
        
        <h1>Find your next job</h1>
        <p class="lead">Here is the agreed setup. When you are ready, we will connect LinkedIn and activate the campaign.</p>
        <div class="hero-box">
          <div class="hero-icon">${icon('users', 46, 2.1)}</div>
          <div>
            <div class="hero-title">We will connect you with<br>${escapeHtml(trial.connections)} ${escapeHtml(trial.title)}s within ${escapeHtml(trial.period)}.</div>
            <p class="hero-sub">You get a concrete test, a clear result, and a direct path into Coherta Campaigns.</p>
          </div>
        </div>
        <div class="mt-8">${summaryStrip(trial)}</div>
        <div class="mt-9 center">${buttonPrimary('next-linkedin', `Continue to LinkedIn ${icon('arrowRight', 21)}`)}</div>`;
    }

    function miniStep(n, label, active, complete) {
        return `
        <div class="mini-step">
          <div class="mini-dot ${complete ? 'complete' : active ? 'active' : ''}">${complete ? icon('check', 18, 3) : n}</div>
          <div class="mini-label ${active ? 'active' : complete ? 'complete' : ''}">${escapeHtml(label)}</div>
        </div>`;
    }

    function linkedinStep() {
        const status = state.linkedinConnected ? 'connected' : state.linkedInStatus;
        const isWaiting = ['submitting', 'waiting', 'app-approval'].includes(status);
        const isConnected = status === 'connected' || state.linkedinConnected;
        const needsCode = status === 'sms' || status === 'authenticator';
        const codeLabel = status === 'authenticator' ? 'Microsoft Authenticator code' : 'SMS or email code';
        return `
        
        <h1>Connect LinkedIn</h1>
        <p class="lead max-3xl">Coherta uses your LinkedIn profile to run the test and measure who accepts the connection request.</p>
        <div class="linkedin-box">
          <div class="mini-steps">
            ${miniStep('1', 'Enter login', !needsCode && !isWaiting && !isConnected, needsCode || isWaiting || isConnected)}
            ${miniStep('2', 'Confirm login', needsCode || status === 'app-approval' || status === 'waiting', isConnected)}
            ${miniStep('3', 'LinkedIn connected', isConnected, isConnected)}
          </div>
          <div class="form-box">
            ${!isConnected ? `
              <label for="linkedin-username">LinkedIn Email</label>
              <input id="linkedin-username" data-field="username" value="${escapeHtml(state.username)}" type="email" placeholder="name@company.com" autocomplete="username" />
              <div class="hint">Use the email address for your LinkedIn account.</div>
              <label for="linkedin-password" style="margin-top:20px">Password</label>
              <div class="password-wrap">
                <input id="linkedin-password" data-field="password" value="${escapeHtml(state.password)}" type="${state.showPassword ? 'text' : 'password'}" autocomplete="current-password" />
                <button type="button" class="icon-button" data-action="toggle-password" aria-label="Show or hide password">${icon('eye', 20)}</button>
              </div>` : ''}
            ${state.linkedinError ? `<div class="alert-bar error show">${icon('alert', 18)} ${escapeHtml(state.linkedinError)}</div>` : ''}
            ${state.linkedinMessage && !state.linkedinError && !isConnected ? `<div class="alert-bar warning show">${status === 'app-approval' ? icon('smartphone', 18) : icon('loader', 18, 2.2, 'spin')} ${escapeHtml(state.linkedinMessage)}</div>` : ''}
            ${isConnected ? `<div class="status-stack"><div class="success-bar">${icon('check', 19)} LinkedIn is connected</div></div>` : ''}
            ${needsCode ? `
              <div class="code-box">
                <label for="linkedin-code">${escapeHtml(codeLabel)}</label>
                <div class="code-row">
                  <input id="linkedin-code" data-field="twoFactorCode" value="${escapeHtml(state.twoFactorCode)}" placeholder="Enter code" autocomplete="one-time-code" />
                  ${buttonPrimary('submit-2fa', 'Validate code', false)}
                </div>
              </div>` : ''}
            ${!isConnected && !needsCode ? `
              <button type="button" class="linkedin-submit" data-action="connect-linkedin" ${status === 'submitting' || status === 'waiting' ? 'disabled' : ''}>
                ${status === 'submitting' || status === 'waiting' ? icon('loader', 20, 2.2, 'spin') : '<span class="in-badge">in</span>'}
                ${status === 'app-approval' ? 'Check LinkedIn status' : 'Connect LinkedIn'}
              </button>` : ''}
          </div>
        </div>
        <div class="below-actions">
          ${buttonSecondary('back-step-1', `${icon('arrowLeft', 19)} Back`)}
          <div style="text-align:center">
            ${buttonPrimary('next-card', `Continue to card details ${icon('arrowRight', 19)}`, !isConnected)}
            ${!isConnected ? '<div class="disabled-note">Activated when LinkedIn is connected.</div>' : ''}
          </div>
        </div>`;
    }

    function cardStep() {
        const trial = state.trial;
        const submitting = state.cardStatus === 'submitting';
        const requiresCard = requiresStripeCardDetails();
        const hasActiveSubscription = customerHasActiveSubscription();
        return `
        
        <h1>${requiresCard ? 'Add card details' : 'Start your Coherta test'}</h1>
        <p class="lead">${requiresCard ? 'The card activates the test for your organization.' : (hasActiveSubscription ? 'Your customer already has an active subscription, so no new card is required.' : 'Your organization is already ready for billing, so you can start the test now.')}</p>
        <div class="price-box">
          <div class="badge-free">${escapeHtml(trial.trialDays)} days free</div>
          <div class="price-grid">
            <div>
              <div class="price-big">${escapeHtml(trial.price)}</div>
              <div class="price-desc">per month after the first ${escapeHtml(trial.trialDays)} days</div>
              <div class="check-list">
                ${[
            `The first ${trial.trialDays} days are free`,
            `Applies to the full ${trial.organizationLabel}`,
            requiresCard ? 'Payment is handled securely through Revolut' : (hasActiveSubscription ? 'Active subscription found — we continue without card details' : 'No new card is required for this offer')
        ].map((text) => `<div class="check-line"><span class="check-circle">${icon('check', 17, 3)}</span>${escapeHtml(text)}</div>`).join('')}
              </div>
            </div>
            <div class="credit-illustration">${icon('creditCard', 70, 2.1)}</div>
          </div>
        </div>
        ${state.cardError ? `<div class="alert-bar error show">${icon('alert', 18)} ${escapeHtml(state.cardError)}</div>` : ''}
        <div class="center card-actions" style="margin-top:32px; flex-direction:column; align-items:center; gap:16px">
          ${buttonSecondary('back-linkedin', 'Back')}
          ${buttonPrimary('add-card', `${submitting ? icon('loader', 19, 2.2, 'spin') : ''} ${requiresCard ? 'Add card' : (hasActiveSubscription ? 'Continue' : 'Start test')} ${icon('arrowRight', 19)}`, submitting)}
        </div>
        <div class="safe-note">${requiresCard ? 'Card details are handled securely through Revolut.' : (hasActiveSubscription ? 'We skip card collection because the customer already has an active subscription.' : 'The test starts when you confirm.')}</div>`;
    }

    function finalStep() {
        const trial = state.trial;
        return `
        <div class="final-head">
          <div>
            
            <h1>Your Coherta Job Scouter is now live</h1>
          </div>
          <div class="added-pill">${icon('check', 18)} Activated</div>
        </div>
        <p class="lead">Coherta Job Scouter is now ready. Open the dashboard to follow how many recruiters we have connected you with, your messages, replies, and statistics.</p>
        <div class="mt-7">${summaryStrip(trial, true)}</div>
        <div class="what-now">
          <div class="what-title">What happens now?</div>
          <div class="what-grid">
            ${['LinkedIn is connected', 'Coherta starts the campaign', 'You can follow the results live'].map((text) => `<div class="what-item"><span class="check-circle">${icon('check', 16, 3)}</span><span>${escapeHtml(text)}</span></div>`).join('')}
          </div>
        </div>
        <div class="dashboard-box">
          <div>
            <div class="dashboard-title">Go straight to your Coherta Campaigns Dashboard</div>
            <div class="dashboard-sub">Use the dashboard to see contacts, accepted connections, messages, and performance.</div>
          </div>
          <a class="primary-btn" href="${CAMPAIGNS_DASHBOARD_URL}">Open dashboard ${icon('arrowRight', 19)}</a>
        </div>`;
    }

    function loadingContent() {
        return `
        <div class="loading-card">
          <div>
            <div class="spin-wrap">${icon('loader', 34, 2.2, 'spin')}</div>
            <h1>Loading your Coherta test</h1>
            <p class="lead">We are getting the latest setup details from Coherta.</p>
          </div>
        </div>`;
    }

    function errorContent() {
        return `
        <div class="loading-card">
          <div>
            <div class="spin-wrap">${icon('alert', 34)}</div>
            <h1>We could not load this test</h1>
            <p class="lead">${escapeHtml(state.loadError || 'Please check the link and try again.')}</p>
            <div class="mt-8 center">${buttonPrimary('create-manual-test', 'Create a test manually')}</div>
          </div>
        </div>`;
    }

    function content() {
        if (state.loading) return loadingContent();
        if (state.loadError) return errorContent();
        if (state.currentStep === 1) return step1();
        if (state.currentStep === 2) return linkedinStep();
        if (state.currentStep === 3) return cardStep();
        return finalStep();
    }

    function render() {
        document.getElementById('app').innerHTML = `
        <div class="page">
          <div class="wrap">
            <div class="logo-row">
              <img src="${escapeHtml(state.trial.logoSrc)}" alt="Coherta" onerror="this.outerHTML='<div class=&quot;logo-fallback&quot;>Coherta</div>'" />
            </div>
            <div class="layout">
              <main class="main-card">${content()}</main>
              ${state.loading || state.loadError ? '' : testJourney()}
            </div>
          </div>
        </div>`;
        bindEvents();
    }

    function goToStep(targetStep) {
        if (!canVisitStep(targetStep, state.currentStep, state.linkedinConnected, state.cardAdded)) return;
        state.currentStep = targetStep;
        render();
    }

    function saveManualSetupFromForm() {
        const form = document.getElementById('manualSetupForm');
        const alert = document.getElementById('manualSetupAlert');
        if (!form.checkValidity()) {
            if (alert) {
                alert.textContent = 'Please fill in the required fields before continuing.';
                alert.classList.add('show');
            }
            form.reportValidity();
            return;
        }
        const formData = new FormData(form);
        const draft = {
            ...(readManualSetupDraft() || {}),
            customer_name: String(formData.get('customer_name') || '').trim(),
            email: String(formData.get('email') || '').trim(),
            company_name: '',
            website: '',
            target_contact_type: DEFAULT_TRIAL.title,
            target_country: String(formData.get('target_country') || '').trim(),
            contacts_to_identify: DEFAULT_TRIAL.connections,
            number_of_days: DEFAULT_TRIAL.trialDays,
            plan: DEFAULT_SUBSCRIPTION_PLAN.plan,
            plan_label: DEFAULT_SUBSCRIPTION_PLAN.plan_label,
            billing_cycle: DEFAULT_SUBSCRIPTION_PLAN.billing_cycle,
            currency: DEFAULT_SUBSCRIPTION_PLAN.currency,
            price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
            stripe_price_id: DEFAULT_SUBSCRIPTION_PLAN.stripe_price_id,
            products: [DEFAULT_SUBSCRIPTION_PLAN.stripe_product_id],
            trial_days: DEFAULT_TRIAL.trialDays,
            trial_period_days: DEFAULT_TRIAL.trialDays,
            pricing: DEFAULT_SUBSCRIPTION_PLAN.monthly_price
        };
        applyManualSetup(draft);
        state.currentStep = 2;
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
                if (action === 'next-card') { if (state.linkedinConnected) state.currentStep = 3; return render(); }
                if (action === 'back-linkedin') { state.currentStep = 2; return render(); }
                if (action === 'toggle-password') { state.showPassword = !state.showPassword; return render(); }
                if (action === 'connect-linkedin') return connectLinkedIn();
                if (action === 'submit-2fa') return submitTwoFactorCode();
                if (action === 'add-card') return addCard();
            });
        });
    }

    async function init() {
        render();
        try {
            const params = new URLSearchParams(window.location.search);
            const forceNameScreen = isJobSeekersRoute() && !params.has('billing');
            offerId = getOfferIdFromUrl();
            if (forceNameScreen) offerId = '';
            manualSetupMode = forceNameScreen || !offerId || params.get('manual_trial') === '1';

            if (manualSetupMode) {
                const draft = forceNameScreen ? null : readManualSetupDraft();
                if (draft) applyManualSetup(draft);
                else {
                    testId = 'manual_' + simpleHash('manual|' + Date.now());
                    storageKey = 'coherta_free_test_' + testId;
                    linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
                }
            } else {
                const offer = await fetchOfferData(offerId);
                state.trial = buildTrialFromOffer(offer);
                state.username = state.trial.customerEmail || '';
                testId = normalizeIdentifier(firstValue(offer, ['test_id', 'trial_id'], '')) || ('test_' + offerId);
                storageKey = 'coherta_free_test_' + testId;
                linkedInStorageKey = 'coherta_free_test_linkedin_connected_' + testId;
            }

            await hydrateLinkedInState();

            const stored = readStoredTest();
            if (!manualSetupMode && stored && stored.started_at) {
                state.trial.startedAt = stored.started_at;
                state.cardAdded = true;
                state.currentStep = 4;
            }

            if (forceNameScreen) {
                state.currentStep = 1;
                state.trial = { ...DEFAULT_TRIAL };
                state.username = '';
                state.password = '';
                state.twoFactorCode = '';
                state.linkedinConnected = false;
                state.linkedInStatus = 'idle';
                state.linkedinError = '';
                state.linkedinMessage = '';
                state.cardAdded = false;
                state.cardStatus = 'idle';
                state.cardError = '';
            } else if (manualSetupMode && !params.has('billing')) {
                state.currentStep = 1;
                state.cardAdded = false;
                state.cardStatus = 'idle';
                state.cardError = '';
            }

            if (params.get('billing') === 'success') {
                const pending = JSON.parse(localStorage.getItem(PENDING_BILLING_KEY) || 'null');
                if (pending) localStorage.removeItem(PENDING_BILLING_KEY);
                state.loading = false;
                state.currentStep = 3;
                await completeStartTest();
                window.history.replaceState({}, document.title, window.location.pathname + (offerId ? '?offerId=' + encodeURIComponent(offerId) : '?manual_trial=1'));
                return;
            }

            if (params.get('billing') === 'cancelled') {
                state.cardError = 'Card setup was cancelled. You can try again when you are ready.';
                state.currentStep = 3;
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
