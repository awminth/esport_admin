<?php 
if(isset($_SESSION['esport_admin_userid'])){

    $aid = (isset($_SESSION["esport_admin_userid"])?$_SESSION["esport_admin_userid"]:0);
    $sql = "select * from tbluser where AID={$aid}";
    $result = mysqli_query($con,$sql) or die("SQL a Query");
    $row = mysqli_fetch_array($result);

    $profile_img = roothtml.'lib/images/user.png';
    if($row["Img"] != "" || $row["Img"] != NULL){
        $profile_img = roothtml.'lib/images/'.$row["Img"];
    }

    $P1 = $row["P1"];
    $P2 = $row["P2"];

    $M1 = $row["M1"];
    $M1P1 = $row["M1P1"];
    $M1P2 = $row["M1P2"];
    $M1P3 = $row["M1P3"];

    $M2 = $row["M2"];
    $M2P1 = $row["M2P1"];
    $M2P2 = $row["M2P2"];
    $M2P3 = $row["M2P3"];

    $M3 = $row["M3"];
    $M3P1 = $row["M3P1"];
    $M3P2 = $row["M3P2"];
    $M3P3 = $row["M3P3"];

    $M4 = $row["M4"];
    $M4P1 = $row["M4P1"];
    $M4P2 = $row["M4P2"];
    $M4P3 = $row["M4P3"];
    $M4P4 = $row["M4P4"];

    $M5 = $row["M5"];
    $M5P1 = $row["M5P1"];
    $M5P2 = $row["M5P2"];
    $M5P3 = $row["M5P3"];

    // top-up notification
    if (!isset($_SESSION['user_topup_count'])) {
        $_SESSION['user_topup_count'] = 0;
    }
    $initial_topup_style = ($_SESSION['user_topup_count'] > 0) ? '' : 'style="display:none;"';

    // withdraw notification
    if (!isset($_SESSION['user_withdraw_count'])) {
        $_SESSION['user_withdraw_count'] = 0;
    }
    $initial_withdraw_style = ($_SESSION['user_withdraw_count'] > 0) ? '' : 'style="display:none;"';

?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>HITUPMM Admin Dashboard</title>
    <link rel="apple-touch-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet"> -->

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/vendors.min.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/vendors/css/weather-icons/climacons.min.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/fonts/meteocons/style.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/charts/morris.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/vendors/css/charts/chartist.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/vendors/css/charts/chartist-plugin-tooltip.css'?>">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/bootstrap-extended.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/colors.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/components.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/icheck/icheck.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/icheck/custom.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/plugins/forms/checkboxes-radios.css'?>">
    
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/toggle/bootstrap-switch.min.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/vendors/css/forms/toggle/switchery.min.css'?>">    
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/fonts/simple-line-icons/style.min.css'?>">
    <link rel="stylesheet" type="text/css" href="<?=roothtml.'lib/app-assets/css/plugins/forms/switch.css'?>">
    <!-- END: Theme CSS-->

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/css/core/menu/menu-types/vertical-menu-modern.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/css/core/colors/palette-gradient.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/fonts/simple-line-icons/style.css'?>">
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/app-assets/css/pages/timeline.css'?>">
    <link rel="stylesheet" type="text/css"
        href="<?php echo roothtml.'lib/app-assets/css/pages/dashboard-ecommerce.css'?>">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo roothtml.'lib/assets/css/style.css'?>">
    <!-- END: Custom CSS-->

    <!-- Sweet Alarm -->
    <link href="<?=roothtml.'lib/sweet_v2/sweetalert2.min.css' ?>" rel="stylesheet" />
    <script src="<?=roothtml.'lib/sweet_v2/sweetalert2.min.js' ?>"></script>
    <!-- for print -->
    <link href="<?php echo roothtml.'lib/print.min.css' ?>" rel="stylesheet" />
    <!-- Select2 -->
    <link rel="stylesheet" type="text/css"
        href="<?=roothtml.'lib/app-assets/vendors/css/forms/selects/select2.min.css'?>">

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern 2-columns   fixed-navbar" data-open="click"
    data-menu="vertical-menu-modern" data-col="2-columns">

    <!-- BEGIN: Header-->
    <nav
        class="header-navbar navbar-expand-lg navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav flex-row">
                    <li class="nav-item mobile-menu d-lg-none mr-auto"><a
                            class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i
                                class="ft-menu font-large-1"></i></a></li>
                    <li class="nav-item mr-auto"><a class="navbar-brand" href="<?=roothtml.'home/home.php'?>"><img class="brand-logo"
                                alt="modern admin logo"
                                src="<?php echo roothtml.'lib/images/apple-icon-120.png'?>">
                            <h3 class="brand-text">HITUPMM</h3>
                        </a></li>
                    <li class="nav-item d-none d-lg-block nav-toggle"><a class="nav-link modern-nav-toggle pr-0"
                            data-toggle="collapse"><i class="toggle-icon ft-toggle-right font-medium-3 white"
                                data-ticon="ft-toggle-right"></i></a></li>
                    <li class="nav-item d-lg-none"><a class="nav-link open-navbar-container" data-toggle="collapse"
                            data-target="#navbar-mobile"><i class="la la-ellipsis-v"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content">
                <div class="collapse navbar-collapse" id="navbar-mobile">
                    <ul class="nav navbar-nav mr-auto float-left">
                        <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand" href="#"><i
                                    class="ficon ft-maximize"></i></a></li>
                    </ul>
                    <ul class="nav navbar-nav float-right">
                        <li <?=($M4P2==1)?'' : 'style="display:none"' ?>  
                            class="dropdown dropdown-notification nav-item show_topupnoti" <?=$initial_topup_style;?>>
                            <a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
                                <i class="ficon ft-bell text-success"></i><span class="badge badge-pill badge-success badge-up badge-glow show_topup" 
                                ><?=$_SESSION["user_topup_count"]?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                                <li class="dropdown-menu-header">
                                    <h6 class="dropdown-header m-0"><span class="grey darken-2">Top-up</span></h6>
                                    <span class="notification-tag badge badge-success float-right m-0 show_topup" ><?=$_SESSION["user_topup_count"]?> New</span>
                                </li>
                                <li class="scrollable-container media-list w-100 ps" id="load_data_topup">
                                    <!-- here topup data -->
                                    
                                </li>
                                <li class="dropdown-menu-footer">
                                    <a class="dropdown-item text-muted text-center" 
                                        href="<?=roothtml.'wallet/deposit.php'?>">Show All Top-up</a>
                                </li>
                            </ul>
                        </li>
                        <li <?=($M4P3==1)?'' : 'style="display:none"' ?>  
                            class="dropdown dropdown-notification nav-item show_withdrawnoti" <?=$initial_withdraw_style;?>>
                            <a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
                                <i class="ficon ft-credit-card text-danger"></i>
                                <span class="badge badge-pill badge-danger badge-up badge-glow show_withdraw">
                                    <?=$_SESSION["user_withdraw_count"]?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                                <li class="dropdown-menu-header">
                                    <h6 class="dropdown-header m-0"><span class="grey darken-2">Withdraw</span></h6>
                                    <span class="notification-tag badge badge-danger float-right m-0">
                                        <?=$_SESSION["user_withdraw_count"]?></span>
                                </li>
                                <li class="scrollable-container media-list w-100 ps" id="load_data_withdraw">
                                    <!-- here show withdraw -->
                                    
                                </li>
                                <li class="dropdown-menu-footer">
                                    <a class="dropdown-item text-muted text-center" 
                                        href="<?=roothtml.'wallet/withdraw.php'?>">Show All Withdraw</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown dropdown-user nav-item"><a
                                class="dropdown-toggle nav-link dropdown-user-link" href="#"
                                data-toggle="dropdown"><span class="mr-1 user-name text-bold-700"><?=$_SESSION["esport_admin_username"]?></span><span
                                    class="avatar avatar-online"><img
                                        src="<?=$profile_img?>"
                                        alt="avatar"><i></i></span></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?=roothtml.'user/profile.php'?>"><i class="ft-user"></i> Edit Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" id="btnlogout"><i class="ft-power"></i> Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <!-- END: Header-->

    <!-- BEGIN: Main Menu-->

    <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
        <div class="main-menu-content">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <li <?=($P1==1)?'' : 'style="display:none"' ?> 
                    class="<?=(curlink == 'home.php')?'active':''?>" >
                    <a href="<?=roothtml.'home/home.php'?>"><i class="la la-home"></i><span class="menu-title"
                            data-i18n="Dashboard">Dashboard</span></a>
                </li>
                <li <?=($M1==1)?'' : 'style="display:none"'?> class=" navigation-header">
                    <span data-i18n="Ecommerce">Manage Account</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="Ecommerce"></i>
                </li>
                <li <?=($M1P1==1)?'' : 'style="display:none"' ?> 
                    class="nav-item <?=(curlink == 'user.php' || curlink == 'profile.php')?'active':''?>"><a href="<?=roothtml.'user/user.php'?>">
                        <i class="ft-users"></i><span class="menu-title" data-i18n="Shop">User Control</span></a>
                </li>
                <li <?=($M1P2==1)?'' : 'style="display:none"' ?> 
                    class="nav-item <?=(curlink == 'agent.php')?'active':''?>"><a href="<?=roothtml.'agent/agent.php'?>">
                        <i class="la la-user-secret"></i><span class="menu-title" data-i18n="Shop">Manage Agent</span></a>
                </li>
                <li <?=($M1P3==1)?'' : 'style="display:none"' ?> 
                    class="nav-item <?=(curlink == 'player.php')?'active':''?>"><a href="<?=roothtml.'player/player.php'?>">
                        <i class="ft-user-plus"></i><span class="menu-title" data-i18n="Shop">Manage Player</span></a>
                </li>
                <li <?=($M2==1)?'' : 'style="display:none"' ?> class="navigation-header">
                    <span data-i18n="User Interface">Bet</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="User Interface"></i>
                </li> 
                <li <?=($M2==1)?'' : 'style="display:none"' ?> class=" nav-item" >
                    <a href="#"><i class="la la-soccer-ball-o"></i><span class="menu-title"
                            data-i18n="Components">Sport Bet</span></a>
                    <ul class="menu-content">
                        <li <?=($M2P1==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'runningbet.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'sportbet/runningbet.php'?>">
                                <i></i><span data-i18n="Alerts">Running</span>
                            </a>
                        </li>   
                        <li <?=($M2P2==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'settledbet.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'sportbet/settledbet.php'?>">
                                <i></i><span data-i18n="Alerts">Settled</span>
                            </a>
                        </li>  
                        <li <?=($M2P3==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'cancelbet.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'sportbet/cancelbet.php'?>">
                                <i></i><span data-i18n="Alerts">Cancel</span>
                            </a>
                        </li>                  
                    </ul>
                </li>
                <li <?=($M3==1)?'' : 'style="display:none"' ?>  class=" nav-item">
                    <a href="#"><i class="la la-empire"></i><span class="menu-title"
                            data-i18n="Components">Casino Bet</span></a>
                    <ul class="menu-content">
                        <li <?=($M3P1==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'runningcasino.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'casinobet/runningcasino.php'?>">
                                <i></i><span data-i18n="Alerts">Running</span>
                            </a>
                        </li> 
                        <li <?=($M3P2==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'settlecasino.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'casinobet/settlecasino.php'?>">
                                <i></i><span data-i18n="Alerts">Settled</span>
                            </a>
                        </li> 
                        <li <?=($M3P3==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'cancelcasino.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'casinobet/cancelcasino.php'?>">
                                <i></i><span data-i18n="Alerts">Cancel</span>
                            </a>
                        </li>                  
                    </ul>
                </li>
                <li <?=($M4==1)?'' : 'style="display:none"' ?>  class=" navigation-header">
                    <span data-i18n="User Interface">Wallet</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="User Interface"></i>
                </li>           
                <li <?=($M4==1)?'' : 'style="display:none"' ?> class=" nav-item">
                    <a href="#"><i class="la la-dollar"></i><span class="menu-title"
                            data-i18n="Components">Players</span></a>
                    <ul class="menu-content">
                        <li <?=($M4P1==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'playerbalance.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'wallet/playerbalance.php'?>">
                                <i></i><span data-i18n="Alerts">Player Balance</span>
                            </a>
                        </li> 
                        <li <?=($M4P2==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'deposit.php')?'active':''?>">
                            <a class="menu-item text-success" href="<?=roothtml.'wallet/deposit.php'?>">
                            <i></i><span data-i18n="Alerts">Top-up</span>
                                <span class="badge badge-pill badge-success badge-up badge-glow show_topup" <?=$initial_topup_style;?>>
                                    <?=$_SESSION["user_topup_count"]?></span>
                            </a>
                        </li>
                        <li <?=($M4P3==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'withdraw.php')?'active':''?>">
                            <a class="menu-item text-danger" href="<?=roothtml.'wallet/withdraw.php'?>">
                                <i></i><span data-i18n="Alerts">Withdraw</span>
                                    <span class="badge badge-pill badge-danger badge-up badge-glow show_withdraw" <?=$initial_withdraw_style;?>>
                                        <?=$_SESSION["user_withdraw_count"]?></span>
                            </a>
                        </li>
                        <li <?=($M4P4==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'wallethistory.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'wallet/wallethistory.php'?>">
                                <i></i><span data-i18n="Alerts">Wallet History</span>
                            </a>
                        </li>                       
                    </ul>
                </li>
                <li <?=($M5==1)?'' : 'style="display:none"' ?> class=" navigation-header">
                    <span data-i18n="User Interface">Reports</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="User Interface"></i>
                </li> 
                <li <?=($M5==1)?'' : 'style="display:none"' ?> class=" nav-item">
                    <a href="#"><i class="la la-file-text-o"></i><span class="menu-title"
                            data-i18n="Components">Bet Reports</span></a>
                    <ul class="menu-content">
                        <li <?=($M5P1==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'runningreport.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'reports/runningreport.php'?>">
                                <i></i><span data-i18n="Alerts">Running Report</span>
                            </a>
                        </li>   
                        <li <?=($M5P2==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'settledreport.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'reports/settledreport.php'?>">
                                <i></i><span data-i18n="Alerts">Settled Report</span>
                            </a>
                        </li>  
                        <li <?=($M5P3==1)?'' : 'style="display:none"' ?> 
                            class="<?=(curlink == 'cancelreport.php')?'active':''?>">
                            <a class="menu-item " href="<?=roothtml.'reports/cancelreport.php'?>">
                                <i></i><span data-i18n="Alerts">Cancel Report</span>
                            </a>
                        </li>                  
                    </ul>
                </li> 
                <li 
                    class="<?=(curlink == 'agent_rate.php' || curlink == 'agent_rate_detail.php')?'active':''?>">
                    <a href="<?=roothtml.'agent/agent_rate.php'?>"><i class="la la-lightbulb-o">
                        </i><span class="menu-title"
                            data-i18n="Dashboard">Agents Report</span></a>
                </li>
                <li class=" navigation-header">
                    <span data-i18n="User Interface">Point Q & A</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="User Interface"></i>
                </li>
                <li 
                    class="<?=(curlink == 'question_answer.php')?'active':''?>">
                    <a href="<?=roothtml.'question/question_answer.php'?>"><i class="la la-question-circle">
                        </i><span class="menu-title"
                            data-i18n="Dashboard">Q & A</span></a>
                </li>
                <li class=" navigation-header">
                    <span data-i18n="User Interface">Others</span><i
                        class="la la-ellipsis-h" data-toggle="tooltip" data-placement="right"
                        data-original-title="User Interface"></i>
                </li>
                <li 
                    class="<?=(curlink == 'siteheader.php')?'active':''?>">
                    <a href="<?=roothtml.'others/siteheader.php'?>"><i class="la la-header">
                        </i><span class="menu-title"
                            data-i18n="Dashboard">Main Header</span></a>
                </li>
                <li <?=($P2==1)?'' : 'style="display:none"' ?>
                    class="<?=(curlink == 'log.php')?'active':''?>">
                    <a href="<?=roothtml.'log/log.php'?>"><i class="la la-binoculars">
                        </i><span class="menu-title"
                            data-i18n="Dashboard">Log History</span></a>
                </li>
            </ul>
        </div>
    </div>

    <!-- END: Main Menu-->

    <?php } else{  header("location:". roothtml."errorpage.php"); } ?>