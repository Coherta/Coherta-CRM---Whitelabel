<?php
require_once "./base.php";
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
if(isset($_GET['template_id']))
    $searched_template = $client->get("gtm/templates?id=".intval($_GET['template_id']));

if(!$website_plugin_installed)
    $website_plugin_css = "disabled='disabled' ";
else
    $website_plugin_css = "";

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

                    <section class="section" id="section-templates">
                        <div class="section-head">
                            <div class="section-copy">
                                <h3>GTM Templates</h3>
                                <p>Start from a ready-made GTM template from Coherta. The most used templates are listed first — and you can edit them afterwards without starting from scratch.</p>
                            </div>
                        </div>
                        <div class="template-toolbar">
                            <input class="search-field" id="templateSearch" type="text" placeholder="Find a GTM template...">
                        </div>
                        <script>
                            document.getElementById('templateSearch').addEventListener('input', function() {
                                const search = this.value.trim();
                                const searchCards = document.getElementById('searchResultCard');
                                const allCards = document.querySelectorAll('.template-card:not(.search-data)');
                                if (search.length === 0) {
                                    allCards.forEach(card => card.style.display = '');
                                    document.querySelectorAll('.GTMSearch').forEach(el => {
                                        console.log('Removing:', el);
                                        el.remove();
                                    });
                                    searchCards.style.display = 'none';
                                    return;
                                }
                                fetch('https://api.coherta.com/gtm/templates?search=' + encodeURIComponent(search))
                                    .then(res => res.json())
                                    .then(data => {
                                        allCards.forEach(card => card.style.display = 'none');
                                        const card = document.getElementById('searchResultCard');
                                        if (data && data[0]) {
                                            document.querySelectorAll('.GTMSearch').forEach(el => {
                                                console.log('Removing:', el);
                                                el.remove();
                                            });
                                            data.forEach(item => {
                                                console.log(item);
                                                // Clone the card and append it to the grid
                                                const cardClone = card.cloneNode(true);
                                                cardClone.id = '';
                                                cardClone.classList.add('GTMSearch');
                                                cardClone.setAttribute('data-template', item.id);
                                                cardClone.setAttribute('data-template', item.gtm_title);
                                                cardClone.setAttribute('data-website', item.website_list);
                                                cardClone.setAttribute('data-json', JSON.stringify(item));
                                                cardClone.innerHTML = `
                                                <div class="template-title">
                                                    <strong>${item.gtm_title}</strong>
                                                    <span>${item.gtm_description}</span>
                                                </div>
                                                <div class="template-meta">
                                                    <span class="meta-pill">${(JSON.parse(item.filters || '[]')).length} filters</span>
                                                </div>
                                                <div class="template-footer">
                                                    <div class="template-stat">
                                                        <strong>${item.results_found}</strong>
                                                        <span>companies</span>
                                                    </div>
                                                    <span>Click to fetch and edit</span>
                                                </div>
                                            `;
                                                cardClone.style.display = '';
                                                card.parentNode.appendChild(cardClone);
                                            });
                                        } else {
                                        }
                                        checkTemplateExpand();
                                        makeTemplateCardsClickable();
                                    })
                                    .catch(err => {
                                        console.log(err);
                                        document.getElementById('searchResultCard').style.display = 'none';
                                        allCards.forEach(card => card.style.display = '');
                                    });
                            });
                        </script>
                        <div class="template-grid" id="templateGrid">
                            <button class="template-card" type="button"  id="searchResultCard" style="display:none"></button>
                            <?php
                            foreach($searched_template as $template) {
                                echo '<button class="template-card" id="TemplateCard" type="button" data-title="'.$template['gtm_title'].'" data-template="'.$template['id'].'" data-website="'.$template['website_list'].'" data-json="'.htmlspecialchars(json_encode($template)).'">
                                        <div class="template-title">
                                            <strong>'.$template['gtm_title'].'</strong>
                                            <span>'.$template['gtm_description'].'</span>
                                        </div>
                                        <div class="template-meta">';
                                $filters = json_decode($template['filters'], true);
                                echo '<span class="meta-pill">'.count($filters).' filters</span>';
                                echo '</div>
                                        <div class="template-footer">
                                            <div class="template-stat">
                                                <strong>'.$template['results_found'].'</strong>
                                                <span>companies</span>
                                            </div>
                                            <span>Click to fetch and edit</span>
                                        </div>
                                    </button>';
                                echo "<script>
                                window.addEventListener('DOMContentLoaded', function() {
                                    const firstCard = document.getElementById('TemplateCard');
                                    if (firstCard) firstCard.click();
                                });
                                </script>";
                            }
                            $searched_ids = array_column($searched_template, 'id');
                            foreach($featured as $template) {
                                if (in_array($template['id'], $searched_ids)) continue;

                                echo '<button class="template-card" type="button" 
                                data-title="'.$template['gtm_title'].'" data-template="'.$template['id'].'" 
                                data-website="'.$template['website_list'].'" data-json="'.htmlspecialchars(json_encode($template)).'">
                                        <div class="template-title">
                                            <strong>'.$template['gtm_title'].'</strong>
                                            <span>'.$template['gtm_description'].'</span>
                                        </div>
                                        <div class="template-meta">';
                                $filters = json_decode($template['filters'], true);
                                echo '<span class="meta-pill">'.count($filters).' filters</span>';
                                echo '</div>
                                        <div class="template-footer">
                                            <div class="template-stat">
                                                <strong>'.$template['results_found'].'</strong>
                                                <span>companies</span>
                                            </div>
                                            <span>Click to fetch and edit</span>
                                        </div>
                                    </button>';
                            }
                            ?>
                        </div>
                        <div class="template-expand-overlay" id="templateExpandOverlay">
                            <button class="secondary-btn" id="expandTemplatesBtn" style="margin-top:10px;">Show all templates</button>
                        </div>
                    </section>
                    <script>
                        function checkTemplateExpand() {
                            const grid = document.getElementById('templateGrid');
                            const cards = Array.from(document.querySelectorAll('.template-card')).filter(card => card.style.display !== 'none');
                            const overlay = document.getElementById('templateExpandOverlay');
                            if(cards.length > 3) {
                                overlay.style.display = '';
                                grid.style.maxHeight = 'calc(1.5 * 196px + 10px)';
                                grid.style.overflow = 'hidden';
                            } else {
                                overlay.style.display = 'none';
                                grid.style.maxHeight = '';
                                grid.style.overflow = '';
                            }
                        }
                        document.getElementById('expandTemplatesBtn').onclick = function() {
                            const grid = document.getElementById('templateGrid');
                            grid.style.maxHeight = '';
                            grid.style.overflow = '';
                            document.getElementById('templateExpandOverlay').style.display = 'none';
                        };
                        window.addEventListener('DOMContentLoaded', checkTemplateExpand);

                        function makeTemplateCardsClickable() {
                            document.querySelectorAll('.template-card').forEach(card => {
                                card.addEventListener('click', function() {
                                    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('active'));
                                    this.classList.add('active');
                                    const templateObj = JSON.parse(this.dataset.json);
                                    const filtersObj = JSON.parse(templateObj.filters);
                                    let website = this.dataset.website;
                                    if (website && website === "1") {
                                        const checkboxElement = document.getElementById('websiteInfoCheckbox');
                                        checkboxElement.checked = true;
                                    }
                                    filtersObj.forEach(filter => {
                                        var btn = document.getElementById(filter.key + 'Btn');
                                        var label = btn ? btn.querySelector('span,div,i').nextSibling.textContent.trim() : filter.key;
                                        addCompanyInput(filter.key, label, filter.value, filter.origin, filter.andor);
                                    });
                                    const firstBtn = document.getElementById('ShowNumberOfLeads');
                                    if (firstBtn) {
                                        $("#title").val(templateObj.gtm_title);
                                        firstBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        const firstAddLeadListBtn = document.querySelector('.firstAddLeadListBtn');
                                        firstAddLeadListBtn.disabled = false;
                                        firstAddLeadListBtn.focus();
                                    }
                                });
                            });
                        }
                        makeTemplateCardsClickable()
                    </script>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="mb-0">Step 1: ICP -> Company Requirements</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php
                                    if(isset($_GET['pipeline_id'])) {
                                        echo '<input type="hidden" name="pipeline_id" id="pipeline_id" value="'.htmlspecialchars($_GET['pipeline_id']).'">';
                                    }
                                    ?>
                                    <div class="tab">
                                        <div class="mb-2">
                                            <div class="col-form-label">Name of your new Lead list</div>
                                            <input type="text"
                                                   class="form-control filter-value"
                                                   placeholder="Lead list name"
                                                   value="<?php echo $list_name; ?>"
                                                   name="title"
                                                   id="title"
                                                   required
                                                   onclick="if(this.value.toLowerCase() === 'lead list') this.value=''">
                                        </div>
                                        <div class="mb-2">
                                            <div class="col-form-label">Country of Origin</div>
                                            <div class="select2-drpdwn">
                                                <select class="js-basic-single col-sm-12" name="origin" multiple="multiple">
                                                    <option value="DK" selected>Denmark</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 add-filters">
                                            <div class="mb-2">
                                                <div class="col-form-label">Type of Filter</div>
                                                <?php

                                                echo '<div id="websiteInfoBox" style="display:none !important;" class="alert alert-info mt-2 mb-3 d-flex gap-3 flex-row flex-wrap align-items-center">
                                                    <div class="flex-grow-1">
                                                        Do you want to add ALL domains that match your filter, from our domain database, including domains that do not currently contain company information?
                                                    </div>
                                                    <div>
                                                        <input type="checkbox" id="websiteInfoCheckbox" name="websiteInfoCheckbox" style="transform: scale(1.5);"
                                                        ';

                                                if($pipeline['website_list'] == 1) { echo " checked "; }
                                                echo '
                                                        >
                                                    </div>
                                                </div>';
                                                ?>
                                                <div id="companyInputFields" class="mb-3"></div>
                                                <div id="companyButtons" class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <h4>Company data:</h4>
                                                </div>
                                                <div id="companyButtons" class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="company_nameBtn" onclick="addCompanyInput('company_name', 'Company Name', 'Enter company name')">
                                                        <i class="fa fa-building" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Company Name
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="start_dateBtn" onclick="addCompanyInput('start_date', 'Founding Date', 'Enter founding date')">
                                                        <i class="fa fa-calendar" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Founding Date
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="postal_codeBtn" onclick="addCompanyInput('postal_code', 'Postal Code', 'Enter postal code')">
                                                        <i class="fa fa-map-marker" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Postal Code
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="road_nameBtn" onclick="addCompanyInput('road_name', 'Road Name', 'Enter road name')">
                                                        <i class="fa fa-road" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Road Name
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="cityBtn" onclick="addCompanyInput('city', 'City Name', 'Enter city name')">
                                                        <i class="fa fa-location-arrow" style="font-size:24px; margin-bottom:4px;"></i>
                                                        City Name
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="phoneBtn" onclick="addCompanyInput('phone', 'Phone Number', 'Enter phone number')">
                                                        <i class="fa fa-phone" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Phone Number
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="emailBtn" onclick="addCompanyInput('email', 'Email', 'Enter email')">
                                                        <i class="fa fa-envelope" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Email
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="websiteBtn" onclick="addCompanyInput('website', 'Website', 'Enter website')">
                                                        <i class="fa fa-globe" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="main_industryBtn" onclick="addCompanyInput('main_industry', 'Industry', 'Enter industry')">
                                                        <i class="fa fa-industry" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Industry
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="category_codeBtn" onclick="addCompanyInput('category_code', 'Category Code', 'Enter category code')">
                                                        <i class="fa fa-barcode" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Category Code
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="employeesBtn" onclick="addCompanyInput('employees', 'Number of Employees', 'Enter number of employees')">
                                                        <i class="fa fa-users" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Number of Employees
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="company_purposeBtn" onclick="addCompanyInput('company_purpose', 'Company Purpose', 'Enter company purpose')">
                                                        <i class="fa fa-bullseye" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Company Purpose
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="company_formBtn" onclick="addCompanyInput('company_form', 'Company Form', 'Enter company Form')">
                                                        <i class="fa-solid fa-id-card" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Company Form
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="financials_profit_lossBtn" onclick="addCompanyInput('financials_profit_loss', 'Financial Results: Profit/Loss', 'Enter profit/loss')">
                                                        <i class="fa fa-balance-scale" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Financial Results: Profit/Loss
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="financials_ebitBtn" onclick="addCompanyInput('financials_ebit', 'Financial Results: EBIT', 'Enter EBIT')">
                                                        <i class="fa fa-balance-scale" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Financial Results: EBIT
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="financials_equityBtn" onclick="addCompanyInput('financials_equity', 'Financial Results: Equity', 'Enter equity')">
                                                        <i class="fa fa-balance-scale" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Financial Results: Equity
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="financials_cashBtn" onclick="addCompanyInput('financials_cash', 'Financial Results: Cash', 'Enter cash')">
                                                        <i class="fa fa-money" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Financial Results: Cash
                                                    </button>
                                                </div>
                                                <div class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <h4>Website Data:</h4>
                                                </div>

                                                <?php
                                                if(!$website_plugin_installed)
                                                    echo '<div class="btn btn-info mb-3 d-flex gap-3 flex-row flex-wrap"><a style="color:#FFF" href="https://www.coherta.com/app/upgrade?product=Tj6iHmP3XyIEdS">To use our Website Scraping data, you need to upgrade your subscription.<br />Click here to upgrade</a></div>';
                                                ?>
                                                <div id="companyButtons" class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_analytics_toolBtn" onclick="addCompanyInput('website_analytics_tool', 'Website Analytics', 'Enter analytics tool')">
                                                        <i data-feather="bar-chart-2" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Website Analytics
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_chat_platformBtn" onclick="addCompanyInput('website_chat_platform', 'Website Chat', 'Enter chat platform')">
                                                        <i class="fa fa-comments" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Chat
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_cms_typeBtn" onclick="addCompanyInput('website_cms_type', 'Website CMS', 'Enter CMS type')">
                                                        <i class="fa fa-desktop" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website CMS
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_frameworkBtn" onclick="addCompanyInput('website_framework', 'Website Framework', 'Enter framework')">
                                                        <i class="fa fa-cogs" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Framework
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_ecommerceBtn" onclick="addCompanyInput('website_ecommerce', 'Website Webshop Platform', 'Enter webshop platform')">
                                                        <i class="fa fa-shopping-cart" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Webshop Platform
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="payment_systemsBtn" onclick="addCompanyInput('payment_systems', 'Website Payment Method', 'Enter payment method')">
                                                        <i class="fa fa-credit-card" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Payment Method
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_widgetsBtn" onclick="addCompanyInput('website_widgets', 'Website Widgets', 'Enter widgets')">
                                                        <i class="fa fa-puzzle-piece" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Widgets
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_avg_priceBtn" onclick="addCompanyInput('website_avg_price', 'Average Product Price', 'Enter average product price')">
                                                        <i class="fa fa-tag" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Average Product Price
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_total_productsBtn" onclick="addCompanyInput('website_total_products', 'Total Products', 'Enter total products')">
                                                        <i class="fa fa-cubes" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Total Products
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_software_systemsBtn" onclick="addCompanyInput('website_software_systems', 'All Software Tags', 'Enter software tags')">
                                                        <i class="fa fa-tags" style="font-size:24px; margin-bottom:4px;"></i>
                                                        All Software Tags
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="trustpilot_reviewsBtn" onclick="addCompanyInput('trustpilot_reviews', 'Trustpilot Reviews', 'Enter Trustpilot reviews')">
                                                        <i class="fa fa-star" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Trustpilot Reviews
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center" id="trustpilot_scoreBtn" onclick="addCompanyInput('trustpilot_score', 'Trustpilot Score', 'Enter Trustpilot score')">
                                                        <i data-feather="star" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Trustpilot Score
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?>  type="button" class="btn btn-outline-primary d-flex flex-column align-items-center" id="lighthouse_performanceBtn" onclick="addCompanyInput('lighthouse_performance', 'Lighthouse Performance Score', 'Enter Lighthouse Performance Score')">
                                                        <i data-feather="star" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse Performance Score
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_accessibilityBtn" onclick="addCompanyInput('lighthouse_accessibility', 'Lighthouse Accessibility', 'Enter Lighthouse Accessibility')">
                                                        <i data-feather="activity" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse Accessibility
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_best_practicesBtn" onclick="addCompanyInput('lighthouse_best_practices', 'Lighthouse Best Practices', 'Enter Lighthouse Best Practices')">
                                                        <i data-feather="check-circle" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse Best Practices
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_seoBtn" onclick="addCompanyInput('lighthouse_seo', 'Lighthouse SEO', 'Enter Lighthouse SEO')">
                                                        <i data-feather="search" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse SEO
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_first_contentful_paint_secondsBtn" onclick="addCompanyInput('lighthouse_first_contentful_paint_seconds', 'Lighthouse First Contentful Paint (s)', 'Enter FCP (seconds)')">
                                                        <i data-feather="clock" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse FCP (s)
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_largest_contentful_paint_secondsBtn" onclick="addCompanyInput('lighthouse_largest_contentful_paint_seconds', 'Lighthouse Largest Contentful Paint (s)', 'Enter LCP (seconds)')">
                                                        <i data-feather="clock" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse LCP (s)
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_total_blocking_time_msBtn" onclick="addCompanyInput('lighthouse_total_blocking_time_ms', 'Lighthouse Total Blocking Time (ms)', 'Enter TBT (ms)')">
                                                        <i data-feather="pause-circle" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse TBT (ms)
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_cumulative_layout_shiftBtn" onclick="addCompanyInput('lighthouse_cumulative_layout_shift', 'Lighthouse Cumulative Layout Shift', 'Enter CLS')">
                                                        <i data-feather="shuffle" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse CLS
                                                    </button>
                                                    <button <?php echo $website_plugin_css; ?> type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="lighthouse_speed_index_secondsBtn" onclick="addCompanyInput('lighthouse_speed_index_seconds', 'Lighthouse Speed Index (s)', 'Enter Speed Index (seconds)')">
                                                        <i data-feather="fast-forward" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Lighthouse Speed Index (s)
                                                    </button>
                                                </div>
                                                <div id="companyButtons" class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <h4>Employee data:</h4>
                                                </div>
                                                <div id="companyButtons" class="mb-3 d-flex gap-3 flex-row flex-wrap">
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center" id="employee_titleBtn" onclick="addCompanyInput('employee_title', 'Employee Title', 'Enter Employee Title')">
                                                        <i class="fa fa-briefcase" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Employee Title
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="ShowNumberOfLeads">
                                        <div class="text-end btn-mb">
                                            Total identified leads:
                                            <h2 id="SubjctsCompletingFilters">0</h2>
                                        </div>
                                        <div class="text-end calculation_with_active"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="text-end btn-mb">
                                        <button class="btn btn-primary firstAddLeadListBtn" type="button"
                                                onclick="SavePipeline(<?php echo $client->getCurrentCompanies()[0]["id"]; ?>)"><?php
                                            if(isset($_GET['pipeline_id'])) {
                                                echo "Update Leadlist";
                                            } else {
                                                echo "Add Leadlist";
                                            }
                                            ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Step 2: ICP -> Purchase Stakeholder</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="jobtitle-input" class="form-label">Add Job Title</label>
                                        <div class="input-group mb-2">
                                            <input type="text" id="jobtitle-input" class="form-control" placeholder="Enter job title">
                                            <button class="btn btn-primary" type="button" id="add-jobtitle-btn">Add to list</button>
                                        </div>
                                        <ul id="jobtitle-list" class="list-group mb-2"></ul>

                                        <div class="btn-group" data-settings="CohertaVisibleLeadsFilter" role="group" aria-label="Job Title Filters">
                                            <button type="button" class="m-5 btn btn-primary" id="show-all-leads">All Total Identified Leads on list</button>
                                            <button type="button" class="m-5 btn btn-outline-primary" id="show-qualified-leads">Identified Leads on list <b>WITH</b> qualified Job Titles</button>
                                            <button type="button" class="m-5 btn btn-outline-primary" id="show-unqualified-leads">Identified Leads on list <b>WITHOUT</b> qualified Job Titles</button>
                                        </div>
                                        <hr />
                                        <div class="btn-group" data-settings="SyncronizationLeadsFilter" role="group" aria-label="Job Title Transfer Filters">
                                            <button type="button" class="m-5 btn btn-primary" id="show-all-leads">Send to external CRM all Total Identified Leads on list</button>
                                            <button type="button" class="m-5 btn btn-outline-primary" id="show-qualified-leads">Send to external CRM only Identified Leads on list <b>WITH</b> qualified Job Titles</button>
                                            <button type="button" class="m-5 btn btn-outline-primary" id="show-unqualified-leads">Send to external CRM only Identified Leads on list <b>WITHOUT</b> Qualified Job Titles</button>
                                        </div>
                                    </div>
                                    <script>
                                        const jobtitleInput = document.getElementById('jobtitle-input');
                                        const addJobtitleBtn = document.getElementById('add-jobtitle-btn');
                                        const jobtitleList = document.getElementById('jobtitle-list');
                                        let jobtitles = [];

                                        jobtitleInput.addEventListener('paste', function(e) {
                                            setTimeout(() => {
                                                let input = jobtitleInput.value.trim();
                                                if (!input) return;

                                                // Check if input starts with ( and ends with ), then remove both
                                                let trimmedInput = input;
                                                if (trimmedInput.startsWith('(') && trimmedInput.endsWith(')')) {
                                                    input = trimmedInput.slice(1, -1);
                                                }

                                                // Parse input for quoted and unquoted titles separated by OR
                                                const titles = [];
                                                const regex = /"([^"]+)"|(\bOR\b)|([^\s"]+)/gi;
                                                let match;
                                                let current = [];
                                                while ((match = regex.exec(input)) !== null) {
                                                    if (match[1]) {
                                                        current.push(match[1]);
                                                    } else if (match[2]) {
                                                        // OR found, push current and reset
                                                        if (current.length) {
                                                            titles.push(current.join(' '));
                                                            current = [];
                                                        }
                                                    } else if (match[3]) {
                                                        current.push(match[3]);
                                                    }
                                                }
                                                if (current.length) {
                                                    titles.push(current.join(' '));
                                                }

                                                // Remove empty and duplicate titles
                                                const uniqueTitles = [...new Set(titles.map(t => t.trim()).filter(Boolean))];

                                                // Empty the list and insert the new titles
                                                jobtitles = uniqueTitles;
                                                renderJobtitles();
                                                jobtitleInput.value = '';
                                            }, 0);
                                        });


                                        jobtitleInput.addEventListener('keypress', function(e) {
                                            if (e.key === 'Enter') {
                                                addJobtitleBtn.click();
                                                e.preventDefault();
                                            }
                                            console.log("Key press");
                                        });

                                        addJobtitleBtn.addEventListener('click', function() {
                                            const title = jobtitleInput.value.trim();
                                            if (title && !jobtitles.includes(title)) {
                                                jobtitles.push(title);
                                                renderJobtitles();
                                                jobtitleInput.value = '';
                                            }
                                        });

                                        jobtitleList.addEventListener('click', function(e) {
                                            let li;
                                            if (e.target.tagName === 'svg' || e.target.closest('svg')) {
                                                li = e.target.closest('li');
                                            } else if (e.target.tagName === 'LI') {
                                                li = e.target;
                                            }
                                            if (li) {
                                                // Extract the job title text (after the SVG)
                                                const title = li.childNodes[li.childNodes.length - 1].textContent.trim();
                                                jobtitles = jobtitles.filter(jt => jt !== title);
                                                renderJobtitles();
                                            }
                                        });
                                        function renderJobtitles() {
                                            jobtitleList.innerHTML = '';
                                            jobtitles.forEach(title => {
                                                const li = document.createElement('li');
                                                li.className = 'list-group-item list-group-item-action';
                                                li.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" style="float:left;padding-right: 10px;" width="32" height="32" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"></path>
                                              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"></path>
                                            </svg> ${title}`;
                                                li.style.lineHeight = '32px';
                                                jobtitleList.appendChild(li);
                                            });
                                        }


                                        document.querySelectorAll('.btn-group[aria-label="Job Title Filters"] .btn').forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                document.querySelectorAll('.btn-group[aria-label="Job Title Filters"] .btn').forEach(function(b) {
                                                    b.classList.remove('btn-primary');
                                                    b.classList.add('btn-outline-primary');
                                                });
                                                btn.classList.remove('btn-outline-primary');
                                                btn.classList.add('btn-primary');
                                            });
                                        });

                                        document.querySelectorAll('.btn-group[aria-label="Job Title Transfer Filters"] .btn').forEach(function(btn) {
                                            btn.addEventListener('click', function() {
                                                document.querySelectorAll('.btn-group[aria-label="Job Title Transfer Filters"] .btn').forEach(function(b) {
                                                    b.classList.remove('btn-primary');
                                                    b.classList.add('btn-outline-primary');
                                                });
                                                btn.classList.remove('btn-outline-primary');
                                                btn.classList.add('btn-primary');
                                            });
                                        });
                                        <?php
                                        if (!empty($pipeline['jobtitles_json'])) {
                                            $jobtitles = json_decode($pipeline['jobtitles_json'], true);
                                            if (is_array($jobtitles)) {
                                                foreach ($jobtitles as $jt) {
                                                    if(trim(json_decode(json_encode($jt))) !== "") {
                                                        echo "jobtitles.push('" . json_decode(json_encode($jt)) . "');";
                                                    }
                                                }
                                            }
                                        }
                                        if(!empty($pipeline_settings)) {
                                            foreach ($pipeline_settings as $setting_key => $setting_value) {
                                                echo "document.querySelectorAll('.btn-group[data-settings=\"" . addslashes($setting_key) . "\"] .btn').forEach(function(btn) {
                                                    if (btn.id === '" . addslashes($setting_value) . "') {
                                                        btn.click();
                                                    }
                                                });
                                                window.addEventListener('load', function() { filterSettingsSelections['".addslashes($setting_key)."'] = \"".addslashes($setting_value)."\"; });";
                                            }
                                        }
                                        if (empty($pipeline['jobtitles_json']) or $pipeline['jobtitles_json'] == "{}" or $pipeline['jobtitles_json'] == "[null]") {
                                            $jobtitles = ["CEO", "CTO", "CFO", "COO", "Founder", "Co-Founder", "Owner", "Managing Director", "Director", "Adm. Dir", "Direktør"];
                                            foreach ($jobtitles as $jt) {
                                                echo "jobtitles.push('" . $jt . "');";
                                            }
                                        }
                                        ?>
                                        renderJobtitles();
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="text-end btn-mb">
                                        <button class="btn btn-primary" id="nextBtn" type="button"
                                                onclick="SavePipeline(<?php echo $client->getCurrentCompanies()[0]["id"]; ?>)"><?php
                                            if(isset($_GET['pipeline_id'])) {
                                                echo "Update Leadlist";
                                            } else {
                                                echo "Add Leadlist";
                                            }
                                            ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <?php include('partial/footer.php');?>
        </div>
    </div>
<?php include('partial/scripts.php'); ?>
    <script>
        function showWebsiteInfoBoxIfOnlyWebsiteFilters() {
            // List of website filter keys
            const websiteKeys = [
                'website_analytics_tool', 'website_chat_platform', 'website_cms_type', 'website_framework',
                'website_ecommerce', 'payment_systems', 'website_widgets', 'website_avg_price',
                'website_total_products', 'website_software_systems', 'trustpilot_reviews', 'trustpilot_score',
                'lighthouse_performance', 'lighthouse_accessibility', 'lighthouse_best_practices', 'lighthouse_seo',
                'lighthouse_first_contentful_paint_seconds', 'lighthouse_largest_contentful_paint_seconds',
                'lighthouse_total_blocking_time_ms', 'lighthouse_cumulative_layout_shift', 'lighthouse_speed_index_seconds',
            ];
            const filterDivs = document.querySelectorAll('#companyInputFields .addedFilters');
            let onlyWebsite = filterDivs.length > 0;
            filterDivs.forEach(div => {
                const type = div.getAttribute('data-type');
                if (!websiteKeys.includes(type)) onlyWebsite = false;
            });
            if(!onlyWebsite) {
                document.getElementById('websiteInfoBox')?.style.setProperty('display', 'none', 'important');
                document.getElementById('websiteInfoCheckbox').checked = false;
            }
            else {
                document.getElementById('websiteInfoBox')?.removeAttribute('style');
                document.getElementById('websiteInfoCheckbox').checked = true;
            }
        }

        function addCompanyInput(type, label, placeholder, optionType = "Contains", andor = "and") {
            // Allow multiple fields of the same type by generating a unique id
            let idx = 1;
            let uniqueId = type + "InputDiv";
            while (document.getElementById(uniqueId)) {
                idx++;
                uniqueId = type + "InputDiv" + idx;
            }
            const div = document.createElement('div');
            div.className = "mb-2 addedFilters position-relative";
            div.setAttribute("data-type", type);
            div.id = uniqueId;
            div.innerHTML = `
            <div class="input-group align-items-center">
                <span class="input-group-text" style="min-width:200px;">${label}</span>
                <select class="form-select" onchange="sendFilters()" name="${type}_options[]"  style="max-width: 160px;">
                    <option value="contains" ${optionType === "contains" ? "selected" : ""}>Contains</option>
                    <option value="more_than" ${optionType === "more_than" ? "selected" : ""}>More than</option>
                    <option value="less_than" ${optionType === "less_than" ? "selected" : ""}>Less than</option>
                    <option value="between" ${optionType === "between" ? "selected" : ""}>Between</option>
                    <option value="equals" ${optionType === "equals" ? "selected" : ""}>Exact</option>
                    <option value="not_contains" ${optionType === "not_contains" ? "selected" : ""}>Do not contain</option>
                    <option value="is_not_empty" ${optionType === "is_not_empty" ? "selected" : ""}>Is not empty</option>
                </select>
                <input type="text" onblur="console.log(this.value); if(this.value.trim() === '') this.value=this.placeholder;sendFilters(); " id="${type}Input${idx}" name="${type}[]" class="form-control" placeholder="${placeholder}" value="${placeholder}" onclick="if(this.value === this.placeholder) this.value='';">
                <div class="calculation" style="position:absolute;right:100px;"></div>
                <button type="button" class="btn btn-outline-danger" onclick="removeCompanyInputById('${uniqueId}', '${type}Btn');sendFilters()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                <div class="flex-grow-1" style="border-top:1px solid #dee2e6;"></div>
                <button type="button" data-type='and' class="btn-and-or btn ${andor === "and" ? "btn-success" : "btn-outline-success"} and-or-btn" onclick="toggleAndOr(this, 'AND');sendFilters()">AND</button>
                <button type="button" data-type='or' class="btn-and-or btn ${andor === "or" ? "btn-success" : "btn-outline-success"} and-or-btn" onclick="toggleAndOr(this, 'OR');sendFilters()">OR</button>
                <div class="flex-grow-1" style="border-top:1px solid #dee2e6;"></div>
            </div>
            `;
            document.getElementById('companyInputFields').appendChild(div);
            document.getElementById(type + "Btn").style.display = "";
            document.getElementById(type + "Input" + idx).value = "";
            document.getElementById(type + "Input" + idx).focus();

            showWebsiteInfoBoxIfOnlyWebsiteFilters();
        }
        function toggleAndOr(btn, selected) {
            const container = btn.parentElement;
            const buttons = container.querySelectorAll('.and-or-btn');
            buttons.forEach(b => {
                if (b.textContent.trim() === selected) {
                    b.classList.add('btn-success');
                    b.classList.remove('btn-outline-success');
                } else {
                    b.classList.add('btn-outline-success');
                    b.classList.remove('btn-success');
                }
            });
        }
        function removeCompanyInputById(divId, btnId) {
            const div = document.getElementById(divId);
            if (div) div.remove();
            document.getElementById(btnId).style.display = "";
            showWebsiteInfoBoxIfOnlyWebsiteFilters();

        }
    </script>
    <script>
        <?php
        if(isset($_GET['pipeline_id'])) {
            if (!empty($pipeline_filters['filters'])) {
                foreach($pipeline_filters['filters'] as $filter) {
                    echo "(function() {
            var btn = document.getElementById('".addslashes($filter['key'])."Btn');
            var label = btn ? btn.querySelector('span,div,i').nextSibling.textContent.trim() : '".addslashes($filter['key'])."';
            addCompanyInput('".$filter['key']."', label, '".$filter['value']."', '".$filter['origin']."', '".$filter['andor']."'); })();;";
                }
            }
            echo "setTimeout(function() { sendFilters(); }, 1000);";
        }
        if(isset($_GET['pipeline_id'])) {
            echo " setTimeout(function() { $(\"#nextBtn\").prop(\"disabled\", false); }, 5000);";

        }

        ?>
    </script>
    <script src="assets/js/tooltip-init.js"></script>
    <script src="assets/js/select2/select2.full.min.js"></script>
    <script src="assets/js/select2/select2-custom.js"></script>
    <script src="assets/js/select2/addFilters.js"></script>

<?php include('partial/footer-end.php'); ?>