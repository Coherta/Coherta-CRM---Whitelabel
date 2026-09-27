 <!-- Google font-->
 <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/font-awesome.css">
 <!-- ico-font-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/icofont.css">
 <!-- Themify icon-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/themify.css">
 <!-- Flag icon-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/flag-icon.css">
 <!-- Feather icon-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/feather-icon.css">
 <!-- Plugins css start-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/slick.css">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/slick-theme.css">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/scrollbar.css">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/animate.css">
 <!-- Plugins css Ends-->
 <!-- Bootstrap css-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/bootstrap.css">
 <!-- App css-->
 <link id="color" rel="stylesheet" href="/app/assets/css/color-1.css" media="screen">
 <!-- Responsive css-->
 <link rel="stylesheet" type="text/css" href="/app/assets/css/style.css">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/responsive.css">
 <link rel="stylesheet" type="text/css" href="/da/css/maalgruppe-assistent.css">
 <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/themify.css">
 <style>
   .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper {
     height: 100vh !important;
   }
   .page-wrapper.compact-wrapper .page-body-wrapper .sidebar-wrapper > div {
     display: flex;
     flex-direction: column;
     height: 100%;
     min-height: 0;
   }
   .sidebar-wrapper .sidebar-main {
     flex: 1 1 auto;
     min-height: 0;
     padding-bottom: 104px;
   }
   .sidebar-wrapper .sidebar-main #sidebar-menu {
     height: 100%;
     min-height: 0;
   }
   .sidebar-wrapper .sidebar-main .sidebar-links {
     /* Anchor SimpleBar to the menu, which already follows the profile. */
     position: relative;
     display: block;
     height: 100% !important;
     margin-top: 8px !important;
     margin-bottom: 0 !important;
   }
   .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .sidebar-main .sidebar-links {
     left: 0;
     overflow-x: hidden;
   }
   .page-wrapper.compact-wrapper .sidebar-wrapper .sidebar-main .simplebar-content-wrapper {
     overflow-x: hidden !important;
   }
   .page-wrapper.compact-wrapper .sidebar-wrapper .sidebar-main .simplebar-track.simplebar-horizontal {
     display: none;
   }
   .page-wrapper.compact-wrapper .sidebar-wrapper .sidebar-main .simplebar-mask,
   .page-wrapper.compact-wrapper .sidebar-wrapper .sidebar-main .simplebar-track.simplebar-vertical {
     top: 0;
   }
   .sidebar-wrapper .sidebar-personal-profile {
     position: relative;
     z-index: 101;
     display: flex;
     align-items: center;
     gap: 11px;
     margin: 8px 18px 0;
     padding: 10px 12px;
     border: 1px solid #edf0f5;
     border-radius: 12px;
     color: #2f2f3b;
     text-decoration: none;
     background: #f8f9fb;
   }
   .sidebar-wrapper .sidebar-personal-profile + .sidebar-main {
     position: relative;
     z-index: 1;
     margin-top: 0;
   }
   .sidebar-wrapper .sidebar-personal-profile:hover {
     color: #0a66c2;
     background: #f2f6fb;
   }
   .sidebar-wrapper .sidebar-personal-profile img {
     width: 46px;
     height: 46px;
     flex: 0 0 46px;
     border-radius: 50%;
     object-fit: cover;
     border: 2px solid #fff;
     box-shadow: 0 2px 8px rgba(20, 40, 70, 0.16);
   }
   .sidebar-wrapper .sidebar-personal-profile span {
     min-width: 0;
   }
   .sidebar-wrapper .sidebar-personal-profile small,
   .sidebar-wrapper .sidebar-personal-profile strong {
     display: block;
     overflow: hidden;
     text-overflow: ellipsis;
     white-space: nowrap;
   }
   .sidebar-wrapper .sidebar-personal-profile small {
     margin-bottom: 2px;
     color: #8a8a9a;
     font-size: 10px;
   }
   .sidebar-wrapper .sidebar-personal-profile strong {
     font-size: 13px;
     font-weight: 600;
   }
   .sidebar-wrapper.close_icon .sidebar-personal-profile {
     justify-content: center;
     margin-right: 10px;
     margin-left: 10px;
     padding-right: 6px;
     padding-left: 6px;
   }
   .sidebar-wrapper.close_icon .sidebar-personal-profile span {
     display: none;
   }
   .sidebar-wrapper.close_icon:hover .sidebar-personal-profile {
     justify-content: flex-start;
     margin-right: 18px;
     margin-left: 18px;
     padding-right: 12px;
     padding-left: 12px;
   }
   .sidebar-wrapper.close_icon:hover .sidebar-personal-profile span {
     display: block;
   }
   .sidebar-wrapper .sidebar-subscription-action {
     position: absolute;
     right: 18px;
     bottom: 46px;
     left: 18px;
     z-index: 102;
     display: flex;
     align-items: center;
     gap: 10px;
     min-height: 44px;
     padding: 10px 14px;
     border: 1px solid #dce8f5;
     border-radius: 12px;
     background: #eef5fc;
     color: #0a66c2;
     font-size: 13px;
     font-weight: 600;
     text-decoration: none;
     box-shadow: 0 8px 20px rgba(20, 50, 90, 0.08);
   }
   .sidebar-wrapper .sidebar-subscription-action:hover {
     background: #e4f0fb;
     color: #084f96;
   }
   .sidebar-wrapper .sidebar-subscription-action svg {
     width: 18px;
     height: 18px;
     flex: 0 0 18px;
   }
   .sidebar-wrapper.close_icon .sidebar-subscription-action {
     right: 10px;
     left: 10px;
     justify-content: center;
     padding-right: 8px;
     padding-left: 8px;
   }
   .sidebar-wrapper.close_icon .sidebar-subscription-action span {
     display: none;
   }
   .sidebar-wrapper.close_icon:hover .sidebar-subscription-action {
     right: 18px;
     left: 18px;
     justify-content: flex-start;
     padding-right: 14px;
     padding-left: 14px;
   }
   .sidebar-wrapper.close_icon:hover .sidebar-subscription-action span {
     display: inline;
   }
   .sidebar-wrapper .sidebar-account-ids {
     position: absolute;
     right: 18px;
     bottom: 18px;
     left: 18px;
     z-index: 102;
     color: #9ca3af;
     font-size: 11px;
     line-height: 16px;
     white-space: nowrap;
   }
   .sidebar-wrapper.close_icon .sidebar-account-ids {
     display: none;
   }
   .sidebar-wrapper.close_icon:hover .sidebar-account-ids {
     display: block;
   }
 </style>
 <?php
 if(isset($stylesheets))
  foreach ($stylesheets as $sheet)
      echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($sheet) . '">' . "\n";
 ?>
