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
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <canvas id="myBarGraph"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Line Graph</h5>
                            </div>
                            <div class="card-body chart-block">
                                <canvas id="myGraph"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Radar Graph</h5>
                            </div>
                            <div class="card-body chart-block">
                                <canvas id="myRadarGraph"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Line Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <canvas id="myLineCharts"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Doughnut Chart</h5>
                            </div>
                            <div class="card-body chart-block chart-vertical-center">
                                <canvas id="myDoughnutGraph"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Polar Chart</h5>
                            </div>
                            <div class="card-body chart-block chart-vertical-center">
                                <canvas id="myPolarGraph"></canvas>
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

<script src="../assets/js/chart/chartjs/chart.min.js"></script>
<script src="../assets/js/chart/chartjs/chart.custom.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>