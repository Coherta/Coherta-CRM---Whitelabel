<?php include('partial/header.php');?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/datatables.css">

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
                    <!-- Server Side Processing start-->
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Server Side Processing</h5><span>In some tables you might wish to have some content
                                    generated automatically.</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display datatables" id="server-side-datatable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Server Side Processing end-->
                    <!-- http Server Side start-->
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Custom HTTP variables</h5><span>The example below shows server-side processing being
                                    used with an extra parameter being sent to the server by using the ajax.data option
                                    as a function</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display datatables" id="datatable-http">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- http Server Side end-->
                    <!-- post data Server Side start-->
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>POST Data</h5><span>The example below shows server-side processing being used with
                                    an extra parameter being sent to the server by using the ajax.data option as a
                                    function</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display datatables" id="datatable-post">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </tfoot>
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
<script src="../assets/js/datatable/datatables/datatable.custom.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>