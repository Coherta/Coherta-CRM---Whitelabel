<?php
require_once "./base.php";

$PageTitle = "Simple Wizard";


include('partial/header.php');
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
            <form class="form-wizard" id="regForm" action="#" method="POST">
                <input type="hidden" name="origin" value="DK">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card mb-4">
                                <div class="card-header text-center">
                                    <h4>Generate your next Pipeline</h4>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <div class="mb-4">
                                            <h5>Name of your new Lead list</h5>
                                            <input type="text"
                                                   class="form-control filter-value"
                                                   placeholder="Lead list name"
                                                   value="Lead list"
                                                   name="title"
                                                   id="title"
                                                   required
                                                   onclick="if(this.value.toLowerCase() === 'lead list') this.value=''">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div>
                                        <div class="addedFilters">
                                            <div class="mb-4 filter-row" data-key="city">
                                                <select type="hidden" name="origin" style="display:none;">
                                                    <option>equals</option>
                                                </select>
                                                <h5>In what city are your leads located?</h5>
                                                <input type="text" class="form-control filter-value" placeholder="City" value="" onchange="sendFilters()" required>
                                                <div class="invalid-feedback">Please enter a value.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="choosecategoryoption" class="mb-4 filter-row p-4 rounded shadow-sm bg-light d-flex justify-content-center gap-4" data-key="category_code">
                    <div class="choose-category-option card border-secondary text-center bg-white" style="cursor:pointer; min-width:180px;" onclick="toggleCategoryInput(true)">
                        <div class="card-body">
                            <i class="bi bi-tags fs-2 text-secondary"></i>
                            <h5 class="fw-bold mt-2 text-secondary">I want a specific company category</h5>
                        </div>
                    </div>
                    <div class="choose-category-option card border-secondary text-center bg-white" style="cursor:pointer; min-width:180px;" onclick="toggleCategoryInput(false)">
                        <div class="card-body">
                            <i class="bi bi-x-circle fs-2 text-secondary"></i>
                            <h5 class="fw-bold mt-2 text-secondary">Any category is fine</h5>
                        </div>
                    </div>
                </div>
                <div id="categoryInputDiv" style="display:none;" class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div>
                                        <div class="addedFilters">
                                            <div class="mb-4 filter-row" data-key="category_code">
                                                <select type="hidden" name="origin" style="display:none;">
                                                    <option>equals</option>
                                                </select>
                                                <h5>What type of company are you looking for?</h5>
                                                <input type="text" class="form-control filter-value" placeholder="Value" value="" onchange="sendFilters()" required="" data-gtm-form-interact-field-id="0" id="categoryInput" autocomplete="off" list="categoryDatalist">
                                                <datalist id="categoryDatalist"></datalist>
                                                <div class="invalid-feedback">Please enter a value.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chooseemployeeoption" class="mb-4 filter-row p-4 rounded shadow-sm bg-light d-flex justify-content-center gap-4" data-key="category_code">
                    <div class="choose-employee-option card border-secondary text-center bg-white" style="cursor:pointer; min-width:180px;" onclick="toggleEmployeeInput(true)">
                        <div class="card-body">
                            <i class="bi bi-tags fs-2 text-secondary"></i>
                            <h5 class="fw-bold mt-2 text-secondary">I want to define minimum number of employees</h5>
                        </div>
                    </div>
                    <div class="choose-employee-option card border-secondary text-center bg-white" style="cursor:pointer; min-width:180px;" onclick="toggleEmployeeInput(false)">
                        <div class="card-body">
                            <i class="bi bi-x-circle fs-2 text-secondary"></i>
                            <h5 class="fw-bold mt-2 text-secondary">All companies are relevant</h5>
                        </div>
                    </div>
                </div>
                <div id="employeeInputDiv" style="display:none;" class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div>
                                        <div class="addedFilters">
                                            <div class="mb-4 filter-row" data-key="employees">
                                                <select type="hidden" name="origin" style="display:none;">
                                                    <option>more_than</option>
                                                </select>
                                                <h5>How many employees should be on payroll for the company you are looking for?</h5>
                                                <input type="text" class="form-control filter-value" placeholder="Value" value="" onchange="sendFilters()" required="" data-gtm-form-interact-field-id="0">
                                                <div class="invalid-feedback">Please enter a value.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <div class="card mb-4">
                                <div class="card-header text-center">
                                    <div>
                                        <div class="text-end btn-mb">
                                            Total identified leads:
                                            <h2 id="SubjctsCompletingFilters">0</h2>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-end btn-mb">
                                            <button class="btn btn-primary" id="nextBtn" type="button"
                                                    onclick="SavePipeline(<?php echo $client->getCurrentCompanies()[0]["id"]; ?>)">Add Leadlist</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <?php include('partial/footer.php');?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/form-wizard/form-wizard.js"></script>
<script src="assets/js/tooltip-init.js"></script>
<script src="assets/js/select2/select2.full.min.js"></script>
<script src="assets/js/select2/select2-custom.js"></script>
<script src="assets/js/select2/addFilters.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('categoryInput');
            let categories = [];

            // Preload categories
            fetch('https://api.coherta.com/filter/company/categories')
                .then(res => res.json())
                .then(data => {
                    categories = data;
                });

            input.addEventListener('input', function() {
                const val = this.value.toLowerCase();
                if (val.length < 2) return;
                let datalist = document.getElementById('categoryDatalist');
                datalist.innerHTML = '';
                categories
                    .filter(item => item.category_text.toLowerCase().includes(val))
                    .forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.category_text;
                        input.setAttribute('data-value', item.category_code);
                        datalist.appendChild(option);
                    });
            });
        });

    </script>
    <script>
        function toggleCategoryInput(show) {
            document.getElementById('choosecategoryoption')?.remove();
            document.getElementById('categoryInputDiv').style.display = show ? 'block' : 'none';
        }
        function toggleEmployeeInput(show) {
            document.getElementById('chooseemployeeoption')?.remove();
            document.getElementById('employeeInputDiv').style.display = show ? 'block' : 'none';
        }
    </script>
<?php include('partial/footer-end.php'); ?>