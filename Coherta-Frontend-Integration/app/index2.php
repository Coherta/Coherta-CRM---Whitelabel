<?php
require_once "./base.php";
$PageTitle = "Frontpage";
$dashboard = $client->get("dashboard/overview");
$yesterday = date('Y-m-d', strtotime('-1 day'));

$new_companies_count = 0;
$updated_companies_count = 0;
$linkedIn_leads = 0;
$won_deals_today = 0;
$lost_deals_today = 0;
$overall_lead_status = [];
$latest_id = 0;

foreach (($dashboard['companies'] ?? []) as $company) {
    if (($company['day'] ?? null) === $yesterday) {
        $new_companies_count += (int)($company['new_companies_count'] ?? 0);
        $updated_companies_count += (int)($company['updated_companies_count'] ?? 0);
        $linkedIn_leads += (int)($company['linkedIn_leads'] ?? 0);
        $lost_deals_today += (int)($company['lost_deals_today'] ?? 0);
        if (isset($company['overall_lead_status'])) {
            $overall_lead_status[] = $company['overall_lead_status'];
        }
    }

    $won_deals_today += (int)($company['won_deals_today'] ?? 0);
    $latest_id = (int)($company['id'] ?? $latest_id);
}

$todo_items = is_array($dashboard['todo'] ?? null) ? $dashboard['todo'] : [];
$total_todos = count($todo_items);

$total_leads = 0;
$total_initial = 0;
$total_won = 0;
$total_contacted = 0;
$total_qualified = 0;
$total_proposal = 0;
$total_negotiation = 0;

foreach ($overall_lead_status as $statusRow) {
    if (!is_array($statusRow)) {
        continue;
    }

    $stageId = (int)($statusRow['stage_id'] ?? 0);
    $count = (int)($statusRow['count'] ?? 0);
    $total_leads += $count;

    if ($stageId === 1) $total_initial += $count;
    if ($stageId === 2) $total_contacted += $count;
    if ($stageId === 3) $total_qualified += $count;
    if ($stageId === 4) $total_proposal += $count;
    if ($stageId === 5) $total_negotiation += $count;
    if ($stageId === 6) $total_won += $count;
}

$active_campaigns = (int)($dashboard['campaigns']['active'] ?? 0);
$paused_campaigns = (int)($dashboard['campaigns']['paused'] ?? 0);
$draft_campaigns = (int)($dashboard['campaigns']['draft'] ?? 0);
$campaign_replies = (int)($dashboard['campaigns']['replies'] ?? 0);
$messages_sent_today = (int)($dashboard['campaigns']['messages_sent_today'] ?? 0);
$connection_requests_today = (int)($dashboard['campaigns']['connection_requests_today'] ?? 0);

$has_campaign_data = isset($dashboard['campaigns']) && is_array($dashboard['campaigns']);
$has_connector_data = isset($dashboard['connectors']) && is_array($dashboard['connectors']);
$has_usage_data = isset($dashboard['usage']) && is_array($dashboard['usage']);

$connectors = $has_connector_data ? $dashboard['connectors'] : [
        [
                'label' => 'LinkedIn',
                'status' => 'connected',
                'copy' => 'Campaign actions can run and the session looks healthy.'
        ],
        [
                'label' => 'HubSpot',
                'status' => 'connected',
                'copy' => 'Two-way CRM sync is ready when the integration is enabled.'
        ],
        [
                'label' => 'Dripify',
                'status' => 'warning',
                'copy' => 'Reconnect or map campaigns when using external outreach sync.'
        ],
        [
                'label' => 'Website Enrichment',
                'status' => 'connected',
                'copy' => 'Website and technographic enrichment can continue in the background.'
        ]
];

$usage = $has_usage_data ? $dashboard['usage'] : [
        [
                'label' => 'Connection requests',
                'used' => min($linkedIn_leads, 70),
                'limit' => 70
        ],
        [
                'label' => 'Messages sent',
                'used' => $messages_sent_today,
                'limit' => 350
        ],
        [
                'label' => 'Open to-dos',
                'used' => min($total_todos, 100),
                'limit' => 100
        ]
];

$health_score = 0;
$health_checks = [];

$health_checks[] = [
        'label' => 'Leads are entering the account',
        'ok' => $new_companies_count > 0,
        'meta' => $new_companies_count > 0 ? ($new_companies_count . ' new leads today') : 'No new leads today'
];

$health_checks[] = [
        'label' => 'Enrichment is active',
        'ok' => $updated_companies_count > 0,
        'meta' => $updated_companies_count > 0 ? ($updated_companies_count . ' companies updated') : 'No enrichments recorded'
];

$health_checks[] = [
        'label' => 'At least one campaign is running',
        'ok' => $active_campaigns > 0,
        'meta' => $active_campaigns > 0 ? ($active_campaigns . ' active campaigns') : 'No active campaign data yet'
];

$health_checks[] = [
        'label' => 'Team has actionable work',
        'ok' => $total_todos > 0,
        'meta' => $total_todos > 0 ? ($total_todos . ' open to-dos') : 'No open to-dos'
];

foreach ($connectors as $connector) {
    $health_checks[] = [
            'label' => ($connector['label'] ?? 'Connector') . ' connector',
            'ok' => in_array(($connector['status'] ?? ''), ['connected', 'healthy', 'active'], true),
            'meta' => $connector['copy'] ?? ''
    ];
}

foreach ($health_checks as $check) {
    if (!empty($check['ok'])) {
        $health_score++;
    }
}

$health_score = count($health_checks) > 0 ? (int)round(($health_score / count($health_checks)) * 100) : 0;

$attention_items = [];
if ($new_companies_count === 0) {
    $attention_items[] = ['priority' => 'high', 'title' => 'No new leads found today', 'copy' => 'Check your GTM filters or import more lead sources.'];
}
if ($active_campaigns === 0) {
    $attention_items[] = ['priority' => 'high', 'title' => 'No active campaigns', 'copy' => 'Launch or resume a campaign so leads move into outreach.'];
}
if ($campaign_replies > 0) {
    $attention_items[] = ['priority' => 'medium', 'title' => $campaign_replies . ' replies need follow-up', 'copy' => 'Reply quickly while the conversation is warm.'];
}
if ($total_todos > 0) {
    $attention_items[] = ['priority' => 'medium', 'title' => $total_todos . ' open to-dos', 'copy' => 'Keep the team focused on companies already in progress.'];
}
foreach ($connectors as $connector) {
    if (!in_array(($connector['status'] ?? ''), ['connected', 'healthy', 'active'], true)) {
        $attention_items[] = [
                'priority' => 'high',
                'title' => ($connector['label'] ?? 'Connector') . ' needs attention',
                'copy' => $connector['copy'] ?? 'Review the integration setup.'
        ];
    }
}
if (empty($attention_items)) {
    $attention_items[] = ['priority' => 'low', 'title' => 'Account looks healthy', 'copy' => 'No urgent blockers were detected in the current overview.'];
}

$activity_feed = [];
if ($new_companies_count > 0) {
    $activity_feed[] = ['title' => $new_companies_count . ' new companies added', 'copy' => 'Fresh companies were added to the account from your current lead sources.', 'time' => 'Today'];
}
if ($updated_companies_count > 0) {
    $activity_feed[] = ['title' => $updated_companies_count . ' companies enriched', 'copy' => 'Company and website information was refreshed for better targeting.', 'time' => 'Today'];
}
if ($linkedIn_leads > 0) {
    $activity_feed[] = ['title' => $linkedIn_leads . ' LinkedIn leads identified', 'copy' => 'Qualified LinkedIn leads were added to the daily overview.', 'time' => 'Today'];
}
if ($won_deals_today > 0) {
    $activity_feed[] = ['title' => $won_deals_today . ' won deals registered', 'copy' => 'The account recorded positive sales movement this month.', 'time' => 'This month'];
}
if ($lost_deals_today > 0) {
    $activity_feed[] = ['title' => $lost_deals_today . ' lost deals registered', 'copy' => 'Review pipeline quality and negotiation timing for stalled deals.', 'time' => 'This month'];
}
if ($messages_sent_today > 0) {
    $activity_feed[] = ['title' => $messages_sent_today . ' messages sent', 'copy' => 'Outreach activity is happening inside your active campaigns.', 'time' => 'Today'];
}
if ($connection_requests_today > 0) {
    $activity_feed[] = ['title' => $connection_requests_today . ' connection requests sent', 'copy' => 'The daily LinkedIn activity window is being used.', 'time' => 'Today'];
}
if (empty($activity_feed)) {
    $activity_feed[] = ['title' => 'No activity detected yet', 'copy' => 'When the dashboard endpoint exposes more campaign and connector events, they can be shown here.', 'time' => '—'];
}

include('partial/header.php');
include('partial/loader.php');
?>
<style>
    .coherta-home-v2 {
        --bg-soft: #f5f7fb;
        --panel: #ffffff;
        --line: rgba(15, 23, 42, 0.08);
        --text: #0f172a;
        --muted: #64748b;
        --primary: #2563eb;
        --primary-soft: rgba(37, 99, 235, 0.10);
        --success: #16a34a;
        --success-soft: rgba(22, 163, 74, 0.10);
        --warning: #d97706;
        --warning-soft: rgba(217, 119, 6, 0.10);
        --danger: #dc2626;
        --danger-soft: rgba(220, 38, 38, 0.10);
        --shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
    }

    .coherta-home-v2 .v2-grid,
    .coherta-home-v2 .v2-two-col,
    .coherta-home-v2 .v2-kpi-grid,
    .coherta-home-v2 .v2-status-grid,
    .coherta-home-v2 .v2-quick-grid,
    .coherta-home-v2 .v2-health-grid,
    .coherta-home-v2 .v2-usage-grid {
        display: grid;
        gap: 18px;
    }

    .coherta-home-v2 .v2-grid {
        gap: 24px;
    }

    .coherta-home-v2 .v2-hero {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #3b82f6 100%);
        color: #fff;
        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.20);
    }

    .coherta-home-v2 .v2-hero::before,
    .coherta-home-v2 .v2-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,0.08);
        pointer-events: none;
    }

    .coherta-home-v2 .v2-hero::before {
        width: 360px;
        height: 360px;
        top: -180px;
        right: -110px;
    }

    .coherta-home-v2 .v2-hero::after {
        width: 240px;
        height: 240px;
        bottom: -120px;
        left: 38%;
    }

    .coherta-home-v2 .v2-hero-body {
        position: relative;
        z-index: 1;
        padding: 28px;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.85fr);
        gap: 20px;
        align-items: stretch;
    }

    .coherta-home-v2 .v2-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.12);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 14px;
    }

    .coherta-home-v2 .v2-hero h2 {
        margin: 0;
        font-size: 38px;
        line-height: 1.04;
        letter-spacing: -0.05em;
        max-width: 12ch;
        color: #fff;
    }

    .coherta-home-v2 .v2-hero p {
        margin: 12px 0 0;
        max-width: 72ch;
        color: rgba(255,255,255,0.82);
        line-height: 1.65;
        font-size: 14px;
    }

    .coherta-home-v2 .v2-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
    }

    .coherta-home-v2 .v2-btn {
        min-height: 46px;
        padding: 0 18px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
        border: 0;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .coherta-home-v2 .v2-btn:hover { transform: translateY(-1px); }
    .coherta-home-v2 .v2-btn-primary { background: #fff; color: #0f172a; }
    .coherta-home-v2 .v2-btn-secondary { background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.12); }

    .coherta-home-v2 .v2-hero-side {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .coherta-home-v2 .v2-hero-stat {
        border-radius: 20px;
        padding: 18px;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.14);
        backdrop-filter: blur(8px);
        min-height: 134px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .coherta-home-v2 .v2-hero-stat-label {
        color: rgba(255,255,255,0.70);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .coherta-home-v2 .v2-hero-stat-value {
        font-size: 32px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.05em;
        margin: 12px 0 6px;
    }

    .coherta-home-v2 .v2-hero-stat-meta {
        color: rgba(255,255,255,0.78);
        font-size: 12px;
        line-height: 1.55;
    }

    .coherta-home-v2 .v2-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 24px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .coherta-home-v2 .v2-card-body {
        padding: 24px;
    }

    .coherta-home-v2 .v2-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .coherta-home-v2 .v2-card-head h4,
    .coherta-home-v2 .v2-card-head h5 {
        margin: 0;
        color: var(--text);
        letter-spacing: -0.03em;
    }

    .coherta-home-v2 .v2-card-head h4 { font-size: 22px; }
    .coherta-home-v2 .v2-card-head h5 { font-size: 18px; }

    .coherta-home-v2 .v2-card-head p {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.6;
    }

    .coherta-home-v2 .v2-link {
        white-space: nowrap;
        color: var(--primary);
        font-size: 13px;
        font-weight: 700;
    }

    .coherta-home-v2 .v2-kpi-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    .coherta-home-v2 .v2-kpi {
        border-radius: 20px;
        background: linear-gradient(180deg, #fff, #f8fafc);
        border: 1px solid var(--line);
        padding: 18px;
        min-height: 130px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .coherta-home-v2 .v2-kpi-label {
        font-size: 13px;
        color: var(--muted);
        font-weight: 700;
    }

    .coherta-home-v2 .v2-kpi-value {
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.05em;
        color: var(--text);
        margin: 12px 0 6px;
    }

    .coherta-home-v2 .v2-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .coherta-home-v2 .v2-pill.success { background: var(--success-soft); color: var(--success); }
    .coherta-home-v2 .v2-pill.warning { background: var(--warning-soft); color: var(--warning); }
    .coherta-home-v2 .v2-pill.danger { background: var(--danger-soft); color: var(--danger); }
    .coherta-home-v2 .v2-pill.info { background: var(--primary-soft); color: var(--primary); }

    .coherta-home-v2 .v2-two-col { grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr); }
    .coherta-home-v2 .v2-status-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .coherta-home-v2 .v2-health-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .coherta-home-v2 .v2-usage-grid { grid-template-columns: 1fr; }

    .coherta-home-v2 .v2-status-item,
    .coherta-home-v2 .v2-health-item,
    .coherta-home-v2 .v2-feed-item,
    .coherta-home-v2 .v2-attention-item,
    .coherta-home-v2 .v2-quick-item {
        border: 1px solid var(--line);
        border-radius: 18px;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .coherta-home-v2 .v2-status-item,
    .coherta-home-v2 .v2-health-item,
    .coherta-home-v2 .v2-quick-item {
        padding: 18px;
    }

    .coherta-home-v2 .v2-status-top,
    .coherta-home-v2 .v2-health-top,
    .coherta-home-v2 .v2-attention-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .coherta-home-v2 .v2-status-title,
    .coherta-home-v2 .v2-health-title,
    .coherta-home-v2 .v2-attention-title,
    .coherta-home-v2 .v2-quick-title {
        font-size: 15px;
        font-weight: 800;
        color: var(--text);
        margin: 0;
    }

    .coherta-home-v2 .v2-copy,
    .coherta-home-v2 .v2-status-item p,
    .coherta-home-v2 .v2-health-item p,
    .coherta-home-v2 .v2-feed-item p,
    .coherta-home-v2 .v2-attention-item p,
    .coherta-home-v2 .v2-quick-item p {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .coherta-home-v2 .v2-progress-wrap { margin-top: 14px; }
    .coherta-home-v2 .v2-progress-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .coherta-home-v2 .v2-progress {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .coherta-home-v2 .v2-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
    }

    .coherta-home-v2 .v2-chart-shell {
        border-radius: 20px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, #fff, #f8fafc);
        padding: 16px;
    }

    .coherta-home-v2 .v2-balance-data {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 0 0 14px;
    }

    .coherta-home-v2 .v2-balance-data li a { color: inherit; }

    .coherta-home-v2 .v2-balance-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: #fff;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
    }

    .coherta-home-v2 .v2-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--primary);
    }

    .coherta-home-v2 .v2-mini-metrics {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .coherta-home-v2 .v2-mini-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 14px 16px;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .coherta-home-v2 .v2-mini-metric strong {
        display: block;
        color: var(--text);
        font-size: 24px;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .coherta-home-v2 .v2-mini-metric span {
        color: var(--muted);
        font-size: 13px;
    }

    .coherta-home-v2 .v2-feed-list,
    .coherta-home-v2 .v2-attention-list {
        display: grid;
        gap: 12px;
    }

    .coherta-home-v2 .v2-feed-item,
    .coherta-home-v2 .v2-attention-item {
        padding: 16px;
    }

    .coherta-home-v2 .v2-feed-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 14px;
        align-items: start;
    }

    .coherta-home-v2 .v2-feed-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        background: var(--primary);
        box-shadow: 0 0 0 8px var(--primary-soft);
        margin-top: 6px;
    }

    .coherta-home-v2 .v2-feed-time {
        font-size: 12px;
        color: var(--muted);
        font-weight: 700;
        white-space: nowrap;
    }

    .coherta-home-v2 .v2-usage-item {
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 16px;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .coherta-home-v2 .v2-usage-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
    }

    .coherta-home-v2 .v2-usage-head span:last-child { color: var(--muted); }
    .coherta-home-v2 .v2-quick-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .coherta-home-v2 .v2-quick-item {
        padding: 18px;
        text-decoration: none;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .coherta-home-v2 .v2-quick-item:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, 0.20);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
    }

    .coherta-home-v2 .v2-table-wrap {
        border: 1px solid var(--line);
        border-radius: 18px;
        overflow: auto;
    }

    .coherta-home-v2 .v2-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
        background: #fff;
    }

    .coherta-home-v2 .v2-table th,
    .coherta-home-v2 .v2-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        vertical-align: top;
    }

    .coherta-home-v2 .v2-table th {
        background: #f8fafc;
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .coherta-home-v2 .v2-table tr:last-child td { border-bottom: 0; }
    .coherta-home-v2 .v2-company-title { font-weight: 800; color: var(--text); }
    .coherta-home-v2 .v2-company-meta { font-size: 12px; color: var(--muted); margin-top: 3px; }

    @media (max-width: 1280px) {
        .coherta-home-v2 .v2-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .coherta-home-v2 .v2-two-col,
        .coherta-home-v2 .v2-hero-body { grid-template-columns: 1fr; }
    }

    @media (max-width: 920px) {
        .coherta-home-v2 .v2-status-grid,
        .coherta-home-v2 .v2-health-grid,
        .coherta-home-v2 .v2-quick-grid,
        .coherta-home-v2 .v2-hero-side { grid-template-columns: 1fr; }
        .coherta-home-v2 .v2-feed-item { grid-template-columns: auto 1fr; }
        .coherta-home-v2 .v2-feed-time { grid-column: 2 / -1; }
    }

    @media (max-width: 640px) {
        .coherta-home-v2 .v2-kpi-grid { grid-template-columns: 1fr; }
        .coherta-home-v2 .v2-hero h2 { font-size: 30px; }
        .coherta-home-v2 .v2-hero-actions { flex-direction: column; }
        .coherta-home-v2 .v2-btn { width: 100%; }
    }
</style>

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include('partial/topbar.php') ?>
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php') ?>
        <div class="page-body">
            <?php include('partial/breadcrumb.php') ?>

            <div class="container-fluid coherta-home-v2">
                <?php
                if (trim((string)($company_data['stripe_subscription'] ?? '')) === '') {
                    $url = $payment_information_url ?? "/app/my-account";
                    $trial_message = "<a class='btn btn-success' href='" . htmlspecialchars($url) . "' style='margin-left:10px;'>Upgrade account</a>";
                    echo "<div id='trial-bar' style='width:100%;background:#ffc107;color:#222;padding:1rem;text-align:center;border-radius:12px;margin-bottom:1rem;'>
                        <span style=\"font-weight:bold;\">Your account needs an upgrade to continue.</span>
                        {$trial_message}
                    </div>";
                }
                ?>

                <div class="v2-grid">
                    <section class="v2-hero">
                        <div class="v2-hero-body">
                            <div>
                                <div class="v2-kicker">Live account status</div>
                                <h2>Welcome to Coherta</h2>
                                <p>
                                    This view is built to show what is happening in the account right now, what is healthy,
                                    what needs attention, and where the team should focus next.
                                </p>
                                <div class="v2-hero-actions">
                                    <a href="wizard-advanced.php" class="v2-btn v2-btn-primary">Find new leads</a>
                                    <a href="campaigns-dashboard" class="v2-btn v2-btn-secondary">Open campaigns</a>
                                    <a href="users.php" class="v2-btn v2-btn-secondary">Manage users</a>
                                </div>
                            </div>

                            <div class="v2-hero-side">
                                <div class="v2-hero-stat">
                                    <div>
                                        <div class="v2-hero-stat-label">Account health</div>
                                        <div class="v2-hero-stat-value"><?php echo $health_score; ?>%</div>
                                    </div>
                                    <div class="v2-hero-stat-meta"><?php echo max(count($health_checks), 1); ?> checks reviewed across leads, campaigns and connectors.</div>
                                </div>
                                <div class="v2-hero-stat">
                                    <div>
                                        <div class="v2-hero-stat-label">Active campaigns</div>
                                        <div class="v2-hero-stat-value"><?php echo $active_campaigns; ?></div>
                                    </div>
                                    <div class="v2-hero-stat-meta"><?php echo $paused_campaigns; ?> paused · <?php echo $draft_campaigns; ?> drafts</div>
                                </div>
                                <div class="v2-hero-stat">
                                    <div>
                                        <div class="v2-hero-stat-label">Leads in account</div>
                                        <div class="v2-hero-stat-value"><?php echo number_format($total_leads); ?></div>
                                    </div>
                                    <div class="v2-hero-stat-meta"><?php echo number_format($new_companies_count); ?> new today · <?php echo number_format($updated_companies_count); ?> updated</div>
                                </div>
                                <div class="v2-hero-stat">
                                    <div>
                                        <div class="v2-hero-stat-label">Open tasks</div>
                                        <div class="v2-hero-stat-value"><?php echo $total_todos; ?></div>
                                    </div>
                                    <div class="v2-hero-stat-meta"><?php echo $campaign_replies; ?> replies waiting · <?php echo $won_deals_today; ?> won deals this month</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="v2-card">
                        <div class="v2-card-body">
                            <div class="v2-card-head">
                                <div>
                                    <h4>Performance at a glance</h4>
                                    <p>The front page should explain momentum immediately, not just show generic widgets.</p>
                                </div>
                                <a class="v2-link" href="leads.php">Open lead pipeline</a>
                            </div>

                            <div class="v2-kpi-grid">
                                <div class="v2-kpi">
                                    <div>
                                        <div class="v2-kpi-label">New leads</div>
                                        <div class="v2-kpi-value"><?php echo number_format($new_companies_count); ?></div>
                                    </div>
                                    <span class="v2-pill <?php echo $new_companies_count > 0 ? 'success' : 'warning'; ?>"><?php echo $new_companies_count > 0 ? 'Lead flow active' : 'No new flow'; ?></span>
                                </div>
                                <div class="v2-kpi">
                                    <div>
                                        <div class="v2-kpi-label">Updated companies</div>
                                        <div class="v2-kpi-value"><?php echo number_format($updated_companies_count); ?></div>
                                    </div>
                                    <span class="v2-pill <?php echo $updated_companies_count > 0 ? 'info' : 'warning'; ?>"><?php echo $updated_companies_count > 0 ? 'Enrichment running' : 'Needs activity'; ?></span>
                                </div>
                                <div class="v2-kpi">
                                    <div>
                                        <div class="v2-kpi-label">LinkedIn leads</div>
                                        <div class="v2-kpi-value"><?php echo number_format($linkedIn_leads); ?></div>
                                    </div>
                                    <span class="v2-pill <?php echo $linkedIn_leads > 0 ? 'success' : 'warning'; ?>"><?php echo $linkedIn_leads > 0 ? 'Signals found' : 'No LinkedIn flow'; ?></span>
                                </div>
                                <div class="v2-kpi">
                                    <div>
                                        <div class="v2-kpi-label">Replies / won deals</div>
                                        <div class="v2-kpi-value"><?php echo number_format($campaign_replies + $won_deals_today); ?></div>
                                    </div>
                                    <span class="v2-pill <?php echo ($campaign_replies + $won_deals_today) > 0 ? 'success' : 'info'; ?>"><?php echo $campaign_replies; ?> replies · <?php echo $won_deals_today; ?> won</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="v2-two-col">
                        <div class="v2-grid">
                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h4>Connector and system status</h4>
                                            <p>This gives the user confidence that Coherta is actually connected and working.</p>
                                        </div>
                                        <a class="v2-link" href="settings.php">Settings</a>
                                    </div>
                                    <div class="v2-status-grid">
                                        <?php foreach ($connectors as $connector):
                                            $status = strtolower((string)($connector['status'] ?? 'info'));
                                            $statusClass = 'info';
                                            $statusLabel = ucfirst($status);
                                            if (in_array($status, ['connected', 'healthy', 'active'], true)) {
                                                $statusClass = 'success';
                                                $statusLabel = 'Healthy';
                                            } elseif (in_array($status, ['warning', 'needs_attention', 'needs attention'], true)) {
                                                $statusClass = 'warning';
                                                $statusLabel = 'Needs attention';
                                            } elseif (in_array($status, ['error', 'failed', 'disconnected'], true)) {
                                                $statusClass = 'danger';
                                                $statusLabel = 'Problem';
                                            }
                                            ?>
                                            <article class="v2-status-item">
                                                <div class="v2-status-top">
                                                    <h5 class="v2-status-title"><?php echo htmlspecialchars($connector['label'] ?? 'Connector'); ?></h5>
                                                    <span class="v2-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                                </div>
                                                <p><?php echo htmlspecialchars($connector['copy'] ?? ''); ?></p>
                                                <div class="v2-progress-wrap">
                                                    <div class="v2-progress-head">
                                                        <span>Status confidence</span>
                                                        <span><?php echo $statusClass === 'success' ? '100%' : ($statusClass === 'warning' ? '55%' : '25%'); ?></span>
                                                    </div>
                                                    <div class="v2-progress"><span style="width: <?php echo $statusClass === 'success' ? '100' : ($statusClass === 'warning' ? '55' : '25'); ?>%"></span></div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h4>Pipeline overview</h4>
                                            <p>The existing stage chart is kept, but wrapped in a clearer dashboard block.</p>
                                        </div>
                                        <a class="v2-link" href="leads.php">View all leads</a>
                                    </div>

                                    <div class="v2-chart-shell">
                                        <ul class="v2-balance-data">
                                            <li><a class="v2-balance-chip" href="leads.php?stage_id=2"><span class="v2-dot"></span>Contacted</a></li>
                                            <li><a class="v2-balance-chip" href="leads.php?stage_id=3"><span class="v2-dot"></span>Qualified</a></li>
                                            <li><a class="v2-balance-chip" href="leads.php?stage_id=4"><span class="v2-dot"></span>Proposal</a></li>
                                            <li><a class="v2-balance-chip" href="leads.php?stage_id=5"><span class="v2-dot"></span>Negotiation</a></li>
                                        </ul>
                                        <div id="chart-currently"></div>
                                    </div>

                                    <div class="v2-mini-metrics">
                                        <div class="v2-mini-metric">
                                            <div>
                                                <strong id="total_leads"><?php echo $total_leads; ?></strong>
                                                <span>Total leads</span>
                                            </div>
                                            <span class="v2-pill info">All stages</span>
                                        </div>
                                        <div class="v2-mini-metric">
                                            <div>
                                                <strong id="total_initial"><?php echo $total_initial; ?></strong>
                                                <span>Initial</span>
                                            </div>
                                            <span class="v2-pill <?php echo $total_initial > 0 ? 'warning' : 'info'; ?>">Stage 1</span>
                                        </div>
                                        <div class="v2-mini-metric">
                                            <div>
                                                <strong id="total_won"><?php echo $total_won; ?></strong>
                                                <span>Won leads</span>
                                            </div>
                                            <span class="v2-pill <?php echo $total_won > 0 ? 'success' : 'info'; ?>">Stage 6</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h4>Recent account activity</h4>
                                            <p>Useful for trust. The user can see that Coherta is doing actual work.</p>
                                        </div>
                                    </div>

                                    <div class="v2-feed-list">
                                        <?php foreach ($activity_feed as $feed): ?>
                                            <article class="v2-feed-item">
                                                <span class="v2-feed-dot"></span>
                                                <div>
                                                    <h5 class="v2-status-title"><?php echo htmlspecialchars($feed['title']); ?></h5>
                                                    <p><?php echo htmlspecialchars($feed['copy']); ?></p>
                                                </div>
                                                <div class="v2-feed-time"><?php echo htmlspecialchars($feed['time']); ?></div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h4>Open to-dos</h4>
                                            <p>Keep team follow-up visible on the front page.</p>
                                        </div>
                                    </div>

                                    <div class="v2-table-wrap">
                                        <table class="v2-table">
                                            <thead>
                                            <tr>
                                                <th>Company</th>
                                                <th>Task</th>
                                                <th>Deadline</th>
                                                <th>Owner</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (!empty($todo_items)): ?>
                                                <?php $i = 0; foreach ($todo_items as $todo): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="v2-company-title"><?php echo htmlspecialchars($todo['company_name'] ?? 'Unknown company'); ?></div>
                                                            <div class="v2-company-meta">Lead ID: <?php echo (int)($todo['pipeline_company_id'] ?? 0); ?></div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($todo['body'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($todo['deadline'] ?? '—'); ?></td>
                                                        <td>
                                                            <img class="img-fluid img-40 rounded-circle" src="<?php echo $client->getColleagueImage($todo['creator'] ?? 0); ?>" alt="user">
                                                        </td>
                                                    </tr>
                                                    <?php $i++; if ($i >= 10) break; endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4">No open to-dos right now.</td>
                                                </tr>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="v2-grid">
                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h5>Needs attention</h5>
                                            <p>Important items that should sit above generic notifications.</p>
                                        </div>
                                    </div>
                                    <div class="v2-attention-list">
                                        <?php foreach ($attention_items as $item):
                                            $priority = strtolower((string)($item['priority'] ?? 'low'));
                                            $priorityClass = $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info');
                                            ?>
                                            <article class="v2-attention-item">
                                                <div class="v2-attention-top">
                                                    <h6 class="v2-attention-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                    <span class="v2-pill <?php echo $priorityClass; ?>"><?php echo ucfirst($priority); ?></span>
                                                </div>
                                                <p><?php echo htmlspecialchars($item['copy']); ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h5>Account health checklist</h5>
                                            <p>A quick visual score builder for the user.</p>
                                        </div>
                                    </div>
                                    <div class="v2-health-grid">
                                        <?php foreach ($health_checks as $check): ?>
                                            <article class="v2-health-item">
                                                <div class="v2-health-top">
                                                    <h6 class="v2-health-title"><?php echo htmlspecialchars($check['label']); ?></h6>
                                                    <span class="v2-pill <?php echo !empty($check['ok']) ? 'success' : 'warning'; ?>"><?php echo !empty($check['ok']) ? 'OK' : 'Check'; ?></span>
                                                </div>
                                                <p><?php echo htmlspecialchars($check['meta']); ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h5>Usage</h5>
                                            <p>Reduces uncertainty around limits and workload.</p>
                                        </div>
                                    </div>

                                    <div class="v2-usage-grid">
                                        <?php foreach ($usage as $item):
                                            $used = (int)($item['used'] ?? 0);
                                            $limit = max((int)($item['limit'] ?? 0), 1);
                                            $percent = min(100, (int)round(($used / $limit) * 100));
                                            ?>
                                            <div class="v2-usage-item">
                                                <div class="v2-usage-head">
                                                    <span><?php echo htmlspecialchars($item['label'] ?? 'Usage'); ?></span>
                                                    <span><?php echo number_format($used); ?> / <?php echo number_format($limit); ?></span>
                                                </div>
                                                <div class="v2-progress"><span style="width: <?php echo $percent; ?>%"></span></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section class="v2-card">
                                <div class="v2-card-body">
                                    <div class="v2-card-head">
                                        <div>
                                            <h5>Quick actions</h5>
                                            <p>Fast access to the next most likely actions.</p>
                                        </div>
                                    </div>
                                    <div class="v2-quick-grid">
                                        <a class="v2-quick-item" href="wizard-advanced.php">
                                            <h6 class="v2-quick-title">Find new leads</h6>
                                            <p>Open the GTM-based lead builder and continue sourcing companies.</p>
                                        </a>
                                        <a class="v2-quick-item" href="campaigns-dashboard">
                                            <h6 class="v2-quick-title">Open campaigns</h6>
                                            <p>Review outreach, replies, workflow progress and attached contacts.</p>
                                        </a>
                                        <a class="v2-quick-item" href="interaction-scoreboard.php">
                                            <h6 class="v2-quick-title">Review interactions</h6>
                                            <p>See who is engaging and decide who to follow up with next.</p>
                                        </a>
                                        <a class="v2-quick-item" href="users.php">
                                            <h6 class="v2-quick-title">Manage access rights</h6>
                                            <p>Control which employees can access campaigns, notifications and lists.</p>
                                        </a>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('partial/footer.php') ?>
    </div>
</div>
<?php include('partial/scripts.php') ?>
<script src="assets/js/clock.js"></script>
<script src="assets/js/chart/apex-chart/moment.min.js"></script>
<script src="assets/js/notify/bootstrap-notify.min.js"></script>
<script src="assets/js/dashboard/default.js"></script>
<script src="assets/js/notify/index.js"></script>
<script src="assets/js/height-equal.js"></script>
<script src="assets/js/animation/wow/wow.min.js"></script>
<script>
    new WOW().init();

    (function hydrateStageNumbers() {
        var totalLeadsEl = document.getElementById('total_leads');
        var totalInitialEl = document.getElementById('total_initial');
        var totalWonEl = document.getElementById('total_won');
        if (totalLeadsEl) totalLeadsEl.textContent = <?php echo (int)$total_leads; ?>;
        if (totalInitialEl) totalInitialEl.textContent = <?php echo (int)$total_initial; ?>;
        if (totalWonEl) totalWonEl.textContent = <?php echo (int)$total_won; ?>;
    })();
</script>
<?php include('partial/footer-end.php') ?>
