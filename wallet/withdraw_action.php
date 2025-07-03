<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];
$usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");

if($action == 'show'){      
    $limit_per_page = ""; 
    if($_POST['entryvalue'] == ""){
        $limit_per_page = 10; 
    } else{
        $limit_per_page = $_POST['entryvalue']; 
    }
    
    $page="";
    $no=0;
    if(isset($_POST["page_no"])){
        $page=$_POST["page_no"];                
    }
    else{
        $page=1;                      
    }

    $offset = ($page-1) * $limit_per_page;                                               
   
    $search = $_POST['search']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid' AND b.WinLossStatus='withdraw' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC LIMIT $offset, $limit_per_page";
    $result = $con->query($sql);
    if (!$result) {
        die("Query failed: " . $con->error);
    }
    if ($result->num_rows > 0) {
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
            <tr> 
                <th width="7%;">No</th>
                <th>Account Name</th>
                <th>Pay Type</th> 
                <th>Pay Name</th> 
                <th>Pay PhNo</th> 
                <th>Transition Code</th> 
                <th>Top-up</th> 
                <th>Date / Time</th>    
                <th class="text-center">Status</th>    
            </tr>
        </thead>
        <tbody>
        ';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // $no = $no + 1;
            $out .= '<tr>
                    <td>'.$no.'</td>
                    <td class="text-primary">'.$row['UserName'].'</td>
                    <td>'.$row['PayType'].'</td>
                    <td>'.$row['KpayName'].'</td>
                    <td>'.$row['KpayNo'].'</td>
                    <td>'.$row['TransitionCode'].'</td>
                    <td class="text-danger">'.number_format($row['Amount']).'</td>
                    <td>'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>
                    <td class="text-center">
                        <button type="button" 
                            id="btnwithdrawconfirm" 
                            data-aid="'.$row['AID'].'" 
                            data-playerid="'.$row['PlayerID'].'" 
                            data-playername="'.$row['UserName'].'" 
                            data-amount="'.$row['Amount'].'" 
                            class="btn btn-sm btn-outline-warning round">Pending</button>
                    </td>
                </tr>';
            $no++;
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(b.AID) AS total FROM tblbalanceout b,tblplayer p 
        WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid'  AND b.WinLossStatus='withdraw' " .$a;
        $record = $con->query($sql_total);
        $total_record = $record->fetch_assoc()['total'];
        $total_links = ceil($total_record / $limit_per_page);

        $out.='<div class="float-left"><p>Total Records -  ';
        $out.=$total_record;
        $out.='</p></div>';

        $out.='<div class="float-right">
                <ul class="pagination">
            ';      
        
        $previous_link = '';
        $next_link = '';
        $page_link = '';

        if($total_links > 4){
            if($page < 5){
                for($count = 1; $count <= 5; $count++)
                {
                    $page_array[] = $count;
                }
                $page_array[] = '...';
                $page_array[] = $total_links;
            }else{
                $end_limit = $total_links - 5;
                if($page > $end_limit){
                    $page_array[] = 1;
                    $page_array[] = '...';
                    for($count = $end_limit; $count <= $total_links; $count++)
                    {
                        $page_array[] = $count;
                    }
                }else{
                    $page_array[] = 1;
                    $page_array[] = '...';
                    for($count = $page - 1; $count <= $page + 1; $count++)
                    {
                        $page_array[] = $count;
                    }
                    $page_array[] = '...';
                    $page_array[] = $total_links;
                }
            }            

        }else{
            for($count = 1; $count <= $total_links; $count++)
            {
                $page_array[] = $count;
            }
        }

        for($count = 0; $count < count($page_array); $count++){
            if($page == $page_array[$count]){
                $page_link .= '<li class="page-item active">
                                    <a class="page-link" href="#">'.$page_array[$count].' <span class="sr-only">(current)</span></a>
                                </li>';

                $previous_id = $page_array[$count] - 1;
                if($previous_id > 0){
                    $previous_link = '<li class="page-item">
                                            <a class="page-link" href="javascript:void(0)" data-page_number="'.$previous_id.'">Previous</a>
                                    </li>';
                }
                else{
                    $previous_link = '<li class="page-item disabled">
                                            <a class="page-link" href="#">Previous</a>
                                    </li>';
                }

                $next_id = $page_array[$count] + 1;
                if($next_id > $total_links){
                    $next_link = '<li class="page-item disabled">
                                        <a class="page-link" href="#">Next</a>
                                </li>';
                }else{
                    $next_link = '<li class="page-item">
                                    <a class="page-link" href="javascript:void(0)" data-page_number="'.$next_id.'">Next</a>
                                </li>';
                }
            }else{
                if($page_array[$count] == '...')
                {
                    $page_link .= '<li class="page-item disabled">
                                        <a class="page-link" href="#">...</a>
                                    </li> ';
                }else{
                    $page_link .= '<li class="page-item">
                                        <a class="page-link" href="javascript:void(0)" data-page_number="'.$page_array[$count].'">'.$page_array[$count].'</a>
                                    </li> ';
                }
            }
        }

        $out .= $previous_link . $page_link . $next_link;

        $out .= '</ul></div>';
        $con->close();
        echo $out;
    } else {
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr>
            <th width="7%;">No</th>
            <th>Account Name</th>
            <th>Pay Type</th> 
            <th>Transition Code</th>  
            <th>Date</th>    
            <th>Status</th>   
            <th width="10%;" class="text-center">Action</th>              
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" class="text-center text-danger">No record found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    } 
}

// admin အကောင့်မှ withdraw လုပ်ခြင်း
if($action == 'save'){
    $playerid = $_POST['playerid'];
    $paytype = $_POST['paytype'];
    $transitioncode = $_POST['transitioncode'];
    $amount = $_POST['amount'];
    $dt = date('Y-m-d H:i:s');
    $payname = $_POST['payname'];
    $payphno = $_POST['payphno'];
    try{
        // get player current balance 
        $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE AID = ? FOR UPDATE", [$playerid]);
        // check if current balance is enough for withdraw
        if($current_balance < $amount){
            echo 2; // Not enough balance
            exit;
        }
        $sql = "INSERT INTO tblbalanceout (PlayerID, Amount, TransitionCode, PayType, 
        PrepaidStatus, WinLossStatus, DateTime, KpayName, KpayNo) 
        VALUES (?, ?, ?, ?, 'success', 'withdraw', ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("idsssss", $playerid, $amount, $transitioncode, $paytype, $dt, 
        $payname, $payphno);
        if($stmt->execute()){
            
            $final_balance = $current_balance - $amount;
            // update player balance
            $sql2 = "UPDATE tblplayer SET Balance = ? WHERE AID = ?";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bind_param("ii", $final_balance, $playerid);
            $stmt2->execute();

            // save log
            $playername = GetString("SELECT UserName FROM tblplayer WHERE AID = ?", [$playerid]);
            save_log($_SESSION["esport_admin_username"]." သည် [".$playername."] ၏ withdraw [Amt : ".$amount."] အား ဖြည့်သွားသည်။");

            mysqli_commit($con);
            echo 1;
        }else{
            mysqli_rollback($con);
            error_log("Database error in withdraw by admin 1 ".$stmt->error."\n", 3, root."wallet/my_log_file.log");
            echo 0;
        }
    }catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        error_log("Database error in withdraw by admin 2\n, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("External error in withdraw by admin 3\n, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    }
}

// withdraw အတည်ပြု success ပေး
if($action == 'success'){
    $aid = $_POST['aid'];
    $playerid = $_POST['playerid'];
    $playername = $_POST['playername'];
    $amount = $_POST['amount'];

    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);
    try{
        $sql = "UPDATE tblbalanceout SET PrepaidStatus='success' WHERE AID = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $aid);
        if($stmt->execute()){
            // save log
            save_log($_SESSION["esport_admin_username"]." သည် [".$playername."] ၏ withdraw [Amt : ".$amount."] အား Confirm သွားသည်။");

            mysqli_commit($con);
            echo 1;
        }else{
            mysqli_rollback($con);
            error_log("Update data error in withdraw success", 3, root."wallet/my_log_file.log");
            echo 0;
        }
    }catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        error_log("Database error in withdraw success, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("External error in withdraw success, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    }
}

// withdraw ပယ်ဖျက် fail ပေး
if($action == 'fail'){
    $aid = $_POST['aid'];
    $playerid = $_POST['playerid'];
    $playername = $_POST['playername'];
    $amount = $_POST['amount'];

    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);
    try{
        $sql = "UPDATE tblbalanceout SET PrepaidStatus='fail' WHERE AID = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $aid);
        if($stmt->execute()){
            // get player current balance 
            $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? FOR UPDATE", [$playername]);
            $final_balance = $current_balance + $amount;
            // update player balance
            $sql2 = "UPDATE tblplayer SET Balance = ? WHERE UserName = ?";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bind_param("is", $final_balance, $playername);
            $stmt2->execute();

            // save log
            save_log($_SESSION["esport_admin_username"]." သည် [".$playername."] ၏ withdraw [Amt : ".$amount."] အား Cancel သွားသည်။");

            mysqli_commit($con);
            echo 1;
        }else{
            mysqli_rollback($con);
            error_log("Update data error in withdraw fail", 3, root."wallet/my_log_file.log");
            echo 0;
        }
    }catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        error_log("Database error in withdraw fail, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("External error in withdraw fail, " . $e->getMessage(), 3, root."wallet/my_log_file.log");
        echo 0;
    }
}

if($action == 'excel'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid' AND b.WinLossStatus='withdraw' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerWithdraw_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="9" align="center"><h3>Player Withdraw</h3></td>
            </tr>
            <tr><td colspan="9"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">Pay Type</th>  
                <th style="border: 1px solid ;">Pay Name</th> 
                <th style="border: 1px solid ;">Pay PhoneNo</th> 
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["KpayName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["KpayNo"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ; text-color:red;">Pending</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="9" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.PrepaidStatus='prepaid' AND b.WinLossStatus='withdraw' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerWithdraw_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Player Withdraw</h3>
        <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">Pay Type</th>  
                <th style="border: 1px solid ;">Pay Name</th>  
                <th style="border: 1px solid ;">Pay PhoneNo</th>  
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["KpayName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["KpayNo"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ; color:red;">Pending</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="9" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    $mpdf = new \Mpdf\Mpdf();
    //$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']); // Set to landscape
    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont   = true;  
    $stylesheet = file_get_contents(roothtml.'lib/mypdfcss.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);  
    $mpdf->WriteHTML($out,2);
    $file = 'PlayerWithdraw_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}




?>