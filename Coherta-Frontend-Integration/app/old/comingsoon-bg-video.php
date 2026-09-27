<?php include('partial/header.php');?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Body Start-->
    <div class="container-fluid p-0">
        <div class="comingsoon auth-bg-video">
            <video class="bgvideo-comingsoon" id="bgvid" poster="../assets/images/other-images/coming-soon-bg.jpg"
                playsinline="" autoplay="" muted="" loop="">
                <source src="../assets/video/auth-bg.mp4" type="video/mp4">
            </video>
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

<!-- Plugins JS start-->
<script src="../assets/js/countdown.js"></script>
<!-- Plugins JS Ends-->

<?php include('partial/footer-end.php'); ?>