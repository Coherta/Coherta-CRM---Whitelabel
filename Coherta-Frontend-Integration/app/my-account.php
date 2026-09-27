<?php
require_once "./base.php";
$cohertaFreeIntroductionPage = true;
if(isset($_GET['start_impersonate'])) {
    session_start();
    $token = $_SESSION['access_token'];
    if($token == "" or $token == NULL) {
        $token = $_SESSION['token'];
    }
    if($token == "" or $token == NULL) {
        echo "<script>
                window.location.href = 'https://www.coherta.com/app/login?url=" . urlencode("https://www.coherta.com/app/my-account.php?start_impersonate=".$_GET['start_impersonate']) . "';
            </script>";
        die();
    }
    $client->setToken($token);
    $user = $client->getCurrentCompanies();
    if($user == NULL) {
        echo "<script>
                const sessionToken = localStorage.getItem('session_id');
                window.location.href = 'https://api.coherta.com/coherta/impersonate?company_id=".$_GET['start_impersonate']."&session_id=' + sessionToken;
            </script>";
        die();
    }
    if($user[0]['id'] != 1) {
        $url = "https://www.coherta.com/app/my-account.php?start_impersonate=".$_GET['start_impersonate'];
        echo "<script>
                localStorage.removeItem('session_id');
                window.location.href = 'https://www.coherta.com/app/login?url=" . urlencode($url) ."'
            </script>";
        die();
    }
    header("Location: https://api.coherta.com/coherta/impersonate?company_id=".$_GET['start_impersonate']."&session_id=".$token);
    exit;
}
if(isset($_GET['impersonate'])) {
    $agency_token = $_SESSION["token"];
    session_destroy();
    session_start();
    $id = $client->set_access_token($_GET['impersonate']);
    $client->setToken($_GET['impersonate']);
    $user = $client->getCurrentUser();
    if(!isset($_GET['s']))
        $_SESSION['return_to_agency'] = $agency_token;
    header("location: my-account.php");
    exit;
}
$PageTitle = "Settings";
include('partial/header.php');
?>

<link rel="stylesheet" type="text/css" href="assets/css/vendors/todo.css">

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
            <div class="container-fluid email-wrap bookmark-wrap todo-wrap">
                <div class="row">
                    <?php

                        echo '
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="company-activity.php">Company activity</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="to-do.php">Taskboard</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="users.php">Users</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="connect_linkedin.php">LinkedIn Connect</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="/app/connect_whatsapp">WhatsApp Connect</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="clean_connections.php">Clean my connections</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="connect_office365.php">Office365 Integration</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                            <div class="card social-profile">
                                <div class="card-body position-relative">
                                    <div class="social-details">
                                        <h5 class="mb-1">
                                            <a class="stretched-link" href="change_password.php">Change Password</a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    ?>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                        <div class="card social-profile">
                            <div class="card-body position-relative">
                                <div class="social-details">
                                    <h5 class="mb-1">
                                        <a class="stretched-link" href="dripify">Dripify Integration</a>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                        <div class="card social-profile">
                            <div class="card-body position-relative">
                                <div class="social-details">
                                    <h5 class="mb-1">
                                        <a class="stretched-link" href="campaign">Coherta Campaigns</a>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
            <!-- Container-fluid ends                    -->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>
<?php include('partial/scripts.php'); ?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    jQuery(function() {
        let currentAutocompleteRequest = null;
        jQuery(function() {
            jQuery("#customer-autocomplete").autocomplete({
                source: function(request, response) {
                    if (currentAutocompleteRequest) {
                        currentAutocompleteRequest.abort();
                    }
                    currentAutocompleteRequest = $.ajax({
                        url: "https://api.coherta.com/pipeline/leads/search",
                        dataType: "json",
                        headers: {
                            'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                            'Accept': 'application/json'
                        },
                        data: { company_name: request.term },
                        success: function(data) {
                            response($.map(data, function(item, key) {
                                return {
                                    label: item,
                                    value: key
                                };
                            }));
                        },
                        complete: function() {
                            currentAutocompleteRequest = null;
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    jQuery(this).val(ui.item.label);
                    jQuery(this).attr('data-id', ui.item.value);
                    return false;
                }
            });
        });

    });
</script>
<script src="assets/js/todo/todo.js"></script>
<script src="assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>
