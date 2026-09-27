<?php include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/jsgrid.css">

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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h3 class="mb-3">Basic Scenario</h3><span>Grid with filtering, editing, inserting, deleting, sorting and paging. Data provided by controller.</span>
                            </div>
                            <div class="card-body">
                                <div class="basic_table" id="basicScenario">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h3 class="mb-3">Sorting Scenario</h3><span>Sorting can be done not only with column header interaction, but also with sort method.</span>
                            </div>
                            <div class="card-body">
                                <div class="sort-panel mb-3">
                                    <label>Sorting Field:
                                        <select class="btn btn-primary dropdown-toggle btn-md pe-5" id="sortingField">
                                            <option>Id</option>
                                            <option>Product</option>
                                            <option>Order Id</option>
                                            <option>Price</option>
                                            <option>Quantity</option>
                                            <option>Shipped</option>
                                            <option>Total</option>
                                        </select>
                                    </label>
                                    <div class="d-inline">
                                        <button class="btn btn-md btn-secondary" id="sort" type="button">Sort</button>
                                    </div>
                                </div>
                                <div class="js-shorting" id="sorting-table"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h3 class="mb-3">Batch Delete</h3><span>Cell content of every column can be customized with itemTemplate, headerTemplate, filterTemplate and insertTemplate functions specified in field config. This example shows how to implement batch deleting with custom field for selecting items.</span>
                            </div>
                            <div class="card-body">
                                <div id="batchDelete"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="../assets/js/jsgrid/jsgrid.min.js"></script>
<script src="../assets/js/jsgrid/griddata.js"></script>
<script src="../assets/js/jsgrid/jsgrid.js"></script>
<?php include('partial/footer-end.php'); ?>