<?php
    include('../config.php');
    include(root.'master/header.php');

    $usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");
    $agentid = (isset($_SESSION["esport_admin_agentid"])?$_SESSION["esport_admin_agentid"]:0);
    // check admin or agent
    $player_a = "";
    if($usertype == "Agent"){        
        $player_a = " WHERE AgentID='{$agentid}' ";
    }
    $player_cnt = GetInt("SELECT COUNT(AID) FROM tblplayer ".$player_a);

    $agent_cnt = GetInt("SELECT COUNT(AID) FROM tblagent ");

    $topup_a = "";
    if($usertype == "Agent"){        
        $topup_a = " AND p.AgentID='{$agentid}' ";
    }
    $topup_amt = GetInt("SELECT SUM(b.Amount) FROM tblbalancein b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='success' AND b.WinlossStatus='deposit' ".$topup_a);

    $withdraw_a = "";
    if($usertype == "Agent"){        
        $withdraw_a = " AND p.AgentID='{$agentid}' ";
    }
    $withdraw_amt = GetInt("SELECT SUM(b.Amount) FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='success' AND b.WinlossStatus='withdraw' ".$withdraw_a);
?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-left">
                                        <h3 class="success"><?=$player_cnt?></h3>
                                        <h6>Players</h6>
                                    </div>
                                    <div>
                                        <i class="icon-users success font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                    <div class="progress-bar bg-gradient-x-success" role="progressbar"
                                        style="width: <?=$player_cnt?>%" aria-valuenow="<?=$player_cnt?>"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-left">
                                        <h3 class="info"><?=$agent_cnt?></h3>
                                        <h6>Agents</h6>
                                    </div>
                                    <div>
                                        <i class="icon-user-following info font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                    <div class="progress-bar bg-gradient-x-info" role="progressbar"
                                        style="width: <?=$agent_cnt?>%" aria-valuenow="<?=$agent_cnt?>"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-left">
                                        <h3 class="warning"><?=number_format($topup_amt);?></h3>
                                        <h6>Top-up</h6>
                                    </div>
                                    <div>
                                        <i class="icon-wallet warning font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                    <div class="progress-bar bg-gradient-x-warning" role="progressbar"
                                        style="width: <?=$topup_amt?>%" aria-valuenow="<?=$topup_amt?>"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-left">
                                        <h3 class="danger"><?=number_format($withdraw_amt);?></h3>
                                        <h6>Withdraw</h6>
                                    </div>
                                    <div>
                                        <i class="icon-rocket danger font-large-2 float-right"></i>
                                    </div>
                                </div>
                                <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                    <div class="progress-bar bg-gradient-x-danger" role="progressbar"
                                        style="width: <?=$withdraw_amt?>%" aria-valuenow="70" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row match-height">
                <div class="col-xl-6 col-lg-12">
                    <div class="card" style="">
                        <div class="card-header">
                            <h4 class="card-title">Agent Lists</h4>
                            <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-content">
                            <div id="new-orders" class="media-list position-relative ps">
                                <div class="table-responsive">
                                    <table id="new-orders-table" class="table table-hover table-xl mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-top-0">Agent Name</th>
                                                <th class="border-top-0 text-center">Player Count</th>
                                                <th class="border-top-0 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_agent = "SELECT * FROM tblagent ORDER BY AID DESC";
                                            $result_agent = $con->query($sql_agent);
                                            if (!$result_agent) {
                                                die("Query failed: " . $con->error);
                                            }
                                            if ($result_agent->num_rows > 0) {
                                                while ($row_agent = $result_agent->fetch_assoc()) {
                                                    $statuscolor = "bg-success text-white";
                                                    if($row_agent["Status"] == "Suspend"){
                                                        $statuscolor = "bg-warning text-white";
                                                    }
                                                    if($row_agent["Status"] == "Closed"){
                                                        $statuscolor = "bg-danger text-white";
                                                    }
                                                    $p_count = GetInt("SELECT COUNT(AID) FROM tblplayer WHERE AgentID='{$row_agent["AID"]}'");
                                            ?>
                                            <tr>
                                                <td class="text-truncate text-info"><?=$row_agent["UserName"]?></td>
                                                <td class="text-truncate text-center"><?=number_format($p_count)?></td>
                                                <td class="text-truncate text-center <?=$statuscolor?>"><?=$row_agent["Status"]?>
                                                </td>
                                            </tr>
                                            <?php  
                                                }
                                            }else{
                                            ?>
                                            <tr>
                                                <td class="text-truncate text-center" colspan="3">No data.</td>
                                            </tr>
                                            <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                </div>
                                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-12">
                    <div class="card" style="">
                        <div class="card-header">
                            <h4 class="card-title">Player Lists</h4>
                            <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-content">
                            <div id="new-orders" class="media-list position-relative ps">
                                <div class="table-responsive">
                                    <table id="new-orders-table" class="table table-hover table-xl mb-0">
                                        <thead>
                                            <tr>
                                                <th class="border-top-0">Player Name</th>
                                                <th class="border-top-0 text-center">Balance</th>
                                                <th class="border-top-0 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_player = "SELECT * FROM tblplayer ".$player_a." ORDER BY AID DESC";
                                            $result_player = $con->query($sql_player);
                                            if (!$result_player) {
                                                die("Query failed: " . $con->error);
                                            }
                                            if ($result_player->num_rows > 0) {
                                                while ($row_player = $result_player->fetch_assoc()) {
                                                    $statuscolor = "bg-success text-white";
                                                    if($row_player["Status"] == "Suspend"){
                                                        $statuscolor = "bg-warning text-white";
                                                    }
                                                    if($row_player["Status"] == "Closed"){
                                                        $statuscolor = "bg-danger text-white";
                                                    }
                                            ?>
                                            <tr>
                                                <td class="text-truncate text-primary"><?=$row_player["UserName"]?></td>
                                                <td class="text-truncate text-center"><?=number_format($row_player["Balance"])?></td>
                                                <td class="text-truncate text-center <?=$statuscolor?>"><?=$row_player["Status"]?>
                                                </td>
                                            </tr>
                                            <?php  
                                                }
                                            }else{
                                            ?>
                                            <tr>
                                                <td class="text-truncate text-center" colspan="3">No data.</td>
                                            </tr>
                                            <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                </div>
                                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Content-->



<?php
    include(root.'master/footer.php');
?>