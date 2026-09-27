<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "./base.php";
$sidebar_close = true;
$PageTitle = "Outbound Phone";
$list_name = "Lead list";
include('partial/header.php');

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
$posts = $client->get("pipeline/leads/outreach");
$leadLists = [];
foreach ($client->getCurrentPipelines() as $lead) {
    $leadLists[] = [
            "id" => $lead['id'],
            "name" => $lead['title'],
            'pipeline_id' => $lead['id'],
    ];
}

$lead = null;
$latestNote = '';
$latestNoteTime = '';
$oldestTask = null;
$vat_code = '';
$company_name = '';
$company_website = '';
$company_address = '';
$company_postal = '';
$company_city = '';
$company_phone = '';
$google_maps_phone = '';
$key_contact_name = '';
$key_contact_title = '';
$key_contact_phone = '';
$key_employee_linkedin = '';

if (isset($_GET['pipeline_id'])) {
    $leadId = $_GET['lead'] ?? '';
    $posts = $client->get("pipeline/leads/outreach?task_next=1&pipeline_id=" . urlencode($_GET['pipeline_id']) . "&stage=" . urlencode($_GET['state'] ?? ''));
    $lead = $posts[0] ?? null;

    $vat_code = $lead['vat_code'] ?? '';
    $company_name = $lead['company_name'] ?? '';
    $company_website = $lead['company_website'] ?? '';
    $company_address = $lead['company_address'] ?? '';
    $company_postal = $lead['company_postal'] ?? '';
    $company_city = $lead['company_city'] ?? '';
    $company_phone = $lead['company_phone'] ?? '';
    $google_maps_phone = $lead['google_maps_phone'] ?? '';
    $key_contact_name = $lead['key_employee']['employee']['contact_name'] ?? '';
    $key_contact_title = $lead['key_employee']['employee']['title'] ?? '';
    $key_contact_phone = $lead['key_employee']['employee']['phone'] ?? '';
    $key_employee_linkedin = $lead['key_employee']['employee']['linkedin'] ?? '';

    if ($key_contact_phone == "" || $key_contact_phone == null) {
        $key_contact_phone = $company_phone;
    }

    if ($key_contact_phone == "" || $key_contact_phone == null) {
        $key_contact_phone = $google_maps_phone;
    }

    if (!empty($lead['id'])) {
        $notes = $client->get("pipeline/leads/notes?id=" . urlencode($lead['id']));
        $latestNote = $notes[0]['note'] ?? '';
        $latestNoteTime = $notes[0]['created'] ?? '';

        $tasks = $client->get("pipeline/leads/todo/" . urlencode($lead['id']));
        foreach ($tasks as $task) {
            if (($task['completed_time'] ?? null) == null) {
                $oldestTask = $task['deadline'] ?? null;
                break;
            }
        }
    }
}

$states = [
        "1" => "Initial",
        "2" => "Contacted",
        "3" => "Qualified",
        "4" => "Proposal",
        "5" => "Negotiation"
];
$selectedState = isset($_GET['state']) ? (int)$_GET['state'] : 1;
$currentStateLabel = $states[(string)$selectedState] ?? 'Unknown';
$hasLead = !empty($lead);
$leadIdValue = $lead['id'] ?? '';
$companyUrlDisplay = $company_website ?: 'No website found';
$contactNameDisplay = $key_contact_name ?: 'No key contact found';
$contactTitleDisplay = $key_contact_title ?: 'No title found';
$contactPhoneDisplay = $key_contact_phone ?: 'No phone found';
$companyPhoneDisplay = $company_phone ?: ($google_maps_phone ?: 'No company phone found');
?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body no-top-margin">
            <div class="app-shell">
                <main class="content">
                    <style>
                        :root {
                            --bg: #f5f7fb;
                            --panel: #ffffff;
                            --panel-soft: #f8fbff;
                            --line: #e7ebf3;
                            --line-strong: #d7deeb;
                            --text: #101828;
                            --muted: #667085;
                            --muted-2: #98a2b3;
                            --primary: #4f46e5;
                            --primary-2: #7c3aed;
                            --primary-soft: rgba(79, 70, 229, .08);
                            --success: #12b76a;
                            --danger: #f04438;
                            --warning: #f79009;
                            --shadow-lg: 0 20px 50px rgba(15, 23, 42, .08);
                            --shadow-md: 0 10px 30px rgba(15, 23, 42, .06);
                            --radius-xl: 28px;
                            --radius-lg: 22px;
                            --radius-md: 16px;
                            --radius-sm: 12px;
                        }

                        * { box-sizing: border-box; }

                        body {
                            margin: 0;
                            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                            background:
                                    radial-gradient(circle at top right, rgba(124, 58, 237, .10), transparent 24%),
                                    linear-gradient(180deg, #f8faff 0%, var(--bg) 100%);
                            color: var(--text);
                        }

                        .phone-page {
                            min-width: 0;
                            max-width: 1380px;
                            margin: 0 auto;
                            padding: 24px;
                        }

                        .phone-topbar,
                        .phone-panel {
                            border-radius: var(--radius-xl);
                            background: rgba(255,255,255,.88);
                            border: 1px solid rgba(255,255,255,.75);
                            box-shadow: var(--shadow-lg);
                            overflow: hidden;
                        }

                        .phone-topbar {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 16px;
                            padding: 18px 22px;
                            border: 1px solid rgba(255,255,255,.7);
                            background: rgba(255,255,255,.74);
                            backdrop-filter: blur(18px);
                            box-shadow: var(--shadow-md);
                            margin-bottom: 22px;
                        }

                        .phone-copy h1 {
                            margin: 0;
                            font-size: 28px;
                            letter-spacing: -.04em;
                        }

                        .phone-copy p {
                            margin: 4px 0 0;
                            color: var(--muted);
                            font-size: 14px;
                        }

                        .pill {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            min-height: 40px;
                            padding: 0 14px;
                            border-radius: 999px;
                            background: var(--primary-soft);
                            color: #3447c7;
                            border: 1px solid rgba(79, 70, 229, .10);
                            font-weight: 700;
                            font-size: 13px;
                            white-space: nowrap;
                        }

                        .pill.success {
                            background: rgba(18, 183, 106, .10);
                            color: #067647;
                            border-color: rgba(18, 183, 106, .16);
                        }

                        .pill.warning {
                            background: rgba(247, 144, 9, .12);
                            color: #b54708;
                            border-color: rgba(247, 144, 9, .18);
                        }

                        .panel-inner { padding: 24px; }

                        .hero {
                            position: relative;
                            overflow: hidden;
                            background:
                                    radial-gradient(circle at 0% 0%, rgba(255,255,255,.36), transparent 30%),
                                    linear-gradient(135deg, #1f2a6e 0%, #3547c8 45%, #6d48e5 100%);
                            color: #fff;
                            margin-bottom: 22px;
                        }

                        .hero::after {
                            content: '';
                            position: absolute;
                            inset: auto -50px -70px auto;
                            width: 260px;
                            height: 260px;
                            background: rgba(255,255,255,.10);
                            border-radius: 50%;
                            filter: blur(4px);
                        }

                        .hero-grid {
                            display: grid;
                            grid-template-columns: minmax(0, 1.45fr) minmax(260px, .9fr);
                            gap: 18px;
                            align-items: end;
                            position: relative;
                            z-index: 1;
                        }

                        .hero h2 {
                            margin: 0;
                            font-size: 38px;
                            line-height: 1.02;
                            letter-spacing: -.05em;
                            max-width: 12ch;
                        }

                        .hero p {
                            margin: 14px 0 0;
                            color: rgba(255,255,255,.80);
                            line-height: 1.6;
                            max-width: 60ch;
                        }

                        .hero-stats {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
                            gap: 12px;
                        }

                        .hero-stat {
                            padding: 18px;
                            border-radius: 22px;
                            background: rgba(255,255,255,.10);
                            border: 1px solid rgba(255,255,255,.14);
                            backdrop-filter: blur(14px);
                        }

                        .hero-stat strong {
                            display: block;
                            font-size: 28px;
                            line-height: 1;
                            letter-spacing: -.05em;
                            margin-bottom: 6px;
                        }

                        .hero-stat span {
                            display: block;
                            color: rgba(255,255,255,.82);
                            font-size: 13px;
                            line-height: 1.4;
                        }

                        .filters-grid,
                        .content-grid,
                        .meta-grid {
                            display: grid;
                            gap: 18px;
                        }

                        .filters-grid {
                            grid-template-columns: minmax(0, 1.1fr) minmax(0, .8fr) auto;
                            align-items: end;
                            margin-bottom: 22px;
                        }

                        .content-grid {
                            grid-template-columns: minmax(300px, .72fr) minmax(300px, .72fr) minmax(540px, 1.56fr);
                            align-items: start;
                        }

                        .meta-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }

                        .card {
                            border: 1px solid var(--line);
                            border-radius: var(--radius-lg);
                            background: linear-gradient(180deg, #fff, #f8fbff);
                            padding: 22px;
                            box-shadow: 0 8px 24px rgba(15,23,42,.04);
                        }

                        .card.soft {
                            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
                        }

                        .section-head {
                            display: flex;
                            align-items: flex-start;
                            justify-content: space-between;
                            gap: 16px;
                            margin-bottom: 18px;
                        }

                        .section-head h3,
                        .card h3 {
                            margin: 0;
                            font-size: 22px;
                            letter-spacing: -.03em;
                        }

                        .section-head p,
                        .card p.subtle {
                            margin: 6px 0 0;
                            color: var(--muted);
                            line-height: 1.6;
                            font-size: 14px;
                        }

                        .field {
                            display: grid;
                            gap: 8px;
                            margin-bottom: 16px;
                        }

                        .field:last-child { margin-bottom: 0; }

                        .label {
                            font-size: 13px;
                            font-weight: 700;
                            color: #344054;
                        }

                        .input,
                        .select,
                        .textarea,
                        .btn {
                            font: inherit;
                        }

                        .input,
                        .select,
                        .textarea {
                            width: 100%;
                            min-height: 50px;
                            padding: 12px 14px;
                            border-radius: 16px;
                            border: 1px solid var(--line);
                            background: #fff;
                            color: var(--text);
                            outline: 0;
                            transition: border-color .15s ease, box-shadow .15s ease;
                        }

                        .textarea {
                            min-height: 170px;
                            resize: vertical;
                        }

                        .input:focus,
                        .select:focus,
                        .textarea:focus {
                            border-color: rgba(79, 70, 229, .25);
                            box-shadow: 0 0 0 4px rgba(79, 70, 229, .08);
                        }

                        .btn {
                            appearance: none;
                            border: 0;
                            cursor: pointer;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            gap: 10px;
                            min-height: 50px;
                            padding: 0 18px;
                            border-radius: 16px;
                            font-weight: 700;
                            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
                            text-decoration: none;
                        }

                        .btn:hover { transform: translateY(-1px); }
                        .btn:active { transform: translateY(0); }

                        .btn-primary {
                            color: #fff;
                            background: linear-gradient(135deg, #111827, #344054);
                            box-shadow: 0 12px 24px rgba(15, 23, 42, .18);
                        }

                        .btn-success {
                            color: #fff;
                            background: linear-gradient(135deg, #12b76a, #039855);
                            box-shadow: 0 12px 24px rgba(18, 183, 106, .24);
                        }

                        .btn-danger {
                            color: #fff;
                            background: linear-gradient(135deg, #f04438, #d92d20);
                            box-shadow: 0 12px 24px rgba(240, 68, 56, .20);
                        }

                        .btn-light {
                            color: #1d2a7a;
                            background: #fff;
                            border: 1px solid rgba(79,70,229,.10);
                        }

                        .stack { display: grid; gap: 18px; }

                        .info-list { display: grid; gap: 12px; }

                        .info-item {
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 16px;
                            background: #fff;
                        }

                        .info-item__label {
                            font-size: 12px;
                            font-weight: 800;
                            letter-spacing: .05em;
                            text-transform: uppercase;
                            color: var(--muted-2);
                            margin-bottom: 6px;
                        }

                        .info-item__value {
                            color: #101828;
                            font-size: 15px;
                            line-height: 1.55;
                            font-weight: 600;
                            word-break: break-word;
                        }

                        .info-item__value a {
                            color: #3447c7;
                            text-decoration: none;
                        }

                        .info-item__value a:hover { text-decoration: underline; }

                        .note-box {
                            background: linear-gradient(180deg, #f9fbff 0%, #f5f8fd 100%);
                            border: 1px solid #e4ecf7;
                            border-radius: 18px;
                            padding: 18px;
                            color: #334155;
                            line-height: 1.7;
                            min-height: 96px;
                            white-space: pre-wrap;
                        }

                        .notes-layout {
                            display: grid;
                            grid-template-columns: minmax(220px, .88fr) minmax(0, 1.12fr);
                            gap: 18px;
                            align-items: start;
                        }

                        .notes-meta {
                            display: grid;
                            gap: 12px;
                        }

                        .notes-editor {
                            display: grid;
                            gap: 12px;
                            align-content: start;
                        }

                        .notes-editor .field {
                            margin-bottom: 0;
                        }

                        .notes-editor .field:first-child {
                            margin-top: 0;
                        }

                        .notes-editor-card {
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            padding: 16px;
                            background: #fff;
                        }

                        .notes-editor-card .label {
                            display: block;
                            margin-bottom: 10px;
                        }

                        .empty-box {
                            padding: 18px;
                            border: 1px dashed var(--line-strong);
                            border-radius: 18px;
                            background: #fbfcfe;
                            color: var(--muted);
                        }

                        .action-row {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 12px;
                            margin-top: 18px;
                        }

                        .footer-note {
                            margin-top: 22px;
                            text-align: center;
                            color: var(--muted);
                            font-size: 13px;
                        }

                        @media (max-width: 1220px) {
                            .content-grid {
                                grid-template-columns: 1fr 1fr;
                            }

                            .content-grid > :last-child {
                                grid-column: 1 / -1;
                            }
                        }

                        @media (max-width: 980px) {
                            .hero-grid,
                            .filters-grid,
                            .content-grid,
                            .meta-grid,
                            .hero-stats,
                            .notes-layout {
                                grid-template-columns: 1fr;
                            }

                            .phone-topbar {
                                flex-direction: column;
                                align-items: flex-start;
                            }

                            .btn { width: 100%; }
                        }

                        @media (max-width: 767px) {
                            .phone-page { padding: 16px; }
                            .phone-topbar, .panel-inner { padding: 18px; }
                            .hero h2 { font-size: 32px; }
                            .card { padding: 18px; }
                            .section-head { flex-direction: column; }
                        }
                    </style>

                    <div class="app-shell">
                        <main class="phone-page">
                            <div class="phone-topbar">
                                <div class="phone-copy">
                                    <?php if ($hasLead && $company_name): ?>
                                        <h1><?php echo h($company_name); ?></h1>
                                        <p>Company overview, call context and follow-up for the next lead in queue.</p>
                                    <?php else: ?>
                                        <h1>Outbound Phone</h1>
                                        <p>Call queue, company context and follow-up in one focused workspace.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="pill"><?php echo h($currentStateLabel); ?> state</div>
                            </div>
                            <?php
                            if(!isset($_GET['pipeline_id'])) {
                                ?>
                                <section class="phone-panel" style="margin-bottom:22px;">
                                    <div class="panel-inner">
                                        <div class="section-head">
                                            <div>
                                                <h3>Call queue setup</h3>
                                                <p>Choose which list and stage the next call should come from.</p>
                                            </div>
                                        </div>
                                        <form class="filters-grid" method="get">
                                            <div class="card soft">
                                                <div class="field">
                                                    <label class="label" for="pipeline">Pipeline</label>
                                                    <select class="select" id="pipeline" name="pipeline_id">
                                                        <option value="">Choose pipeline</option>
                                                        <?php foreach ($leadLists as $leadList): ?>
                                                            <option value="<?php echo h($leadList['pipeline_id']); ?>"<?php echo (isset($_GET['pipeline_id']) && $_GET['pipeline_id'] == $leadList['pipeline_id']) ? ' selected' : ''; ?>><?php echo h($leadList['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="card soft">
                                                <div class="field">
                                                    <label class="label" for="state">Stage</label>
                                                    <select class="select" id="state" name="state">
                                                        <?php foreach ($states as $value => $label): ?>
                                                            <option value="<?php echo h($value); ?>"<?php echo ($selectedState === (int)$value) ? ' selected' : ''; ?>><?php echo h($label); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary">Load next call</button>
                                        </form>
                                    </div>
                                </section>
                                <?php
                            }
                            ?>

                            <?php if (isset($_GET['pipeline_id'])): ?>
                                <section class="phone-panel">
                                    <div class="panel-inner">
                                        <?php if (!$hasLead): ?>
                                            <div class="empty-box">No lead was returned for this pipeline and stage right now.</div>
                                        <?php else: ?>
                                            <div class="content-grid">
                                                <div class="stack">
                                                    <div class="card">
                                                        <div class="section-head">
                                                            <div>
                                                                <h3>Contact</h3>
                                                                <p class="subtle">Primary person to call right now.</p>
                                                            </div>
                                                            <div class="pill success">Ready to call</div>
                                                        </div>

                                                        <div class="info-list">
                                                            <div class="info-item">
                                                                <div class="info-item__label">Name</div>
                                                                <div class="info-item__value"><?php echo h($contactNameDisplay); ?></div>
                                                            </div>
                                                            <div class="info-item">
                                                                <div class="info-item__label">Title</div>
                                                                <div class="info-item__value"><?php echo h($contactTitleDisplay); ?></div>
                                                            </div>
                                                            <div class="info-item">
                                                                <div class="info-item__label">Direct phone</div>
                                                                <div class="info-item__value">
                                                                    <?php if ($key_contact_phone): ?>
                                                                        <a href="tel:<?php echo h($key_contact_phone); ?>"><?php echo h($key_contact_phone); ?></a>
                                                                    <?php else: ?>
                                                                        No phone found
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="info-item">
                                                                <div class="info-item__label">LinkedIn</div>
                                                                <div class="info-item__value">
                                                                    <?php if ($key_employee_linkedin): ?>
                                                                        <a href="<?php echo h($key_employee_linkedin); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($key_employee_linkedin); ?></a>
                                                                    <?php else: ?>
                                                                        No LinkedIn found
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="stack">
                                                    <div class="card">
                                                        <div class="section-head">
                                                            <div>
                                                                <h3>Company</h3>
                                                                <p class="subtle">Business context for the conversation.</p>
                                                            </div>
                                                            <div class="pill warning"><?php echo h($currentStateLabel); ?></div>
                                                        </div>

                                                        <div class="info-list">
                                                            <div class="info-item">
                                                                <div class="info-item__label">Company name</div>
                                                                <div class="info-item__value"><?php echo h($company_name ?: 'No company found'); ?></div>
                                                            </div>
                                                            <div class="info-item">
                                                                <div class="info-item__label">Website</div>
                                                                <div class="info-item__value">
                                                                    <?php if ($company_website): ?>
                                                                        <a href="<?php echo h($company_website); ?>" target="_blank" rel="noopener noreferrer"><?php echo h($company_website); ?></a>
                                                                    <?php else: ?>
                                                                        <?php echo h($companyUrlDisplay); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="info-item">
                                                                <div class="info-item__label">Address</div>
                                                                <div class="info-item__value"><?php echo h($company_address ?: 'No address found'); ?></div>
                                                            </div>
                                                            <div class="meta-grid">
                                                                <div class="info-item">
                                                                    <div class="info-item__label">Postal code</div>
                                                                    <div class="info-item__value"><?php echo h($company_postal ?: 'No postal code'); ?></div>
                                                                </div>
                                                                <div class="info-item">
                                                                    <div class="info-item__label">City</div>
                                                                    <div class="info-item__value"><?php echo h($company_city ?: 'No city'); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="stack">
                                                    <div class="card">
                                                        <div class="section-head">
                                                            <div>
                                                                <h3>Notes and follow up</h3>
                                                                <p class="subtle">Review the latest context on the left and capture the next action on the right.</p>
                                                            </div>
                                                        </div>

                                                        <div class="notes-layout">
                                                            <div class="notes-meta">
                                                                <div class="info-item">
                                                                    <div class="info-item__label">Deadline</div>
                                                                    <div class="info-item__value"><?php echo h($oldestTask ?: 'No active deadline'); ?></div>
                                                                </div>
                                                                <div class="info-item">
                                                                    <div class="info-item__label">Created</div>
                                                                    <div class="info-item__value"><?php echo h($latestNoteTime ?: 'No note created yet'); ?></div>
                                                                </div>
                                                                <div class="info-item">
                                                                    <div class="info-item__label">Latest note</div>
                                                                    <div class="info-item__value">
                                                                        <?php if ($latestNote): ?>
                                                                            <div class="note-box" style="margin-top: 10px;"><?php echo $latestNote; ?></div>
                                                                        <?php else: ?>
                                                                            No notes yet on this lead.
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="notes-editor">
                                                                <div class="notes-editor-card">
                                                                    <div class="field">
                                                                        <label class="label" for="note">New Note</label>
                                                                        <textarea class="textarea" id="note" name="note" placeholder="Write the outcome of the call, objections, interest and next step..."></textarea>
                                                                    </div>
                                                                </div>

                                                                <div class="field">
                                                                    <label class="label" for="followup">Next follow-up</label>
                                                                    <input class="input" type="datetime-local" id="followup" name="next_follow_up_at" value="<?php echo h(date('Y-m-d\TH:i', time() + 86400)); ?>">
                                                                </div>

                                                                <div class="field">
                                                                    <label class="label" for="next_stage">Next stage after the call</label>
                                                                    <select class="select" id="next_stage">
                                                                        <?php foreach ($states as $value => $label): ?>
                                                                            <option value="<?php echo h($value); ?>"<?php echo ($selectedState === (int)$value) ? ' selected' : ''; ?>><?php echo h($label); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>

                                                                <div class="action-row">
                                                                    <button type="button" onclick="SaveNoteAndContinue()" class="btn btn-success">Save and load next</button>
                                                                    <button type="button" onclick="CloseLeadAsLost()" class="btn btn-danger">Close as lost</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </main>
                    </div>
            </div>
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>
<?php include('partial/scripts.php'); ?>
<script>
    function CloseLeadAsLost() {
        const leadId = <?php echo json_encode((string)$leadIdValue); ?>;
        let note = document.getElementById('note') ? document.getElementById('note').value : '';

        if (!leadId) {
            alert('No lead loaded');
            return;
        }

        fetch('https://api.coherta.com/pipeline/leads/notes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
            },
            body: JSON.stringify({
                id: leadId,
                note: note
            })
        })
            .then(response => response.json())
            .then(() => {
                return fetch('https://api.coherta.com/pipeline/leads/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
                    },
                    body: JSON.stringify({
                        pipeline_company_id: leadId,
                        status: 'lost',
                        reason: 'lost'
                    })
                });
            })
            .then(response => response.json())
            .then(() => {
                window.location.href = 'leads-outreach?pipeline_id=<?php echo urlencode($_GET['pipeline_id'] ?? ''); ?>&state=<?php echo urlencode($_GET['state'] ?? ''); ?>&lead=1';
            })
            .catch(() => {
                alert('Error closing lead as lost');
            });
    }

    function SaveNoteAndContinue() {
        const leadId = <?php echo json_encode((string)$leadIdValue); ?>;
        const noteEl = document.getElementById('note');
        const stageEl = document.getElementById('next_stage');
        const followupEl = document.getElementById('followup');
        const note = noteEl ? noteEl.value : '';

        if (!leadId || !stageEl || !followupEl) {
            alert('No lead loaded');
            return;
        }

        fetch('https://api.coherta.com/pipeline/leads/stage/' + stageEl.value, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
            },
            body: JSON.stringify({
                pipeline_company_id: leadId
            })
        })
            .then(stageResponse => stageResponse.json())
            .then(() => {
                setTimeout(() => {
                    fetch('https://api.coherta.com/pipeline/leads/notes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
                        },
                        body: JSON.stringify({
                            id: leadId,
                            note: note
                        })
                    })
                        .then(response => response.json())
                        .then(() => {
                            fetch('https://api.coherta.com/pipeline/leads/todo/' + leadId, {
                                method: 'GET',
                                headers: {
                                    'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    (data.todos || []).forEach(todo => {
                                        fetch('https://api.coherta.com/pipeline/leads/todo/' + leadId, {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
                                            },
                                            body: JSON.stringify({
                                                tid: todo.id,
                                                id: leadId,
                                                completed: true
                                            })
                                        });
                                    });
                                })
                                .then(() => {
                                    return fetch('https://api.coherta.com/pipeline/leads/todo/' + leadId, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Authorization': 'Bearer <?php echo h($_SESSION['token']); ?>'
                                        },
                                        body: JSON.stringify({
                                            id: leadId,
                                            body: 'Call',
                                            deadline: followupEl.value
                                        })
                                    });
                                })
                                .then(todoResponse => todoResponse.json())
                                .then(() => {
                                    window.location.href = 'leads-outreach?pipeline_id=<?php echo urlencode($_GET['pipeline_id'] ?? ''); ?>&state=<?php echo urlencode($_GET['state'] ?? ''); ?>&lead=1';
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error saving follow-up');
                                });
                        })
                        .catch(() => {
                            alert('Error saving note');
                        });
                }, 1000);
            });
    }
</script>

<?php include('partial/footer-end.php'); ?>
