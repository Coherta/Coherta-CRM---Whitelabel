<?php
require_once "./base.php";
$PageTitle = "GTM Templates";
$list_name = "Lead list";
include('partial/header.php');

$featured = $client->get("gtm/templates");

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
            color: #000;
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
                                                cardClone.setAttribute("href", "wizard-advanced?template_id=" + data[0].id);
                                                cardClone.setAttribute('data-template', item.id);
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
                                    })
                                    .catch(err => {
                                        console.log(err);
                                        document.getElementById('searchResultCard').style.display = 'none';
                                        allCards.forEach(card => card.style.display = '');
                                    });
                            });
                        </script>
                        <div class="template-grid" id="templateGrid">
                            <a class="template-card" type="button" id="searchResultCard" style="display:none"></a>
                            <?php
                            foreach($featured as $template) {
                                $filters = json_decode($template['filters'], true);
                                if(is_string($filters) or $filters === NULL)
                                    continue;
                                echo '<a class="template-card" href="wizard-advanced?template_id='.$template['id'].'" data-template="'.$template['id'].'" data-website="'.$template['website_list'].'" data-json="'.htmlspecialchars(json_encode($template)).'">
                                        <div class="template-title">
                                            <strong>'.$template['gtm_title'].'</strong>
                                            <span>'.$template['gtm_description'].'</span>
                                        </div>
                                        <div class="template-meta">';
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
                    </section>
                    <script>
                        document.getElementById('expandTemplatesBtn').onclick = function() {
                            const grid = document.getElementById('templateGrid');
                            grid.style.maxHeight = '';
                            grid.style.overflow = '';
                            document.getElementById('templateExpandOverlay').style.display = 'none';
                        };

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
                                const firstBtn = document.querySelector('.firstAddLeadListBtn');
                                if (firstBtn) {
                                    firstBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    firstBtn.disabled = false;
                                    firstBtn.focus();
                                }

                            });
                        });
                    </script>
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
    <script src="assets/js/select2/addFilters.js"></script>

<?php include('partial/footer-end.php'); ?>