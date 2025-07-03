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
   
    $a = '';
    // type name search
    $search = $_POST['search'];     
    if (!empty($search)) {
        $a .= " AND (d.UserName LIKE '%$search%' or d.TransferCode LIKE '%$search%' or d.TransactionID LIKE '%$search%') ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(d.BetTime)>='{$from}' AND Date(d.BetTime)<='{$to}' ";
    } 
    // player search
    $playeraccount = $_POST['playeraccount']; 
    if (!empty($playeraccount)) {
        $a .= " AND p.AID = '{$playeraccount}' ";
    }
    // search sport or casino or all
    $hidgametype = $_POST['hidgametype']; 
    if ($hidgametype == "sport") {
        $a .= " AND (d.ProductType = 1 OR d.ProductType = 5) ";
    }else if($hidgametype == "casino"){
        $a .= " AND d.ProductType = 9 ";
    }else{
        $a .= "";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tbldeduct d,tblplayer p   
    WHERE d.UserName = p.UserName AND d.CancelStatus = 'no' 
    AND d.TransferCode NOT IN (SELECT TransferCode FROM tblsettlebet) ".$a." 
    ORDER BY d.AID DESC LIMIT $offset, $limit_per_page";
    
    $result = $con->query($sql);
    if (!$result) {
        die("Query failed: " . $con->error);
    }
    if ($result->num_rows > 0) {
        $out.='
        <table class="table table-hover table-bordered mb-0 table-responsive">
        <thead>
            <tr> 
                <th width="7%;">No</th>
                <th>Account Name</th>
                <th>Ticket ID</th>   
                <th>TransactionID</th> 
                <th class="text-right">Bet Amount</th>
                <th>Date / Time</th>  
            </tr>
        </thead>
        <tbody>
        ';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // $no = $no + 1;
            $out .= '
                <tr>
                    <td>'.$no.'</td>
                    <td class="">'.$row['UserName'].'</td>
                    <td class="">'.$row['TransferCode'].'</td>
                    <td class="">'.$row['TransactionID'].'</td>
                    <td class="text-danger text-right">'.number_format($row['Amount']).'</td>
                    <td class="">'.enDate($row['BetTime']).' / '.enTime($row['BetTime']).'</td>
                </tr>';
            $no++;
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(d.AID) AS total FROM tbldeduct d,tblplayer p
        WHERE d.UserName = p.UserName AND d.CancelStatus = 'no' 
        AND d.TransferCode NOT IN (SELECT TransferCode FROM tblsettlebet) ".$a." 
        ORDER BY d.AID DESC";
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
            <th>Ticket ID</th>   
            <th>TransactionID</th> 
            <th class="text-right">Amount</th>
            <th>Date / Time</th>            
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

if($action == 'excel'){
    $a = '';
    // type name search
    $search = $_POST['ser'];     
    if (!empty($search)) {
        $a .= " AND (d.UserName LIKE '%$search%' or d.TransferCode LIKE '%$search%' or d.TransactionID LIKE '%$search%') ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(d.BetTime)>='{$from}' AND Date(d.BetTime)<='{$to}' ";
    } 
    // player search
    $playeraccount = $_POST['hidplayeraccount']; 
    if (!empty($playeraccount)) {
        $a .= " AND p.AID = '{$playeraccount}' ";
    }
    // search sport or casino or all
    $hidgametype = $_POST['hidgametype']; 
    if ($hidgametype == "sport") {
        $a .= " AND (d.ProductType = 1 OR d.ProductType = 5) ";
    }else if($hidgametype == "casino"){
        $a .= " AND d.ProductType = 9 ";
    }else{
        $a .= "";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tbldeduct d,tblplayer p   
    WHERE d.UserName = p.UserName AND d.CancelStatus = 'no' 
    AND d.TransferCode NOT IN (SELECT TransferCode FROM tblsettlebet) ".$a." 
    ORDER BY d.AID DESC";
    $result = $con->query($sql);
    $fileName = "RunningReportList_".date('d-m-Y').".xls";
    $out .= '
        <head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="6" align="center"><h3>Running Report List</h3></td>
            </tr>
            <tr><td colspan="6"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Account Name</th>  
                <th style="border: 1px solid ;">Ticket ID</th> 
                <th style="border: 1px solid ;">TransactionID</th> 
                <th style="border: 1px solid ;">Bet Amount</th> 
                <th style="border: 1px solid ;">Date / Time</th> 
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;" data-format="text">'.$row["TransferCode"].'</td>  
                    <td style="border: 1px solid ;" data-format="text">'.$row["TransactionID"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>     
                    <td style="border: 1px solid ;">'.enDate($row["BetTime"]).' / '.enTime($row["BetTime"]).'</td>             
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="6" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf'){
    $a = '';
    // type name search
    $search = $_POST['ser'];     
    if (!empty($search)) {
        $a .= " AND (d.UserName LIKE '%$search%' or d.TransferCode LIKE '%$search%' or d.TransactionID LIKE '%$search%') ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(d.BetTime)>='{$from}' AND Date(d.BetTime)<='{$to}' ";
    } 
    // player search
    $playeraccount = $_POST['hidplayeraccount']; 
    if (!empty($playeraccount)) {
        $a .= " AND p.AID = '{$playeraccount}' ";
    }
    // search sport or casino or all
    $hidgametype = $_POST['hidgametype']; 
    if ($hidgametype == "sport") {
        $a .= " AND (d.ProductType = 1 OR d.ProductType = 5) ";
    }else if($hidgametype == "casino"){
        $a .= " AND d.ProductType = 9 ";
    }else{
        $a .= "";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tbldeduct d,tblplayer p   
    WHERE d.UserName = p.UserName AND d.CancelStatus = 'no' 
    AND d.TransferCode NOT IN (SELECT TransferCode FROM tblsettlebet) ".$a." 
    ORDER BY d.AID DESC";
    $result = $con->query($sql);
    $fileName = "RunningReportList_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Running Report List</h3>
        <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Account Name</th>  
                <th style="border: 1px solid ;">Ticket ID</th> 
                <th style="border: 1px solid ;">TransactionID</th> 
                <th style="border: 1px solid ;">Bet Amount</th> 
                <th style="border: 1px solid ;">Date / Time</th> 
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;" data-format="text">'.$row["TransferCode"].'</td>  
                    <td style="border: 1px solid ;" data-format="text">'.$row["TransactionID"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["Amount"]).'</td>     
                    <td style="border: 1px solid ;">'.enDate($row["BetTime"]).' / '.enTime($row["BetTime"]).'</td>             
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="6" align="center">No data found</td>   
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
    $file = 'RunningReportList_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}




?>