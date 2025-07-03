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
    $a = '';
    if (!empty($search)) {
        $a .= " AND (d.UserName LIKE '%$search%' or d.TransferCode LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tblsettlebet d,tblplayer p   
    WHERE d.UserName = p.UserName AND d.ProductType = 9 ".$a." 
    ORDER BY d.AID DESC LIMIT $offset, $limit_per_page";
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
                <th width="15%">Ticket ID</th> 
                <th class="text-right">Bet</th>  
                <th class="text-right">WinLoss</th>
                <th>Date / Time</th>  
            </tr>
        </thead>
        <tbody>
        ';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            $bet_amt = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? ",[$row["TransferCode"]]);
            $txt = "text-danger";
            if($row["WinLoss"] > $bet_amt){
                $txt = "text-success";
            }
            if($row["WinLoss"] == $bet_amt){
                $txt = "text-dark";
            }
            $out .= '
                <tr>
                    <td>'.$no.'</td>
                    <td class="">'.$row['Username'].'</td>
                    <td class="">'.$row['TransferCode'].'</td>
                    <td class="text-right">'.number_format($bet_amt).'</td>
                    <td class="'.$txt.' text-right">'.number_format($row['WinLoss']).'</td>
                    <td class="">'.enDate($row['ResultTime']).' / '.enTime($row['ResultTime']).'</td>
                </tr>';
            $no++;
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(d.AID) AS total FROM tblsettlebet d,tblplayer p
        WHERE d.UserName = p.UserName AND d.ProductType = 9 " .$a." 
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
            <th class="text-right">Amount</th>
            <th>Date / Time</th>              
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="text-center text-danger">No record found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    } 
}

// show detail
if($action == "detail_view"){
    $transfercode = $_POST['transfercode'];
    $out = '';
    $sql = "SELECT d.* FROM tblsettlebet d  
    WHERE d.TransferCode = '$transfercode'";
    $result = $con->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $bet_amt = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? ",[$transfercode]);
        $out .= '
        <p>
            Ticket ID : '.$row['TransferCode'].' <br>
            BetDate : '.enDate($row['ResultTime']).' / '.enTime($row['ResultTime']).'
        </p>
        ';
        $body_data = json_decode($row["ExtraInfo"], true);
        $no = 1;
        // foreach ($body_data as $key => $value) {
        // }
        $label = $body_data["sportType"];
        if($label != "Mix Parlay"){
            $label = $body_data["marketType"];
        }
        $status = '<span class="text-danger">Loss</span>';
        $icon = "ft-x-circle danger";
        if($row["WinLoss"] > $bet_amt){
            $status = '<span class="text-success">Won</span>';
            $icon = "ft-check-circle success";
        }
        if($row["WinLoss"] == $bet_amt){
            $status = '<span class="text-dark">Draw</span>';
            $icon = "ft-minus-circle warning";
        }
        $out.='
        <div id="accordionWrap4" role="tablist" aria-multiselectable="true">
            <div class="card accordion collapse-icon accordion-icon-rotate">
                <a id="heading41" class="card-header py-1 bg-secondary secondary" data-toggle="collapse"
                    href="#accordion41" aria-expanded="false" aria-controls="accordion41">
                    <div class="white">
                        <div class="row">
                            <div class="col-1 pr-0">
                                <span><i class="'.$icon.' h4 align-middle"></i></span>
                            </div>
                            <div class="col-11 pl-1">
                                <span>'.$label.' </span>
                                <span class="text-warning">&nbsp;|&nbsp;</span>
                                <span>'.$body_data["match"].'</span>
                            </div>
                        </div>
                    </div>
                </a>
                <div id="accordion41" role="tabpanel" data-parent="#accordionWrap4" aria-labelledby="heading41"
                    class="border-success card-collapse collapse" aria-expanded="false">
                    <div class="card-content">
                        <div class="card-body p-1">
                            Sport Type : '.$body_data["sportType"].' <br>
                            Bet Type : '.$body_data["marketType"].' <br>                            
                            League : '.$body_data["league"].' <br>
                            Bet Option : '.$body_data["betOption"].' <br>
                            Bet : '.number_format($bet_amt).' <br>
                            WinLoss : '.number_format($row["WinLoss"]).' <br>
                            Status : '.$status.'
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        
    } else {
        $out .= '<p class="text-danger" align="center">Data not found.</p>';
    }
    echo $out;
}

if($action == 'excel'){
    $search = $_POST['ser']; 
    $a = '';
    if (!empty($search)) {
        $a .= " AND (d.Username LIKE '%$search%' or d.TransferCode LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tblsettlebet d,tblplayer p   
    WHERE d.Username = p.UserName AND d.ProductType = 9 ".$a." 
    ORDER BY d.AID DESC";
    $result = $con->query($sql);
    $fileName = "SettledCasinoBetList_".date('d-m-Y').".xls";
    $out .= '
        <head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="6" align="center"><h3>Settled Casino Bet List</h3></td>
            </tr>
            <tr><td colspan="6"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Account Name</th>  
                <th style="border: 1px solid ;">Ticket ID</th>  
                <th style="border: 1px solid ;">Bet Amount</th> 
                <th style="border: 1px solid ;">WinLoss</th>
                <th style="border: 1px solid ;">Date / Time</th> 
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $bet_amt = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? ",[$row["TransferCode"]]);
            $txt = "color:red;";
            if($row["WinLoss"] > $bet_amt){
                $txt = "color:green;";
            }
            if($row["WinLoss"] == $bet_amt){
                $txt = "color:black;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["Username"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransferCode"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($bet_amt).'</td>    
                    <td style="border: 1px solid ;'.$txt.'">'.number_format($row["WinLoss"]).'</td>     
                    <td style="border: 1px solid ;">'.enDate($row["ResultTime"]).' / '.enTime($row["ResultTime"]).'</td>             
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
    $search = $_POST['ser']; 
    $a = '';
    if (!empty($search)) {
        $a .= " AND (d.Username LIKE '%$search%' or d.TransferCode LIKE '%$search%') ";
    }
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND p.AgentID='{$agentid}' ";
    }
    $out = '';
    $sql = "SELECT d.*,p.AID as player_id FROM tblsettlebet d,tblplayer p   
    WHERE d.Username = p.UserName AND d.ProductType = 9 ".$a." 
    ORDER BY d.AID DESC";
    $result = $con->query($sql);
    $fileName = "SettledSportBetList_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Settled Casino Bet List</h3>
        <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Account Name</th>  
                <th style="border: 1px solid ;">Ticket ID</th>  
                <th style="border: 1px solid ;">Bet Amount</th> 
                <th style="border: 1px solid ;">WinLoss</th>
                <th style="border: 1px solid ;">Date / Time</th> 
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $bet_amt = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? ",[$row["TransferCode"]]);
            $txt = "color:red;";
            if($row["WinLoss"] > $bet_amt){
                $txt = "color:green;";
            }
            if($row["WinLoss"] == $bet_amt){
                $txt = "color:black;";
            }
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["Username"].'</td>  
                    <td style="border: 1px solid ;">'.$row["TransferCode"].'</td>  
                    <td style="border: 1px solid ;" align="right">'.number_format($bet_amt).'</td>    
                    <td style="border: 1px solid ;'.$txt.'" align="right">'.number_format($row["WinLoss"]).'</td>     
                    <td style="border: 1px solid ;">'.enDate($row["ResultTime"]).' / '.enTime($row["ResultTime"]).'</td>             
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
    $file = 'SettledCasinoBetList_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}


?>