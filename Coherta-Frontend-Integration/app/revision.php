<?php
require_once "./base.php";
$company_data = $client->getCurrentCompanies()[0] ?? [];
$version = $client->get("scraping/revision-version.php");
$plugin_pipeline_id = null;
$url = "";
if(!isset($_GET["pipeline_id"])) {
    foreach ($company_data["plugins"] as $plugin) {
        if (in_array($plugin['plugin_id'], [8, 10, 11]) && isset($plugin['settings']['pipeline_id'])) {
            $plugin_pipeline_id = $plugin['settings']['pipeline_id'];
            break;
        }
    }
    if(isset($_GET['type'])) {
        $url = "&type=" . urlencode($_GET['type']);
    } else {
        $url = "";
    }
}
if(!isset($_GET["pipeline_id"]) && isset($_GET["type"])) {
    header("Location: revision.php?pipeline_id=" . $plugin_pipeline_id . $url);
}

$PageTitle = "Revision";
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
    }
    $leads =
        $client->get("plugins/revision/?{$pipeline_id_str}{$limit}offset=" .
                (isset($_GET["offset"]) ? (int)$_GET["offset"] : "0") .
                (isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '')
    );
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
        ['key' => 'company_name', 'type' => 'string', 'label' => 'Company Name'],
        ['key' => 'vat_code', 'type' => 'combine', 'combine_with' => 'country_code', 'label' => 'VAT code'],
        ['key' => 'added', 'type' => 'date', 'label' => 'Added'],
        ['key' => 'last_updated', 'type' => 'date', 'label' => 'Last Updated'],
        ['key' => 'stage', 'type' => 'enum', 'enum_value' => 'enum_stages', 'label' => 'Stage'],
        ['key' => 'company_address', 'type' => 'string', 'label' => 'Company Address'],
        ['key' => 'company_postal', 'type' => 'string', 'label' => 'Company Postal'],
        ['key' => 'company_city', 'type' => 'string', 'label' => 'Company City'],
        ['key' => 'company_phone', 'type' => 'string', 'label' => 'Company Phone'],
        ['key' => 'company_website', 'type' => 'string', 'label' => 'Company Website'],
        ['key' => 'number_of_employees', 'type' => 'numeric', 'label' => 'Number Of Employees'],
        ['key' => 'company_profit_before_tax', 'type' => 'numeric', 'label' => 'Company Profit Before Tax'],
        ['key' => 'company_profit_after_tax', 'type' => 'numeric', 'label' => 'Company Profit After Tax'],
];
if(isset($company_data["plugins"][3]) && $company_data["plugins"][3]["installed"]) {
    $website_columns = [];
    $website_columns = [
            ['key' => 'website_analytics_tool', 'type' => 'array', "subkey" => "name", 'label' => 'Analytics Tool'],
            ['key' => 'website_chat_platform', 'type' => 'array', "subkey" => "name", 'label' => 'Chat Platform'],
            ['key' => 'website_cms_type', 'type' => 'array', "subkey" => "name", 'label' => 'CMS Type'],
            ['key' => 'website_framework', 'type' => 'array', "subkey" => "name", 'label' => 'Framework'],
            ['key' => 'website_ecommerce', 'type' => 'array', "subkey" => "name", 'label' => 'E-commerce'],
            ['key' => 'website_software_systems', 'type' => 'array', "subkey" => "name", 'label' => 'Software Systems'],
            ['key' => 'trustpilot_reviews', 'type' => 'numeric', 'label' => 'Reviews on Trustpilot'],
            ['key' => 'trustpilot_score', 'type' => 'numeric', 'label' => 'TrustScore'],
            ['key' => 'payment_systems', 'type' => 'text', 'label' => 'Payment Providers'],
            ['key' => 'alternative_website', 'type' => 'text', 'label' => 'Alternative Website'],
    ];
    $columns = array_merge($columns, $website_columns);
}
if(isset($company_data["plugins"][8]) && $company_data["plugins"][8]["installed"]) {
    $revision_columns = [];
    $revision_columns = [
            ['key' => 'open_tax_statement', 'type' => 'array', "subkey" => "open_tax_statement", 'label' => 'Skattekontoen'],
            ['key' => 'open_tax_statement_file', 'type' => 'file', "subkey" => "file", "filename" => 'filename', 'label' => 'Skattekontoen Fil'],
            ['key' => 'open_vat_statements', 'type' => 'file', "subkey" => "file", "filename" => 'filename', 'label' => 'Momskvitteringer Fil'],
            ['key' => 'open_vat_statements', 'type' => 'array', "subkey" => "from", 'label' => 'Momsdato'],
            ['key' => 'open_e_indkomst_statement', 'type' => 'file', "subkey" => "file", "filename" => 'filename', 'label' => 'E-indkomst Fil'],
            ['key' => 'open_company_registration', 'type' => 'file', "subkey" => "file", "filename" => 'filename', 'label' => 'Registeringsbevis Fil'],
            ['key' => 'open_company_registration', 'type' => 'array', "subkey" => "from", 'label' => 'Fildato'],
    ];
    $columns = array_merge($revision_columns, $columns);
}
$leads["columns"] = ['company_name', 'vat_code'];
if($_GET['type'] == 'tax') {
    $leads["columns"] = ['company_name', 'vat_code', 'open_tax_statement'];
} elseif($_GET['type'] == 'vat') {
    $leads["columns"] = ['company_name', 'vat_code', 'open_vat_statements'];
} elseif($_GET['type'] == 'e_indkomst') {
    $leads["columns"] = ['company_name', 'vat_code', 'open_e_indkomst_statement'];
} elseif($_GET['type'] == 'company_registration') {
    $leads["columns"] = ['company_name', 'vat_code', 'open_company_registration'];
}

$columns = array_map(function($col) use ($leads) {
    if (isset($leads["columns"]) && in_array($col['key'], $leads["columns"])) {
        $col['checked'] = 'yes';
    }
    return $col;
}, $columns);
?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/datatables.css">
    <script>
        const activeColumns = <?php
                $activeCols = [];

                foreach ($columns as $col) {
                    if (isset($col["checked"]) && $col["checked"] == "yes") {
                        $activeCols[] = array_filter($col, function($k) {
                            return in_array($k, ['key', 'type', 'subkey','filename', 'combine_with', 'enum_value']);
                        }, ARRAY_FILTER_USE_KEY);
                    }
                }
                echo json_encode($activeCols);
                ?>;
    </script>

<?php include('partial/loader.php'); ?>


    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php'); ?>
        <!-- Page Header Ends -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php'); ?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <!-- Ajax data source array start-->
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php
                                        if(!isset($_GET["pipeline_id"])) {
                                            $page_title = "Leads overview";
                                        } else {
                                            $page_title = htmlspecialchars($title);
                                        }
                                        ?>
                                        <h5 class="mb-3 leads-pageTitle"><?php echo $page_title; ?></h5>
                                    </div>
                                    <div id="export-button">
                                        <?php if(isset($_GET["pipeline_id"])) { ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">Add Company</button>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Upload Customerlist.rev</button>
                                        <?php } ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>




                                    <?php
                                    if(isset($_GET["pipeline_id"])) {
                                    ?>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                        <div class="table-responsive">
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
                                    </table></div>
                            </div>
                        </div>
                    </div>
                                    <?php
                                    } else {
                                        echo '<div class="row mb-4">';
                                        echo '
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-header">
                                                        <strong>Download Tax Flows Bot for Windows</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <center>
                                                            <a href="https://api.coherta.com/programs/tax-flows/'.$version["version"].'/TaxFlows.exe" target="_blank" style="display:inline-block;">
                                                                <i class="fa fa-download" style="font-size:64px;color:#007bff;"></i>
                                                                <div style="font-size:18px;margin-top:8px;">Download</div>
                                                            </a>
                                                        </center>
                                                    </div>
                                                </div>
                                            </div>';
                                        echo '
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-header">
                                                        <strong>Last time Tax Flows did run</strong>
                                                    </div>
                                                    <div class="card-body">
                                                        <center><h3></h3></center>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>';
                                    }
                                    ?>
                </div>
            </div>
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
    </div>
    </div>
    <!-- Add Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addLeadForm" method="post" action="revision.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLeadModalLabel">Add New Company</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="addLeadBtn" disabled>Add to List</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerForm" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addCustomerform" method="post" action="revision.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCustomerModalLabel">Add New Companies</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cvrNumber" class="form-label">File</label>
                            <input type="file" class="form-control" id="customerList" name="customerList" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="addCustomersBtn">Add to List</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php include('partial/scripts.php'); ?>
<script>
    window.LEADS_ENDPOINT = "revision.php";
    window.LEAD_EDITOR = "revision.php";
    window.ADDITIONAL_FIELDS = ["type"];
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
        // Handle Add Customers (bulk upload)
        document.addEventListener('DOMContentLoaded', function() {
            const addCustomersBtn = document.getElementById('addCustomersBtn');
            const customerListInput = document.getElementById('customerList');
            const pipelineId = new URLSearchParams(window.location.search).get('pipeline_id');
            const bearerToken = document.body.getAttribute('session_id');

            if (addCustomersBtn && customerListInput) {
                addCustomersBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const file = customerListInput.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        let result = event.target.result;

                        // If it's a DataURL like "data:...;base64,XXXX"
                        if (result.startsWith('data:')) {
                            result = result.split(',')[1];
                        }

                        // Clean whitespace/newlines just in case
                        const base64 = result.replace(/\s/g, '');
                        const jsonString = atob(atob(base64));
                        const objects = JSON.parse(jsonString);
                        console.log(objects);

                        Promise.all(
                            objects.map(obj => {
                                const cvr = obj.ID?.CVR;
                                const pipelineId = new URLSearchParams(window.location.search).get('pipeline_id');
                                return fetch('https://api.coherta.com/pipeline/all/add_single', {
                                    method: "PATCH",
                                    headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
                                    body: JSON.stringify({
                                        pipeline_id: pipelineId,
                                        company_id: cvr,
                                        move_lead: true
                                    })
                                });
                            })
                        ).then(() => {
                            window.location.reload();
                        });

                    };
                    reader.readAsDataURL(file);
                });
            }
        });


        addedLead = false;
        document.addEventListener('DOMContentLoaded', function() {
            new Sortable(document.getElementById('column-list'), {
                handle: '.handle',
                animation: 150
            });
        });
    </script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const pipelineId = urlParams.get('pipeline_id');
        let moveLead = false;
        document.addEventListener('DOMContentLoaded', function() {
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
            if (addLeadForm) {
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
                            } else {
                                if(data.detail == "Lead already exists in one of your companies") {
                                    cvrError.textContent = "Lead already exists in another list.";
                                    cvrError.style.display = 'block';
                                    cvrMove.style.display = 'block';
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
            }
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
    </script>
    <style>
        #column-list .handle {
            cursor: grab;
            font-size: 1.2em;
        }
    </style>
<?php include('partial/footer-end.php'); ?>