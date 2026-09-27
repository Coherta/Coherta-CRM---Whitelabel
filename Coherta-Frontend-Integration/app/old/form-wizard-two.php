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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Form Wizard And Validation</h5><span>Validation Step Form Wizard</span>
                            </div>
                            <div class="card-body">
                                <div class="stepwizard">
                                    <div class="stepwizard-row setup-panel">
                                        <div class="stepwizard-step"><a class="btn btn-primary" href="#step-1">1</a>
                                            <p>Step 1</p>
                                        </div>
                                        <div class="stepwizard-step"><a class="btn btn-light" href="#step-2">2</a>
                                            <p>Step 2</p>
                                        </div>
                                        <div class="stepwizard-step"><a class="btn btn-light" href="#step-3">3</a>
                                            <p>Step 3</p>
                                        </div>
                                        <div class="stepwizard-step"><a class="btn btn-light" href="#step-4">4</a>
                                            <p>Step 4</p>
                                        </div>
                                    </div>
                                </div>
                                <form action="#" method="POST">
                                    <div class="setup-content" id="step-1">
                                        <div class="col-xs-12">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="control-label">First Name</label>
                                                    <input class="form-control" type="text" placeholder="Johan"
                                                        required="required">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="control-label">Last Name</label>
                                                    <input class="form-control" type="text" placeholder="Deo"
                                                        required="required">
                                                </div>
                                                <button class="btn btn-primary nextBtn pull-right"
                                                    type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="setup-content" id="step-2">
                                        <div class="col-xs-12">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="control-label">Email</label>
                                                    <input class="form-control" type="text"
                                                        placeholder="name@example.com" required="required">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="control-label">Password</label>
                                                    <input class="form-control" type="password" placeholder="Password"
                                                        required="required">
                                                </div>
                                                <button class="btn btn-primary nextBtn pull-right"
                                                    type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="setup-content" id="step-3">
                                        <div class="col-xs-12">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="control-label">Birth date</label>
                                                    <input class="form-control" type="date" required="required">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="control-label">Have Passport</label>
                                                    <input class="form-control" type="text" placeholder="yes/No"
                                                        required="required">
                                                </div>
                                                <button class="btn btn-primary nextBtn pull-right"
                                                    type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="setup-content" id="step-4">
                                        <div class="col-xs-12">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="control-label">State</label>
                                                    <input class="form-control mt-1" type="text" placeholder="State"
                                                        required="required">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="control-label">City</label>
                                                    <input class="form-control mt-1" type="text" placeholder="City"
                                                        required="required">
                                                </div>
                                                <button class="btn btn-success pull-right"
                                                    type="submit">Finish!</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
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

<script src="../assets/js/form-wizard/form-wizard-two.js"></script>
<script src="../assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>