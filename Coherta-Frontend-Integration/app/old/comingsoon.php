<?php include('partial/header.php');?>
<?php include('partial/loader.php');?>

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Body Start-->
    <div class="container-fluid p-0">
        <div class="comingsoon">
            <div class="comingsoon-inner text-center"><img src="../assets/images/other-images/logo-login.png" alt="" width="248" height="145">
                <h5>WE ARE COMING SOON</h5>
                <div class="countdown" id="clockdiv">
                    <ul>
                        <li><span class="time" id="days"></span><span class="title">days</span></li>
                        <li><span class="time" id="hours"></span><span class="title">Hours</span></li>
                        <li><span class="time" id="minutes"></span><span class="title">Minutes</span></li>
                        <li><span class="time" id="seconds"></span><span class="title">Seconds</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="../assets/js/countdown.js"></script>
<?php include('partial/footer-end.php'); ?>