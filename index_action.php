<?php 

include("config.php");

$action = $_POST["action"];
$usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");

if($action=="login"){

    $username=$_POST["username"];
    $password=$_POST["password"];     
    $sql="select * from tbluser where UserName='{$username}' and Password='{$password}'";
    $result = mysqli_query($con,$sql);     
    
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_array($result);
        $_SESSION["esport_admin_userid"] = $row['AID'];
        $_SESSION["esport_admin_username"] = $row['UserName'];                  
        $_SESSION["esport_admin_usertype"] = $row['UserType'];
        $_SESSION["esport_admin_userpassword"] = $row['Password'];  
        $_SESSION["esport_admin_agentid"] = $row['AgentID'];  
        $_SESSION["esport_admin_img"] = $row['Img'];  
        
        save_log($row['UserName']." သည် Login ဝင်သွားသည်");

        //remember username and password
        if(!empty($_POST['remember'])){
            setcookie("member_login",$row['UserName'],time()+(10*365*24*60*60));
            setcookie("member_password",$row['Password'],time()+(10*365*24*60*60));
        }
        else{
            if(isset($_COOKIE['member_login'])){
                setcookie("member_login",'');
            }
            if(isset($_COOKIE['member_password'])){
                setcookie("member_password",'');
            }
        }
        echo 1;
    }
    else{
        session_unset();
        echo 0;
    }


}

if($action == "logout"){   
    save_log($_SESSION['esport_admin_username']." Logout လုပ်သွားသည်");
    unset($_SESSION["esport_admin_userid"]);
    unset($_SESSION["esport_admin_username"]);
    unset($_SESSION["esport_admin_usertype"]);
    unset($_SESSION["esport_admin_userpassword"]);
    unset($_SESSION["esport_admin_agentid"]);
    unset($_SESSION["esport_admin_img"]);
    unset($_SESSION["user_topup_count"]);
    unset($_SESSION["user_withdraw_count"]);
    unset($_SESSION["go_sport_permission_aid"]);
    unset($_SESSION["go_sport_permission_name"]);
    unset($_SESSION["go_detail_agentid"]);
    echo 1;
}

if($action == 'check_topup'){
    $a = "";
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql = "SELECT COUNT(b.AID) as cnt FROM tblbalancein b,tblplayer p  
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid' AND b.WinLossStatus='deposit' ".$a;
    $result = $con->query($sql);
    $record = 0;
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $record = $row["cnt"];
    }
    $_SESSION["user_topup_count"] = $record;
    echo $record;
}

if($action == 'check_withdraw'){
    $a = "";
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql = "SELECT COUNT(b.AID) as cnt FROM tblbalanceout b,tblplayer p  
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid' AND b.WinLossStatus='withdraw' ".$a;
    $result = $con->query($sql);
    $record = 0;
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $record = $row["cnt"];
    }
    $_SESSION["user_withdraw_count"] = $record;
    echo $record;
}

if($action == 'load_data_topup'){
    $a = "";
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND u.AgentID='{$agentid}' ";
    }
    $sql = "SELECT b.*,u.UserName FROM tblbalancein b,tblplayer u  
            WHERE b.PlayerID = u.AID AND b.PrepaidStatus='prepaid' 
            AND b.WinLossStatus='deposit' ".$a."  
            ORDER BY b.AID DESC";
    $result = $con->query($sql);
    $output = '';
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $output .= '
            <a href="#">
                <div class="media">
                    <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan mr-0"></i></div>
                    <div class="media-body">
                        <h6 class="media-heading">'.$row["UserName"].'</h6>
                        <p class="notification-text font-small-3 text-primary">
                            Amount : '.$row["Amount"].', &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Code : '.$row["TransitionCode"].'</p>
                        <small>
                            <time class="media-meta text-muted">'.timeAgo($row["DateTime"]).'</time>
                        </small>
                    </div>
                </div>
            </a>
            <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
            </div>
            <div class="ps__rail-y" style="top: 0px; right: 0px;">
                <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
            ';
        }
    } else {
        $output .= '<h4 class="text-center text-danger">No records found</h4>';
    }
    echo $output;
}

if($action == 'load_data_withdraw'){
    $a = "";
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND u.AgentID='{$agentid}' ";
    }
    $sql = "SELECT b.*,u.UserName FROM tblbalanceout b,tblplayer u  
            WHERE b.PlayerID = u.AID AND b.PrepaidStatus='prepaid' 
            AND b.WinLossStatus='withdraw' ".$a." 
            ORDER BY b.AID DESC";
    $result = $con->query($sql);
    $output = '';
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $output .= '
            <a href="#">
                <div class="media">
                    <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan mr-0"></i></div>
                    <div class="media-body">
                        <h6 class="media-heading">'.$row["UserName"].'</h6>
                        <p class="notification-text font-small-3 text-primary">
                            Amount : '.$row["Amount"].'</p>
                        <small>
                            <time class="media-meta text-muted">'.timeAgo($row["DateTime"]).'</time>
                        </small>
                    </div>
                </div>
            </a>
            <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
            </div>
            <div class="ps__rail-y" style="top: 0px; right: 0px;">
                <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
            </div>
            ';
        }
    } else {
        $output .= '<h4 class="text-center text-danger">No records found</h4>';
    }
    echo $output;
}


?>