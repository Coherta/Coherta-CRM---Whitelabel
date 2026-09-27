<?php
require_once "./base.php";
$sidebar_close = true;
$PageTitle = "Find New Leads";
$list_name = "Lead list";
include('partial/header.php');
if(isset($_GET['pipeline_id'])) {
    $pipeline_id = intval($_GET['pipeline_id']);
    $pipeline = $client->get("pipeline/all/leads/edit?pipeline_id=$pipeline_id")[0];
    $list_name = $pipeline['title'];
}
$pipeline_settings = json_decode($pipeline['pipeline_settings'], true);
$pipeline_filters = json_decode($pipeline['pipeline_filter'], true);
$company_data = $client->getCurrentCompanies()[0] ?? [];
$website_plugin_installed = coherta_company_has_product($company_data, 'prod_Tj6iHmP3XyIEdS', 3);
$featured = $client->get("gtm/templates?featured=1");
$searched_template = [];
$gtm_title = "";
if(isset($_GET['template_id'])) {
    $searched_template = $client->get("gtm/templates?id=".intval($_GET['template_id']));
    $gtm_title = $searched_template[0]['gtm_title'];
}

if(!$website_plugin_installed)
    $website_plugin_css = "disabled='disabled' ";
else
    $website_plugin_css = "";

$countries = [
        "dk" => "Denmark",
        "se" => "Sweden",
        "no" => "Norway",
        "fi" => "Finland",
        "uk" => "United Kingdom",
        "nordics" => "Nordics (DK, SE, NO, FI)",
];


$filter_field_mapping = [];
$technology_autocomplete_by_field = [];
$all_software_tags = [];

if (!empty($_SESSION['access_token'])) {
    $mapping_ch = curl_init('https://api.coherta.com/filter/field/mapping');
    curl_setopt($mapping_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($mapping_ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $_SESSION['access_token'],
            'Content-Type: application/json'
    ]);
    curl_setopt($mapping_ch, CURLOPT_TIMEOUT, 20);
    $mapping_response = curl_exec($mapping_ch);
    $mapping_status = (int) curl_getinfo($mapping_ch, CURLINFO_HTTP_CODE);
    curl_close($mapping_ch);

    if ($mapping_status >= 200 && $mapping_status < 300 && $mapping_response) {
        $decoded_mapping = json_decode($mapping_response, true);
        if (is_array($decoded_mapping)) {
            $filter_field_mapping = $decoded_mapping;

            foreach ($decoded_mapping as $field_group) {
                if (!is_array($field_group)) {
                    continue;
                }

                $field_key = isset($field_group['field']) ? trim((string) $field_group['field']) : '';
                $options = isset($field_group['options']) && is_array($field_group['options']) ? $field_group['options'] : [];

                if ($field_key !== '' && !isset($technology_autocomplete_by_field[$field_key])) {
                    $technology_autocomplete_by_field[$field_key] = [];
                }

                foreach ($options as $option) {
                    if (!is_array($option)) {
                        continue;
                    }

                    $candidate = '';
                    if (isset($option['value']) && trim((string) $option['value']) !== '') {
                        $candidate = trim((string) $option['value']);
                    } elseif (isset($option['label']) && trim((string) $option['label']) !== '') {
                        $candidate = trim((string) $option['label']);
                    }

                    if ($candidate === '') {
                        continue;
                    }

                    if ($field_key !== '') {
                        $technology_autocomplete_by_field[$field_key][$candidate] = true;
                    }

                    $all_software_tags[$candidate] = true;
                }
            }

            foreach ($technology_autocomplete_by_field as $field_key => $values) {
                $values = array_keys($values);
                natcasesort($values);
                $technology_autocomplete_by_field[$field_key] = array_values($values);
            }

            $all_software_tags = array_keys($all_software_tags);
            natcasesort($all_software_tags);
            $all_software_tags = array_values($all_software_tags);
        }
    }
}

?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
    <style>
        #section-templates {
            position: relative;
            min-height: 300px;
        }
        #section-templates .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        #section-templates .section-copy h3 {
            margin: 0 0 4px 0;
            font-size: 22px;
            font-weight: 700;
        }
        #section-templates .section-copy p {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
        }
        #section-templates .count-badge {
            background: var(--accent);
            color: #fff;
            border-radius: 999px;
            padding: 6px 18px;
            font-size: 13px;
            font-weight: 700;
            margin-left: 18px;
        }
        #section-templates .template-toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 14px;
        }
        #section-templates .search-field {
            flex: 1;
            min-width: 240px;
            height: 46px;
            border-radius: 15px;
            border: 1px solid var(--line-soft);
            background: rgba(255,255,255,.96);
            padding: 0 14px;
            color: var(--text);
            outline: none;
        }
        #section-templates .search-field:focus,
        #section-templates .field-select:focus,
        #section-templates .op-select:focus,
        #section-templates .value-select:focus {
            border-color: rgba(94,52,182,.22);
            box-shadow: 0 0 0 4px rgba(94,52,182,.08);
        }
        #section-templates .template-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 10px;
            overflow: hidden;
            position: relative;
            padding-bottom: 10px;
        }
        #section-templates .template-card {
            background: #fff;
            border: 1px solid var(--line-soft);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            padding: 18px 18px 14px 18px;
            cursor: pointer;
            transition: .18s ease;
            min-height: 196px;
            max-width: 100%;
            text-align: left;
        }
        #section-templates .template-card:hover {
            transform: translateY(-1px);
            border-color: rgba(94,52,182,.14);
        }
        #section-templates .template-card.active {
            border-color: rgba(94,52,182,.18);
            background: linear-gradient(180deg, rgba(94,52,182,.08), rgba(79,109,241,.04));
            box-shadow: inset 0 0 0 1px rgba(94,52,182,.05);
        }
        #section-templates .template-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }
        #section-templates .template-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(94,52,182,.06);
            border: 1px solid rgba(94,52,182,.08);
            color: var(--accent);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        #section-templates .template-title strong {
            display: block;
            font-size: 18px;
            letter-spacing: -.03em;
            margin-bottom: 4px;
        }
        #section-templates .template-title span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        #section-templates .template-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        #section-templates .meta-pill {
            height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            border: 1px solid rgba(17,19,39,.06);
            background: rgba(249,249,252,.9);
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }
        #section-templates .template-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: var(--muted);
            font-size: 12px;
        }
        #section-templates .template-stat strong {
            display: block;
            color: var(--text);
            font-size: 22px;
            line-height: 1;
            letter-spacing: -.04em;
            margin-bottom: 4px;
        }
        #section-templates .selected-banner {
            margin-top: 14px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(94,52,182,.12);
            background: linear-gradient(180deg, rgba(94,52,182,.06), rgba(79,109,241,.04));
            flex-wrap: wrap;
        }
        #section-templates .selected-banner.visible { display: flex; }
        #section-templates .selected-copy strong {
            display: block;
            font-size: 15px;
            letter-spacing: -.02em;
            margin-bottom: 3px;
        }
        #section-templates .selected-copy span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        .template-expand-overlay {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 60px;
            background: linear-gradient(0deg,rgba(255,255,255,0.98) 80%,rgba(255,255,255,0));
            text-align: center;
            z-index: 2;
        }
        .page-body.no-top-margin {
            margin-top: 0px !important;
        }
    </style>
    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --panel-2: #f9fbff;
            --line: #e7ebf3;
            --line-strong: #d7deeb;
            --text: #101828;
            --muted: #667085;
            --muted-2: #98a2b3;
            --primary: #4f46e5;
            --primary-2: #7c3aed;
            --primary-soft: rgba(79, 70, 229, 0.08);
            --success: #12b76a;
            --warning: #f79009;
            --danger: #f04438;
            --shadow-lg: 0 20px 50px rgba(15, 23, 42, 0.08);
            --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.06);
            --radius-xl: 28px;
            --radius-lg: 20px;
            --radius-md: 16px;
            --radius-sm: 12px;
            --sidebar-width: 280px;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                    radial-gradient(circle at top right, rgba(124,58,237,0.10), transparent 24%),
                    linear-gradient(180deg, #f8faff 0%, var(--bg) 100%);
            color: var(--text);
        }

        a { color: inherit; text-decoration: none; }
        button, input, select { font: inherit; }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr;
        }

        .content {
            min-width: 0;
            padding: 24px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border: 1px solid rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.74);
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow-md);
            border-radius: 24px;
            margin-bottom: 22px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .menu-btn {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: white;
            cursor: pointer;
        }

        .page-copy h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -0.04em;
        }

        .page-copy p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 320px;
            padding: 0 14px;
            height: 48px;
            border-radius: 16px;
            background: white;
            border: 1px solid var(--line);
        }

        .search-box input {
            border: 0;
            outline: 0;
            width: 100%;
            background: transparent;
        }

        .profile-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 8px 6px 6px;
            border-radius: 999px;
            background: white;
            border: 1px solid var(--line);
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #111827, #344054);
            color: white;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 22px;
            align-items: start;
        }

        .main-column {
            display: grid;
            gap: 22px;
        }

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
            background:
                    radial-gradient(circle at 0% 0%, rgba(255,255,255,0.36), transparent 30%),
                    linear-gradient(135deg, #1f2a6e 0%, #3547c8 45%, #6d48e5 100%);
            color: white;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -50px -70px auto;
            width: 260px;
            height: 260px;
            background: rgba(255,255,255,0.10);
            border-radius: 50%;
            filter: blur(4px);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(260px, .9fr);
            gap: 18px;
            align-items: end;
            position: relative;
            z-index: 1;
        }

        .hero h2 {
            margin: 0;
            font-size: 36px;
            line-height: 1.05;
            letter-spacing: -0.05em;
            max-width: 11ch;
        }

        .hero p {
            margin: 14px 0 0;
            color: rgba(255,255,255,0.78);
            line-height: 1.6;
            max-width: 60ch;
        }

        .hero-actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            appearance: none;
            border: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 16px;
            font-weight: 700;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            color: white;
            background: linear-gradient(135deg, #111827, #344054);
            box-shadow: 0 12px 24px rgba(15,23,42,0.18);
        }
        .btn-secondary {
            color: white;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.18);
        }
        .btn-light {
            color: #1d2a7a;
            background: white;
            border: 1px solid rgba(79,70,229,0.10);
        }
        .btn-outline {
            color: #344054;
            background: transparent;
            border: 1px solid var(--line-strong);
        }
        .btn-success {
            color: white;
            background: linear-gradient(135deg, #12b76a, #039855);
            box-shadow: 0 12px 24px rgba(18,183,106,0.24);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .hero-stat {
            padding: 18px;
            border-radius: 22px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(14px);
        }

        .hero-stat strong {
            display: block;
            font-size: 30px;
            line-height: 1;
            letter-spacing: -0.05em;
            margin-bottom: 6px;
        }

        .hero-stat span {
            display: block;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            line-height: 1.4;
        }

        .section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .section-head h3 {
            margin: 0;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .section-head p {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.6;
            max-width: 70ch;
        }

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

        .toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .input, .select, .chip-action {
            min-height: 48px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: white;
            color: var(--text);
        }

        .input, .select {
            width: 100%;
            padding: 0 14px;
            outline: 0;
        }

        .input:focus, .select:focus {
            border-color: rgba(79,70,229,0.25);
            box-shadow: 0 0 0 4px rgba(79,70,229,0.08);
        }

        .template-grid-preview {
            position: relative;
            max-height: 232px;
            overflow: hidden;
        }

        .template-grid-preview::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 86px;
            background: linear-gradient(180deg, rgba(248,250,252,0) 0%, rgba(248,250,252,0.88) 52%, rgba(248,250,252,1) 100%);
            pointer-events: none;
            transition: opacity .18s ease;
        }

        .template-grid-preview.expanded {
            max-height: none;
        }

        .template-grid-preview.expanded::after {
            opacity: 0;
        }

        .template-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 14px;
        }

        .template-more-wrap {
            display: flex;
            justify-content: center;
            padding-top: 16px;
        }

        .template-more-btn {
            min-width: 200px;
        }

        .template-card {
            text-align: left;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            min-height: 206px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            cursor: pointer;
            transition: .18s ease;
            width: 100%;
        }

        .template-card:hover {
            transform: translateY(-2px);
            border-color: rgba(79,70,229,0.16);
            box-shadow: 0 18px 34px rgba(15,23,42,0.08);
        }

        .template-card.active {
            background: linear-gradient(180deg, rgba(79,70,229,0.10), rgba(79,70,229,0.04));
            border-color: rgba(79,70,229,0.22);
            box-shadow: inset 0 0 0 1px rgba(79,70,229,0.08);
        }

        .template-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(79,70,229,0.08);
            color: #3447c7;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .template-title strong {
            display: block;
            font-size: 18px;
            letter-spacing: -0.03em;
            margin-bottom: 5px;
        }

        .template-title span {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
        }

        .template-footer {
            margin-top: auto;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
        }

        .template-metric strong {
            display: block;
            font-size: 26px;
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .template-metric span, .template-cta {
            font-size: 13px;
            color: var(--muted);
        }

        .builder-shell {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
            align-items: start;
        }

        .builder-main {
            display: grid;
            gap: 18px;
        }

        .subpanel {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,1), rgba(248,251,255,0.72));
            padding: 18px;
        }

        .subpanel h4 {
            margin: 0 0 6px;
            font-size: 18px;
            letter-spacing: -0.03em;
        }

        .subpanel p {
            margin: 0 0 16px;
            color: var(--muted);
            line-height: 1.55;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            font-size: 13px;
            font-weight: 700;
            color: #344054;
        }

        .field.full { grid-column: 1 / -1; }

        .filter-library-shell {
            display: grid;
            gap: 16px;
        }

        .filter-categories {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .category-card,
        .category-nav-card,
        .category-detail-card {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: white;
            overflow: hidden;
        }

        .category-nav-card {
            padding: 18px;
            cursor: pointer;
            text-align: left;
            transition: .18s ease;
            display: grid;
            gap: 14px;
            min-height: 170px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.03);
        }

        .category-nav-card:hover {
            transform: translateY(-2px);
            border-color: rgba(79,70,229,0.20);
            box-shadow: 0 16px 34px rgba(79,70,229,0.08);
        }

        .category-nav-top,
        .category-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .category-icon {
            display: none;
        }

        .category-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            background: #fcfdff;
        }

        .category-head strong,
        .category-nav-card strong {
            display: block;
            font-size: 20px;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin-bottom: 6px;
        }

        .category-head span,
        .category-nav-card span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .category-nav-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .category-nav-cta {
            font-size: 13px;
            font-weight: 700;
            color: #3447c7;
        }

        .category-detail-card {
            display: none;
        }

        .category-detail-card.active {
            display: block;
        }

        .category-back {
            border: 1px solid var(--line);
            background: white;
            color: #344054;
            border-radius: 12px;
            height: 40px;
            padding: 0 14px;
            cursor: pointer;
            font-weight: 700;
            transition: .18s ease;
        }

        .category-back:hover {
            border-color: rgba(79,70,229,0.20);
            color: #3447c7;
            background: rgba(79,70,229,0.04);
        }

        .filter-actions {
            padding: 14px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .chip-action {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            width: 100%;
            min-height: 76px;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(79,70,229,0.14);
            background: linear-gradient(180deg, #ffffff, #f8faff);
            cursor: pointer;
            transition: .18s ease;
            font-size: 15px;
            font-weight: 700;
            color: #25324b;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
            text-align: left;
        }

        .chip-action-copy {
            display: grid;
            gap: 4px;
        }

        .chip-action-title {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: #25324b;
            line-height: 1.3;
        }

        .chip-action-note {
            display: block;
            font-size: 12px;
            line-height: 1.45;
            color: var(--muted);
            font-weight: 500;
        }

        .chip-action:hover {
            border-color: rgba(79,70,229,0.28);
            color: #2439c8;
            background: rgba(79,70,229,0.06);
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(79, 70, 229, 0.10);
        }

        .active-filter-rules {
            display: grid;
            gap: 12px;
            padding: 14px;
            border-top: 1px solid var(--line);
        }

        .filter-connector-row {
            display: flex;
            justify-content: center;
            margin: -2px 0 -2px;
        }

        .filter-connector,
        .joiner-select {
            width: auto;
            min-width: 110px;
            max-width: 140px;
            height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: white;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #344054;
            text-align: center;
            text-align-last: center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .filter-rule-empty {
            padding: 16px;
            border-radius: 18px;
            border: 1px dashed var(--line-strong);
            background: #fbfcfe;
            color: var(--muted);
            font-size: 14px;
        }

        .filter-rule-row {
            display: grid;
            grid-template-columns: minmax(220px, 1.35fr) 180px minmax(180px, 1fr) 110px 50px;
            gap: 10px;
            align-items: stretch;
            padding: 12px;
            border-radius: 18px;
            border: 1px solid rgba(79,70,229,0.12);
            background: linear-gradient(180deg, #fff, #fbfcff);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .filter-rule-name {
            min-height: 58px;
            display: grid;
            align-content: center;
            gap: 4px;
            padding: 10px 14px;
            border-radius: 14px;
            background: var(--panel-2);
            border: 1px solid var(--line);
            color: #344054;
        }

        .filter-rule-name strong {
            display: block;
            font-size: 14px;
            line-height: 1.35;
        }

        .filter-rule-name small {
            display: block;
            font-size: 12px;
            line-height: 1.45;
            color: var(--muted);
            font-weight: 500;
        }

        .filter-rule-row .select,
        .filter-rule-row .input {
            width: 100%;
            height: 58px;
        }

        .filter-rule-duplicate {
            height: 58px;
            border: 1px solid rgba(79,70,229,0.18);
            border-radius: 16px;
            background: rgba(79,70,229,0.06);
            color: #3447c7;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            transition: .18s ease;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .filter-rule-duplicate:hover {
            border-color: rgba(79,70,229,0.28);
            background: rgba(79,70,229,0.10);
            color: #2539c9;
        }

        .filter-rule-remove {
            width: 50px;
            height: 58px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
            cursor: pointer;
            font-size: 22px;
            font-weight: 700;
            color: #475467;
            transition: .18s ease;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .filter-rule-remove:hover {
            border-color: rgba(240,68,56,0.28);
            color: var(--danger);
            background: rgba(240,68,56,0.04);
        }

        .website-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-radius: 18px;
            padding: 16px;
            background: linear-gradient(180deg, rgba(79,70,229,0.08), rgba(79,70,229,0.04));
            border: 1px solid rgba(79,70,229,0.10);
        }

        .website-info p {
            margin: 0;
            color: #344054;
            font-size: 14px;
        }

        .website-info-inline {
            padding: 0 14px 14px;
            border-top: 1px solid var(--line);
        }

        .website-info-inline[hidden] {
            display: none !important;
        }

        .switch {
            position: relative;
            width: 56px;
            height: 32px;
            flex: 0 0 auto;
        }

        .switch input { display: none; }
        .switch span {
            position: absolute;
            inset: 0;
            background: #d0d5dd;
            border-radius: 999px;
            transition: .18s ease;
            cursor: pointer;
        }

        .switch span::after {
            content: "";
            position: absolute;
            top: 4px;
            left: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: .18s ease;
        }

        .switch input:checked + span { background: linear-gradient(135deg, var(--primary), var(--primary-2)); }
        .switch input:checked + span::after { transform: translateX(24px); }

        .stakeholder-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .jobtitle-input-wrap {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            gap: 10px;
            margin-bottom: 14px;
        }

        .title-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            min-height: 52px;
        }

        .title-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            border-radius: 999px;
            padding: 0 14px;
            background: rgba(18,183,106,0.08);
            border: 1px solid rgba(18,183,106,0.12);
            color: #067647;
            font-weight: 600;
        }

        .title-tag button {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 0;
            cursor: pointer;
            background: rgba(6,118,71,0.12);
            color: #067647;
        }

        .segmented {
            display: grid;
            gap: 10px;
        }

        .segmented-row {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0,1fr));
        }

        .segmented button {
            min-height: 54px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: white;
            color: #475467;
            padding: 12px;
            text-align: left;
            cursor: pointer;
            line-height: 1.45;
        }

        .segmented button.active {
            background: linear-gradient(180deg, rgba(79,70,229,0.10), rgba(79,70,229,0.04));
            border-color: rgba(79,70,229,0.18);
            color: #1d2a7a;
            font-weight: 700;
        }

        .summary-column {
            position: sticky;
            top: 24px;
            display: grid;
            gap: 18px;
            align-self: start;
            max-height: calc(100vh - 24px);
        }

        .summary-card {
            border-radius: var(--radius-xl);
            background: rgba(255,255,255,0.88);
            border: 1px solid rgba(255,255,255,0.75);
            box-shadow: var(--shadow-lg);
            padding: 22px;
            max-height: calc(100vh - 24px);
            overflow: auto;
        }

        .summary-card h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.03em;
        }

        .summary-card p {
            margin: 8px 0 0;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.6;
        }

        .metric-stack {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .metric-box {
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(180deg, #fff, #f8fbff);
            border: 1px solid var(--line);
        }

        .metric-box strong {
            display: block;
            font-size: 30px;
            line-height: 1;
            letter-spacing: -0.05em;
            margin-bottom: 6px;
        }

        .metric-box span {
            color: var(--muted);
            font-size: 13px;
        }

        .selected-list {
            margin-top: 18px;
            display: grid;
            gap: 10px;
        }

        .selected-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
            font-size: 14px;
        }

        .selected-row small {
            color: var(--muted);
            display: block;
            margin-top: 2px;
        }

        .summary-field-trigger {
            display: grid;
            gap: 2px;
            margin-top: 2px;
            cursor: pointer;
            user-select: none;
            transition: color 0.18s ease, transform 0.18s ease;
            outline: none;
        }

        .summary-field-trigger small {
            margin: 0;
        }

        .summary-field-trigger:hover,
        .summary-field-trigger:focus-visible {
            color: var(--text);
            transform: translateX(1px);
        }

        .summary-field-trigger:focus-visible {
            border-radius: 8px;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }

        .choice-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .choice-button {
            width: 100%;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            border-radius: 14px;
            padding: 14px 16px;
            text-align: left;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .choice-button:hover {
            border-color: rgba(59, 130, 246, 0.35);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .choice-button.active {
            border-color: rgba(59, 130, 246, 0.55);
            background: rgba(59, 130, 246, 0.06);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .progress-list {
            margin-top: 18px;
            display: grid;
            gap: 14px;
        }

        .progress-item {
            display: grid;
            gap: 8px;
        }

        .progress-item-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 14px;
            color: #344054;
        }

        .bar {
            height: 10px;
            border-radius: 999px;
            background: #edf2f7;
            overflow: hidden;
        }

        .bar > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--primary), var(--primary-2));
        }

        .summary-actions {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .footer-note {
            margin-top: 22px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 1280px) {
            .main-grid,
            .builder-shell,
            .stakeholder-grid {
                grid-template-columns: 1fr;
            }

            .template-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .filter-actions { grid-template-columns: repeat(2, minmax(0,1fr)); }
        }
        @media (max-width: 980px) {
            .content { padding: 16px; }
            .topbar,
            .panel-inner,
            .summary-card { padding: 18px; }

            .choice-grid {
                grid-template-columns: 1fr;
            }
            .hero-grid,
            .toolbar,
            .segmented-row,
            .form-grid,
            .jobtitle-input-wrap { grid-template-columns: 1fr; }
            .template-grid,
            .filter-actions { grid-template-columns: 1fr; }
            .search-box { min-width: 0; width: 100%; }
            .topbar { align-items: stretch; }
            .topbar-right { width: 100%; justify-content: stretch; }
            .profile-pill { margin-left: auto; }
            .page-copy h1 { font-size: 24px; }
            .hero h2 { font-size: 30px; }
            .hero-stats { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .hero-stats,
            .template-footer {
                grid-template-columns: 1fr;
            }
            .hero-actions { flex-direction: column; }
            .hero-actions .btn,
            .summary-actions .btn { width: 100%; }
            .profile-pill span { display: none; }
            .website-info { align-items: flex-start; }
        }

        @media (max-width: 920px) {
            .filter-rule-row {
                grid-template-columns: 1fr;
            }

            .filter-connector-row {
                justify-content: center;
            }

            .filter-connector {
                width: auto;
            }

            .filter-rule-duplicate,
            .filter-rule-remove {
                width: 100%;
            }
        }



        .btn[disabled],
        .btn.is-disabled {
            opacity: 0.58;
            cursor: not-allowed;
            pointer-events: none;
            transform: none !important;
            box-shadow: none !important;
        }

        .upgrade-notice {
            display: none;
            margin-top: 22px;
            padding: 18px 20px;
            border-radius: 20px;
            border: 1px solid rgba(240, 68, 56, 0.16);
            background: linear-gradient(180deg, rgba(240, 68, 56, 0.06), rgba(247, 144, 9, 0.06));
        }

        .upgrade-notice.is-visible {
            display: block;
        }

        .upgrade-notice strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            color: #7a271a;
        }

        .upgrade-notice p {
            margin: 0;
            color: #7a271a;
            line-height: 1.6;
            font-size: 14px;
        }

        .upgrade-notice a {
            color: #7c3aed;
            font-weight: 700;
            text-decoration: underline;
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.56);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px;
            z-index: 1200;
            overflow-y: auto;
        }

        .modal-overlay[hidden] {
            display: none;
        }

        .modal-card {
            width: min(720px, 100%);
            background: #ffffff;
            border-radius: 28px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }
        #leadListModal .modal-card {
            position: relative;
            overflow: hidden;
        }

        #leadListModal .modal-card #leadlist-animation-overlay {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(128, 128, 128, 0.35);
            z-index: 20;
            border-radius: inherit;
        }

        #leadListModal .modal-card #leadlist-animation-overlay.show {
            display: flex;
        }

        .modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 28px 32px 20px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .modal-head h3 {
            margin: 0 0 6px;
            font-size: 24px;
            line-height: 1.2;
        }

        .modal-head p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .modal-close {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #f8fafc;
            color: #0f172a;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            flex: 0 0 auto;
        }

        .modal-close:hover {
            background: #eef2ff;
        }

        .modal-body {
            padding: 28px 32px 32px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .modal-section {
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
        }

        .modal-section-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 6px 0 18px;
            text-align: center;
        }

        .modal-section-head h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .modal-section-head p {
            margin: 0;
            max-width: 56ch;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            font-weight: 600;
        }

        .priority-select-wrap {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
        }

        .priority-select {
            min-height: 56px;
            border-radius: 10px;
            border: 1px solid var(--line-strong);
            padding: 0 16px;
            font-weight: 700;
            background: #fff;
        }


        .is-clickable {
            cursor: pointer;
        }

        .is-clickable:hover {
            border-color: rgba(79,70,229,0.22);
            box-shadow: 0 14px 30px rgba(79, 70, 229, 0.08);
        }

        .filter-rule-row.is-clickable .filter-rule-name {
            transition: border-color .18s ease, background .18s ease;
        }

        .filter-rule-row.is-clickable:hover .filter-rule-name {
            border-color: rgba(79,70,229,0.18);
            background: rgba(79,70,229,0.04);
        }

        .applied-rules-list {
            display: grid;
            gap: 14px;
        }

        .applied-rule-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .applied-rule-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .applied-rule-top strong {
            display: block;
            font-size: 16px;
            letter-spacing: -0.02em;
            color: #101828;
        }

        .applied-rule-top span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .applied-rule-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .applied-rule-field {
            padding: 14px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #ffffff;
        }

        .applied-rule-field label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted-2);
            margin-bottom: 8px;
        }

        .applied-rule-field div {
            font-size: 15px;
            font-weight: 700;
            color: #101828;
            word-break: break-word;
        }

        .applied-rule-joiner {
            display: flex;
            justify-content: center;
            padding: 2px 0;
        }

        .applied-rule-joiner span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #ffffff;
            color: #344054;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        }

        .applied-rule-empty {
            padding: 22px;
            border-radius: 20px;
            border: 1px dashed var(--line-strong);
            background: #fbfcfe;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
        }


        @media (max-width: 767px) {
            .modal-overlay {
                padding: 12px;
                align-items: flex-start;
                overflow-y: auto;
            }

            .modal-card {
                width: 100%;
                max-height: calc(100vh - 24px);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border-radius: 24px;
            }

            .modal-body {
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        .toolbar {
            background-color: unset;
        }

    </style>
<?php include('partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php');?>

            <div class="page-body no-top-margin">
                <div class="app-shell">

                    <main class="content">

                        <div class="main-grid">
                            <div class="main-column">
                                <?php
                                if(isset($_GET['pipeline_id'])) {
                                    echo '<input type="hidden" name="pipeline_id" id="pipeline_id" value="'.htmlspecialchars($_GET['pipeline_id']).'">';
                                }
                                if(isset($_GET['pipeline_id'])) {
                                    echo '<input type="hidden" name="remove_non_matching_unhandled_leads" id="removeNonMatchingUnhandledLeads" value="0">';
                                }
                                ?>

                                <section class="panel" id="section-templates">
                                    <div class="panel-inner">
                                        <div class="section-head">
                                            <div>
                                                <h3>GTM Templates</h3>
                                            </div>
                                            <div class="count-pill">3 popular shown · 3 more available</div>
                                        </div>

                                        <div class="toolbar">
                                            <input class="input" id="templateSearch" type="text" placeholder="Find a GTM Template..." value="<?php echo isset($gtm_title) ? htmlspecialchars($gtm_title) : ''; ?>" />
                                            <?php
                                            if(isset($gtm_title)) {
                                                ?>
                                                <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var searchInput = document.getElementById('templateSearch');
                                                        if (searchInput) {
                                                            var event = new Event('input', { bubbles: true });
                                                            searchInput.dispatchEvent(event);
                                                            // Optionally, click the first result if available
                                                            setTimeout(function() {
                                                                var card = document.querySelector('.template-card[data-template="<?php echo (int)$_GET['template_id'] ?>"]');
                                                                if (card) card.click();
                                                            }, 1000);
                                                        }
                                                    });
                                                </script>
                                                <?php
                                            }
                                            ?>
                                        </div>

                                        <div class="template-grid-preview">
                                            <div class="template-grid" id="templateGrid">
                                                <?php
                                                foreach ($featured as $template) {
                                                    $filters = json_decode($template['filters'], true);

                                                    echo '<button class="template-card" type="button" data-title="' . $template['gtm_title'] . '" data-template="' . $template['id'] . '" data-website="'.$template['website_list'].'" data-json="'.htmlspecialchars(json_encode($template)).'">
                                                    <div class="template-topline">
                                                        <span class="kicker">Popular</span>
                                                        <span class="meta-pill">Company data</span>
                                                    </div>
                                                    <div class="template-title">
                                                        <strong>' . $template['gtm_title'] . '</strong>
                                                        <span>' . $template['gtm_description'] . '</span>
                                                    </div>
                                                    <div class="meta-row">
                                                        <span class="meta-pill">'.count($filters).' filters</span>
                                                    </div>
                                                    <div class="template-footer">
                                                        <div class="template-metric">
                                                            <strong>'.$template['results_found'].'</strong>
                                                            <span>companies</span>
                                                        </div>
                                                        <span class="template-cta">Click to fetch and edit</span>
                                                    </div>
                                                </button>';
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <div class="template-more-wrap">
                                            <button class="btn btn-outline template-more-btn" id="toggleTemplateExpand" type="button">Show 3 more templates</button>
                                        </div>
                                    </div>
                                </section>

                                <section class="panel">
                                    <div class="panel-inner">
                                        <div class="section-head">
                                            <div>
                                                <h3>Step 1 · ICP → Company Requirements</h3>
                                            </div>
                                            <div class="count-pill">Guided builder</div>
                                        </div>

                                        <div class="builder-shell">
                                            <div class="builder-main">
                                                <div class="subpanel">
                                                    <h4>Filter library</h4>
                                                    <p>The filters are grouped into 9 categories. The user starts in the category overview, chooses an area, and then returns when it is time to pick the next filter group.</p>

                                                    <div class="filter-library-shell">
                                                        <div class="filter-categories" id="filterCategoryOverview"></div>

                                                        <div class="category-detail-card" id="filterCategoryDetail">
                                                            <div class="category-head">
                                                                <div>
                                                                    <strong id="filterCategoryTitle">Category</strong>
                                                                    <span id="filterCategoryDescription">Choose a category to see the related filters.</span>
                                                                </div>
                                                                <div style="display:flex; align-items:center; gap:10px;">
                                                                    <span class="meta-pill" id="filterCategoryCount">0 fields</span>
                                                                    <button class="category-back" id="filterCategoryBack" type="button">← Back to categories</button>
                                                                </div>
                                                            </div>
                                                            <div class="filter-actions" id="filterCategoryActions"></div>
                                                            <div class="active-filter-rules" id="activeFilterRules">
                                                                <div class="filter-rule-empty">Choose one or more filters to configure them instantly.</div>
                                                            </div>
                                                            <div class="website-info-inline" id="websiteInfoInline" hidden>
                                                                <div class="website-info" id="websiteInfoBox">
                                                                    <p>
                                                                        Website-only mode can only be used in Technologies. Ignore company-enrichment requirements.
                                                                    </p>
                                                                    <label class="switch">
                                                                        <input type="checkbox" id="websiteInfoCheckbox" />
                                                                        <span></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </section>

                                <section class="panel">
                                    <div class="panel-inner">
                                        <div class="section-head">
                                            <div>
                                                <h3>Step 2 · ICP → Purchase Stakeholder</h3>
                                            </div>
                                            <div class="count-pill">Stakeholder targeting</div>
                                        </div>

                                        <div class="stakeholder-grid">
                                            <div class="subpanel">
                                                <h4>Qualified job titles</h4>
                                                <p>Add roles one by one or paste in several titles. All active titles are shown as tags, making the list easy to scan and edit.</p>

                                                <div class="jobtitle-input-wrap">
                                                    <input class="input" id="jobtitle-input" type="text" placeholder="Enter job title" />
                                                    <button class="btn btn-primary" id="add-jobtitle-btn">Add to list</button>
                                                </div>
                                                <div class="title-tags" id="jobtitle-list"></div>
                                            </div>

                                        </div>
                                    </div>
                                </section>
                            </div>

                            <aside class="summary-column">
                                <section class="summary-card" id="leadListSummaryCard">
                                    <h3>Lead list summary</h3>
                                    <p>Fixed right column with overall status, active choices, and the most important actions.</p>

                                    <div class="metric-stack">
                                        <div class="metric-box">
                                            <strong id="SubjctsCompletingFilters">-</strong>
                                            <span>Total identified companies</span>
                                        </div>
                                        <div class="metric-box" style="display: none;">
                                            <strong>-</strong>
                                            <span>Companies with qualified stakeholders</span>
                                        </div>
                                        <div class="metric-box is-clickable" id="summaryFilters">
                                            <strong id="summarySelectedFilterCount">0</strong>
                                            <span>Active filter rules</span>
                                        </div>
                                    </div>

                                    <div class="selected-list">
                                        <div class="selected-row is-clickable" id="countrySummaryTrigger" >
                                            <div class="summary-field-trigger" role="button" tabindex="0" aria-label="Choose country">
                                                <strong>Country</strong>
                                                <small id="summaryCountryValue">Denmark</small>
                                            </div>
                                            <span class="meta-pill">Selected</span>
                                        </div>
                                        <div class="selected-row">
                                            <div>
                                                <strong>Owner</strong>
                                                <small><?php echo htmlspecialchars($user['full_name']) ?></small>
                                            </div>
                                            <span class="meta-pill">Assigned</span>
                                        </div>
                                    </div>

                                    <div class="summary-actions">
                                        <?php
                                        if(!isset($_GET['pipeline_id'])) {
                                            echo '<button class="btn btn-success" id="addLeadlistBtn" type="button">Add leadlist</button>';
                                        } else {
                                            echo '<button class="btn btn-success" id="addLeadlistBtn" type="button">Update leadlist</button>';
                                        }
                                        ?>
                                    </div>
                                </section>
                            </aside>
                        </div>

                    </main>
                </div>
            </div>
            <div class="modal-overlay" id="leadListModal" hidden>
                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="leadListModalTitle">
                    <div id="leadlist-animation-overlay"><div class="leadlist-animation-content">
                            <div class="leadlist-animation-spinner"></div>
                            <div class="leadlist-animation-text">Working with your lead list</div>
                        </div></div>
                    <div class="modal-head">
                        <div>
                            <h3 id="leadListModalTitle">Lead list setup</h3>
                            <p>Start with the name, market, and a short description of the list so it feels concrete from the very beginning.</p>
                        </div>
                        <button class="modal-close" id="closeLeadListModal" type="button" aria-label="Close modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="field full">
                                <label for="title">Name of your new Lead list</label>
                                <input class="input" id="title" type="text" value="<?php echo htmlspecialchars($list_name); ?>" />
                            </div>
                            <div class="field">
                                <label for="origin">Country of Origin</label>
                                <select class="select" id="origin" name="origin">
                                    <?php
                                    foreach ($countries as $code=>$country) {
                                        echo '<option' . (strtoupper($code) !== 'DK' ? ' disabled' : '') . ' value="' . $code . '"' . (strtoupper($code) === 'DK' ? ' selected' : '') . '>' . $country . '</option>';                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="owner">Lead list owner</label>
                                <select class="select" id="owner">
                                    <?php
                                    $users  = $client->get("company/users");
                                    if (!empty($users)) {
                                        foreach ($users as $u) {
                                            if($u['disabled'])
                                                continue;
                                            echo "<option value='" . $u['id'] . "' " . ($user['id'] == $u ? 'selected' : '') . ">" . htmlspecialchars($u['full_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-section">
                            <div class="modal-section-head">
                                <h4>Prioritization</h4>
                                <p>For each group of leads, choose the order in which they should be delivered.</p>
                            </div>
                            <div class="priority-select-wrap">
                                <select class="select priority-select" id="leadPriority" aria-label="Choose priority">
                                    <option value="random" selected>Random</option>
                                    <option value="creationDateDesc">Newest companies first</option>
                                    <option value="creationDateAsc">Oldest companies first</option>
                                    <option value="employeeCountDesc">Highest employee count first</option>
                                    <option value="employeeCountAsc">Lowest employee count first</option>
                                    <option value="contactHasPhoneFirst">Contacts with phone first</option>
                                </select>
                            </div>
                        </div>
                        <div class="upgrade-notice" id="extendedPackageNotice" aria-live="polite">
                            <strong>Extended package required</strong>
                            <p>You have selected data from Technology, Recruiter, or Lighthouse. That data is part of our Extended package, which is not active on your account right now. <a href="https://www.coherta.com/app/upgrade?product=Tj6iHmP3XyIEdS" id="extendedUpgradeLink">Upgrade here</a>.</p>
                        </div>
                        <div class="modal-actions">
                            <button class="btn btn-outline" id="cancelLeadListModal" type="button">Cancel</button>
                            <?php
                            if(!isset($_GET['pipeline_id'])) {
                                echo '<button class="btn btn-success" id="createLeadListModal" type="button">Create lead list</button>';
                            } else {
                                echo '<button class="btn btn-success" id="createLeadListModal" type="button">Update lead list</button>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>



            <div class="modal-overlay" id="updateLeadListConfirmModal" hidden>
                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="updateLeadListConfirmModalTitle">
                    <div class="modal-head">
                        <div>
                            <h3 id="updateLeadListConfirmModalTitle">Update lead list</h3>
                            <p>Do you want to remove leads that do not match the new criteria and have not yet been handled?</p>
                        </div>
                        <button class="modal-close" id="closeUpdateLeadListConfirmModal" type="button" aria-label="Close modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="subpanel" style="margin-bottom:16px;">
                            <div class="applied-rules-list" style="display:grid; gap:12px;">
                                <div class="applied-rule" style="display:flex; justify-content:space-between; align-items:center; gap:14px;">
                                    <strong>Old leads that fit new criteria or have already been handled</strong>
                                    <span class="meta-pill" id="updateLeadListOldCount">0</span>
                                </div>
                                <div class="applied-rule" style="display:flex; justify-content:space-between; align-items:center; gap:14px;">
                                    <strong>New Leads Added</strong>
                                    <span class="meta-pill" id="updateLeadListAddedCount">0</span>
                                </div>
                                <div class="applied-rule" style="display:flex; justify-content:space-between; align-items:center; gap:14px;">
                                    <strong>Total number of leads</strong>
                                    <span class="meta-pill" id="updateLeadListTotalCount">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="btn btn-outline" id="keepAllLeadListAction" type="button">No keep all leads</button>
                            <button class="btn btn-success" id="confirmUpdateLeadListAction" type="button">Yes, perform action (non-reversable)</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-overlay" id="countryModal" hidden>
                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="countryModalTitle">
                    <div class="modal-head">
                        <div>
                            <h3 id="countryModalTitle">Choose country</h3>
                            <p>Select the market for this lead list directly from the summary.</p>
                        </div>
                        <button class="modal-close" id="closeCountryModal" type="button" aria-label="Close modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="choice-grid" id="countryOptionGrid">
                            <?php
                            foreach($countries as $code=>$country) {
                                echo '<button ' . (strtoupper($code) !== 'DK' ? ' disabled' : '') . ' class="choice-button' . (strtoupper($code) === 'DK' ? ' active' : '') . '" type="button" data-country="' . $country . '"' . (strtoupper($code) === 'DK' ? '' : ' disabled') . '>' . $country . '</button>';
                            }
                            ?>
                        </div>

                        <div class="modal-actions">
                            <button class="btn btn-outline" id="cancelCountryModal" type="button">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-overlay" id="activeFiltersModal" hidden style="overflow-y: auto; max-height: 100vh;">
                <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="activeFiltersModalTitle">
                    <div class="modal-head">
                        <div>
                            <h3 id="activeFiltersModalTitle">Active filter rules</h3>
                            <p>Review all currently applied filters and their respective values in one overview.</p>
                        </div>
                        <button class="modal-close" id="closeActiveFiltersModal" type="button" aria-label="Close modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="applied-rules-list" id="activeFiltersModalList">
                            <div class="applied-rule-empty">No active filter rules have been added yet.</div>
                        </div>

                        <div class="modal-actions">
                            <button class="btn btn-outline" id="closeActiveFiltersModalFooter" type="button">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php');?>
        </div>
    </div>
<?php include('partial/scripts.php'); ?>
<?php
$default_jobtitles = ["CEO", "CTO", "CFO", "COO", "Founder", "Co-Founder", "Owner", "Managing Director", "Director", "Adm. Dir", "Direktør"];
$prefill_jobtitles = $default_jobtitles;

if (
        !empty($pipeline['jobtitles_json']) &&
        $pipeline['jobtitles_json'] !== '{}' &&
        $pipeline['jobtitles_json'] !== '[null]'
) {
    $decoded_jobtitles = json_decode($pipeline['jobtitles_json'], true);

    if (is_array($decoded_jobtitles)) {
        $prefill_jobtitles = array_values(array_filter(array_map(function ($jt) {
            if (is_array($jt) || is_object($jt)) {
                $jt = json_decode(json_encode($jt), true);
            }
            return trim((string)$jt);
        }, $decoded_jobtitles), function ($jt) {
            return $jt !== '';
        }));
    }
}
?>
    <script>
        window.advancedWizardPrefill = Object.assign({}, window.advancedWizardPrefill || {}, {
            pipelineId: <?php echo isset($pipeline_id) ? (int)$pipeline_id : 'null'; ?>,
            listName: <?php echo json_encode($list_name); ?>,
            filters: <?php echo json_encode($pipeline_filters['filters'] ?? []); ?>,
            websiteList: <?php echo json_encode((int)($pipeline['website_list'] ?? 0)); ?>,
            jobtitles: <?php echo json_encode($prefill_jobtitles); ?>,
            pipelineSettings: <?php echo json_encode(is_array($pipeline_settings) ? $pipeline_settings : new stdClass()); ?>,
            websitePluginInstalled: <?php echo $website_plugin_installed ? 'true' : 'false'; ?>,
            extendedUpgradeUrl: <?php echo json_encode('/billing'); ?>
        });
    </script>
    <script>
        window.cohertaBearerToken = <?php echo json_encode($_SESSION['access_token'] ?? ''); ?>;
        window.filterFieldMapping = <?php echo json_encode($filter_field_mapping, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.technologyAutocompleteByField = <?php echo json_encode($technology_autocomplete_by_field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.allSoftwareTags = <?php echo json_encode($all_software_tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="assets/js/advanced-wizard.js"></script>

    <script>
        (function () {
            var config = window.advancedWizardPrefill || {};
            var websitePluginInstalled = !!config.websitePluginInstalled;
            var premiumFieldKeys = new Set([
                'website_analytics_tool',
                'website_chat_platform',
                'website_cms_type',
                'website_framework',
                'website_ecommerce',
                'payment_systems',
                'website_widgets',
                'website_software_systems',
                'lighthouse_performance',
                'lighthouse_accessibility',
                'lighthouse_best_practices',
                'lighthouse_seo',
                'lighthouse_first_contentful_paint_seconds',
                'lighthouse_largest_contentful_paint_seconds',
                'lighthouse_total_blocking_time_ms',
                'lighthouse_cumulative_layout_shift',
                'lighthouse_speed_index_seconds',
                'recruiter'
            ]);
            var premiumTextMatchers = [
                /\btechnology\b/i,
                /\btechnologies\b/i,
                /\brecruiter\b/i,
                /\blighthouse\b/i,
                /\bcms\b/i,
                /\bframework\b/i,
                /\banalytics\b/i,
                /\bchat platform\b/i,
                /\becommerce\b/i,
                /\bpayment systems?\b/i,
                /\bwidgets?\b/i,
                /\bsoftware systems?\b/i,
                /\bfirst contentful paint\b/i,
                /\blargest contentful paint\b/i,
                /\btotal blocking time\b/i,
                /\bcumulative layout shift\b/i,
                /\bspeed index\b/i,
                /\bemployee title\b/i
            ];

            var addLeadlistBtn = document.getElementById('addLeadlistBtn');
            var createLeadListModalBtn = document.getElementById('createLeadListModal');
            var leadListModal = document.getElementById('leadListModal');
            var extendedPackageNotice = document.getElementById('extendedPackageNotice');
            var summaryColumn = document.querySelector('.summary-column');

            function flattenFilterKeys(filters) {
                var keys = [];
                if (!Array.isArray(filters)) return keys;
                filters.forEach(function (filter) {
                    if (!filter) return;
                    if (typeof filter === 'string') {
                        keys.push(filter);
                        return;
                    }
                    if (Array.isArray(filter)) {
                        filter.forEach(function (item) {
                            if (typeof item === 'string') keys.push(item);
                        });
                        return;
                    }
                    if (typeof filter === 'object') {
                        [filter.field, filter.key, filter.filter_key, filter.name, filter.id].forEach(function (value) {
                            if (typeof value === 'string' && value.trim()) keys.push(value.trim());
                        });
                    }
                });
                return keys;
            }

            function domContainsPremiumSelection() {
                var selectors = [
                    '#activeFilterRules',
                    '#activeFiltersModalList',
                    '#filterCategoryActions',
                    '#filterCategoryDetail',
                    '#stakeholderTags',
                    '#jobtitleTags',
                    '.selected-list',
                    '.main-column'
                ];
                var combinedText = selectors.map(function (selector) {
                    var el = document.querySelector(selector);
                    return el ? el.innerText : '';
                }).join('\n');

                return premiumTextMatchers.some(function (matcher) {
                    return matcher.test(combinedText);
                });
            }

            function currentSelectionNeedsExtendedPackage() {
                var knownFilterKeys = flattenFilterKeys(config.filters);
                var hasPremiumKey = knownFilterKeys.some(function (key) {
                    return premiumFieldKeys.has(String(key).trim());
                });

                if (hasPremiumKey) {
                    return true;
                }

                return domContainsPremiumSelection();
            }

            function syncExtendedPackageState() {
                var blocked = !websitePluginInstalled && currentSelectionNeedsExtendedPackage();

                if (createLeadListModalBtn) {
                    createLeadListModalBtn.disabled = blocked;
                    createLeadListModalBtn.classList.toggle('is-disabled', blocked);
                    createLeadListModalBtn.setAttribute('aria-disabled', blocked ? 'true' : 'false');
                    createLeadListModalBtn.title = blocked
                        ? 'Extended package required for the selected data.'
                        : '';
                }

                if (extendedPackageNotice) {
                    extendedPackageNotice.classList.toggle('is-visible', blocked);
                }

                if (addLeadlistBtn) {
                    addLeadlistBtn.dataset.requiresExtended = blocked ? '1' : '0';
                }
            }

            if (addLeadlistBtn && leadListModal) {
                addLeadlistBtn.addEventListener('click', function () {
                    window.setTimeout(syncExtendedPackageState, 0);
                    window.setTimeout(syncExtendedPackageState, 150);
                }, true);
            }

            ['click', 'change', 'input', 'keyup'].forEach(function (eventName) {
                document.addEventListener(eventName, function () {
                    window.setTimeout(syncExtendedPackageState, 25);
                }, true);
            });

            var filterTargets = [
                document.getElementById('activeFilterRules'),
                document.getElementById('activeFiltersModalList'),
                document.getElementById('filterCategoryActions'),
                document.getElementById('filterCategoryDetail')
            ].filter(Boolean);

            if (filterTargets.length && 'MutationObserver' in window) {
                var observer = new MutationObserver(function () {
                    syncExtendedPackageState();
                });
                filterTargets.forEach(function (target) {
                    observer.observe(target, { childList: true, subtree: true, characterData: true, attributes: true });
                });
            }

            function syncSummaryWidth() {
                if (!summaryColumn) return;
                if (window.innerWidth <= 1280) {
                    summaryColumn.style.width = '';
                    return;
                }
                var rect = summaryColumn.getBoundingClientRect();
                if (rect.width > 0) {
                    summaryColumn.style.width = rect.width + 'px';
                }
            }

            window.addEventListener('resize', syncSummaryWidth);
            window.addEventListener('load', function () {
                syncSummaryWidth();
                syncExtendedPackageState();
            });
            window.addEventListener('scroll', function () {
                syncSummaryWidth();
            }, { passive: true });

            syncExtendedPackageState();

            (function () {
                if (window.__leadListUpdateConfirmInstalled) return;
                window.__leadListUpdateConfirmInstalled = true;

                var pipelineIdEl = document.getElementById('pipeline_id');
                var isUpdateMode = !!pipelineIdEl;
                if (!isUpdateMode) return;

                var triggerBtn = document.getElementById('createLeadListModal');
                var removeFlagInput = document.getElementById('removeNonMatchingUnhandledLeads');
                var confirmModal = document.getElementById('updateLeadListConfirmModal');
                var closeConfirmModalBtn = document.getElementById('closeUpdateLeadListConfirmModal');
                var keepAllBtn = document.getElementById('keepAllLeadListAction');
                var confirmBtn = document.getElementById('confirmUpdateLeadListAction');
                var oldCountEl = document.getElementById('updateLeadListOldCount');
                var addedCountEl = document.getElementById('updateLeadListAddedCount');
                var totalCountEl = document.getElementById('updateLeadListTotalCount');
                var latestChanges = { old_pipeline: 0, contained: 0, added: 0, total: 0 };

                function openModal(modal) {
                    if (!modal) return;
                    modal.hidden = false;
                    document.body.classList.add('modal-open');
                }

                function closeModal(modal) {
                    if (!modal) return;
                    modal.hidden = true;
                    if (!document.querySelector('.modal-overlay:not([hidden])')) {
                        document.body.classList.remove('modal-open');
                    }
                }

                function toNumber(value) {
                    var num = Number(value);
                    return Number.isFinite(num) ? num : 0;
                }

                function applyChanges(changes) {
                    if (!changes || typeof changes !== 'object') return;
                    latestChanges = {
                        old_pipeline: toNumber(changes.old_pipeline),
                        contained: toNumber(changes.contained),
                        added: toNumber(changes.added),
                        total: toNumber(changes.total)
                    };

                    var kept = latestChanges.contained;
                    if (oldCountEl) oldCountEl.textContent = String(kept);
                    if (addedCountEl) addedCountEl.textContent = String(latestChanges.added);
                    if (totalCountEl) totalCountEl.textContent = String(latestChanges.total);
                }

                function inspectPayload(payload) {
                    if (!payload || typeof payload !== 'object') return;
                    if (payload.changes) {
                        applyChanges(payload.changes);
                        return;
                    }
                    if (payload.data && payload.data.changes) {
                        applyChanges(payload.data.changes);
                    }
                }

                var originalFetch = window.fetch;
                if (typeof originalFetch === 'function') {
                    window.fetch = function () {
                        return originalFetch.apply(this, arguments).then(function (response) {
                            try {
                                var url = response && response.url ? String(response.url) : '';
                                if (url.indexOf('company_new') !== -1) {
                                    response.clone().json().then(inspectPayload).catch(function () {});
                                }
                            } catch (err) {}
                            return response;
                        });
                    };
                }

                var originalXHROpen = XMLHttpRequest.prototype.open;
                var originalXHRSend = XMLHttpRequest.prototype.send;
                XMLHttpRequest.prototype.open = function (method, url) {
                    this.__leadListUrl = url;
                    return originalXHROpen.apply(this, arguments);
                };
                XMLHttpRequest.prototype.send = function () {
                    this.addEventListener('load', function () {
                        try {
                            var url = this.__leadListUrl ? String(this.__leadListUrl) : '';
                            if (url.indexOf('company_new') !== -1 && this.responseText) {
                                inspectPayload(JSON.parse(this.responseText));
                            }
                        } catch (err) {}
                    });
                    return originalXHRSend.apply(this, arguments);
                };

                function continueSubmit(removeNonMatchingUnhandled) {
                    if (removeFlagInput) {
                        removeFlagInput.value = removeNonMatchingUnhandled ? '1' : '0';
                    }
                    closeModal(confirmModal);
                    if (!triggerBtn) return;
                    triggerBtn.dataset.updateConfirmBypass = '1';
                    window.setTimeout(function () {
                        triggerBtn.click();
                        window.setTimeout(function () {
                            delete triggerBtn.dataset.updateConfirmBypass;
                        }, 0);
                    }, 0);
                }

                if (triggerBtn && confirmModal) {
                    triggerBtn.addEventListener('click', function (event) {
                        if (!isUpdateMode) return;
                        if (triggerBtn.dataset.updateConfirmBypass === '1') return;
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        if (removeFlagInput) {
                            removeFlagInput.value = '0';
                        }
                        openModal(confirmModal);
                    }, true);
                }

                if (closeConfirmModalBtn) {
                    closeConfirmModalBtn.addEventListener('click', function () {
                        closeModal(confirmModal);
                    });
                }

                if (confirmModal) {
                    confirmModal.addEventListener('click', function (event) {
                        if (event.target === confirmModal) {
                            closeModal(confirmModal);
                        }
                    });
                }

                if (keepAllBtn) {
                    keepAllBtn.addEventListener('click', function () {
                        continueSubmit(false);
                    });
                }

                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function () {
                        removeNonMatchingUnhandledLeads = true;
                        continueSubmit(true);
                    });
                }
            })();

        })();
    </script>
<?php include('partial/footer-end.php'); ?>
