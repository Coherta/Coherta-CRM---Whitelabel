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
if(!$website_plugin_installed)
    $website_plugin_css = "disabled='disabled' ";
else
    $website_plugin_css = "";

?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
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
                                        <div class="mb-2">
                                            <div class="col-form-label">Forklar på dansk, hvilken type kunder du leder efter</div>
                                            <textarea
                                                class="form-control filter-value"
                                                placeholder="Skriv en beskrivelse af dine ønskede kunder her..."
                                                name="customer_description"
                                                id="customer_description"
                                                rows="5"
                                                style="background: #f7f7f8; border-radius: 8px; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; font-size: 1rem; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: none; resize: vertical;"
                                                required
                                            ></textarea>
                                        </div>
                                        <script>
                                        document.getElementById('customer_description').addEventListener('blur', function() {
                                            const desc = this.value.trim();
                                            const bearerToken = document.body.getAttribute('session_id');

                                            if (desc.length > 0) {
                                                document.getElementById('SubjctsCompletingFilters').textContent = 'Finding leads, please wait';
                                                fetch('https://api.coherta.com/cohertus', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'Authorization': 'Bearer ' + bearerToken
                                                    },
                                                    body: JSON.stringify({ description: desc })
                                                })
                                                .then(response => response.text())
                                                .then(data => {
                                                    console.log('API response:', data);
                                                    cohertusFields = JSON.parse(data);
                                                    sendFilters();
                                                })
                                                .catch(error => console.error('API error:', error));
                                            }
                                        });
                                        </script>
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
                'website_total_products', 'website_software_systems', 'trustpilot_reviews', 'trustpilot_score'
            ];
            const filterDivs = document.querySelectorAll('#companyInputFields .addedFilters');
            let onlyWebsite = filterDivs.length > 0;
            filterDivs.forEach(div => {
                const type = div.getAttribute('data-type');
                if (!websiteKeys.includes(type)) onlyWebsite = false;
            });
            console.log(onlyWebsite);
            console.log(filterDivs);
            if(!onlyWebsite) {
                document.getElementById('websiteInfoBox')?.style.setProperty('display', 'none', 'important');
                document.getElementById('websiteInfoCheckbox').checked = false;
            }
            else
                document.getElementById('websiteInfoBox')?.removeAttribute('style');
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
    <script src="assets/js/select2/addFilters2.js"></script>

<?php include('partial/footer-end.php'); ?>