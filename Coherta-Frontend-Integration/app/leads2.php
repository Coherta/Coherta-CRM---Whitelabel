<?php
require_once "./base.php";
$PageTitle = "Leads";
$sidebar_close = true;
if(isset($_GET["action"]) && isset($_GET["id"])) {
    $client->post("pipeline/leads/delete", ["pipeline_id" => $_GET["id"]], [
            'Authorization: Bearer ' . ($_SERVER['HTTP_AUTHORIZATION'] ?? '')
    ]);
    die();
}
$data = [];
$leads = [];
$title = "";
if(
        isset($_GET["day_id"]) ||
        isset($_GET["pipeline_id"]) ||
        isset($_GET["get_list"])) {
    $pipeline_id_str = "";
    if(isset($_GET["pipeline_id"])) {
        $pipeline_id = (int)$_GET["pipeline_id"];
        $pipeline_id_str = "pipeline_id=" . $pipeline_id . "&";
    }
    if(isset($_GET["day_id"])) {
        $day_id = (int)$_GET["day_id"];
        $pipeline_id_str .= "day_id=" . $day_id . "&";
    }
    if(isset($_GET["stage_id"])) {
        $stage_id = (int)$_GET["stage_id"];
        $pipeline_id_str .= "stage_id=" . $stage_id . "&";
    }
    $limit = "";
    if(!isset($_GET["get_list"])) {
        $limit = "limit=1&";
    } else {
        $limit = "limit=".(int)$_GET['limit']."&";
    }
    $leads = $client->get("pipeline/all/leads?{$pipeline_id_str}{$limit}offset=" . (isset($_GET["offset"]) ? (int)$_GET["offset"] : "0"));
} else {
    $leads = $client->get("pipeline/all/leads?limit=1&offset=0");
}

var_dump($leads);
die();

$total_count = $client->get("pipeline/all/leads/count?{$pipeline_id_str}");
$leadCount = 0;
if (is_array($total_count)) {
    if (isset($total_count['count'])) {
        $leadCount = (int)$total_count['count'];
    } elseif (isset($total_count[0]['total_leads'])) {
        $leadCount = (int)$total_count[0]['total_leads'];
    } elseif (isset($total_count[0]['count'])) {
        $leadCount = (int)$total_count[0]['count'];
    }
}

$pipelineFilterForEnrich = null;
if ($leadCount === 0 && isset($pipeline_id) && $pipeline_id > 0) {
    if (isset($leads['pipeline_filter'])) {
        $pipelineFilterForEnrich = $leads['pipeline_filter'];
    } elseif (isset($leads['filters'])) {
        $pipelineFilterForEnrich = $leads['filters'];
    } elseif (isset($leads['filter'])) {
        $pipelineFilterForEnrich = $leads['filter'];
    }
}

if(isset($_GET["get_list"])) {
    if(isset($leads["data"]))
        echo json_encode($leads["data"]);
    else echo json_encode([]);
    die();
}
elseif(isset($_GET["pipeline_id"])) {
    $title = $leads["title"] ?? "";
    $PageTitle = $title;
}
include('partial/header.php');

$columns = [
        ['key' => 'connected_users', 'type' => 'user_images', 'label' => 'Connected Colleagues'],
        ['key' => 'company_name', 'type' => 'string', 'label' => 'Company Name'],
        ['key' => 'vat_code', 'type' => 'combine', 'combine_with' => 'country_code', 'label' => 'VAT code'],
        ['key' => 'added', 'type' => 'date', 'label' => 'Added'],
        ['key' => 'last_updated', 'type' => 'date', 'label' => 'Last Updated'],
        ['key' => 'stage', 'type' => 'enum', 'enum_value' => 'enum_stages', 'label' => 'Stage'],
        ['key' => 'start_date', 'type' => 'string', 'label' => 'Company Founded'],
        ['key' => 'company_address', 'type' => 'string', 'label' => 'Company Address'],
        ['key' => 'company_postal', 'type' => 'string', 'label' => 'Company Postal'],
        ['key' => 'company_city', 'type' => 'string', 'label' => 'Company City'],
        ['key' => 'company_email', 'type' => 'string', 'label' => 'Company Email'],
        ['key' => 'company_phone', 'type' => 'string', 'label' => 'Company Phone'],
        ['key' => 'company_website', 'type' => 'string', 'label' => 'Company Website'],
        ['key' => 'company_form', 'type' => 'string', 'label' => 'Company Form'],
        ['key' => 'category_code', 'type' => 'string', 'label' => 'Company Category Code'],
        ['key' => 'category_text', 'type' => 'string', 'label' => 'Company Category Text'],
        ['key' => 'number_of_employees', 'type' => 'numeric', 'label' => 'Number Of Employees'],
        ['key' => 'number_of_contacts', 'type' => 'numeric', 'label' => 'Number Of Contacts'],
        ['key' => 'tasks_next', 'type' => 'string', 'label' => 'Next Task'],
        ['key' => 'company_profit_before_tax', 'type' => 'numeric', 'label' => 'Company Profit Before Tax'],
        ['key' => 'key_employee.employee.contact_name', 'type' => 'string', 'label' => 'Key Contact Name'],
        ['key' => 'key_employee.employee.first_name', 'type' => 'string', 'label' => 'Key Contact First Name'],
        ['key' => 'key_employee.employee.middle_name', 'type' => 'string', 'label' => 'Key Contact Middle Name'],
        ['key' => 'key_employee.employee.last_name', 'type' => 'string', 'label' => 'Key Contact Last Name'],
        ['key' => 'key_employee.employee.email', 'type' => 'string', 'label' => 'Key Contact Email'],
        ['key' => 'key_employee.employee.phone', 'type' => 'string', 'label' => 'Key Contact Phone'],
        ['key' => 'key_employee.employee.title', 'type' => 'string', 'label' => 'Key Contact Job Title'],
        ['key' => 'key_employee.employee.linkedin', 'type' => 'string', 'label' => 'Key Contact LinkedIn'],
        ['key' => 'financials_equity', 'type' => 'string', 'label' => 'Financials: Equity'],
        ['key' => 'financials_net_income', 'type' => 'string', 'label' => 'Financials: Net income'],
        ['key' => 'financials_revenue', 'type' => 'string', 'label' => 'Financials: Revenue'],
        ['key' => 'financials_liquidity_ratio', 'type' => 'string', 'label' => 'Financials: Liquidity ratio'],
        ['key' => 'financials_cash_ratio', 'type' => 'string', 'label' => 'Financials: Cash ratio'],
        ['key' => 'financials_return_on_equity', 'type' => 'string', 'label' => 'Financials: Return on equity'],
        ['key' => 'financials_debt_ratio', 'type' => 'string', 'label' => 'Financials: Debt ratio'],
        ['key' => 'financials_ebitda', 'type' => 'string', 'label' => 'Financials: EBITDA'],
        ['key' => 'financials_cash', 'type' => 'string', 'label' => 'Financials: Cash and Equivalents'],
        ['key' => 'google_maps_address', 'type' => 'string', 'label' => 'Google Maps Address'],
        ['key' => 'google_maps_display_name', 'type' => 'string', 'label' => 'Google Maps Display Name'],
        ['key' => 'google_maps_price_range_currency', 'type' => 'string', 'label' => 'Google Maps Currency'],
        ['key' => 'google_maps_price_range_start', 'type' => 'string', 'label' => 'Google Maps Price Range Start'],
        ['key' => 'google_maps_price_range_end', 'type' => 'string', 'label' => 'Google Maps Price Range End'],
        ['key' => 'google_maps_primary', 'type' => 'string', 'label' => 'Google Maps Primary Category'],
        ['key' => 'google_maps_phone', 'type' => 'string', 'label' => 'Google Maps Phone number'],
        ['key' => 'google_maps_rating', 'type' => 'string', 'label' => 'Google Maps Rating'],
        ['key' => 'google_maps_rating_count', 'type' => 'string', 'label' => 'Google Maps Rating Count'],

];

if(isset($company_data["plugins"][3]) && $company_data["plugins"][3]["installed"]) {
    $website_columns = [];
    $website_columns = [
            ['key' => 'website_analytics_tool', 'type' => 'array', "subkey" => "name", 'label' => 'Analytics Tool'],
            ['key' => 'website_chat_platform', 'type' => 'array', "subkey" => "name", 'label' => 'Chat Platform'],
            ['key' => 'website_cms_type', 'type' => 'array', "subkey" => "name", 'label' => 'CMS Type'],
            ['key' => 'website_framework', 'type' => 'array', "subkey" => "name", 'label' => 'Framework'],
            ['key' => 'website_ecommerce', 'type' => 'array', "subkey" => "name", 'label' => 'Webshop Platform'],
            ['key' => 'payment_systems', 'type' => 'text', 'label' => 'Payment Providers'],
            ['key' => 'website_widgets', 'type' => 'array', "subkey" => "name", 'label' => 'Widgets'],
            ['key' => 'website_software_systems', 'type' => 'array', "subkey" => "name", 'label' => 'Software Systems'],
            ['key' => 'trustpilot_reviews', 'type' => 'numeric', 'label' => 'Reviews on Trustpilot'],
            ['key' => 'trustpilot_score', 'type' => 'numeric', 'label' => 'TrustScore'],
            ['key' => 'alternative_website', 'type' => 'text', 'label' => 'Alternative Website'],
            ['key' => 'lighthouse_performance', 'type' => 'numeric', 'label' => 'Lighthouse Performance'],
            ['key' => 'lighthouse_accessibility', 'type' => 'numeric', 'label' => 'Lighthouse Accessibility'],
            ['key' => 'lighthouse_best_practices', 'type' => 'numeric', 'label' => 'Lighthouse Best Practices'],
            ['key' => 'lighthouse_seo', 'type' => 'numeric', 'label' => 'Lighthouse SEO'],
            ['key' => 'lighthouse_first_contentful_paint_seconds', 'type' => 'numeric', 'label' => 'Lighthouse FCP (s)'],
            ['key' => 'lighthouse_largest_contentful_paint_seconds', 'type' => 'numeric', 'label' => 'Lighthouse LCP (s)'],
            ['key' => 'lighthouse_total_blocking_time_ms', 'type' => 'numeric', 'label' => 'Lighthouse TBT (ms)'],
            ['key' => 'lighthouse_cumulative_layout_shift', 'type' => 'numeric', 'label' => 'Lighthouse CLS'],
            ['key' => 'lighthouse_speed_index_seconds', 'type' => 'numeric', 'label' => 'Lighthouse Speed Index (s)'],
    ];
    $columns = array_merge($columns, $website_columns);
}
$active_columns = array_filter($columns, function($col) use ($leads) {
    return isset($leads["columns"]) && in_array($col['key'], $leads["columns"]);
});
$columns = array_map(function($col) use ($leads) {
    if (isset($leads["columns"]) && in_array($col['key'], $leads["columns"])) {
        $col['checked'] = 'yes';
    }
    return $col;
}, $columns);
if (isset($leads["columns"])) {
    $active_keys = array_column($active_columns, 'key');
    $active = [];
    $inactive = [];
    foreach ($columns as $col) {
        if (in_array($col['key'], $active_keys)) {
            $active[] = $col;
        } else {
            $inactive[] = $col;
        }
    }
    usort($active, function($a, $b) use ($leads) {
        return array_search($a['key'], $leads["columns"]) <=> array_search($b['key'], $leads["columns"]);
    });
    $columns = array_merge($active, $inactive);
}
?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/datatables.css">
    <script>
        const activeColumns = <?php
                $activeCols = [];

                foreach ($columns as $col) {
                    if (isset($col["checked"]) && $col["checked"] == "yes") {
                        $activeCols[] = array_filter($col, function($k) {
                            return in_array($k, ['key', 'type', 'subkey', 'combine_with', 'enum_value']);
                        }, ARRAY_FILTER_USE_KEY);
                    }
                }
                echo json_encode($activeCols);
                ?>;
    </script>

<?php include('partial/loader.php'); ?>


    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php'); ?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <!-- Ajax data source array start-->
                        <div class="col-sm-12">
                            <div class="card leads-shell-card">
                                <div class="card-body leads-shell-body">
                                    <div class="leads-hero">
                                        <div class="leads-hero-top">
                                            <div class="leads-hero-copy">
                                                <?php
                                                if(!isset($_GET["pipeline_id"])) {
                                                    $page_title = "Leads overview";
                                                } else {
                                                    $page_title = "Leads overview - " . htmlspecialchars($title);
                                                }
                                                ?>
                                                <h1 class="leads-pageTitle"><?php echo $page_title; ?></h1>
                                                <p>Built for dense lead data, faster scanning, and quick actions without wasting horizontal space.</p>
                                            </div>
                                        </div>
                                        <div class="leads-actionbar">
                                            <div class="leads-actionbar-left">
                                                <?php if(isset($_GET["pipeline_id"])) { ?>
                                                    <a href="leads-outreach?pipeline_id=<?php echo (int)$_GET['pipeline_id']; ?>" class="btn btn-primary btn-start-outreach">
                                                        <i class="fa fa-paper-plane"></i>
                                                        <span>Start Outreach</span>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                            <div class="leads-actionbar-right">
                                                <?php if(isset($_GET["pipeline_id"])) { ?>
                                                    <button type="button" class="btn btn-light btn-leads-action" data-bs-toggle="modal" data-bs-target="#addLeadModal" onClick="showStartSelection();">
                                                        <i class="fa fa-plus"></i>
                                                        <span>Add Lead</span>
                                                    </button>
                                                    <a href="wizard-advanced.php?type=edit&pipeline_id=<?php echo $_GET['pipeline_id']; ?>" class="btn btn-light btn-leads-action d-inline-flex align-items-center gap-2 btn-change-filters">
                                                        <i class="fa fa-sliders"></i>
                                                        <span>Change Filters</span>
                                                    </a>
                                                    <button type="button" class="btn btn-light btn-leads-action" data-bs-toggle="modal" data-bs-target="#editLeadListModal">
                                                        <i class="fa fa-gear"></i>
                                                        <span>Settings</span>
                                                    </button>
                                                    <button type="button" class="btn btn-light btn-leads-action" data-bs-toggle="modal" data-bs-target="#exportLeadListModal">
                                                        <i class="fa fa-download"></i>
                                                        <span>Export</span>
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-light btn-leads-action" data-bs-toggle="modal" data-bs-target="#editLeadListModal">
                                                        <i class="fa fa-gear"></i>
                                                        <span>Settings</span>
                                                    </button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card leads-table-card">
                                    <div class="card-body leads-table-card-body">
                                        <div class="leads-table-toolbar">
                                            <div class="leads-table-toolbar-copy">
                                                <h3>Lead list</h3>
                                                <p>Search, scan, and act on large lead sets with fewer clicks.</p>
                                            </div>
                                            <div class="leads-search-slot" id="leadsSearchSlot"></div>
                                        </div>
                                        <div class="leads-bulkbar" id="leadsBulkBar" aria-live="polite">
                                            <div class="leads-bulkbar-left">
                                                <span class="leads-selected-pill"><strong id="leadsSelectedCount">0</strong> selected</span>
                                            </div>
                                            <div class="leads-bulkbar-actions">
                                                <select class="form-select form-select-sm" id="leadsBulkStage" aria-label="Choose stage">
                                                    <option value="">Change stage...</option><option value="0">Initial</option>
                                                    <option value="1">Contacted</option>
                                                    <option value="2">Qualified</option>
                                                    <option value="3">Proposal</option>
                                                    <option value="4">Negotiation</option>
                                                </select>
                                                <button type="button" class="btn btn-primary btn-sm" id="leadsBulkChangeStage" disabled>Change stage</button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" id="leadsBulkDelete" disabled>Delete selected</button>
                                                <button type="button" class="btn btn-light btn-sm" id="leadsBulkClear" disabled>Clear</button>
                                            </div>
                                            <div class="leads-bulk-message" id="leadsBulkMessage"></div>
                                        </div>
                                        <?php if(isset($_GET['pipeline_id']) && $leadCount === 0 && $leads['filter'] == '{"filters": []}') { ?>
                                        <div class="lead-empty-state" id="leadEmptyState">
                                            <div class="lead-empty-hero">
                                                <div class="lead-empty-copy">
                                                    <span class="lead-empty-badge">Preparing your lead list</span>
                                                    <h3>Your leads are being enriched right now</h3>
                                                    <p>We are collecting and enriching leads for this list. While that runs, you can watch the countdown or play a quick game below.</p>
                                                    <div class="lead-empty-countdown">
                                                        Leads related to this list will be available in <strong><span id="leadCountdown">60</span> seconds</strong>
                                                    </div>
                                                    <div class="lead-empty-progress" aria-hidden="true">
                                                        <div class="lead-empty-progress-bar" id="leadEmptyProgressBar"></div>
                                                    </div>
                                                    <div class="lead-empty-tip">Tip: the list refreshes automatically as soon as data becomes available.</div>
                                                </div>
                                                <div class="lead-empty-visual" aria-hidden="true">
                                                    <div class="orb orb-1"></div>
                                                    <div class="orb orb-2"></div>
                                                    <div class="orb orb-3"></div>
                                                    <div class="scanner-ring"></div>
                                                    <div class="scanner-core"></div>
                                                </div>
                                            </div>
                                            <div class="lead-mini-game">
                                                <div class="lead-mini-game-head">
                                                    <div>
                                                        <h4>Mini game: catch the signals</h4>
                                                        <p>Click the moving dots before they disappear.</p>
                                                    </div>
                                                    <div class="lead-mini-game-stats">
                                                        <span>Score: <strong id="signalScore">0</strong></span>
                                                        <span>Hits left: <strong id="signalLives">10</strong></span>
                                                    </div>
                                                </div>
                                                <div class="signal-playground" id="signalPlayground">
                                                    <button type="button" class="signal-start-btn" id="signalStartBtn">Start game</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive" id="leadTableWrap" style="display:none;">
                                            <?php } else { ?>
                                            <div class="table-responsive" id="leadTableWrap">
                                                <?php } ?>
                                                <table class="display datatables" id="ajax-data-array">
                                                    <thead>
                                                    <tr>
                                                        <?php
                                                        foreach ($columns as $col) {
                                                            if (isset($col["checked"]) && $col["checked"] == "yes") {
                                                                echo "<th>" . htmlspecialchars($col["label"]) . "</th>";
                                                            }
                                                        }
                                                        ?>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tfoot>
                                                    <tr>
                                                        <?php
                                                        foreach ($columns as $col) {
                                                            if (isset($col["checked"]) && $col["checked"] == "yes") {
                                                                echo "<th>" . htmlspecialchars($col["label"]) . "</th>";
                                                            }
                                                        }
                                                        ?>
                                                        <th></th>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            <div class="leads-table-bottom-select" id="leadsTableBottomSelect">
                                                <label class="leads-select-all-wrap" for="leadsSelectAllRows">
                                                    <input type="checkbox" id="leadsSelectAllRows" class="leads-row-master-check">
                                                    <span>Select all visible</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('partial/footer.php'); ?>
            </div>
        </div>
        <div class="modal fade" id="invalidSubscription" tabindex="-1" aria-labelledby="invalidSubscription" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="invalidSubscription" method="post" action="leads.php">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="invalidSubscription">Invalid Subscription</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert" role="alert">
                                The pipeline data you want to access requires another subscription. Please contact support for more information about which subscription you should purchase.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Add Lead Modal -->
        <div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="addLeadForm" method="post" action="leads.php">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addLeadModalLabel">Add New Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div class="row mb-3 startSelection">
                                <div class="col-md-4 d-flex flex-column align-items-center justify-content-center" style="border-right:1px solid #eee; cursor:pointer;" onclick="document.getElementById('optionA').checked = true; showOption('A');">
                                    <div style="font-size:2em; color:#007bff;">
                                        <i class="fa fa-user-plus"></i>
                                    </div>
                                    <div class="fw-bold mt-2">Add single Lead</div>
                                    <input type="radio" id="optionA" name="addLeadOption" value="A" checked style="pointer-events:none;display:none;">
                                </div>
                                <div class="col-md-4 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer;" onclick="document.getElementById('optionB').checked = true; showOption('B');">
                                    <div style="font-size:2em; color:#28a745;">
                                        <i class="fa fa-file-csv"></i>
                                    </div>
                                    <div class="fw-bold mt-2">Upload CSV file</div>
                                    <input type="radio" id="optionB" name="addLeadOption" value="B" style="pointer-events:none;display:none;">
                                </div>
                                <div class="col-md-4 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer;" onclick="document.getElementById('optionC').checked = true; showOption('C');">
                                    <div style="font-size:2em; color:#28a745;">
                                        <i class="fa fa-paper-plane"></i>
                                    </div>
                                    <div class="fw-bold mt-2">Add Leads through Email</div>
                                    <input type="radio" id="optionC" name="addLeadOption" value="C" style="pointer-events:none;display:none;">
                                </div>
                            </div>
                            <div id="addSingleLeadSection" style="display:none;">
                                <div class="mb-3">
                                    <label for="cvrNumber" class="form-label">CVR Number</label>
                                    <input type="text" class="form-control" id="cvrNumber" name="cvrNumber" required>
                                    <button type="button" class="btn btn-link mt-2" id="verifyCvrBtn">Verify</button>
                                </div>
                                <div class="mb-3" id="leadNameGroup" style="display:none;">
                                    <label for="leadName" class="form-label">Lead Name</label>
                                    <input type="text" class="form-control" id="leadName" name="leadName" readonly>
                                </div>
                                <div class="alert alert-danger" id="cvrError" style="display:none;"></div>
                                <button class="btn btn-secondary" id="cvrMove" style="display:none;">Continue and move Lead to this list</button>
                            </div>
                            <div id="uploadCsvSection" style="display:none;">
                                <div class="mb-3">
                                    <label for="csvFileInput" class="form-label">Upload CSV file</label>
                                    <input type="file" class="form-control" id="csvFileInput" accept=".csv">
                                    <br />
                                    <a href="assets/csv/csv-sample-upload.csv">Download our Sample CSV-file</a>
                                </div>
                                <div id="csvColumnSelectSection" style="display:none;">
                                    <label for="csvColumnSelect" class="form-label">Select column to match upon:</label>
                                    <select class="form-select" id="csvColumnSelect"></select>
                                    <div class="mt-2" id="csvRowCount"></div>
                                    <button type="button" class="btn btn-primary mt-3" id="csvNextBtn">Next</button>
                                </div>
                                <div id="csvProgressSection" style="display:none;">
                                    <div class="progress">
                                        <div id="csvProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="mt-2" id="csvProgressText"></div>
                                    <a href="javascript:window.location.reload();" class="btn btn-primary">View added leads</a>
                                </div>
                            </div>
                            <div id="throughEmailSection" style="display:none;">
                                <div id="createBccEmailSection" style="display:none;">
                                    <div class="mb-3">
                                        <h4>Add Unique BCC Email</h4>
                                        <p>
                                            This feature allows you to:
                                        <ol>
                                            <li>You can now enable automatic lead enrichment based on inbound leads from your Website.</li>
                                            <li>Add Coherta's BCC email to your leadgen form, and we will automatically pick up any new lead you receive, enrich it, and then ship it to your CRM-system (if you have connected your CRM to Coherta).</li>
                                        </ol>
                                        </p>
                                        <button type="button" class="btn btn-primary" id="enableBccEmailBtn">
                                            Click here to enable your own unique Coherta BCC E-mail address
                                        </button>
                                    </div>
                                </div>
                                <div id="createdBccEmailSection" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label">Your unique BCC email is</label>
                                        <div style="font-weight:bold;font-size:1.1em;">{CohertaBCCMail}</div>
                                    </div>
                                    <div>
                                        <div class="pending-email-setup alert alert-info" style="margin-bottom:1em;">
                                            We are still configuring your e-mail, this may take up to 120 seconds before it has been completed. Please check again in a few minutes.
                                        </div>
                                        <div class="completed-email-setup alert" style="margin-bottom:1em; display:none;">
                                            <p>You can BCC the above email address when sending emails to add new leads to this list.</p>
                                            <blockquote><ul>
                                                    <li>Company Name: {Company Name}</li>
                                                    <li>Company VAT: {Company VAT}</li>
                                                    <li>CVR: {Company VAT}</li>
                                                    <li>CVR Nummer: {Company VAT}</li>
                                                </ul>
                                            </blockquote>
                                            <p>We will then pick up the lead and add it to your list.</p>
                                            <p>Be aware, it might take up to 5 minutes from the email is received by us, until the lead is visible in the overview.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="addLeadBtn" disabled>Add to List</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Edit Lead List Modal -->
        <div class="modal fade" id="editLeadListModal" tabindex="-1" aria-labelledby="editLeadListModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="post" action="leads.php" id="leadSettingsForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editLeadListModalLabel">Settings</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="settings-section">
                                <div class="settings-section-head">
                                    <div>
                                        <h6>List name</h6>
                                        <p>Changes save automatically as you type.</p>
                                    </div>
                                </div>
                                <?php
                                if(isset($_GET['pipeline_id'])) {
                                    echo '<input type="hidden" name="leadListId" value="' . (int)$_GET['pipeline_id'] . '">';
                                    ?>
                                    <input type="text" class="form-control" id="leadListName" name="leadListName" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                                    <input type="hidden" name="pipeline_id" value="<?php echo isset($_GET['pipeline_id']) ? (int)$_GET['pipeline_id'] : ''; ?>">
                                    <?php
                                } else {
                                    ?>
                                    <input type="hidden" name="pipeline_id" value="-1">
                                    <?php
                                }
                                ?>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-head">
                                    <div>
                                        <h6>Visible columns</h6>
                                        <p>Click a category to reveal the fields inside it. Changes save automatically.</p>
                                    </div>
                                </div>
                                <?php
                                $column_categories = [
                                        'Relationship' => ['connected_users', 'stage', 'tasks_next'],
                                        'Company' => ['company_name', 'company_form', 'start_date', 'added', 'last_updated'],
                                        'Identifiers' => ['vat_code', 'category_code', 'category_text'],
                                        'Address' => ['company_address', 'company_postal', 'company_city'],
                                        'Contact' => ['company_email', 'company_phone', 'company_website'],
                                        'People' => ['number_of_employees', 'number_of_contacts', 'key_employee.employee.contact_name', 'key_employee.employee.first_name', 'key_employee.employee.middle_name', 'key_employee.employee.last_name', 'key_employee.employee.email', 'key_employee.employee.phone', 'key_employee.employee.title', 'key_employee.employee.linkedin'],
                                        'Financials' => ['company_profit_before_tax', 'financials_equity', 'financials_net_income', 'financials_revenue', 'financials_liquidity_ratio', 'financials_cash_ratio', 'financials_return_on_equity', 'financials_debt_ratio', 'financials_ebitda', 'financials_cash'],
                                        'Google Maps' => ['google_maps_address', 'google_maps_display_name', 'google_maps_price_range_currency', 'google_maps_price_range_start', 'google_maps_price_range_end', 'google_maps_primary', 'google_maps_phone', 'google_maps_rating', 'google_maps_rating_count'],
                                        'Website' => ['website_analytics_tool', 'website_chat_platform', 'website_cms_type', 'website_framework', 'website_ecommerce', 'payment_systems', 'website_widgets', 'website_software_systems', 'trustpilot_reviews', 'trustpilot_score', 'alternative_website', 'lighthouse_performance', 'lighthouse_accessibility', 'lighthouse_best_practices', 'lighthouse_seo', 'lighthouse_first_contentful_paint_seconds', 'lighthouse_largest_contentful_paint_seconds', 'lighthouse_total_blocking_time_ms', 'lighthouse_cumulative_layout_shift', 'lighthouse_speed_index_seconds'],
                                ];
                                $columns_by_key = [];
                                foreach ($columns as $column_item) {
                                    $columns_by_key[$column_item['key']] = $column_item;
                                }
                                ?>
                                <div class="settings-category-stack">
                                    <?php $category_index = 0; foreach ($column_categories as $category_name => $category_keys) { $category_index++; ?>
                                        <div class="settings-category-card is-collapsed" data-category-card>
                                            <button type="button" class="settings-category-toggle" data-category-toggle aria-expanded="false" aria-controls="settingsCategory<?php echo $category_index; ?>">
                                                <div class="settings-category-toggle-copy">
                                                    <h6><?php echo htmlspecialchars($category_name); ?></h6>
                                                    <span><?php echo count(array_filter($category_keys, function($key) use ($columns_by_key) { return isset($columns_by_key[$key]); })); ?> fields</span>
                                                </div>
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                            <div class="settings-category-body" id="settingsCategory<?php echo $category_index; ?>">
                                                <ul class="list-group leads-settings-list column-list-group" data-category="<?php echo htmlspecialchars($category_name); ?>">
                                                    <?php foreach ($category_keys as $key) { if (!isset($columns_by_key[$key])) { continue; } $col = $columns_by_key[$key]; ?>
                                                        <li class="list-group-item d-flex align-items-center" data-column="<?php echo $col['key']; ?>">
                                                            <input class="form-check-input me-2 leads-column-checkbox" name="columns[]" <?php if (isset($col["checked"]) && $col["checked"] == "yes") { echo "checked"; } ?> type="checkbox" value="<?php echo $col['key']; ?>" id="col_<?php echo $col['key']; ?>">
                                                            <label class="form-check-label flex-grow-1" for="col_<?php echo $col['key']; ?>"><?php echo $col['label']; ?></label>
                                                            <span class="handle ms-auto">&#9776;</span>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if(isset($_GET["pipeline_id"])) { ?>
                                <div class="settings-section">
                                    <div class="settings-section-head">
                                        <div>
                                            <h6>Sync with CRM</h6>
                                            <p>This now sits after Visible Columns and saves instantly when changed.</p>
                                        </div>
                                    </div>
                                    <div class="settings-sync-grid">
                                        <label class="settings-sync-card">
                                            <input class="form-check-input" id="edo-ani" type="radio" name="rdo-ani" <?php if ($leads["syncronization_enabled"]) echo "checked"; ?>>
                                            <div>
                                                <strong>Sync with CRM</strong>
                                                <span>Recommended when this list should stay aligned across systems.</span>
                                            </div>
                                        </label>
                                        <label class="settings-sync-card">
                                            <input class="form-check-input" id="edo-ani1" type="radio" name="rdo-ani" <?php if (!$leads["syncronization_enabled"]) echo "checked"; ?>>
                                            <div>
                                                <strong>Do not sync</strong>
                                                <span>Keep this list isolated from CRM updates.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="settings-section settings-section-danger">
                                    <div class="settings-section-head">
                                        <div>
                                            <h6>Danger zone</h6>
                                            <p>Permanent list actions are kept here to avoid accidental clicks.</p>
                                        </div>
                                    </div>
                                    <button type="button" data-count="<?php echo htmlspecialchars(json_encode($total_count), ENT_QUOTES, 'UTF-8'); ?>" data-pipeline-id="<?php echo $_GET["pipeline_id"] ?>" class="btn btn-outline-danger delete-pipeline">
                                        <i class="fa fa-trash"></i>
                                        <span>Delete List</span>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="exportLeadListModal" tabindex="-1" aria-labelledby="exportLeadListModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportLeadListModalLabel">Export</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body export-choice-body">
                        <button type="button" class="export-choice-card" data-export-trigger="csv">
                            <i class="fa fa-file-csv"></i>
                            <strong>Export as CSV</strong>
                        </button>
                        <button type="button" class="export-choice-card" data-export-trigger="excel">
                            <i class="fa fa-file-excel"></i>
                            <strong>Export as Excel</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="pipeline-toast-stack position-fixed top-0 end-0 p-3">
            <div id="pipelineChangeToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="pipelineChangeToastBody">Pipeline Settings have been updated</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php include('partial/scripts.php'); ?>
        <script>
            function showPipelineToast(message) {
                const toastEl = document.getElementById('pipelineChangeToast');
                const bodyEl = document.getElementById('pipelineChangeToastBody');
                if (!toastEl || !bodyEl) return;
                bodyEl.textContent = message;

                if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                    if (typeof bootstrap.Toast.getOrCreateInstance === 'function') {
                        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2200 }).show();
                        return;
                    }
                    try {
                        (new bootstrap.Toast(toastEl, { delay: 2200 })).show();
                        return;
                    } catch (e) {}
                }

                if (window.jQuery && typeof window.jQuery.fn.toast === 'function') {
                    window.jQuery(toastEl).toast({ delay: 2200 });
                    window.jQuery(toastEl).toast('show');
                }
            }
        </script>
        <script src="assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2/moment.min.js"></script>
        <script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>
        <script src="assets/js/datatable/datatables/datatable.custom.js"></script>
        <script src="assets/js/datepicker/date-picker/datepicker.js"></script>
        <script src="assets/js/datepicker/date-picker/datepicker.en.js"></script>
        <script src="assets/js/datepicker/date-picker/datepicker.custom.js"></script>
        <link rel="stylesheet" type="text/css" href="assets/css/vendors/date-picker.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script>
            let csvData = [];
            let csvHeaders = [];
            let csvSelectedColumn = '';
            const bearerToken = document.body.getAttribute('session_id');
            const pipelineId = "<?php echo isset($_GET['pipeline_id']) ? (int)$_GET['pipeline_id'] : ''; ?>";
            const addSingleLeadSection = document.getElementById('addSingleLeadSection');
            const uploadCsvSection = document.getElementById('uploadCsvSection');
            const throughEmailSection = document.getElementById('throughEmailSection');
            const optionA = document.getElementById('optionA');
            const optionB = document.getElementById('optionB');
            const optionC = document.getElementById('optionC');
            const addLeadBtn = document.getElementById('addLeadBtn');
            <?php
            if($company_data["method"] == "stripe" && $company_data['stripe_subscription'] == "") {
                echo "const exportOnlyFirst5 = true;";
            } else {
                echo "const exportOnlyFirst5 = false;";
            }
            ?>

            function getLeadRowId(rowData) {
                if (!rowData || typeof rowData !== 'object') return '';
                return rowData.id || rowData.pipeline_company_id || rowData.crm_pipeline_companies_id || rowData.lead_id || rowData.company_uid || rowData.uid || '';
            }
            function normalizeLeadRowId(value) { return String(value || '').trim(); }
            function initLeadBulkActions() {
                const bulkBar = document.getElementById('leadsBulkBar');
                const masterCheck = document.getElementById('leadsSelectAllRows');
                const selectedCount = document.getElementById('leadsSelectedCount');
                const stageSelect = document.getElementById('leadsBulkStage');
                const changeStageBtn = document.getElementById('leadsBulkChangeStage');
                const deleteBtn = document.getElementById('leadsBulkDelete');
                const clearBtn = document.getElementById('leadsBulkClear');
                const messageBox = document.getElementById('leadsBulkMessage');
                const selectedIds = new Set();
                if (!bulkBar || !window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
                function setMessage(type, text) { if (!messageBox) return; messageBox.className = 'leads-bulk-message ' + (type || ''); messageBox.textContent = text || ''; }
                function getTable() { if (!jQuery.fn.DataTable.isDataTable('#ajax-data-array')) return null; return jQuery('#ajax-data-array').DataTable(); }
                function getVisibleIds() { const table = getTable(); const ids = []; if (!table) return ids; table.rows({ page: 'current' }).every(function() { const id = normalizeLeadRowId(getLeadRowId(this.data())); if (id) ids.push(id); }); return ids; }
                function updateBulkState() {
                    const count = selectedIds.size;
                    if (selectedCount) selectedCount.textContent = String(count);
                    bulkBar.classList.toggle('has-selection', count > 0);
                    [changeStageBtn, deleteBtn, clearBtn].forEach(btn => { if (btn) btn.disabled = count === 0; });
                    const visibleIds = getVisibleIds();
                    const selectedVisible = visibleIds.filter(id => selectedIds.has(id)).length;
                    if (masterCheck) { masterCheck.checked = visibleIds.length > 0 && selectedVisible === visibleIds.length; masterCheck.indeterminate = selectedVisible > 0 && selectedVisible < visibleIds.length; }
                    document.querySelectorAll('.leads-row-check').forEach(cb => { const id = normalizeLeadRowId(cb.value); cb.checked = selectedIds.has(id); cb.closest('tr')?.classList.toggle('leads-row-selected', cb.checked); });
                }
                function ensureSelectionColumnChrome() {
                    const tableEl = document.getElementById('ajax-data-array');
                    if (!tableEl) return;
                    tableEl.querySelectorAll('thead tr, tfoot tr').forEach(function(row) {
                        if (row.querySelector('.leads-select-column-head')) return;
                        const th = document.createElement('th');
                        th.className = 'leads-select-column-head';
                        th.setAttribute('aria-label', 'Select rows');
                        row.insertBefore(th, row.firstElementChild);
                    });
                }
                function makeDeleteClickTargetsLarger(rowNode) {
                    if (!rowNode) return;
                    const actionCell = rowNode.querySelector('td:last-child');
                    if (!actionCell) return;
                    actionCell.classList.add('leads-row-action-cell');
                    actionCell.querySelectorAll('a, button').forEach(function(el) {
                        const text = (el.textContent || '').trim().toLowerCase();
                        const href = (el.getAttribute('href') || '').toLowerCase();
                        const cls = (el.className || '').toString().toLowerCase();
                        const title = (el.getAttribute('title') || el.getAttribute('aria-label') || '').toLowerCase();
                        if (text === 'x' || text === '×' || href.includes('delete') || cls.includes('delete') || title.includes('delete') || el.querySelector('.fa-trash, .fa-times, .fa-xmark')) {
                            el.classList.add('leads-row-delete-hit');
                            if (!el.getAttribute('aria-label')) el.setAttribute('aria-label', 'Delete lead');
                        }
                    });
                }
                function hydrateRowCheckboxes() {
                    const table = getTable(); if (!table) return;
                    ensureSelectionColumnChrome();
                    table.rows({ page: 'current' }).every(function() {
                        const rowData = this.data(); const id = normalizeLeadRowId(getLeadRowId(rowData)); const node = this.node();
                        if (!id || !node) return;
                        node.setAttribute('data-lead-id', id);
                        makeDeleteClickTargetsLarger(node);
                        let selectCell = node.querySelector('td.leads-select-column-cell');
                        if (!selectCell) {
                            selectCell = document.createElement('td');
                            selectCell.className = 'leads-select-column-cell';
                            node.insertBefore(selectCell, node.firstElementChild);
                        }
                        if (selectCell.querySelector('.leads-row-check')) return;
                        const label = document.createElement('label');
                        label.className = 'leads-row-check-wrap';
                        label.innerHTML = '<input type="checkbox" class="leads-row-check" value="' + id.replace(/"/g, '&quot;') + '" aria-label="Select lead">';
                        selectCell.appendChild(label);
                    });
                    updateBulkState();
                }
                async function bulkApiCall(action, payload) {
                    const endpoint = action === 'stage' ? 'pipeline/leads/stage' : 'pipeline/leads/delete';
                    const method = action === 'stage' ? 'PATCH' : 'POST';
                    const ids = Array.from(selectedIds); let failed = 0;
                    for (const id of ids) {
                        const body = Object.assign({}, payload, { id: id, pipeline_id: pipelineId });
                        const response = await fetch('https://api.coherta.com/' + endpoint, { method: method, headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + bearerToken }, body: JSON.stringify(body) });
                        if (!response.ok) failed++;
                    }
                    return { processed: ids.length, failed: failed };
                }
                function setLoading(isLoading) { [masterCheck, stageSelect, changeStageBtn, deleteBtn, clearBtn].forEach(el => { if (el) el.disabled = isLoading; }); if (!isLoading) updateBulkState(); }
                jQuery('#ajax-data-array').on('draw.dt', hydrateRowCheckboxes);
                window.setTimeout(hydrateRowCheckboxes, 600);
                window.setInterval(hydrateRowCheckboxes, 1200);
                document.addEventListener('change', function(e) { const rowCheck = e.target.closest('.leads-row-check'); if (!rowCheck) return; const id = normalizeLeadRowId(rowCheck.value); if (!id) return; if (rowCheck.checked) selectedIds.add(id); else selectedIds.delete(id); setMessage('', ''); updateBulkState(); });
                masterCheck?.addEventListener('change', function() { getVisibleIds().forEach(id => masterCheck.checked ? selectedIds.add(id) : selectedIds.delete(id)); setMessage('', ''); updateBulkState(); });
                clearBtn?.addEventListener('click', function() { selectedIds.clear(); setMessage('', ''); updateBulkState(); });
                changeStageBtn?.addEventListener('click', async function() { if (!selectedIds.size) return; const stageId = stageSelect ? stageSelect.value : ''; if (stageId === '') { setMessage('error', 'Choose a stage first.'); return; } setLoading(true); setMessage('', ''); try { const result = await bulkApiCall('stage', { stage_id: parseInt(stageId, 10) }); setMessage(result.failed ? 'error' : 'ok', result.failed ? (result.failed + ' of ' + result.processed + ' could not be updated.') : (result.processed + ' leads updated.')); if (!result.failed) window.setTimeout(() => window.location.reload(), 700); } catch (err) { setMessage('error', 'Stage could not be changed.'); } finally { setLoading(false); } });
                deleteBtn?.addEventListener('click', async function() { if (!selectedIds.size) return; if (!confirm('Delete ' + selectedIds.size + ' selected leads?')) return; setLoading(true); setMessage('', ''); try { const idsToRemove = Array.from(selectedIds); const result = await bulkApiCall('delete', {}); setMessage(result.failed ? 'error' : 'ok', result.failed ? (result.failed + ' of ' + result.processed + ' could not be deleted.') : (result.processed + ' leads deleted.')); if (!result.failed) { const table = getTable(); idsToRemove.forEach(id => selectedIds.delete(id)); if (table) table.rows(function(idx, data) { return idsToRemove.includes(normalizeLeadRowId(getLeadRowId(data))); }).remove().draw(false); updateBulkState(); } } catch (err) { setMessage('error', 'Selected leads could not be deleted.'); } finally { setLoading(false); } });
            }
            function showStartSelection() {
                document.querySelectorAll('.startSelection').forEach(el => el.style.display = '');
                document.getElementById('addSingleLeadSection').style.display = 'none';
                document.getElementById('uploadCsvSection').style.display = 'none';
                document.getElementById('throughEmailSection').style.display = 'none';
                document.getElementById('addLeadBtn').style.display = 'none';
            }
            function showOption(option) {
                if (option === 'A') {
                    document.querySelectorAll('.startSelection').forEach(el => el.style.display = 'none');
                    addSingleLeadSection.style.display = '';
                    uploadCsvSection.style.display = 'none';
                    throughEmailSection.style.display = 'none';
                    addLeadBtn.style.display = '';
                    optionA.checked = true;
                } else if (option === 'B') {
                    document.querySelectorAll('.startSelection').forEach(el => el.style.display = 'none');
                    addSingleLeadSection.style.display = 'none';
                    throughEmailSection.style.display = 'none';
                    uploadCsvSection.style.display = '';
                    addLeadBtn.style.display = 'none';
                    optionB.checked = true;
                } else if (option === 'C') {
                    document.getElementById('createBccEmailSection').style.display = 'none';
                    document.getElementById('createdBccEmailSection').style.display = 'none';
                    fetch('https://api.coherta.com/pipeline/leads/email?pipeline_id=<?php echo $pipeline_id; ?>', {
                        method: "GET",
                        headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                    })
                        .then(response => response.json())
                        .then(emailInfo => {
                            if (!emailInfo || emailInfo[0]?.email === null) {
                                document.getElementById('createBccEmailSection').style.display = '';
                                document.getElementById('createdBccEmailSection').style.display = 'none';
                            } else {
                                const email = emailInfo[0]?.email || '{CohertaBCCMail}';
                                const pending = emailInfo[0]?.pending_email_setup === 1;
                                document.querySelector('#throughEmailSection .form-label + div').innerHTML =
                                    `<div style="font-weight:bold;font-size:1.1em;">${email}</div>`;
                                if (pending) {
                                    document.querySelector('.pending-email-setup').style.display = '';
                                    document.querySelector('.completed-email-setup').style.display = 'none';
                                } else {
                                    document.querySelector('.pending-email-setup').style.display = 'none';
                                    document.querySelector('.completed-email-setup').style.display = '';
                                }
                                document.getElementById('createBccEmailSection').style.display = 'none';
                                document.getElementById('createdBccEmailSection').style.display = '';
                            }
                        });
                    document.querySelectorAll('.startSelection').forEach(el => el.style.display = 'none');
                    addSingleLeadSection.style.display = 'none';
                    throughEmailSection.style.display = '';
                    uploadCsvSection.style.display = 'none';
                    addLeadBtn.style.display = 'none';
                    optionC.checked = true;
                }
            }
            document.getElementById('enableBccEmailBtn').addEventListener('click', function() {
                fetch('https://api.coherta.com/pipeline/leads/email', {
                    method: "POST",
                    headers: {"Content-Type": "application/json", "Authorization": "Bearer " + bearerToken},
                    body: JSON.stringify({pipeline_id: pipelineId})
                })
                    .then(response => response.json())
                    .then(() => {
                        showOption('C');
                    });
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('sidebar-close');
                const pageWrapper = document.getElementById('pageWrapper');
                if (pageWrapper) pageWrapper.classList.add('sidebar-close');

                initLeadBulkActions();

                const moveSearchIntoToolbar = () => {
                    const searchNode = document.querySelector('.dataTables_filter');
                    const slot = document.getElementById('leadsSearchSlot');
                    if (searchNode && slot && !slot.contains(searchNode)) {
                        slot.appendChild(searchNode);
                        const label = searchNode.querySelector('label');
                        const input = searchNode.querySelector('input');
                        if (label && input && !searchNode.dataset.ready) {
                            label.childNodes[0] && (label.childNodes[0].textContent = '');
                            input.placeholder = 'Search leads, company, contact, city...';
                            input.classList.add('form-control');
                            searchNode.dataset.ready = '1';
                        }
                    }
                };

                moveSearchIntoToolbar();
                window.setInterval(moveSearchIntoToolbar, 500);

                // Option switch logic
                const csvFileInput = document.getElementById('csvFileInput');
                const csvColumnSelectSection = document.getElementById('csvColumnSelectSection');
                const csvColumnSelect = document.getElementById('csvColumnSelect');
                const csvRowCount = document.getElementById('csvRowCount');
                const csvNextBtn = document.getElementById('csvNextBtn');
                const csvProgressSection = document.getElementById('csvProgressSection');
                const csvProgressBar = document.getElementById('csvProgressBar');
                const csvProgressText = document.getElementById('csvProgressText');
                let csvRows = [];

                // CSV upload logic
                if (csvFileInput) {
                    csvFileInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            const text = evt.target.result;
                            const lines = text.split(/\r?\n/).filter(l => l.trim().length > 0);
                            if (lines.length < 2) return;
                            csvHeaders = lines[0].split(',');
                            csvRows = lines.slice(1).map(l => l.split(','));
                            // Populate select
                            csvColumnSelect.innerHTML = '';
                            const defaultOpt = document.createElement('option');
                            defaultOpt.value = '';
                            defaultOpt.textContent = '--- Select Column ---';
                            defaultOpt.selected = true;
                            csvColumnSelect.appendChild(defaultOpt);
                            csvHeaders.forEach((h, i) => {
                                const opt = document.createElement('option');
                                opt.value = i;
                                opt.textContent = h;
                                csvColumnSelect.appendChild(opt);
                            });
                            csvColumnSelectSection.style.display = '';
                            csvRowCount.textContent = csvRows.length + ' rows detected in CSV file.';
                            csvNextBtn.style.display = 'none';
                            csvProgressSection.style.display = 'none';
                        };
                        reader.readAsText(file);
                    });
                }
                if (csvColumnSelect) {
                    csvColumnSelect.addEventListener('change', function() {
                        csvSelectedColumn = parseInt(csvColumnSelect.value, 10);
                        if (!isNaN(csvSelectedColumn)) {
                            csvNextBtn.style.display = '';
                        } else {
                            csvNextBtn.style.display = 'none';
                        }
                    });
                }
                if (csvNextBtn) {
                    csvNextBtn.addEventListener('click', async function() {
                        csvProgressSection.style.display = '';
                        csvProgressBar.style.width = '0%';
                        csvProgressText.textContent = 'Starting upload...';
                        let successCount = 0;
                        for (let i = 0; i < csvRows.length; i++) {
                            const cvr = csvRows[i][csvSelectedColumn];
                            if (!cvr) continue;
                            // Simulate verifyBtn.click() logic
                            await fetch('https://api.coherta.com/pipeline/all/add_single', {
                                method: "POST",
                                headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                                body: JSON.stringify({
                                    pipeline_id: pipelineId,
                                    company_id: cvr,
                                    move_lead: true })
                            })
                                .then(response => response.json())
                                .then(async data => {
                                    await fetch('https://api.coherta.com/pipeline/all/add_single', {
                                        method: "PATCH",
                                        headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                                        body: JSON.stringify({
                                            pipeline_id: pipelineId,
                                            company_id: data.company_id,
                                            move_lead: true
                                        })
                                    })
                                        .then(response => response.json())
                                        .then(patch_data => {
                                            console.log(patch_data);
                                            if (patch_data.success) successCount++;
                                        });
                                });
                            csvProgressBar.style.width = Math.round(((i+1)/csvRows.length)*100) + '%';
                            csvProgressText.textContent = `Processed ${i+1} of ${csvRows.length} rows.`;
                        }
                        csvProgressText.textContent = `Done! ${successCount} leads added.`;
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const syncRadios = document.querySelectorAll('input[name="rdo-ani"]');
                const nameInput = document.getElementById('leadListName');
                const editLeadListModalEl = document.getElementById('editLeadListModal');
                let saveTimer = null;
                let columnsChangedSinceOpen = false;
                let titleChangedSinceOpen = false;

                function collectSelectedColumns() {
                    return Array.from(document.querySelectorAll('.column-list-group .list-group-item')).map(function(item) {
                        const checkbox = item.querySelector('input[type="checkbox"]');
                        if (!checkbox || !checkbox.checked) return null;
                        return checkbox.value;
                    }).filter(Boolean);
                }

                function updateDisplayedPipelineTitle(nextTitle) {
                    const trimmedTitle = (nextTitle || '').trim();
                    const heading = document.querySelector('.leads-pageTitle');
                    if (heading && trimmedTitle) {
                        heading.textContent = 'Leads overview - ' + trimmedTitle;
                    }
                    if (trimmedTitle) {
                        document.title = trimmedTitle;
                    }
                }

                function savePipelineSettings(changeType) {
                    if (!pipelineId) return Promise.resolve(false);
                    const payload = {
                        pipeline_id: Number(pipelineId),
                        title: nameInput ? nameInput.value.trim() : '',
                        columns: collectSelectedColumns()
                    };
                    const syncEnabledEl = document.getElementById('edo-ani');
                    if (syncEnabledEl) {
                        payload.syncronization_enabled = syncEnabledEl.checked ? 1 : 0;
                    }

                    return fetch('https://api.coherta.com/pipeline', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + bearerToken
                        },
                        body: JSON.stringify(payload)
                    }).then(function(response) {
                        if (!response.ok) throw new Error('Save failed');
                        if (changeType === 'title') {
                            titleChangedSinceOpen = true;
                            updateDisplayedPipelineTitle(payload.title);
                        }
                        if (changeType === 'columns') {
                            columnsChangedSinceOpen = true;
                        }
                        showPipelineToast('Pipeline Settings have been updated');
                        return true;
                    }).catch(function() {
                        showPipelineToast('Could not save changes');
                        return false;
                    });
                }

                function debouncedSave(changeType) {
                    window.clearTimeout(saveTimer);
                    saveTimer = window.setTimeout(function() {
                        savePipelineSettings(changeType);
                    }, 450);
                }

                syncRadios.forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        savePipelineSettings('sync');
                    });
                });

                if (nameInput) {
                    nameInput.addEventListener('input', function() {
                        debouncedSave('title');
                    });
                    nameInput.addEventListener('change', function() {
                        savePipelineSettings('title');
                    });
                }

                document.querySelectorAll('.leads-column-checkbox').forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        savePipelineSettings('columns');
                    });
                });

                document.querySelectorAll('.column-list-group').forEach(function(group) {
                    new Sortable(group, {
                        handle: '.handle',
                        animation: 150,
                        onEnd: function() {
                            savePipelineSettings('columns');
                        }
                    });
                });

                if (editLeadListModalEl) {
                    editLeadListModalEl.addEventListener('hidden.bs.modal', function() {
                        if (columnsChangedSinceOpen) {
                            window.location.reload();
                        }
                    });
                }

                document.querySelectorAll('[data-category-toggle]').forEach(function(toggle) {
                    toggle.addEventListener('click', function() {
                        const card = toggle.closest('[data-category-card]');
                        const expanded = toggle.getAttribute('aria-expanded') === 'true';
                        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                        if (card) card.classList.toggle('is-open', !expanded);
                        if (card) card.classList.toggle('is-collapsed', expanded);
                    });
                });

                document.querySelectorAll('[data-export-trigger]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const targetClass = btn.getAttribute('data-export-trigger') === 'csv' ? '.buttons-csv' : '.buttons-excel';
                        const exportBtn = document.querySelector('.dt-buttons ' + targetClass) || document.querySelector(targetClass);
                        if (exportBtn) {
                            exportBtn.click();
                            const exportModalEl = document.getElementById('exportLeadListModal');
                            if (exportModalEl) {
                                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                                        bootstrap.Modal.getOrCreateInstance(exportModalEl).hide();
                                    } else {
                                        try {
                                            (new bootstrap.Modal(exportModalEl)).hide();
                                        } catch (e) {
                                            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                                                window.jQuery(exportModalEl).modal('hide');
                                            }
                                        }
                                    }
                                } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                                    window.jQuery(exportModalEl).modal('hide');
                                }
                            }
                        } else {
                            showPipelineToast('Export is not ready yet');
                        }
                    });
                });

                window.setTimeout(function() {
                    document.querySelectorAll('.dt-buttons').forEach(function(group) {
                        group.classList.add('leads-hidden-export-buttons');
                    });
                    if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
                        const table = jQuery('#ajax-data-array').DataTable();
                        if (table && table.fixedHeader) {
                            try { table.fixedHeader.disable(); } catch (e) {}
                        }
                    }
                }, 1200);
            });
        </script>
        <script>
            addedLead = false;
        </script>
        <script>
            const urlParams = new URLSearchParams(window.location.search);
            let moveLead = false;
            document.addEventListener('DOMContentLoaded', function() {
                // Add Lead Modal logic
                const verifyBtn = document.getElementById('verifyCvrBtn');
                const cvrInput = document.getElementById('cvrNumber');
                const leadNameGroup = document.getElementById('leadNameGroup');
                const leadNameInput = document.getElementById('leadName');
                const addLeadBtn = document.getElementById('addLeadBtn');
                const cvrError = document.getElementById('cvrError');
                const cvrMove = document.getElementById('cvrMove');
                const bearerToken = document.body.getAttribute('session_id');
                const addLeadForm = document.getElementById('addLeadForm');

                cvrInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        verifyBtn.click();
                    }
                });

                if (verifyBtn) {
                    verifyBtn.addEventListener('click', function() {
                        const cvr = cvrInput.value.trim();
                        cvrError.style.display = 'none';
                        leadNameGroup.style.display = 'none';
                        addLeadBtn.disabled = true;
                        if (!cvr) {
                            cvrError.textContent = 'Please enter a CVR number.';
                            cvrError.style.display = 'block';
                            return;
                        }
                        // Example AJAX call to verify CVR and get lead name
                        fetch('https://api.coherta.com/pipeline/all/add_single', {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                            body: JSON.stringify(
                                { company_id: cvr }
                            )
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    cvrInput.value = data.company_id;
                                    leadNameInput.value = data.name;
                                    leadNameGroup.style.display = 'block';
                                    addLeadBtn.disabled = false;
                                    addLeadBtn.focus();
                                } else {
                                    cvrError.textContent = data.error || 'CVR not found.';
                                    cvrError.style.display = 'block';
                                }
                            })
                            .catch(() => {
                                cvrError.textContent = 'Error verifying CVR.';
                                cvrError.style.display = 'block';
                            });
                    });
                }
                addLeadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    cvrError.style.display = 'none';
                    cvrMove.style.display = 'none';
                    addLeadBtn.disabled = true;

                    const cvr = cvrInput.value.trim();

                    if (!cvr || !pipelineId) {
                        cvrError.textContent = 'Missing CVR number or pipeline ID.';
                        cvrError.style.display = 'block';
                        addLeadBtn.disabled = false;
                        return;
                    }

                    fetch('https://api.coherta.com/pipeline/all/add_single', {
                        method: "PATCH",
                        headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                        body: JSON.stringify({
                            pipeline_id: pipelineId,
                            company_id: cvr,
                            move_lead: moveLead
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                cvrInput.value = "";
                                leadNameInput.value = "";
                                leadNameGroup.style.display = 'none';
                                cvrError.style.display = 'none';
                                cvrInput.focus();
                                addedLead = true;
                                moveLead = false;
                            } else {
                                if(data.detail == "Lead already exists in one of your companies") {
                                    cvrError.textContent = "Lead already exists in " + data.pipeline_title;
                                    cvrError.style.display = 'block';
                                    cvrMove.style.display = 'block';
                                    cvrMove.focus();
                                } else {
                                    cvrError.textContent = data.detail || 'Failed to add lead.';
                                    cvrError.style.display = 'block';
                                    addLeadBtn.disabled = false;
                                }
                            }
                        })
                        .catch(() => {
                            cvrError.textContent = 'Error adding lead.';
                            cvrError.style.display = 'block';
                            addLeadBtn.disabled = false;
                        });
                });

                $('#addLeadModal').on('hidden.bs.modal', function () {
                    if (addedLead) {
                        window.location.href = window.location.pathname + '?pipeline_id=' + encodeURIComponent(pipelineId);
                    }
                });
                cvrMove.addEventListener('click', function(e) {
                    e.preventDefault();
                    moveLead = true;
                    addLeadForm.requestSubmit();
                });
            });
            let initialLeadCount = <?php echo (int)$leadCount; ?>;
            <?php if(isset($_GET['pipeline_id']) && $leadCount === 0) { ?>

            console.log("Initial lead count:", initialLeadCount);
            let offset = 0;

            function startLeadCountdown(seconds = 60) {
                const countdownEl = document.getElementById('leadCountdown');
                const progressEl = document.getElementById('leadEmptyProgressBar');
                if (!countdownEl || !progressEl) return;

                let remaining = seconds;
                countdownEl.textContent = remaining;
                progressEl.style.width = '0%';

                const timer = setInterval(() => {
                    remaining -= 1;
                    if (remaining < 0) {
                        clearInterval(timer);
                        window.location.reload();
                        return;
                    }
                    countdownEl.textContent = remaining;
                    const progress = ((seconds - remaining) / seconds) * 100;
                    progressEl.style.width = progress + '%';
                }, 1000);
            }

            function initSignalGame() {
                const playground = document.getElementById('signalPlayground');
                const startBtn = document.getElementById('signalStartBtn');
                const scoreEl = document.getElementById('signalScore');
                const livesEl = document.getElementById('signalLives');
                if (!playground || !startBtn || !scoreEl || !livesEl) return;

                let score = 0;
                let lives = 10;
                let roundTimer = null;

                function renderStats() {
                    scoreEl.textContent = String(score);
                    livesEl.textContent = String(lives);
                }

                function clearSignals() {
                    playground.querySelectorAll('.signal-dot').forEach(dot => dot.remove());
                }

                function spawnSignal() {
                    if (lives <= 0) {
                        clearSignals();
                        startBtn.hidden = false;
                        startBtn.textContent = 'Play again';
                        return;
                    }

                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'signal-dot';
                    const maxX = Math.max(20, playground.clientWidth - 70);
                    const maxY = Math.max(20, playground.clientHeight - 70);
                    dot.style.left = Math.floor(Math.random() * maxX) + 'px';
                    dot.style.top = Math.floor(Math.random() * maxY) + 'px';

                    let clicked = false;
                    dot.addEventListener('click', function() {
                        clicked = true;
                        score += 1;
                        renderStats();
                        dot.remove();
                        window.setTimeout(spawnSignal, 220);
                    });

                    playground.appendChild(dot);

                    window.setTimeout(() => {
                        if (!clicked && dot.isConnected) {
                            dot.remove();
                            lives -= 1;
                            renderStats();
                            spawnSignal();
                        }
                    }, 950);
                }

                startBtn.addEventListener('click', function() {
                    score = 0;
                    lives = 10;
                    renderStats();
                    clearSignals();
                    startBtn.hidden = true;
                    if (roundTimer) {
                        window.clearTimeout(roundTimer);
                    }
                    roundTimer = window.setTimeout(spawnSignal, 150);
                });

                renderStats();
            }
            async function triggerLeadEnrichment() {
                if (initialLeadCount !== 0 || !pipelineId) return;

                const reloadKey = 'lead-enrich-reloaded-' + pipelineId;
                const alreadyReloaded = sessionStorage.getItem(reloadKey) === '1';

                const payload = {
                    pipeline_id: Number(pipelineId),
                    pipeline_filter: <?php echo json_encode($pipelineFilterForEnrich, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
                };

                try {
                    const response = await fetch('https://api.coherta.com/pipeline/leads/enrich/leads', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + bearerToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    if ((response.status === 200 || response.status === 201) && !alreadyReloaded) {
                        sessionStorage.setItem(reloadKey, '1');
                        window.location.reload();
                        return;
                    }

                    if (response.status !== 200 && response.status !== 201) {
                        console.warn('Lead enrichment responded with status', response.status);
                    }
                } catch (error) {
                    console.warn('Lead enrichment request failed', error);
                }
            }
            function fetchLeadsList() {
                if(initialLeadCount === 0) {
                    startLeadCountdown(60);
                    initSignalGame();
                    triggerLeadEnrichment();
                    setTimeout(() => {
                        window.reloadLeadsInBatches(250);
                    }, 2500);
                } else {
                    if (pipelineId) {
                        sessionStorage.removeItem('lead-enrich-reloaded-' + pipelineId);
                    }
                    setTimeout(() => {
                        window.reloadLeadsInBatches(250);
                    }, 500);
                }
            }
            fetchLeadsList();
            <?php } else {
            ?>
            setTimeout(() => {
                window.reloadLeadsInBatches(250);
            }, 500);
            <?php
            } ?>
        </script>
        <style>
            #column-list .handle {
                cursor: grab;
                font-size: 1.2em;
            }

            .lead-empty-state {
                display: grid;
                gap: 24px;
                padding: 8px 0 20px;
            }

            .lead-empty-hero {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
                gap: 24px;
                align-items: center;
                padding: 28px;
                border-radius: 24px;
                background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
                border: 1px solid rgba(13, 110, 253, 0.12);
                overflow: hidden;
                position: relative;
            }

            .lead-empty-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(13, 110, 253, 0.08);
                color: #0d6efd;
                font-weight: 700;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: .04em;
                margin-bottom: 14px;
            }

            .lead-empty-copy h3 {
                margin: 0 0 10px;
                font-size: 28px;
                line-height: 1.1;
            }

            .lead-empty-copy p,
            .lead-empty-tip,
            .lead-mini-game-head p {
                margin: 0;
                color: #5b6475;
            }

            .lead-empty-countdown {
                margin-top: 18px;
                font-size: 18px;
                font-weight: 500;
                color: #172033;
            }

            .lead-empty-progress {
                margin-top: 16px;
                width: 100%;
                height: 12px;
                border-radius: 999px;
                background: rgba(23, 32, 51, 0.08);
                overflow: hidden;
            }

            .lead-empty-progress-bar {
                height: 100%;
                width: 0%;
                border-radius: inherit;
                background: linear-gradient(90deg, #0d6efd 0%, #4cc9f0 100%);
                transition: width .9s linear;
            }

            .lead-empty-tip {
                margin-top: 12px;
                font-size: 14px;
            }

            .lead-empty-visual {
                min-height: 260px;
                position: relative;
                border-radius: 24px;
                background: radial-gradient(circle at center, rgba(76, 201, 240, 0.16), rgba(13, 110, 253, 0.04) 45%, transparent 60%);
            }

            .scanner-ring,
            .scanner-core,
            .orb {
                position: absolute;
                border-radius: 50%;
            }

            .scanner-ring {
                inset: 40px;
                border: 1px solid rgba(13, 110, 253, 0.16);
                box-shadow: inset 0 0 0 18px rgba(13, 110, 253, 0.03);
                animation: pulseRing 3s ease-in-out infinite;
            }

            .scanner-core {
                width: 120px;
                height: 120px;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                background: radial-gradient(circle at 30% 30%, #ffffff, #8ec5ff 45%, #0d6efd 100%);
                box-shadow: 0 0 40px rgba(13, 110, 253, 0.28);
                animation: floatCore 4.5s ease-in-out infinite;
            }

            .orb {
                width: 18px;
                height: 18px;
                background: #4cc9f0;
                box-shadow: 0 0 18px rgba(76, 201, 240, 0.5);
            }

            .orb-1 { top: 34px; left: 50px; animation: orbitA 5s linear infinite; }
            .orb-2 { right: 60px; top: 52px; animation: orbitB 6s linear infinite; }
            .orb-3 { left: 80px; bottom: 36px; animation: orbitC 7s linear infinite; }

            .lead-mini-game {
                padding: 24px;
                border: 1px solid rgba(23, 32, 51, 0.08);
                border-radius: 24px;
                background: #fff;
                box-shadow: 0 18px 50px rgba(23, 32, 51, 0.06);
            }

            .lead-mini-game-head {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: center;
                margin-bottom: 18px;
            }

            .lead-mini-game-head h4 {
                margin: 0 0 6px;
            }

            .lead-mini-game-stats {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                font-size: 14px;
                color: #172033;
            }

            .signal-playground {
                position: relative;
                min-height: 280px;
                border-radius: 20px;
                background: linear-gradient(180deg, #0c1222 0%, #111a30 100%);
                overflow: hidden;
                border: 1px solid rgba(255,255,255,0.05);
            }

            .signal-playground::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image: radial-gradient(rgba(255,255,255,0.09) 1px, transparent 1px);
                background-size: 22px 22px;
                opacity: .35;
            }

            .signal-start-btn {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 2;
                border: 0;
                border-radius: 999px;
                padding: 12px 20px;
                font-weight: 700;
                background: #ffffff;
                color: #172033;
                box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            }

            .signal-dot {
                position: absolute;
                z-index: 2;
                width: 46px;
                height: 46px;
                border-radius: 50%;
                border: 0;
                background: radial-gradient(circle at 30% 30%, #fff, #7dd3fc 35%, #0d6efd 100%);
                box-shadow: 0 0 18px rgba(125, 211, 252, 0.55);
                animation: signalPop .95s ease-out forwards;
                cursor: pointer;
            }

            @keyframes pulseRing {
                0%, 100% { transform: scale(1); opacity: .75; }
                50% { transform: scale(1.04); opacity: 1; }
            }

            @keyframes floatCore {
                0%, 100% { transform: translate(-50%, -50%) translateY(0); }
                50% { transform: translate(-50%, -50%) translateY(-10px); }
            }

            @keyframes orbitA {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(90px, 60px) scale(1.2); }
                100% { transform: translate(0, 0) scale(1); }
            }

            @keyframes orbitB {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-80px, 110px) scale(.85); }
                100% { transform: translate(0, 0) scale(1); }
            }

            @keyframes orbitC {
                0% { transform: translate(0, 0) scale(.9); }
                50% { transform: translate(120px, -90px) scale(1.15); }
                100% { transform: translate(0, 0) scale(.9); }
            }

            @keyframes signalPop {
                0% { transform: scale(.5); opacity: 0; }
                20% { transform: scale(1); opacity: 1; }
                100% { transform: scale(.85); opacity: .9; }
            }

            @media (max-width: 991px) {
                .lead-empty-hero {
                    grid-template-columns: 1fr;
                }

                .lead-mini-game-head {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }

            .page-body {
                margin-top: 0 !important;
            }

            .page-body .container-fluid {
                max-width: 100%;
                padding-top: 20px;
                padding-left: 20px;
                padding-right: 20px;
            }

            .leads-shell-card,
            .leads-table-card {
                border: 0;
                border-radius: 26px;
                overflow: hidden;
                box-shadow: 0 18px 60px rgba(15, 23, 42, 0.08);
            }

            .leads-shell-body {
                padding: 28px 30px;
                background:
                        radial-gradient(circle at top left, rgba(13, 110, 253, 0.10), transparent 32%),
                        linear-gradient(135deg, #ffffff 0%, #f7f9fc 100%);
            }

            .leads-hero {
                display: grid;
                gap: 22px;
            }

            .leads-hero-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 16px;
            }

            .leads-hero-copy h1 {
                margin: 0;
                font-size: 32px;
                line-height: 1.05;
            }

            .leads-hero-copy p {
                margin: 10px 0 0;
                max-width: 760px;
                color: #5b6475;
                font-size: 15px;
            }

            .leads-actionbar {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: start;
                gap: 16px;
                width: 100%;
                padding-top: 8px;
                border-top: 1px solid rgba(15, 23, 42, 0.06);
            }

            .leads-actionbar-left,
            .leads-actionbar-right {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .leads-actionbar-right {
                justify-content: flex-end;
            }

            .btn-start-outreach {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 56px;
                min-width: 220px;
                padding: 0 26px;
                border: 0;
                border-radius: 18px;
                font-weight: 700;
                box-shadow: 0 14px 35px rgba(13, 110, 253, 0.28);
                align-self: flex-start;
            }

            .btn-start-outreach i,
            .btn-leads-action i {
                margin-right: 8px;
            }

            .btn-leads-action {
                min-height: 48px;
                padding: 0 18px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 16px;
                background: #fff;
                color: #172033;
                font-weight: 600;
            }

            .btn-leads-action:hover,
            .btn-leads-action:focus {
                background: #f8fafc;
                border-color: rgba(13, 110, 253, 0.18);
                color: #0d6efd;
            }

            .leads-table-card-body {
                padding: 0;
            }

            .leads-table-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
                flex-wrap: wrap;
                padding: 22px 24px 16px;
                border-bottom: 1px solid rgba(15, 23, 42, 0.06);
                background: #fff;
            }

            .leads-table-toolbar-copy h3 {
                margin: 0;
                font-size: 18px;
            }

            .leads-table-toolbar-copy p {
                margin: 4px 0 0;
                color: #667085;
                font-size: 14px;
            }

            .leads-search-slot {
                margin-left: auto;
            }

            .leads-search-slot .dataTables_filter {
                margin: 0;
            }

            .leads-search-slot .dataTables_filter label {
                margin: 0;
                display: block;
            }

            .leads-search-slot .dataTables_filter input {
                width: min(360px, 70vw);
                height: 46px;
                border-radius: 14px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                background: #f8fafc;
                padding: 0 14px;
                margin-left: 0 !important;
            }

            .table-responsive {
                border-radius: 0 0 26px 26px;
                overflow-x: auto;
                overflow-y: visible;
                background: #fff;
            }

            table.dataTable {
                margin-top: 0 !important;
                border-collapse: separate !important;
                border-spacing: 0;
                width: 100% !important;
            }

            table.dataTable thead th {
                background: #f8fafc !important;
                color: #344054;
                font-size: 12px;
                letter-spacing: .04em;
                text-transform: uppercase;
                border-bottom: 1px solid rgba(15, 23, 42, 0.08) !important;
                padding: 16px 14px !important;
                white-space: nowrap;
            }

            table.dataTable tbody td {
                padding: 16px 14px !important;
                border-bottom: 1px solid rgba(15, 23, 42, 0.06) !important;
                vertical-align: middle;
                color: #172033;
                background: #fff;
                white-space: nowrap;
            }

            table.dataTable tbody tr:hover td {
                background: #f8fbff;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                padding: 16px 24px 20px;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                border-radius: 12px !important;
                border: 1px solid transparent !important;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: rgba(13, 110, 253, 0.12) !important;
                border-color: rgba(13, 110, 253, 0.12) !important;
                color: #0d6efd !important;
            }

            .modal-dialog {
                max-width: 1120px;
            }

            .modal-content {
                border: 0;
                border-radius: 24px;
                overflow: hidden;
            }

            .modal-header,
            .modal-footer {
                padding-left: 24px;
                padding-right: 24px;
            }

            .modal-body {
                padding: 20px 24px 24px;
            }

            .settings-section {
                padding: 18px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 20px;
                background: #fff;
                margin-bottom: 18px;
            }

            .settings-section-head h6 {
                margin: 0;
                font-size: 16px;
            }

            .settings-section-head p {
                margin: 6px 0 0;
                color: #667085;
                font-size: 14px;
            }

            .settings-sync-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
                margin-top: 16px;
            }

            .settings-sync-card {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 16px;
                border-radius: 18px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                background: #f8fafc;
                cursor: pointer;
            }

            .settings-sync-card strong,
            .settings-sync-card span {
                display: block;
            }

            .settings-sync-card span {
                margin-top: 4px;
                color: #667085;
                font-size: 13px;
                line-height: 1.45;
            }


            .settings-category-stack {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-top: 16px;
                align-items: start;
            }


            @media (max-width: 991px) {
                .settings-category-stack {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767px) {
                .settings-category-stack {
                    grid-template-columns: 1fr;
                }
            }

            .settings-category-card {
                border: 1px solid rgba(15, 23, 42, 0.08);
                border-radius: 18px;
                background: #f8fafc;
                overflow: hidden;
            }

            .settings-category-toggle {
                width: 100%;
                border: 0;
                background: transparent;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 16px 18px;
                text-align: left;
            }

            .settings-category-toggle-copy {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                width: 100%;
            }

            .settings-category-toggle-copy h6 {
                margin: 0;
                font-size: 14px;
            }

            .settings-category-toggle-copy span {
                font-size: 12px;
                color: #667085;
                white-space: nowrap;
            }

            .settings-category-toggle .fa-chevron-down {
                color: #667085;
                transition: transform .2s ease;
            }

            .settings-category-card.is-open .fa-chevron-down {
                transform: rotate(180deg);
            }

            .settings-category-body {
                display: none;
                padding: 0 18px 10px;
                border-top: 1px solid rgba(15, 23, 42, 0.06);
            }

            .settings-category-card.is-open .settings-category-body {
                display: block;
            }

            .leads-settings-list .list-group-item {
                border: 0;
                border-bottom: 1px solid rgba(15, 23, 42, 0.06);
                padding: 14px 2px;
            }

            .leads-settings-list .list-group-item:last-child {
                border-bottom: 0;
            }

            .settings-section-danger {
                background: linear-gradient(135deg, rgba(220, 53, 69, 0.03), rgba(220, 53, 69, 0.01));
            }

            @media (max-width: 991px) {
                .leads-hero-copy h1 {
                    font-size: 28px;
                }

                .leads-actionbar {
                    grid-template-columns: 1fr;
                }

                .leads-actionbar-left,
                .leads-actionbar-right {
                    width: 100%;
                    justify-content: flex-start;
                }

                .btn-start-outreach,
                .btn-leads-action {
                    width: 100%;
                    justify-content: center;
                }

                .leads-search-slot {
                    width: 100%;
                }

                .leads-search-slot .dataTables_filter input {
                    width: 100%;
                }


            }

            .fixedHeader-floating,
            .fixedHeader-locked {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }


            .pipeline-toast-stack {
                position: fixed;
                top: 0;
                right: 0;
                z-index: 9999999 !important;
                pointer-events: none;
            }

            .pipeline-toast-stack .toast {
                pointer-events: auto;
                box-shadow: 0 20px 60px rgba(15, 23, 42, 0.28);
            }

            .export-choice-body {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .export-choice-card {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                padding: 24px 18px;
                border-radius: 18px;
                border: 1px solid rgba(15, 23, 42, 0.08);
                background: #f8fafc;
                text-align: center;
                font-size: 16px;
                font-weight: 700;
            }

            .export-choice-card i {
                font-size: 34px;
                color: #4f46e5;
            }

            .export-choice-card strong {
                display: block;
            }

            @media (max-width: 767px) {
                .export-choice-body {
                    grid-template-columns: 1fr;
                }
            }

            .leads-hidden-export-buttons {
                display: none !important;
            }
            .leads-bulkbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:12px 14px; margin:0 0 14px; border:1px solid rgba(15,23,42,.08); border-radius:16px; background:rgba(248,250,252,.92); box-shadow:0 10px 30px rgba(15,23,42,.04); }
            .leads-bulkbar.has-selection { border-color:rgba(59,130,246,.22); background:linear-gradient(135deg, rgba(239,246,255,.95), rgba(248,250,252,.95)); }
            .leads-bulkbar-left, .leads-bulkbar-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
            .leads-select-all-wrap, .leads-row-check-wrap { display:inline-flex; align-items:center; gap:8px; margin:0; cursor:pointer; user-select:none; }
            .leads-table-bottom-select { display:flex; align-items:center; justify-content:flex-start; padding:12px 4px 0; }
            .leads-table-bottom-select .leads-select-all-wrap { padding:8px 12px; border-radius:999px; background:#fff; border:1px solid rgba(15,23,42,.08); font-size:12px; font-weight:700; color:#334155; }
            .leads-select-column-head, .leads-select-column-cell { width:48px !important; min-width:48px !important; max-width:48px !important; text-align:center !important; padding-left:8px !important; padding-right:8px !important; }
            .leads-selected-pill { display:inline-flex; align-items:center; gap:4px; padding:6px 10px; border-radius:999px; background:#fff; border:1px solid rgba(15,23,42,.08); font-size:12px; font-weight:700; color:#334155; }
            .leads-bulkbar-actions .form-select { min-width:160px; }
            .leads-bulk-message { width:100%; min-height:18px; font-size:12px; font-weight:700; }
            .leads-bulk-message.ok { color:#15803d; }
            .leads-bulk-message.error { color:#b91c1c; }
            .leads-row-check-wrap { width:36px; height:36px; justify-content:center; border-radius:12px; background:rgba(248,250,252,.95); border:1px solid rgba(15,23,42,.08); margin:0; }
            .leads-row-action-cell { white-space:nowrap; }
            .leads-row-delete-hit { display:inline-flex !important; align-items:center; justify-content:center; min-width:38px; min-height:38px; padding:8px 10px !important; border-radius:12px !important; line-height:1 !important; }
            .leads-row-delete-hit i { pointer-events:none; }
            .leads-row-check-wrap input, .leads-select-all-wrap input { width:16px; height:16px; cursor:pointer; }
            table.dataTable tbody tr.leads-row-selected td { background:rgba(219,234,254,.42) !important; }
            @media (max-width:768px) { .leads-bulkbar { align-items:stretch; } .leads-bulkbar-left, .leads-bulkbar-actions { width:100%; } .leads-bulkbar-actions .form-select, .leads-bulkbar-actions .btn { flex:1 1 100%; } }
        </style>
<?php
include('partial/footer-end.php'); ?>