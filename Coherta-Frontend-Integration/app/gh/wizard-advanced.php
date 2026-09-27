<?php
require_once "../base.php";
$PageTitle = "Find New Leads";
$list_name = "Lead list";
if(isset($_GET['pipeline_id'])) {
    $pipeline_id = intval($_GET['pipeline_id']);
    $pipeline = $client->get("pipeline/all/leads/edit?pipeline_id=$pipeline_id")[0];
    $list_name = $pipeline['title'];
}
$pipeline_settings = json_decode($pipeline['pipeline_settings'], true);
$pipeline_filters = json_decode($pipeline['pipeline_filter'], true);
include('../partial/header.php');
?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<?php include('../partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="page-body-wrapper">
            <div class="">
                <div class="container-fluid">
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
                                                <div id="companyInputFields" class="mb-3"></div>
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
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_analytics_toolBtn" onclick="addCompanyInput('website_analytics_tool', 'Website Analytics', 'Enter analytics tool')">
                                                        <i data-feather="bar-chart-2" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Website Analytics
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_chat_platformBtn" onclick="addCompanyInput('website_chat_platform', 'Website Chat', 'Enter chat platform')">
                                                        <i class="fa fa-comments" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Chat
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_cms_typeBtn" onclick="addCompanyInput('website_cms_type', 'Website CMS', 'Enter CMS type')">
                                                        <i class="fa fa-desktop" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website CMS
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_frameworkBtn" onclick="addCompanyInput('website_framework', 'Website Framework', 'Enter framework')">
                                                        <i class="fa fa-cogs" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Framework
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_ecommerceBtn" onclick="addCompanyInput('website_ecommerce', 'Website Webshop Platform', 'Enter webshop platform')">
                                                        <i class="fa fa-shopping-cart" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Webshop Platform
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="payment_systemsBtn" onclick="addCompanyInput('payment_systems', 'Website Payment Method', 'Enter payment method')">
                                                        <i class="fa fa-credit-card" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Payment Method
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_widgetsBtn" onclick="addCompanyInput('website_widgets', 'Website Widgets', 'Enter widgets')">
                                                        <i class="fa fa-puzzle-piece" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Website Widgets
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_avg_priceBtn" onclick="addCompanyInput('website_avg_price', 'Average Product Price', 'Enter average product price')">
                                                        <i class="fa fa-tag" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Average Product Price
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_total_productsBtn" onclick="addCompanyInput('website_total_products', 'Total Products', 'Enter total products')">
                                                        <i class="fa fa-cubes" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Total Products
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="website_software_systemsBtn" onclick="addCompanyInput('website_software_systems', 'All Software Tags', 'Enter software tags')">
                                                        <i class="fa fa-tags" style="font-size:24px; margin-bottom:4px;"></i>
                                                        All Software Tags
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center me-2" id="trustpilot_reviewsBtn" onclick="addCompanyInput('trustpilot_reviews', 'Trustpilot Reviews', 'Enter Trustpilot reviews')">
                                                        <i class="fa fa-star" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Trustpilot Reviews
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center" id="trustpilot_scoreBtn" onclick="addCompanyInput('trustpilot_score', 'Trustpilot Score', 'Enter Trustpilot score')">
                                                        <i data-feather="star" style="width:24px; height:24px; margin-bottom:4px;"></i>
                                                        Trustpilot Score
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-flex flex-column align-items-center" id="employee_titleBtn" onclick="addCompanyInput('employee_title', 'Employee Title', 'Enter Employee Title')">
                                                        <i class="fa fa-briefcase" style="font-size:24px; margin-bottom:4px;"></i>
                                                        Employee Title
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
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

                                        jobtitleInput.addEventListener('keypress', function(e) {
                                            if (e.key === 'Enter') {
                                                addJobtitleBtn.click();
                                                e.preventDefault();
                                            }
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
                                            if (e.target.tagName === 'LI') {
                                                const title = e.target.tagName === 'LI' ? e.target.childNodes[e.target.childNodes.length - 1].textContent.trim() : '';
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
                                            $jobtitles = ["CEO", "CTO", "CFO", "COO", "Founder", "Co-Founder", "Owner", "Managing Director", "Director"];
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
        </div>
    </div>
<?php include('../partial/scripts.php'); ?>
    <script>
        function addCompanyInput(type, label, placeholder) {
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
                    <option value="contains">Contains</option>
                    <option value="more_than">More than</option>
                    <option value="less_than">Less than</option>
                    <option value="between">Between</option>
                    <option value="equals">equals</option>
                    <option value="not_contains">Do not contain</option>
                    <option value="is_not_empty">Is not empty</option>
                </select>
                <input type="text" onchange="sendFilters()" id="${type}Input${idx}" name="${type}[]" class="form-control" placeholder="${placeholder}">
                <div class="calculation" style="position:absolute;right:100px;"></div>
                <button type="button" class="btn btn-outline-danger" onclick="removeCompanyInputById('${uniqueId}', '${type}Btn');sendFilters()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            </div>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                <div class="flex-grow-1" style="border-top:1px solid #dee2e6;"></div>
                <button type="button" data-type='and' class="btn-and-or btn btn-success and-or-btn" onclick="toggleAndOr(this, 'AND');sendFilters()">AND</button>
                <button type="button" data-type='or' class="btn-and-or btn btn-outline-success and-or-btn" onclick="toggleAndOr(this, 'OR');sendFilters()">OR</button>
                <div class="flex-grow-1" style="border-top:1px solid #dee2e6;"></div>
            </div>
            `;
            document.getElementById('companyInputFields').appendChild(div);
            document.getElementById(type + "Btn").style.display = "";
            document.getElementById(type + "Input" + idx).focus();
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
            addCompanyInput('".$filter['key']."', label, '".$filter['value']."'); })();;";
                }
            }
            echo "setTimeout(function() { sendFilters(); }, 1000);";
        }
        if(isset($_GET['pipeline_id'])) {
            echo " setTimeout(function() { $(\"#nextBtn\").prop(\"disabled\", false); }, 5000);";

        }

        ?>
    </script>
    <script src="/app/assets/js/tooltip-init.js"></script>
    <script src="/appassets/js/select2/select2.full.min.js"></script>
    <script src="/appassets/js/select2/select2-custom.js"></script>
    <script src="/appassets/js/select2/addFilters.js"></script>

<?php include('../partial/footer-end.php'); ?>