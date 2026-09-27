<?php include('partial/header.php') ?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/jkanban.css">
<?php include('partial/loader.php') ?>
<!-- page-wrapper Start-->
<div class="page-wrapper default-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php') ?>
    <!-- Page Header Ends-->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php') ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php') ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid jkanban-container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Default Demo </h5>
                            </div>
                            <div class="card-body">
                                <div id="demo1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Custom Board </h5>
                                <p class="mb-0">| colors, gutter, click on board&apos;s item and restricting which boards to drag items to </p>
                            </div>
                            <div class="card-body">
                                <div id="demo2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>API</h5>
                                <p class="mb-0">add item, add board, delete board: </p>
                            </div>
                            <div class="card-body">
                                <div id="demo3"></div>
                                <button class="btn btn-success" id="addDefault">Add &quot;Default&quot; board</button>
                                <button class="btn btn-success" id="addToDo">Add element in &quot;To Do&quot; Board</button>
                                <button class="btn btn-danger" id="removeBoard">Remove &quot;Done&quot; Board</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card note p-20">jKanban is Pure agnostic Javascript plugin for Kanban boards for more options please refer <a href="http://www.riccardotartaglia.it/jkanban/" target="_blank">jkanban Official site </a>And <a href="https://github.com/riktar/jkanban" target="_blank">githup link</a></div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
        <?php include('partial/footer.php') ?>
    </div>
</div>
<?php include('partial/scripts.php') ?>
<!-- Plugins JS start-->
<script src="../assets/js/jkanban/jkanban.js"></script>
<script src="../assets/js/jkanban/custom.js"></script>
<script src="../assets/js/typeahead/handlebars.js"></script>
<script src="../assets/js/typeahead/typeahead.bundle.js"></script>
<script src="../assets/js/typeahead/typeahead.custom.js"></script>
<script src="../assets/js/typeahead-search/handlebars.js"></script>
<script src="../assets/js/typeahead-search/typeahead-custom.js"></script>
<script src="../assets/js/animation/wow/wow.min.js"></script>
<!-- Plugins JS Ends-->
<script>
    new WOW().init();
</script>
<?php include('partial/footer-end.php') ?>