<?php include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="../assets/css/vendors/calendar.css">

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
            <div class="container-fluid calendar-basic">
                <div class="card">
                    <div class="card-body">
                        <div class="row" id="wrap">
                            <div class="col-xxl-3 box-col-12">
                                <div class="md-sidebar mb-3"><a class="btn btn-primary md-sidebar-toggle" href="javascript:void(0)">calendar filter</a>
                                    <div class="md-sidebar-aside job-left-aside custom-scrollbar">
                                        <div id="external-events">
                                            <h4>Draggable Events</h4>
                                            <div id="external-events-list">
                                                <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event">
                                                    <div class="fc-event-main"> <i class="fa fa-birthday-cake me-2"></i>Birthday Party</div>
                                                </div>
                                                <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event">
                                                    <div class="fc-event-main"> <i class="fa fa-user me-2"></i>Meeting With Team.</div>
                                                </div>
                                                <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event">
                                                    <div class="fc-event-main"> <i class="fa fa-plane me-2"></i>Tour & Picnic</div>
                                                </div>
                                                <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event">
                                                    <div class="fc-event-main"> <i class="fa fa-file-text me-2"></i>Reporting Schedule</div>
                                                </div>
                                                <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event">
                                                    <div class="fc-event-main"> <i class="fa fa-briefcase me-2"></i>Lunch & Break</div>
                                                </div>
                                            </div>
                                            <p>
                                                <input class="checkbox_animated" id="drop-remove" type="checkbox">
                                                <label for="drop-remove">remove after drop</label>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-9 box-col-12">
                                <div class="calendar-default" id="calendar-container">
                                    <div id="calendar"></div>
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
<script src="../assets/js/calendar/fullcalendar.min.js"></script>
<script src="../assets/js/calendar/fullcalendar-custom.js"></script>
<?php include('partial/footer-end.php'); ?>