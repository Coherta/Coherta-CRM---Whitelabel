<?php include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/datatables.css">

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
            <div class="container-fluid">
                <div class="page-title">
                    <div class="row">
                        <div class="col-6">
                            <h3>Recent Orders</h3>
                        </div>
                        <div class="col-6">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="theme/index.php"> <i data-feather="home"></i></a></li>
                                <li class="breadcrumb-item">Ecommerce</li>
                                <li class="breadcrumb-item active"> Recent Orders</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>New Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-sm-4 g-3">
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#"> Women Shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-4.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Cyclamen </a></h6>
                                                    </div>
                                                    <div class="rating"> <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-3.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Men Solid Denim jacket</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-2.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Blue shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-1.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">red shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-1.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Red shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-primary btn-xs" href="#">Processing</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5>Shipped Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-sm-4 g-3">
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-4.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Cyclamen</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-3.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Men Solid Denim jacket</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-3.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Blue shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-2.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">red shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Red shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"> </i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-1.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-success btn-xs" href="#">Shipped</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5>Cancelled Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-sm-4 g-3">
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-4.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Cyclamen</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-3.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Men Solid Denim jacket</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-2.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Blue shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-1.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">red shirt </a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-1.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Red shirt</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-6.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women Top</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-md-6">
                                        <div class="prooduct-details-box">
                                            <div class="media"><img class="align-self-center img-fluid img-60" src="../assets/images/ecommerce/product-table-5.png" alt="#" width="64" height="64">
                                                <div class="media-body ms-3">
                                                    <div class="product-name">
                                                        <h6><a href="#">Women shorts</a></h6>
                                                    </div>
                                                    <div class="rating"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                                                    <div class="price d-flex">
                                                        <div class="text-muted me-2">Price</div>: 210$
                                                    </div>
                                                    <div class="avaiabilty">
                                                        <div class="text-success">In stock</div>
                                                    </div><a class="btn btn-danger btn-xs" href="#">Cancelled</a><i class="close" data-feather="x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Datatable order history</h5>
                            </div>
                            <div class="card-body">
                                <div class="order-history table-responsive">
                                    <table class="table table-bordernone display" id="basic-1">
                                        <thead>
                                            <tr>
                                                <th scope="col">Prdouct</th>
                                                <th scope="col">Prdouct name</th>
                                                <th scope="col">Size</th>
                                                <th scope="col">Color</th>
                                                <th scope="col">Article number</th>
                                                <th scope="col">Units</th>
                                                <th scope="col">Price</th>
                                                <th scope="col"><i class="fa fa-angle-down"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/1.png" alt="#" width="600" height="600"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Long Top</a>
                                                        <div class="order-process"><span class="order-process-circle"></span>Processing</div>
                                                    </div>
                                                </td>
                                                <td>M</td>
                                                <td>Lavander</td>
                                                <td>4215738</td>
                                                <td>1</td>
                                                <td>$21</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/13.png" alt="#" width="750" height="807"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Fancy watch</a>
                                                        <div class="order-process"><span class="order-process-circle"></span>Processing</div>
                                                    </div>
                                                </td>
                                                <td>35mm</td>
                                                <td>Blue</td>
                                                <td>5476182</td>
                                                <td>1</td>
                                                <td>$10</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/4.png" alt="#" width="600" height="600"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Man shoes</a>
                                                        <div class="order-process"><span class="order-process-circle"></span>Processing</div>
                                                    </div>
                                                </td>
                                                <td>8</td>
                                                <td>Black & white</td>
                                                <td>1756457</td>
                                                <td>1</td>
                                                <td>$18</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/10.png" alt="#" width="893" height="1024"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Ledis side bag</a>
                                                        <div class="order-process"><span class="order-process-circle shipped-order"></span>Shipped</div>
                                                    </div>
                                                </td>
                                                <td>22cm x 18cm</td>
                                                <td>Brown</td>
                                                <td>7451725</td>
                                                <td>1</td>
                                                <td>$13</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/12.png" alt="#" width="875" height="1024"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Ledis Slipper</a>
                                                        <div class="order-process"><span class="order-process-circle shipped-order"></span>Shipped</div>
                                                    </div>
                                                </td>
                                                <td>6</td>
                                                <td>Brown & white</td>
                                                <td>4127421</td>
                                                <td>1</td>
                                                <td>$6</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/3.png" alt="#" width="1600" height="1571"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Fancy ledis Jacket</a>
                                                        <div class="order-process"><span class="order-process-circle shipped-order"></span>Shipped</div>
                                                    </div>
                                                </td>
                                                <td>Xl</td>
                                                <td>Light gray</td>
                                                <td>3581714</td>
                                                <td>1</td>
                                                <td>$24</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/2.png" alt="#" width="526" height="526"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Ledis Handbag</a>
                                                        <div class="order-process"><span class="order-process-circle shipped-order"></span>Shipped</div>
                                                    </div>
                                                </td>
                                                <td>25cm x 20cm</td>
                                                <td>Black</td>
                                                <td>6748142</td>
                                                <td>1</td>
                                                <td>$14</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/15.png" alt="#" width="1024" height="1024"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Iphone6 mobile</a>
                                                        <div class="order-process"><span class="order-process-circle cancel-order"></span>Cancelled</div>
                                                    </div>
                                                </td>
                                                <td>10cm x 15cm</td>
                                                <td>Black</td>
                                                <td>5748214</td>
                                                <td>1</td>
                                                <td>$25</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/14.png" alt="#" width="1800" height="1800"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Watch</a>
                                                        <div class="order-process"><span class="order-process-circle cancel-order"></span>Cancelled</div>
                                                    </div>
                                                </td>
                                                <td>27mm</td>
                                                <td>Brown</td>
                                                <td>2471254</td>
                                                <td>1</td>
                                                <td>$12</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                            <tr>
                                                <td><img class="img-fluid img-30" src="../assets/images/product/11.png" alt="#" width="1762" height="1581"></td>
                                                <td>
                                                    <div class="product-name"><a href="#">Slipper</a>
                                                        <div class="order-process"><span class="order-process-circle cancel-order"></span>Cancelled</div>
                                                    </div>
                                                </td>
                                                <td>6</td>
                                                <td>Blue</td>
                                                <td>8475112</td>
                                                <td>1</td>
                                                <td>$6</td>
                                                <td><i data-feather="more-vertical"></i></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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

<script src="../assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
<script src="../assets/js/datatable/datatables/datatable.custom.js"></script>

<?php include('partial/footer-end.php'); ?>