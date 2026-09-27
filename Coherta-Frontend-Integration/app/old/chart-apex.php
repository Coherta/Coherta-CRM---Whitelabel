<?php include('partial/header.php');?>

<?php include('partial/loader.php');?>

<!-- page-wrapper Start-->
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
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Basic Area Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="basic-apex"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Area Spaline Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="area-spaline"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar chart</h5>
                            </div>
                            <div class="card-body">
                                <div id="basic-bar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Column Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="column-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>
                                    3d Bubble Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="chart-bubble"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Stepline Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="stepline"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-12 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Column Chart</h5>
                            </div>
                            <div class="card-body">
                                <div id="annotationchart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Pie Chart </h5>
                            </div>
                            <div class="card-body apex-chart">
                                <div id="piechart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Donut Chart</h5>
                            </div>
                            <div class="card-body apex-chart">
                                <div id="donutchart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Mixed Chart</h5>
                            </div>
                            <div class="card-body">
                                <div id="mixedchart"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Candlestick Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="candlestick"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Radar Chart </h5>
                            </div>
                            <div class="card-body">
                                <div id="radarchart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Radial Bar Chart</h5>
                            </div>
                            <div class="card-body">
                                <div id="circlechart"></div>
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

<script src="../assets/js/chart/apex-chart/apex-chart.js"></script>
<script src="../assets/js/chart/apex-chart/stock-prices.js"></script>
<script src="../assets/js/chart/apex-chart/chart-custom.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>