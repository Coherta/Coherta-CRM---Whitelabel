<?php
/*
 * Offer approval/access flow
 * Step 1: Review access
 * Step 2: Optional LinkedIn
 * Step 3: Payment / approve
 * Step 4: Account ready
 */

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtDate(?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    return $ts ? date('d-m-Y', $ts) : h($date);
}

function cohertaPost(string $url, array $payload): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = null;
    if ($response !== false && $response !== null && $response !== '') {
        $json = json_decode($response, true);
    }

    return [
        'http_code' => (int)$code,
        'error' => $err ?: null,
        'raw' => $response,
        'json' => $json,
    ];
}

function iconCheck(): string {
    return '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
}

function iconArrow(): string {
    return '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
}

$query = $_SERVER['QUERY_STRING'] ?? '';
if (!preg_match('/(offer_[a-f0-9]+)/i', $query, $m)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'missing_offer_id']);
    exit;
}
$offerId = $m[1];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve') {
    header('Content-Type: application/json; charset=utf-8');

    $approve = cohertaPost(
        'https://api.coherta.com/coherta/trial/approve/' . rawurlencode($offerId),
        ['offer_id' => $offerId]
    );

    $ok = !$approve['error'] && $approve['http_code'] >= 200 && $approve['http_code'] < 300;

    echo json_encode([
        'success' => $ok,
        'http_code' => $approve['http_code'],
        'error' => $ok ? null : ($approve['error'] ?: 'approve_failed'),
        'data' => $approve['json'],
    ]);
    exit;
}

$offerResponse = cohertaPost(
    'https://api.coherta.com/coherta/trial/view/' . rawurlencode($offerId),
    ['offer_id' => $offerId]
);
$offer = json_decode((string)$offerResponse['raw'], true);
if (!is_array($offer)) {
    $offer = [];
}

$currencyMap = [
    208 => 'DKK',
    978 => 'EUR',
    840 => 'USD',
];
$currencyCode = $currencyMap[(int)($offer['currency'] ?? 208)] ?? 'DKK';
$pricingVal = (float)($offer['pricing'] ?? 0);
$periodMonths = max(0, (int)($offer['period_months'] ?? 0));
$monthlyVal = $periodMonths > 0 && $pricingVal > 0 ? $pricingVal / $periodMonths : $pricingVal;
$priceDisplay = $monthlyVal > 0
    ? $currencyCode . ' ' . number_format($monthlyVal, 0, ',', '.') . ' / md.'
    : $currencyCode . ' 0 / md.';
$billingDisplay = $periodMonths >= 12
    ? 'Pr. organisation - faktureres årligt'
    : ($periodMonths > 0 ? 'Pr. organisation - faktureres ' . $periodMonths . ' mdr.' : 'Pr. organisation');

$approvedAlready = isset($_GET['created']) && (string)$_GET['created'] === '1';
$receiverName = $offer['receiver_name'] ?? $offer['name'] ?? 'Kontaktperson';
$email = $offer['email'] ?? '-';
$companyUid = $offer['company_uid'] ?? '-';
$companyName = $offer['company_name'] ?? 'Din konto';
$packageName = $offer['plan_label'] ?? $offer['package_name'] ?? 'Extended';

$features = [
    'Emnedatabase og segmentering',
    'Emnelister',
    'Overvågning af beslutningstagere (150)',
    'Coherta Campaigns',
    'AI-personalisering',
    'Samlet inbox og teamrettigheder',
    'Prioriteret support',
];

$publicPage = true;
$PageTitle = 'Review and approve your offer';
include('partial/header.php');
?>

<style>
    :root {
        --co-blue:#2563eb;
        --co-blue-dark:#1d4ed8;
        --co-blue-soft:#eff6ff;
        --co-blue-line:#bfdbfe;
        --co-ink:#0f172a;
        --co-muted:#64748b;
        --co-line:#e2e8f0;
        --co-green:#10b981;
        --co-bg:#f8fafc;
        --co-shadow:0 18px 45px rgba(15,23,42,.07);
    }
    .co-access-wrap, .co-access-wrap * { box-sizing:border-box; }
    .co-access-wrap {
        min-height:calc(100vh - 80px);
        margin:0 -15px;
        padding:18px 28px 22px;
        background:var(--co-bg);
        color:var(--co-ink);
        font-family:Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .co-shell { max-width:1320px; margin:0 auto; }
    .co-topbar {
        min-height:64px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:18px;
        position:sticky;
        top:0;
        z-index:20;
        padding:6px 0;
        background:rgba(248,250,252,.92);
        backdrop-filter:blur(14px);
    }
    .co-brand { display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; }
    .co-brand img { width:36px; height:36px; object-fit:contain; }
    .co-brand-name { font-size:23px; line-height:1; font-weight:680; letter-spacing:-.03em; }
    .co-proof { display:flex; align-items:center; gap:12px; }
    .co-member {
        width:238px;
        min-height:52px;
        border:1px solid #dbe3ee;
        border-radius:15px;
        background:#fff;
        display:flex;
        align-items:center;
        gap:12px;
        padding:0 16px;
        box-shadow:0 7px 16px rgba(16,24,40,.035);
    }
    .co-member img { display:block; width:32px; height:auto; max-height:32px; object-fit:contain; }
    .co-member img.co-de-logo { width:38px; border-radius:3px; }
    .co-member span { font-size:13.5px; line-height:1.12; font-weight:650; letter-spacing:-.015em; color:#203a5f; }
    .co-layout { display:grid; grid-template-columns:1fr 300px; gap:18px; align-items:start; }
    .co-main {
        border:1px solid var(--co-line);
        border-radius:24px;
        background:#fff;
        box-shadow:var(--co-shadow);
        padding:24px 30px 30px;
    }
    .co-main-content { padding-top:24px; }
    .co-right-rail { position:sticky; top:86px; display:grid; gap:14px; }
    .co-journey {
        border:1px solid #dbeafe;
        border-radius:18px;
        background:#f8fbff;
        padding:13px 14px 14px;
    }
    .co-journey-list { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .co-j-step {
        width:100%;
        min-height:54px;
        display:grid;
        grid-template-columns:26px 1fr;
        gap:8px;
        align-items:center;
        text-align:left;
        border:1px solid transparent;
        border-radius:15px;
        background:#fff;
        padding:10px;
        color:inherit;
        cursor:pointer;
    }
    .co-j-step:hover:not(:disabled) { background:#f8fafc; }
    .co-j-step.active { border-color:var(--co-blue-line); background:var(--co-blue-soft); }
    .co-j-step.done { border-color:#d1fae5; }
    .co-j-step:disabled { opacity:.55; cursor:not-allowed; }
    .co-dot {
        width:24px;
        height:24px;
        border-radius:50%;
        display:grid;
        place-items:center;
        background:#f1f5f9;
        color:#64748b;
        font-size:11px;
        font-weight:700;
    }
    .co-j-step.active .co-dot { background:var(--co-blue); color:#fff; }
    .co-j-step.done .co-dot { background:var(--co-green); color:#fff; }
    .co-j-title { font-size:12.5px; line-height:1.18; font-weight:650; letter-spacing:-.012em; }
    .co-j-sub { margin-top:2px; font-size:11px; line-height:1.18; color:var(--co-muted); font-weight:450; }
    .co-contact-card {
        border:1px solid var(--co-line);
        border-radius:22px;
        background:#fff;
        box-shadow:0 14px 34px rgba(15,23,42,.045);
        padding:26px;
    }
    .co-contact-label { font-size:14px; font-weight:500; color:var(--co-blue-dark); margin-bottom:20px; }
    .co-contact-person { display:grid; grid-template-columns:72px 1fr; gap:16px; align-items:center; }
    .co-contact-photo { width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #fff; box-shadow:0 12px 28px rgba(16,24,40,.12); }
    .co-contact-name { font-size:15.5px; font-weight:560; letter-spacing:-.018em; line-height:1.18; }
    .co-contact-role { margin-top:6px; font-size:12.5px; color:var(--co-muted); font-weight:430; }
    .co-contact-note { margin-top:24px; padding-top:18px; border-top:1px solid var(--co-line); font-size:12.8px; line-height:1.48; color:#475569; }
    .co-step { display:none; }
    .co-step.active { display:block; }
    .co-step-compact { display:flex; flex-direction:column; gap:14px; min-height:calc(100vh - 170px); }
    .co-step-hero { display:grid; grid-template-columns:1fr 270px; gap:18px; align-items:stretch; }
    .co-step h1 {
        margin:0;
        font-size:35px;
        line-height:1.04;
        font-weight:650;
        letter-spacing:-.048em;
        color:var(--co-ink);
    }
    .co-lead { max-width:730px; margin:13px 0 0; font-size:15.2px; line-height:1.52; font-weight:410; color:#53657a; }
    .co-lead.tight { max-width:620px; margin-top:10px; font-size:15px; line-height:1.45; }
    .co-intro-grid { margin-top:22px; display:grid; grid-template-columns:minmax(0,1.45fr) minmax(280px,.55fr); gap:22px; align-items:stretch; }
    .co-card {
        border:1px solid var(--co-line);
        border-radius:20px;
        background:#fff;
        padding:30px;
    }
    .co-card.blue { border-color:#bfdbfe; background:#eff6ff; padding:34px; }
    .co-intro-title { font-size:25px; line-height:1.13; font-weight:740; letter-spacing:-.045em; color:var(--co-ink); }
    .co-intro-copy { margin-top:10px; font-size:14px; line-height:1.5; color:var(--co-muted); font-weight:450; }
    .co-feature-block { margin-top:30px; padding-top:26px; border-top:1px solid #cfe3ff; }
    .co-feature-title { font-size:13px; font-weight:680; color:#1d4ed8; letter-spacing:-.015em; margin-bottom:13px; }
    .co-list { display:grid; gap:13px; margin-top:16px; }
    .co-feature-list { grid-template-columns:1fr 1fr; gap:18px 34px; margin-top:0; }
    .co-list-item {
        display:grid;
        grid-template-columns:24px 1fr;
        gap:11px;
        align-items:flex-start;
        font-size:14.8px;
        line-height:1.4;
        color:#334155;
        font-weight:420;
    }
    .co-check {
        width:19px;
        height:19px;
        border-radius:50%;
        background:var(--co-green);
        color:#fff;
        display:grid;
        place-items:center;
        flex:0 0 auto;
        margin-top:1px;
    }
    .co-actions { margin-top:26px; display:flex; gap:12px; align-items:center; justify-content:center; flex-wrap:wrap; }
    .co-actions.between { justify-content:space-between; }
    .co-offer-actions { justify-content:flex-end; }
    .co-btn {
        min-height:46px;
        border-radius:13px;
        padding:0 22px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:9px;
        font-size:14px;
        font-weight:750;
        border:0;
        text-decoration:none;
        transition:.15s ease;
        background:var(--co-blue);
        color:#fff;
        box-shadow:0 12px 26px rgba(37,99,235,.18);
    }
    .co-btn:hover:not(:disabled) { background:var(--co-blue-dark); transform:translateY(-1px); }
    .co-btn.secondary { background:#fff; color:var(--co-blue-dark); border:1px solid var(--co-line); box-shadow:none; }
    .co-btn:disabled { background:#e2e8f0; color:#94a3b8; border-color:transparent; box-shadow:none; transform:none; cursor:not-allowed; }
    .co-step-page { max-width:900px; margin:0 auto; padding-top:2px; }
    .co-panel { margin-top:28px; padding:28px; border:1px solid #cfe3ff; border-radius:22px; background:#f4f9ff; }
    .co-helper-grid, .co-payment-grid, .co-result-grid { display:grid; grid-template-columns:.9fr 1.1fr; gap:28px; align-items:stretch; }
    .co-quiet-card, .co-price-card, .co-result-card { border:1px solid var(--co-line); background:#fff; border-radius:19px; padding:24px; }
    .co-quiet-title { font-size:16px; font-weight:540; letter-spacing:-.025em; margin-bottom:12px; color:#0f172a; }
    .co-quiet-copy { font-size:13.8px; line-height:1.55; font-weight:380; color:#52637a; margin:0 0 16px; }
    .co-form { max-width:500px; margin:0 auto; padding:2px 0 0; }
    .co-form label { display:block; font-size:12.8px; font-weight:560; color:#172033; margin:16px 0 7px; }
    .co-form input { width:100%; height:46px; border:1px solid #cbd5e1; border-radius:12px; background:#fff; padding:0 13px; outline:none; color:#334155; font-size:14px; font-weight:390; }
    .co-form input:focus { border-color:var(--co-blue); box-shadow:0 0 0 4px #dbeafe; }
    .co-linkedin-mark { display:grid; place-items:center; width:20px; height:20px; border-radius:3px; background:#0a66c2; color:#fff; font-family:Arial, Helvetica, sans-serif; font-weight:800; line-height:1; }
    .co-status, .co-error {
        margin-top:12px;
        min-height:40px;
        display:none;
        align-items:center;
        justify-content:center;
        gap:8px;
        border-radius:10px;
        padding:10px 14px;
        font-size:13px;
        font-weight:740;
    }
    .co-status.show { display:flex; }
    .co-status.ok { background:#ecfdf5; border:1px solid #bbf7d0; color:#047857; }
    .co-status.info { background:#fff; border:1px solid var(--co-blue-line); color:var(--co-blue-dark); width:100%; }
    .co-error.show { display:flex; justify-content:flex-start; border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; }
    .co-payment-grid { grid-template-columns:1fr .92fr; gap:26px; }
    .co-soft-list { display:grid; gap:16px; }
    .co-soft-list .co-list-item { min-height:34px; }
    .co-result-grid { grid-template-columns:1fr 1fr; gap:24px; }
    .co-result-card h3 { margin:0 0 14px; font-size:17px; font-weight:540; letter-spacing:-.025em; }
    .co-result-card p { margin:0; color:#52637a; font-size:13.8px; line-height:1.55; font-weight:380; }
    .co-status-actions { display:flex; gap:12px; align-items:center; justify-content:space-between; margin-top:22px; flex-wrap:wrap; }
    .co-status-note { font-size:12.8px; font-weight:380; color:#64748b; }
    .co-success-wrap {
        width:58px;
        height:58px;
        border-radius:50%;
        display:grid;
        place-items:center;
        margin-bottom:18px;
        background:#ecfdf5;
        color:#047857;
        border:1px solid #bbf7d0;
    }
    .co-footer { margin-top:13px; text-align:center; font-size:12px; font-weight:380; color:#64748b; }
    @media(max-width:1100px) {
        .co-layout { grid-template-columns:1fr; }
        .co-right-rail { position:static; }
        .co-intro-grid, .co-step-hero { grid-template-columns:1fr; }
    }
    @media(max-width:900px) {
        .co-feature-list, .co-helper-grid, .co-payment-grid, .co-result-grid { grid-template-columns:1fr; }
        .co-actions.between { justify-content:flex-end; }
        .co-actions.between > span { display:none; }
    }
    @media(max-width:760px) {
        .co-access-wrap { padding:14px; }
        .co-topbar { align-items:flex-start; flex-direction:column; }
        .co-proof { width:100%; flex-direction:column; align-items:stretch; }
        .co-member { width:100%; }
        .co-main { padding:20px; }
        .co-step h1 { font-size:30px; }
        .co-journey-list { grid-template-columns:1fr 1fr; }
    }
</style>

<div class="container-fluid p-0">
    <div class="co-access-wrap">
        <div class="co-shell">
            <div class="co-topbar">
                <a class="co-brand" href="/da/" aria-label="Coherta">
                    <img src="/assets/img/logo/logo.png?w=80" alt="" width="3200" height="715">
                    <span class="co-brand-name">Coherta</span>
                </a>
                <div class="co-proof">
                    <div class="co-member"><img src="/da/assets/img/dansk-industri-logo.png" alt="Dansk Industri" width="176" height="128"><span>Medlem af Dansk Industri</span></div>
                    <div class="co-member"><img class="co-de-logo" src="/da/assets/img/dansk-erhverv-logo.svg" alt="Dansk Erhverv" width="512" height="512"><span>Medlem af Dansk Erhverv</span></div>
                </div>
            </div>

            <div class="co-layout" id="coOfferFlow" data-approved="<?php echo $approvedAlready ? '1' : '0'; ?>">
                <main class="co-main">
                    <aside class="co-journey" aria-label="Offer steps">
                        <div class="co-journey-list">
                            <button type="button" class="co-j-step" data-step-nav="1"><span class="co-dot">1</span><span><span class="co-j-title">Gennemgå adgang</span><span class="co-j-sub">Pakke og konto</span></span></button>
                            <button type="button" class="co-j-step" data-step-nav="2"><span class="co-dot">2</span><span><span class="co-j-title">Forbind LinkedIn</span><span class="co-j-sub" data-step-sub="2">Valgfrit</span></span></button>
                            <button type="button" class="co-j-step" data-step-nav="3"><span class="co-dot">3</span><span><span class="co-j-title">Betaling</span><span class="co-j-sub" data-step-sub="3">Stripe</span></span></button>
                            <button type="button" class="co-j-step" data-step-nav="4"><span class="co-dot">4</span><span><span class="co-j-title">Konto klar</span><span class="co-j-sub">Adgang åbnet</span></span></button>
                        </div>
                    </aside>

                    <div class="co-main-content">
                        <section class="co-step co-step-compact" data-step="1">
                            <div class="co-step-hero">
                                <div>
                                    <h1>Din <?php echo h($packageName); ?>-adgang er klar</h1>
                                    <p class="co-lead tight">Vi har klargjort din konto og den pakke, vi har aftalt med dig. Gennemgå adgangen, fortsæt til LinkedIn, og tilføj betalingskort via Stripe for at åbne kontoen.</p>
                                </div>
                            </div>

                            <div class="co-intro-grid">
                                <section class="co-card blue">
                                    <div class="co-intro-title"><?php echo h($packageName); ?></div>
                                    <p class="co-intro-copy">Til salgsorganisationer der vil kombinere Campaigns, signaler og flere workflows.</p>
                                    <div class="co-feature-block">
                                        <div class="co-feature-title">Det får du adgang til</div>
                                        <div class="co-list co-feature-list">
                                            <?php foreach ($features as $feature): ?>
                                                <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span><?php echo h($feature); ?></span></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </section>

                                <section class="co-card">
                                    <div class="co-intro-title">Din konto</div>
                                    <div class="co-list">
                                        <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Gennemgå din <?php echo h($packageName); ?>-adgang.</span></div>
                                        <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Forbind LinkedIn nu, eller spring over og gør det senere.</span></div>
                                        <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Tilføj betalingskort via Stripe for at åbne adgangen.</span></div>
                                    </div>
                                </section>
                            </div>

                            <div class="co-actions between co-offer-actions"><span></span><button type="button" class="co-btn" data-next="2">Fortsæt <?php echo iconArrow(); ?></button></div>
                        </section>

                        <section class="co-step co-step-page" data-step="2">
                            <h1>Forbind LinkedIn</h1>
                            <p class="co-lead">Forbind LinkedIn nu, hvis adgangen skal bruges til aktivering og outreach. Du kan også springe over og åbne kontoen først.</p>
                            <div class="co-panel">
                                <div class="co-helper-grid">
                                    <section class="co-quiet-card">
                                        <div class="co-quiet-title">LinkedIn-aktivering</div>
                                        <p class="co-quiet-copy">Vi anbefaler at forbinde LinkedIn nu, så kontoen er klar til Campaigns og outreach fra start.</p>
                                        <div class="co-list">
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Forbind LinkedIn og fortsæt til betaling.</span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Spring over, hvis du vil åbne kontoen først.</span></div>
                                        </div>
                                    </section>
                                    <section class="co-quiet-card">
                                        <div class="co-form">
                                            <label for="coLinkedinUser">LinkedIn e-mail</label>
                                            <input id="coLinkedinUser" type="email" autocomplete="username" placeholder="navn@virksomhed.dk">
                                            <label for="coLinkedinPassword">Adgangskode</label>
                                            <input id="coLinkedinPassword" type="password" autocomplete="current-password" placeholder="Indtast adgangskode">
                                            <button type="button" class="co-btn" id="coConnectLinkedin" style="width:100%;margin-top:16px"><span class="co-linkedin-mark">in</span><span>Forbind LinkedIn</span></button>
                                            <div class="co-status ok" id="coLoginStatus"><?php echo iconCheck(); ?> Login er godkendt</div>
                                            <button type="button" class="co-status info" id="coApproveLinkedin">Bekræft på mobil for at fortsætte</button>
                                            <div class="co-status ok" id="coTwoFactorStatus"><?php echo iconCheck(); ?> Two-factor er godkendt</div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <div class="co-actions between">
                                <button type="button" class="co-btn secondary" data-prev="1">Tilbage</button>
                                <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end">
                                    <button type="button" class="co-btn secondary" id="coSkipLinkedin">Spring over</button>
                                    <button type="button" class="co-btn" id="coContinuePayment" disabled>Fortsæt <?php echo iconArrow(); ?></button>
                                </div>
                            </div>
                        </section>

                        <section class="co-step co-step-page" data-step="3">
                            <h1>Betaling</h1>
                            <p class="co-lead">Tilføj betalingskort via Stripe for at åbne adgangen til den klargjorte <?php echo h($packageName); ?>-konto. Kortoplysninger håndteres sikkert via Stripe.</p>
                            <div class="co-panel">
                                <div class="co-payment-grid">
                                    <section class="co-price-card">
                                        <div class="co-quiet-title">Aftalen</div>
                                        <div class="co-soft-list">
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span><strong>Pakke:</strong> <?php echo h($packageName); ?></span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span><strong>Pris:</strong> <?php echo h($priceDisplay); ?></span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span><strong>Fakturering:</strong> <?php echo h($billingDisplay); ?></span></div>
                                        </div>
                                    </section>
                                    <section class="co-price-card">
                                        <div class="co-quiet-title">Inden du fortsætter</div>
                                        <div class="co-soft-list">
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Du accepterer adgang til den aftalte <?php echo h($packageName); ?>-pakke.</span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Betalingen kører via Stripe, så kort kan gemmes sikkert.</span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span id="coLinkedinPaymentState">LinkedIn kan forbindes senere fra kontoen.</span></div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <div class="co-error" id="coApproveError">Kunne ikke godkende tilbuddet. Prøv igen.</div>
                            <div class="co-actions between">
                                <button type="button" class="co-btn secondary" data-prev="2">Tilbage</button>
                                <button type="button" class="co-btn" id="coApproveOffer">Godkend og tilføj kort <?php echo iconArrow(); ?></button>
                            </div>
                            <div class="co-footer">Kortoplysninger håndteres sikkert via Stripe.</div>
                        </section>

                        <section class="co-step co-step-page" data-step="4">
                            <div class="co-success-wrap"><?php echo iconCheck(); ?></div>
                            <h1>Kontoen er klar</h1>
                            <p class="co-lead">Din adgang til Coherta er åbnet. Du kan nu gå til kontoen og arbejde videre med den <?php echo h($packageName); ?>-pakke, der er gjort klar til dig.</p>
                            <div class="co-panel">
                                <div class="co-result-grid">
                                    <section class="co-result-card">
                                        <h3>Hvad sker der nu?</h3>
                                        <div class="co-list">
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Du får adgang til den aftalte pakke: <strong><?php echo h($packageName); ?></strong>.</span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Du kan se og administrere din konto fra kontosiden.</span></div>
                                            <div class="co-list-item"><span class="co-check"><?php echo iconCheck(); ?></span><span>Hvis LinkedIn ikke er forbundet endnu, kan det gøres senere.</span></div>
                                        </div>
                                    </section>
                                    <section class="co-result-card">
                                        <h3>Din konto</h3>
                                        <p>Herfra får du adgang til den konto, der er åbnet på baggrund af aftalen.</p>
                                        <div class="co-status-actions">
                                            <div class="co-status-note">Kontosiden er det faste overblik for adgang, betaling og opsætning.</div>
                                            <a class="co-btn" href="/app/">Tilgå min konto <?php echo iconArrow(); ?></a>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </section>
                    </div>
                </main>

                <aside class="co-right-rail">
                    <div class="co-contact-card">
                        <div class="co-contact-label">Din kontaktperson</div>
                        <div class="co-contact-person">
                            <img class="co-contact-photo" src="https://api.coherta.com/images/img_68ed7e19c4ed8.jpg" alt="Valdemar Bilecka">
                            <div>
                                <div class="co-contact-name">Valdemar Bilecka</div>
                                <div class="co-contact-role">Co-founder</div>
                            </div>
                        </div>
                        <div class="co-contact-note">Spørgsmål til tilbuddet eller LinkedIn?<br>Skriv eller ring direkte, så hjælper vi dig videre.</div>
                    </div>
                </aside>
            </div>
            <div class="co-footer">© 2026 Coherta ApS</div>
        </div>
    </div>
</div>

<script>
    (function() {
        const flow = document.getElementById('coOfferFlow');
        if (!flow) return;

        let currentStep = flow.dataset.approved === '1' ? 4 : 1;
        let maxStep = currentStep;
        let linkedinConnected = false;
        let linkedinSkipped = false;
        let approved = currentStep === 4;

        const steps = Array.from(flow.querySelectorAll('[data-step]'));
        const navs = Array.from(flow.querySelectorAll('[data-step-nav]'));
        const paymentState = document.getElementById('coLinkedinPaymentState');

        function setPaymentState() {
            if (!paymentState) return;
            paymentState.textContent = linkedinConnected
                ? 'LinkedIn er forbundet.'
                : (linkedinSkipped ? 'LinkedIn er sprunget over og kan forbindes senere.' : 'LinkedIn kan forbindes senere fra kontoen.');
        }

        function render() {
            steps.forEach((step) => step.classList.toggle('active', Number(step.dataset.step) === currentStep));
            navs.forEach((nav) => {
                const step = Number(nav.dataset.stepNav);
                const isDone = step < currentStep || (step === 2 && linkedinConnected && currentStep > 2) || (step === 3 && approved);
                const canVisit = step <= maxStep || (approved && step === 4);
                nav.classList.toggle('active', step === currentStep);
                nav.classList.toggle('done', isDone);
                nav.disabled = !canVisit;
                const dot = nav.querySelector('.co-dot');
                if (dot) dot.innerHTML = isDone ? <?php echo json_encode(iconCheck()); ?> : String(step);
            });
            const step2Sub = flow.querySelector('[data-step-sub="2"]');
            const step3Sub = flow.querySelector('[data-step-sub="3"]');
            if (step2Sub) step2Sub.textContent = linkedinConnected ? 'Forbundet' : (linkedinSkipped ? 'Sprunget over' : 'Valgfrit');
            if (step3Sub) step3Sub.textContent = approved ? 'Godkendt' : 'Stripe';
            setPaymentState();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goTo(step) {
            if (step > maxStep && !(approved && step === 4)) return;
            currentStep = step;
            render();
        }

        flow.addEventListener('click', async function(event) {
            const next = event.target.closest('[data-next]');
            const prev = event.target.closest('[data-prev]');
            const nav = event.target.closest('[data-step-nav]');
            if (next) {
                const step = Number(next.dataset.next);
                maxStep = Math.max(maxStep, step);
                currentStep = step;
                render();
                return;
            }
            if (prev) {
                goTo(Number(prev.dataset.prev));
                return;
            }
            if (nav) {
                goTo(Number(nav.dataset.stepNav));
            }
        });

        const connectBtn = document.getElementById('coConnectLinkedin');
        const loginStatus = document.getElementById('coLoginStatus');
        const approveLinkedin = document.getElementById('coApproveLinkedin');
        const twoFactorStatus = document.getElementById('coTwoFactorStatus');
        const continuePayment = document.getElementById('coContinuePayment');
        const skipLinkedin = document.getElementById('coSkipLinkedin');
        const approveOffer = document.getElementById('coApproveOffer');
        const approveError = document.getElementById('coApproveError');

        if (connectBtn) {
            connectBtn.addEventListener('click', function() {
                connectBtn.disabled = true;
                connectBtn.querySelector('span:last-child').textContent = 'Forbinder...';
                window.setTimeout(function() {
                    loginStatus.classList.add('show');
                    approveLinkedin.classList.add('show');
                    connectBtn.querySelector('span:last-child').textContent = 'LinkedIn afventer';
                }, 350);
            });
        }

        if (approveLinkedin) {
            approveLinkedin.addEventListener('click', function() {
                linkedinConnected = true;
                linkedinSkipped = false;
                approveLinkedin.classList.remove('show');
                twoFactorStatus.classList.add('show');
                connectBtn.querySelector('span:last-child').textContent = 'LinkedIn forbundet';
                continuePayment.disabled = false;
                continuePayment.innerHTML = 'Fortsæt til betaling <?php echo h(iconArrow()); ?>';
                render();
            });
        }

        if (skipLinkedin) {
            skipLinkedin.addEventListener('click', function() {
                linkedinSkipped = true;
                linkedinConnected = false;
                maxStep = Math.max(maxStep, 3);
                currentStep = 3;
                render();
            });
        }

        if (continuePayment) {
            continuePayment.addEventListener('click', function() {
                if (continuePayment.disabled) return;
                maxStep = Math.max(maxStep, 3);
                currentStep = 3;
                render();
            });
        }

        if (approveOffer) {
            approveOffer.addEventListener('click', async function() {
                approveError.classList.remove('show');
                approveOffer.disabled = true;
                const original = approveOffer.innerHTML;
                approveOffer.textContent = 'Godkender...';
                try {
                    const fd = new FormData();
                    fd.append('action', 'approve');
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: fd
                    });
                    const data = await res.json();
                    if (!data || !data.success) throw new Error(data && data.error ? data.error : 'approve_failed');
                    approved = true;
                    maxStep = 4;
                    currentStep = 4;
                    render();
                } catch (error) {
                    approveError.textContent = 'Kunne ikke godkende tilbuddet. Prøv igen.';
                    approveError.classList.add('show');
                    approveOffer.disabled = false;
                    approveOffer.innerHTML = original;
                }
            });
        }

        render();
    })();
</script>

<?php include('partial/footer-end.php'); ?>
