<?php include('partial/header.php');?>         
    
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
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Mouse Speed Chart</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder" id="mouse-speed-chart-sparkline"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Simple Bar Charts</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder" id="custom-line-chart"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Line Chart</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder line-chart-sparkline" id="line-chart-sparkline"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Simple Line Chart</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder" id="simple-line-chart-sparkline"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Bar Chart</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder" id="bar-chart-sparkline"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6 col-sm-12 box-col-6">
            <div class="card">
              <div class="card-header">
                <h5>Pie chart</h5>
              </div>
              <div class="card-body chart-block">
                <div class="flot-chart-container">
                  <div class="flot-chart-placeholder" id="pie-sparkline-chart"></div>
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

<script src="../assets/js/chart/sparkline/sparkline.js"></script>
<script src="../assets/js/chart/sparkline/sparkline-script.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>