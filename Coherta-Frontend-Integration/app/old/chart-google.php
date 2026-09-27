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
                                <h5>Area Chart <span>1</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="area-chart1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Area Chart <span>2</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="area-chart2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Column Chart <span>1 </span></h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="chart-overflow" id="column-chart1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Column Chart <span>2</span></h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="chart-overflow" id="column-chart2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Gantt Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="chart-overflow" id="gantt_chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Line Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="chart-overflow" id="line-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Combo Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="chart-overflow" id="combo-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>bar-chart2</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div id="bar-chart2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>word tree</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="word-tree" id="wordtree_basic"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Pie Chart <span>1</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="pie-chart1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Pie Chart <span>2</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="pie-chart3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Pie Chart <span>3</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="pie-chart4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6 box-col-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Pie Chart <span>4</span></h5>
                            </div>
                            <div class="card-body p-0 chart-block">
                                <div class="chart-overflow" id="pie-chart2"></div>
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

<script src="../assets/js/chart/google/google-chart-loader.js"></script>
<script src="../assets/js/chart/google/google-chart.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>