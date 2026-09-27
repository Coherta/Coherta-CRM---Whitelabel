<?php include('partial/header.php');?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/vector-map.css">

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
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR WORLD MAP</h5><span>Below Map is displaying the world map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="world-map"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR USA MAP</h5><span>Below Map is displaying the usa map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="usa"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR CANADA MAP</h5><span>Below Map is displaying the canada map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="canada"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR INDIA MAP</h5><span>Below Map is displaying the india map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="india"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR ASIA MAP</h5><span>Below Map is displaying the asia map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="asia"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR CANADA MAP</h5><span>Below Map is displaying the uk map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="uk"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR CHICAGO MAP</h5><span>Below Map is displaying the chicago map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="chicago"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>VECTOR AUSTRALIA MAP</h5><span>Below Map is displaying the australia map.</span>
                            </div>
                            <div class="card-body">
                                <div class="jvector-map-height" id="australia"></div>
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

<script src="../assets/js/vector-map/jquery-jvectormap-2.0.2.min.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-world-mill-en.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-us-aea-en.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-uk-mill-en.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-au-mill.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-chicago-mill-en.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-in-mill.js"></script>
<script src="../assets/js/vector-map/map/jquery-jvectormap-asia-mill.js"></script>
<script src="../assets/js/vector-map/map-vector.js"></script>
<script src="../assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php'); ?>