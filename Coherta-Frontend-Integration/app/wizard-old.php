<?php
require_once "./base.php";

$PageTitle = "Wizard";


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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Generate your next Leadlist</h5>
                            </div>
                            <div class="card-body">
                                <form class="form-wizard" id="regForm" action="#" method="POST">
                                    <div class="tab">
                                        <div class="mb-2">
                                            <div class="col-form-label">Name of your new Lead list</div>
                                            <input type="text"
                                                   class="form-control filter-value"
                                                   placeholder="Lead list name"
                                                   value="Lead list"
                                                   name="title"
                                                   id="title"
                                                   required
                                                   onclick="if(this.value.toLowerCase() === 'lead list') this.value=''">
                                        </div>
                                        <div class="mb-2">
                                            <div class="col-form-label">Country of Origin</div>
                                            <div class="select2-drpdwn">
                                                <select class="js-basic-single col-sm-12" name="origin" multiple="multiple">
                                                    <option value="DK">Denmark</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="addedFilters"></div>
                                        <div class="mb-3 select2-hidden-accessible add-filters">
                                            <div class="mb-2">
                                                <div class="col-form-label">Type of Filter</div>
                                                <div class="select2-drpdwn">
                                                    <select class="js-basic-single col-sm-12 allFilters" multiple="multiple">
                                                        <option value="company_name">Company name</option>
                                                        <option value="postal_code">Postal Code</option>
                                                        <option value="road_name">Road name</option>
                                                        <option value="city">City name</option>
                                                        <option value="phone">Phone number</option>
                                                        <option value="email">Email</option>
                                                        <option value="website">Website</option>
                                                        <option value="main_industry">Industry</option>
                                                        <option value="category_code">Category Code</option>
                                                        <option value="employees">Number of Employees</option>
                                                    </select>
                                                </div>
                                                <center>
                                                    <button class="btn btn-primary" type="button" data-bs-toggle="tooltip" title="btn btn-primary" onClick="renderFilters()">Add filters</button>
                                                </center>
                                            </div>
                                        </div>
                                        <div class="mb-3 findFilters">
                                            <center>
                                                <button class="btn btn-primary" type="button" data-bs-toggle="tooltip" title="btn btn-primary" onClick="findFilters()">Add more filters</button>
                                            </center>
                                        </div>
                                    </div>
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
                                </form>
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

<script src="assets/js/form-wizard/form-wizard.js"></script>
<script src="assets/js/tooltip-init.js"></script>
<script src="assets/js/select2/select2.full.min.js"></script>
<script src="assets/js/select2/select2-custom.js"></script>
<script src="assets/js/select2/addFilters.js"></script>

<?php include('partial/footer-end.php'); ?>