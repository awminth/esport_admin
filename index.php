<?php
    include('config.php');
?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description"
        content="Modern admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities with bitcoin dashboard.">
    <meta name="keywords"
        content="admin template, modern admin template, dashboard template, flat admin template, responsive admin template, web app, crypto dashboard, bitcoin dashboard">
    <meta name="author" content="PIXINVENT">
    <title>HITUPMM Admin Dashboard</title>
    <link rel="apple-touch-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700"
        rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/vendors.min.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/icheck/icheck.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/icheck/custom.css'?>">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap-extended.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/colors.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/components.css'?>">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/css/core/menu/menu-types/vertical-menu-modern.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/css/core/colors/palette-gradient.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/pages/login-register.css'?>">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/assets/css/style.css'?>">
    <!-- END: Custom CSS-->
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo roothtml.'lib/plugins/fontawesome-free/css/all.min.css' ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?php echo roothtml.'lib/plugins/icheck-bootstrap/icheck-bootstrap.min.css' ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo roothtml.'lib/dist/css/adminlte.min.css' ?>">
    <!-- Sweet Alarm -->
    <link href="<?=roothtml.'lib/sweet_v2/sweetalert2.min.css' ?>" rel="stylesheet" />
    <script src="<?=roothtml.'lib/sweet_v2/sweetalert2.min.js' ?>"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

    * {
        font-family: 'Poppins', sans-serif;
    }

    .loader {
        position: fixed;
        z-index: 999;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
        background-color: Black;
        filter: alpha(opacity=60);
        opacity: 0.7;
        -moz-opacity: 0.8;
    }

    .center-load {
        z-index: 1000;
        margin: 300px auto;
        padding: 10px;
        width: 130px;
        background-color: black;
        border-radius: 10px;
        filter: 1;
        -moz-opacity: 1;
    }

    .center-load img {
        height: 128px;
        width: 128px;
    }
    </style>

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern 1-column bg-secondary bg-full-screen-image blank-page" data-open="click"
    data-menu="vertical-menu-modern" data-col="1-column">
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section class="row flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="col-lg-4 col-md-8 col-10 box-shadow-2 p-0">
                            <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
                                <div class="card-header border-0">
                                    <div class="card-title text-center">
                                        <img src="<?php echo roothtml.'lib/images/apple-icon-120.png'?>"
                                            alt="branding logo">
                                    </div>
                                    <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                                        <span>Using Account Detail</span>
                                    </h6>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form-horizontal" id="frmlogin" method="post" novalidate>
                                            <input type="hidden" name="action" value="login" />
                                            <fieldset class="form-group position-relative has-icon-left">
                                                <input type="text" class="form-control"
                                                    value="<?php if(isset($_COOKIE['member_login'])){ echo $_COOKIE['member_login'];}?>"
                                                    name="username" placeholder="Your Username" required id="username">
                                                <div class="form-control-position">
                                                    <i class="la la-user"></i>
                                                </div>
                                            </fieldset>
                                            <fieldset class="form-group position-relative has-icon-left">
                                                <input type="password" class="form-control"
                                                    value="<?php if(isset($_COOKIE['member_login'])){ echo $_COOKIE['member_password'];}?>"
                                                    name="password" placeholder="Enter Password" required>
                                                <div class="form-control-position">
                                                    <i class="la la-key"></i>
                                                </div>
                                            </fieldset>
                                            <div class="form-group row">
                                                <div class="col-sm-6 col-12 text-center text-sm-left pr-0">
                                                    <fieldset>
                                                        <input type="checkbox"
                                                            <?php if(isset($_COOKIE['member_password'])){?> checked
                                                            <?php } ?> id="remember" class="chk-remember">
                                                        <label for="remember-me"> Remember Me</label>
                                                    </fieldset>
                                                </div>
                                                <div class="col-sm-6 col-12 float-sm-left text-center text-sm-right"><a
                                                        href="#" class="card-link">Forgot Password?</a></div>
                                            </div>
                                            <button type="submit" id="btnlogin"
                                                class="btn btn-outline-info btn-block"><i class="ft-unlock"></i>
                                                Login</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <div class="loader" style="display:none;">
        <div class="center-load">
            <img src="<?php echo roothtml.'lib/images/ajax-loader1.gif'?>" />
        </div>
    </div>
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/vendors.min.js'?>"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/validation/jqBootstrapValidation.js'?>"></script>
    <script src="<?php echo roothtml.'lib/app-assets/vendors/js/forms/icheck/icheck.min.js'?>"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="<?php echo roothtml.'lib/app-assets/js/core/app-menu.js'?>"></script>
    <script src="<?php echo roothtml.'lib/app-assets/js/core/app.js'?>"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="<?php echo roothtml.'lib/app-assets/js/scripts/forms/form-login-register.js'?>"></script>
    <!-- END: Page JS-->

    <script>
    $(document).ready(function() {

        $("#username").focus();

        $("#frmlogin").on("submit", function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: "post",
                url: "<?php echo roothtml.'index_action.php' ?>",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".loader").show();
                },
                success: function(data) {
                    $(".loader").hide();
                    if (data == 1) {
                        Swal.fire(
                            'Success!',
                            'Login Successfully.',
                            'success'
                        );
                        location.href = "<?=roothtml.'home/home.php' ?>";
                    } else {
                        Swal.fire(
                            'Error!',
                            'Login failed.',
                            'error'
                        );
                    }
                }
            });
        });

    });
    </script>

</body>
<!-- END: Body-->

</html>