<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];
$usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");

// for topup history
if($action == 'show_table_topup'){      
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
    $sql = "SELECT b.*,p.UserName FROM tblbalancein b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='deposit' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
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
                <th>Transition Code</th> 
                <th class="text-right">Top-up</th> 
                <th>Date / Time</th>     
                <th>Status</th>  
            </tr>
        </thead>
        <tbody>
        ';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // $no = $no + 1;
            $colour = "text-danger";
            if($row['PrepaidStatus'] == "success"){
                $colour = "text-success";
            }
            $out .= '<tr>
                    <td>'.$no.'</td>
                    <td class="text-primary">'.$row['UserName'].'</td>
                    <td>'.$row['PayType'].'</td>
                    <td>'.$row['TransitionCode'].'</td>
                    <td class="text-danger text-right">'.number_format($row['Amount']).'</td>
                    <td>'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>
                    <td class="'.$colour.'">'.$row['PrepaidStatus'].'</td>
                </tr>';
            $no++;
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(b.AID) AS total FROM tblbalancein b,tblplayer p 
        WHERE b.PlayerID=p.AID AND b.WinLossStatus='deposit' " .$a;
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
                                    <a class="page-link tlink" href="#">'.$page_array[$count].' <span class="sr-only">(current)</span></a>
                                </li>';

                $previous_id = $page_array[$count] - 1;
                if($previous_id > 0){
                    $previous_link = '<li class="page-item">
                                            <a class="page-link tlink" href="javascript:void(0)" data-page_number="'.$previous_id.'">Previous</a>
                                    </li>';
                }
                else{
                    $previous_link = '<li class="page-item disabled">
                                            <a class="page-link tlink" href="#">Previous</a>
                                    </li>';
                }

                $next_id = $page_array[$count] + 1;
                if($next_id > $total_links){
                    $next_link = '<li class="page-item disabled">
                                        <a class="page-link tlink" href="#">Next</a>
                                </li>';
                }else{
                    $next_link = '<li class="page-item">
                                    <a class="page-link tlink" href="javascript:void(0)" data-page_number="'.$next_id.'">Next</a>
                                </li>';
                }
            }else{
                if($page_array[$count] == '...')
                {
                    $page_link .= '<li class="page-item disabled">
                                        <a class="page-link tlink" href="#">...</a>
                                    </li> ';
                }else{
                    $page_link .= '<li class="page-item">
                                        <a class="page-link tlink" href="javascript:void(0)" data-page_number="'.$page_array[$count].'">'.$page_array[$count].'</a>
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
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="text-center text-danger">No record found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    } 
}

if($action == 'excel_topup'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalancein b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='deposit' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerDepositHistory_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="7" align="center"><h3>Player Deposit History</h3></td>
            </tr>
            <tr><td colspan="7"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">PayType</th>  
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $colour = "color: red;";;
            if($row['PrepaidStatus'] == "success"){
                $colour = "color: green;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ;'.$colour.'">'.$row["PrepaidStatus"].'</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="7" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf_topup'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalancein b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='deposit' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerDepositHistory_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Player Deposit History</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">PayType</th>  
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $colour = "color: red;";;
            if($row['PrepaidStatus'] == "success"){
                $colour = "color: green;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ;'.$colour.'">'.$row["PrepaidStatus"].'</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="7" align="center">No data found</td>   
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
    $file = 'PlayerDepositHistory_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}

/////////////////////////////////
// for withdraw history
if($action == 'show_table_withdraw'){      
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
     
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='withdraw' ";
    $a = '';
    $search = $_POST['search'];
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
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
                <th>Transition Code</th> 
                <th class="text-right">Withdraw</th> 
                <th>Date / Time</th>  
                <th>Status</th>      
            </tr>
        </thead>
        <tbody>
        ';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // $no = $no + 1;
            $colour = "text-danger";
            if($row['PrepaidStatus'] == "success"){
                $colour = "text-success";
            }
            $out .= '<tr>
                    <td>'.$no.'</td>
                    <td class="text-primary">'.$row['UserName'].'</td>
                    <td>'.$row['PayType'].'</td>
                    <td>'.$row['TransitionCode'].'</td>
                    <td class="text-danger text-right">'.number_format($row['Amount']).'</td>
                    <td>'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td class="'.$colour.'">'.$row['PrepaidStatus'].'</td>                  
                </tr>';
            $no++;
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(b.AID) AS total FROM tblbalanceout b,tblplayer p 
        WHERE b.PlayerID=p.AID AND b.WinLossStatus='withdraw' " .$a;
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
                                    <a class="page-link wlink" href="#">'.$page_array[$count].' <span class="sr-only">(current)</span></a>
                                </li>';

                $previous_id = $page_array[$count] - 1;
                if($previous_id > 0){
                    $previous_link = '<li class="page-item">
                                            <a class="page-link wlink" href="javascript:void(0)" data-page_number="'.$previous_id.'">Previous</a>
                                    </li>';
                }
                else{
                    $previous_link = '<li class="page-item disabled">
                                            <a class="page-link wlink" href="#">Previous</a>
                                    </li>';
                }

                $next_id = $page_array[$count] + 1;
                if($next_id > $total_links){
                    $next_link = '<li class="page-item disabled">
                                        <a class="page-link wlink" href="#">Next</a>
                                </li>';
                }else{
                    $next_link = '<li class="page-item">
                                    <a class="page-link wlink" href="javascript:void(0)" data-page_number="'.$next_id.'">Next</a>
                                </li>';
                }
            }else{
                if($page_array[$count] == '...')
                {
                    $page_link .= '<li class="page-item disabled">
                                        <a class="page-link wlink" href="#">...</a>
                                    </li> ';
                }else{
                    $page_link .= '<li class="page-item">
                                        <a class="page-link wlink" href="javascript:void(0)" data-page_number="'.$page_array[$count].'">'.$page_array[$count].'</a>
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
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="text-center text-danger">No record found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    } 
}

if($action == 'excel_withdraw'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='withdraw' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerWithdrawHistory_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="7" align="center"><h3>Player Withdraw History</h3></td>
            </tr>
            <tr><td colspan="7"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">PayType</th>  
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $colour = "color: red;";;
            if($row['PrepaidStatus'] == "success"){
                $colour = "color: green;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ;'.$colour.'">'.$row["PrepaidStatus"].'</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="7" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf_withdraw'){
    $search = $_POST['ser']; 
    $out = '';
    $sql = "SELECT b.*,p.UserName FROM tblbalanceout b,tblplayer p 
    WHERE b.PlayerID=p.AID AND b.WinLossStatus='withdraw' ";
    $a = '';
    if (!empty($search)) {
        $a .= " AND (p.UserName LIKE '%$search%' OR b.TransitionCode LIKE '%$search%' OR b.PayType LIKE '%$search%') ";
    }
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(b.DateTime)>='{$from}' AND Date(b.DateTime)<='{$to}' ";
    }
    $status = $_POST['status'];
    if (!empty($status)) {
        $a .= " AND b.PrepaidStatus = '$status' ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $sql .= $a . " ORDER BY AID DESC";
    $result = $con->query($sql);
    $fileName = "PlayerWithdrawHistory_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Player Withdraw History</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">PayType</th>  
                <th style="border: 1px solid ;">TransitionCode</th>  
                <th style="border: 1px solid ;">Amount</th>  
                <th style="border: 1px solid ;">Date / Time</th>
                <th style="border: 1px solid ;">Status</th>  
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $colour = "color: red;";;
            if($row['PrepaidStatus'] == "success"){
                $colour = "color: green;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["PayType"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransitionCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>  
                    <td style="border: 1px solid ;">'.enDate($row['DateTime']).' / '.enTime($row['DateTime']).'</td>  
                    <td style="border: 1px solid ;'.$colour.'">'.$row["PrepaidStatus"].'</td>                 
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="7" align="center">No data found</td>   
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
    $file = 'PlayerWithdrawHistory_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}




?>