
<?php include('partial/header.php');?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/datatables.css">
<link rel="stylesheet" type="text/css" href="../assets/css/vendors/datatable-extension.css">

<?php include('partial/loader.php');?>

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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-3">Basic Table Scroll</h5><span>Scroller is a plug-in for DataTables which
                                    enhances DataTables' built-in scrolling features to allow large amounts of data to
                                    be rendered on page very quickly.This is done by Scroller through the use of a
                                    virtual rendering technique that will render only the part of the table that is
                                    actually required for the current view.</span>
                            </div>
                            <div class="card-body">
                                <div class="dt-ext table-responsive">
                                    <table class="display nowrap" id="basic-scroller">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First name</th>
                                                <th>Last name</th>
                                                <th>ZIP / Post code</th>
                                                <th>Country</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-3">State Saving</h5><span>Scroller will automatically integrate with
                                    DataTables in order to save the scrolling position of the table, if state saving is
                                    enabled in the DataTable (stateSave). This example shows that in practice - to
                                    demonstrate, scroll the table and then reload the page.</span>
                            </div>
                            <div class="card-body">
                                <div class="dt-ext table-responsive">
                                    <table class="display nowrap" id="state-saving">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First name</th>
                                                <th>Last name</th>
                                                <th>ZIP / Post code</th>
                                                <th>Country</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-3">API</h5><span>This example shows a trivial use of the API methods that
                                    Scroller adds to the DataTables API to scroll to a row once the table's data has
                                    been loaded.</span>
                            </div>
                            <div class="card-body">
                                <div class="dt-ext table-responsive">
                                    <table class="display nowrap" id="api">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First name</th>
                                                <th>Last name</th>
                                                <th>ZIP / Post code</th>
                                                <th>Country</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        
        <?php include('partial/footer.php');?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>

<script src="../assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.buttons.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/jszip.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/buttons.colVis.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/pdfmake.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/vfs_fonts.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.autoFill.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.select.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/buttons.html5.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/buttons.print.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.bootstrap4.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.responsive.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/responsive.bootstrap4.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.keyTable.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.colReorder.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.fixedHeader.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.rowReorder.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/dataTables.scroller.min.js"></script>
<script src="../assets/js/datatable/datatable-extension/custom.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>