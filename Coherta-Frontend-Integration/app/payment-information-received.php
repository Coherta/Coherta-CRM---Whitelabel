<?php
$checkoutSessionId = isset($_GET['session_id']) ? trim((string)$_GET['session_id']) : '';
$reference = $checkoutSessionId !== '' ? substr(preg_replace('/[^A-Za-z0-9_]/', '', $checkoutSessionId), 0, 18) : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Coherta has received your payment information and will contact you about the next onboarding step.">
    <title>Payment Information Received | Coherta</title>
    <link rel="icon" href="/app/assets/images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="/app/assets/images/favicon.png">
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #5f6f85;
            --line: #dfe7f1;
            --blue: #2563eb;
            --blue-dark: #1d4ed8;
            --blue-soft: #eff6ff;
            --green: #10b981;
            --green-dark: #047857;
            --shadow: 0 24px 70px rgba(15, 23, 42, 0.11);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(239, 246, 255, 0.82), rgba(255, 255, 255, 0.92) 44%, rgba(236, 253, 245, 0.74)),
                var(--bg);
            color: var(--ink);
        }
        a { color: inherit; }
        .page {
            min-height: 100vh;
            padding: 22px 28px 26px;
        }
        .shell {
            max-width: 1180px;
            margin: 0 auto;
        }
        .topbar {
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }
        .brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }
        .brand span {
            font-size: 24px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0;
        }
        .secure-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid #c8f1de;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            color: var(--green-dark);
            font-size: 13px;
            font-weight: 720;
            box-shadow: 0 8px 22px rgba(16, 185, 129, 0.08);
        }
        .secure-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--green);
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.12);
        }
        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 324px;
            gap: 18px;
            align-items: stretch;
            margin-top: 22px;
        }
        .main {
            position: relative;
            min-height: 620px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: var(--shadow);
        }
        .main::before {
            position: absolute;
            inset: 0;
            content: "";
            background:
                linear-gradient(120deg, rgba(37, 99, 235, 0.08), transparent 44%),
                radial-gradient(circle at 84% 18%, rgba(16, 185, 129, 0.16), transparent 28%);
            pointer-events: none;
        }
        .hero {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 30px;
            align-items: center;
            min-height: 510px;
            padding: 54px;
        }
        .status-mark {
            width: 76px;
            height: 76px;
            display: grid;
            place-items: center;
            border: 1px solid #bbf7d0;
            border-radius: 50%;
            background: #ecfdf5;
            color: var(--green-dark);
            box-shadow: 0 18px 38px rgba(16, 185, 129, 0.13);
            margin-bottom: 26px;
        }
        h1 {
            max-width: 720px;
            margin: 0;
            font-size: clamp(40px, 6vw, 72px);
            line-height: 0.96;
            font-weight: 730;
            letter-spacing: 0;
        }
        .lead {
            max-width: 650px;
            margin: 22px 0 0;
            color: #334155;
            font-size: 18px;
            line-height: 1.58;
            font-weight: 430;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
        }
        .button {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 18px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            color: var(--blue-dark);
            font-size: 14px;
            font-weight: 760;
            text-decoration: none;
        }
        .button.primary {
            border-color: var(--blue);
            background: var(--blue);
            color: #fff;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.17);
        }
        .button:hover {
            transform: translateY(-1px);
        }
        .summary-panel {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.07);
            padding: 22px;
        }
        .summary-eyebrow {
            color: var(--blue-dark);
            font-size: 12px;
            font-weight: 780;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .summary-title {
            margin-top: 10px;
            font-size: 21px;
            line-height: 1.18;
            font-weight: 690;
        }
        .steps {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }
        .step {
            display: grid;
            grid-template-columns: 34px 1fr;
            gap: 12px;
            align-items: start;
        }
        .step-number {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--blue-soft);
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 800;
        }
        .step strong {
            display: block;
            font-size: 14px;
            line-height: 1.28;
        }
        .step span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.38;
        }
        .reference {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }
        .reference code {
            color: var(--ink);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12px;
        }
        .contact-card {
            border: 1px solid var(--line);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.07);
            padding: 24px;
        }
        .contact-label {
            color: var(--blue-dark);
            font-size: 14px;
            font-weight: 650;
            margin-bottom: 18px;
        }
        .contact-person {
            display: grid;
            grid-template-columns: 72px 1fr;
            gap: 15px;
            align-items: center;
        }
        .contact-person img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 12px 28px rgba(16, 24, 40, 0.12);
            background: #fff;
        }
        .contact-name {
            font-size: 16px;
            font-weight: 650;
            line-height: 1.16;
        }
        .contact-role {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
        }
        .contact-links {
            display: grid;
            gap: 12px;
            margin-top: 22px;
        }
        .contact-links a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 560;
            text-decoration: none;
            overflow-wrap: anywhere;
        }
        .contact-links a:hover {
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .contact-note {
            margin: 22px 0 0;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
        svg { display: block; }
        @media (max-width: 980px) {
            .layout,
            .hero {
                grid-template-columns: 1fr;
            }
            .hero {
                min-height: auto;
                padding: 34px;
            }
            .main {
                min-height: 0;
            }
        }
        @media (max-width: 640px) {
            .page {
                padding: 16px;
            }
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
            .secure-pill {
                width: 100%;
                justify-content: center;
            }
            .hero {
                padding: 24px;
            }
            .status-mark {
                width: 64px;
                height: 64px;
            }
            .actions,
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="/da/" aria-label="Coherta front page">
                <img src="/app/assets/images/logo/logo.png" alt="Coherta logo">
                <span>Coherta</span>
            </a>
            <div class="secure-pill"><span class="secure-dot"></span> Payment information received</div>
        </header>

        <div class="layout">
            <main class="main">
                <section class="hero" aria-labelledby="payment-title">
                    <div>
                        <div class="status-mark" aria-hidden="true">
                            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5"></path>
                            </svg>
                        </div>
                        <h1 id="payment-title">We have received your payment information.</h1>
                        <p class="lead">Thank you. Coherta has received the payment details for your agreement. Our team will review the setup and contact you about the next step in the onboarding process.</p>
                        <div class="actions">
                            <a class="button primary" href="/da/">Back to Coherta</a>
                            <a class="button" href="mailto:valdemar@coherta.com">Contact Coherta</a>
                        </div>
                    </div>

                    <aside class="summary-panel" aria-label="What happens next">
                        <div class="summary-eyebrow">Next steps</div>
                        <div class="summary-title">No further action is needed from you right now.</div>
                        <div class="steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div><strong>We confirm the account details</strong><span>We match the payment information with your Coherta agreement.</span></div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div><strong>We prepare onboarding</strong><span>Your contact person reviews the agreed setup and the first operational steps.</span></div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div><strong>We contact you directly</strong><span>You will hear from Coherta with the next step for access, setup, and onboarding.</span></div>
                            </div>
                        </div>
                        <?php if ($reference !== '') { ?>
                            <div class="reference">Stripe reference<br><code><?php echo htmlspecialchars($reference, ENT_QUOTES, 'UTF-8'); ?></code></div>
                        <?php } ?>
                    </aside>
                </section>
            </main>

            <aside class="contact-card" aria-label="Your Coherta contact person">
                <div class="contact-label">Your contact person</div>
                <div class="contact-person">
                    <img src="https://api.coherta.com/images/img_68ed7e19c4ed8.jpg" alt="Valdemar Bilecka">
                    <div>
                        <div class="contact-name">Valdemar Bilecka</div>
                        <div class="contact-role">Co-founder</div>
                    </div>
                </div>
                <div class="contact-links">
                    <a href="mailto:valdemar@coherta.com">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                        valdemar@coherta.com
                    </a>
                    <a href="tel:+4570505666">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.89.32 1.76.59 2.6a2 2 0 0 1-.45 2.11L8 9.7a16 16 0 0 0 6.3 6.3l1.27-1.27a2 2 0 0 1 2.11-.45c.84.27 1.71.47 2.6.59A2 2 0 0 1 22 16.92Z"></path></svg>
                        +45 70 50 56 66
                    </a>
                    <a href="https://www.linkedin.com/in/valdemarbilecka" target="_blank" rel="noreferrer">
                        <span style="width:18px;height:18px;display:grid;place-items:center;border-radius:4px;background:#0a66c2;color:#fff;font-size:11px;font-weight:800;">in</span>
                        linkedin.com/in/valdemarbilecka
                    </a>
                </div>
                <p class="contact-note">Questions about the agreement or onboarding? Write or call directly, and we will help you move forward.</p>
            </aside>
        </div>

        <div class="footer">Coherta ApS</div>
    </div>
</div>
</body>
</html>
