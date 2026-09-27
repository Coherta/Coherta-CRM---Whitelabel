
<?php include('partial/header.php');?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/timepicker.css">

<?php include('partial/header.php');?>

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
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Default:</h5>
                            </div>
                            <div class="card-body">
                                <form class="theme-form">
                                    <div>
                                        <div class="input-group clockpicker">
                                            <input class="form-control" type="text" value="09:30"><span
                                                class="input-group-addon"><span
                                                    class="glyphicon glyphicon-time"></span></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Align the arrow to top, auto close:</h5>
                            </div>
                            <div class="card-body">
                                <form class="theme-form">
                                    <div>
                                        <div class="input-group clockpicker pull-center" data-placement="left"
                                            data-align="top" data-autoclose="true">
                                            <input class="form-control" type="text" value="13:14"><span
                                                class="input-group-addon"><span
                                                    class="glyphicon glyphicon-time"></span></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Set options in javascript, instead of data-* :</h5>
                            </div>
                            <div class="card-body">
                                <form class="theme-form">
                                    <div>
                                        <div class="input-group clockpicker" data-placement="top" data-align="left"
                                            data-donetext="Done">
                                            <input class="form-control" type="text" value="18:00"><span
                                                class="input-group-addon"><span
                                                    class="glyphicon glyphicon-time"></span></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Set default value, input without addon:</h5>
                            </div>
                            <div class="card-body">
                                <div class="clearfix">
                                    <form class="theme-form">
                                        <div>
                                            <input class="form-control" id="single-input" type="text" value=""
                                                placeholder="Addon">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid ends                    -->
        </div>
        
        <?php include('partial/footer.php');?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="../assets/js/time-picker/jquery-clockpicker.min.js"></script>
<script src="../assets/js/time-picker/highlight.min.js"></script>
<script src="../assets/js/time-picker/clockpicker.js"></script>
<script src="../assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php'); ?>