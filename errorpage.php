<?php 
include('config.php');
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>HITUPMM Admin Dashboard</title>
    <link rel="apple-touch-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700"
        rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/vendors.min.css' ?>">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap-extended.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/colors.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/components.css' ?>">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/core/menu/menu-types/horizontal-menu.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/core/colors/palette-gradient.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/pages/error.css' ?>">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/assets/css/style.css' ?>">
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="horizontal-layout horizontal-menu horizontal-menu-padding 1-column   blank-page" data-open="click"
    data-menu="horizontal-menu" data-col="1-column">
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section class="flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="col-md-4 col-10 p-0">
                            <div class="card-header bg-transparent border-0">
                                <h2 class="error-code text-center mb-2">400</h2>
                                <h3 class="text-uppercase text-center">Bad Request</h3>
                            </div>
                            <div class="card-content">
                                <div class="row py-2">
                                    <div class="col-12 col-sm-12 col-md-12 mb-1">
                                        <a href="<?=roothtml.'index.php'?>" class="btn btn-primary btn-block"><i class="ft-home"></i>
                                            Home</a>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/vendors.min.js' ?>"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/ui/jquery.sticky.js' ?>"></script>
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/validation/jqBootstrapValidation.js' ?>"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="<?php echo roothtml.'lib/app-assets/js/core/app-menu.js' ?>"></script>
    <script src="<?php echo roothtml.'lib/app-assets/js/core/app.js' ?>"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="<?php echo roothtml.'lib/app-assets/js/scripts/forms/form-login-register.js' ?>"></script>
    <!-- END: Page JS-->

</body>
<!-- END: Body-->

</html>