<?php
require_once "./base.php";
session_destroy();
require_once "./base.php";
$publicPage = True;
$PageTitle = "Logout";
include('partial/header.php');
?>
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card">
                    <div>
                        <p>You have been logged out successfully.<br>Redirecting to login page...</p>
                        <script>
                            sessionStorage.clear();
                            localStorage.clear();
                            setTimeout(function() {
                                window.location.href = '/app/login<?php if(isset($_GET['url'])) { echo "?url=".$_GET['url']; } ?>';
                            }, 5000);
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include('partial/footer-end.php'); ?>