<?php include('partial/header.php');?>
<link rel="stylesheet" type="text/css" href="../assets/css/vendors/prism.css">
<body class="dark-only">
    <?php include('partial/loader.php'); ?>
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php'); ?>
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
                            <div class="card alert alert-primary" role="alert">
                                <h5>Tip!</h5>
                                <p>Add the class="dark-only" to the body tag to get this layout.</p>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Title</h5>
                                    <div class="card-header-right">
                                        <ul class="list-unstyled card-option">
                                            <li><i class="fa fa-spin fa-cog"></i></li>
                                            <li><i class="view-html fa fa-code"></i></li>
                                            <li><i class="icofont icofont-maximize full-card"></i></li>
                                            <li><i class="icofont icofont-minus minimize-card"></i></li>
                                            <li><i class="icofont icofont-refresh reload-card"></i></li>
                                            <li><i class="icofont icofont-error close-card"></i></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body"><span>Start Here</span>
                                    <div class="code-box-copy">
                                        <button class="code-box-copy__btn btn-clipboard"
                                            data-clipboard-target="#example-head" title="Copy"><i
                                                class="icofont icofont-copy-alt"></i></button>
                                        <pre><code class="language-html" id="example-head">&lt;!-- Cod Box Copy begin --&gt;
&lt;div class=&quot;card-body&quot;&gt;
&lt;span&gt;Start Here&lt;/span&gt;
&lt;/div&gt;    
&lt;!-- Cod Box Copy end --&gt;</code></pre>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <h6 class="mb-0">Card Footer</h6>
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
<?php include('partial/scripts.php');?>
<script src="../assets/js/prism/prism.min.js"></script>
<script src="../assets/js/clipboard/clipboard.min.js"></script>
<script src="../assets/js/custom-card/custom-card.js"></script>
<script src="../assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php');?>