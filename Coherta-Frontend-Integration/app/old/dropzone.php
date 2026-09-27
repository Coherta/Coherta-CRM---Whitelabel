<?php include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/dropzone.css">

<?php include('partial/loader.php'); ?>

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
              <div class="card-header">
                <h5>Single File Upload</h5>
              </div>
              <div class="card-body">
                <form class="dropzone" id="singleFileUpload" action="/upload.php">
                  <div class="dz-message needsclick"><i class="icon-cloud-up"></i>
                    <h6>Drop files here or click to upload.</h6><span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="card">
              <div class="card-header">
                <h5>Multi File Upload</h5>
              </div>
              <div class="card-body">
                <form class="dropzone dropzone-primary" id="multiFileUpload" action="/upload.php">
                  <div class="dz-message needsclick"><i class="icon-cloud-up"></i>
                    <h6>Drop files here or click to upload.</h6><span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="card">
              <div class="card-header">
                <h5>File Type Validation</h5>
              </div>
              <div class="card-body">
                <form class="dropzone dropzone-info" id="fileTypeValidation" action="/upload.php">
                  <div class="dz-message needsclick"><i class="icon-cloud-up"></i>
                    <h6>Drop files here or click to upload.</h6><span class="note needsclick">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span>
                  </div>
                </form>
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

<script src="../assets/js/dropzone/dropzone.js"></script>
<script src="../assets/js/dropzone/dropzone-script.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>