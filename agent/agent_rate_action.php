<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];
$usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");

if($action == 'show'){    
    unset($_SESSION["go_detail_agentid"]);

    $limit_per_page=""; 
    if($_POST['entryvalue']==""){
        $limit_per_page=10; 
    } else{
        $limit_per_page=$_POST['entryvalue']; 
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
    $a = "";
    if($search != ''){  
        $a .= " AND (UserName like '%$search%') ";
    }   
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND AID='{$agentid}' ";
    }    
    $sql = "select * from tblagent 
    where AID is not null ".$a." 
    order by AID desc limit {$offset},{$limit_per_page}";
        
    $result=mysqli_query($con,$sql) or die("SQL a Query");
    $out="";
    if(mysqli_num_rows($result) > 0){
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr> 
            <th width="7%;">No</th>
            <th>Agent Name</th>
            <th>Currency</th>  
            <th class="text-center">Player Count</th> 
            <th class="text-center">Status</th> 
            <th width="15%" class="text-center">Action</th>      
        </tr>
        </thead>
        <tbody>
        ';
        $no = (($page - 1) * $limit_per_page);
        while($row = mysqli_fetch_array($result)){
            $no = $no + 1;
            $statuscolor = "bg-success";
            if($row["Status"] == "Suspend"){
                $statuscolor = "bg-warning";
            }
            if($row["Status"] == "Closed"){
                $statuscolor = "bg-danger";
            }
            $player_cnt = GetInt("SELECT COUNT(AID) FROM tblplayer WHERE AgentID = ?", [$row['AID']]);
            $out.="<tr>
                <td>{$no}</td>
                <td>{$row["UserName"]}</td>
                <td>{$row["Currency"]}</td>
                <td class='text-center text-danger'>".number_format($player_cnt)."</td>
                <td class='".$statuscolor." text-white text-center'>{$row["Status"]}</td>
                <td class='text-center' >
                    <button type='button' class='btn btn-primary btn-sm' 
                        id='btngo_detail' 
                        data-agentid='{$row["AID"]}' >
                        <i class='la la-eye'></i>&nbsp;Detail</button>
                </td>
            </tr>";
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "select AID from tblagent 
        where AID is not null ".$a." 
        order by AID desc";
        $record = mysqli_query($con,$sql_total) or die("fail query");
        $total_record = mysqli_num_rows($record);
        $total_links = ceil($total_record/$limit_per_page);

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

        echo $out; 
        
    }
    else{
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr>
            <th width="7%;">No</th>
            <th>Agent Name</th>
            <th>Currency</th>  
            <th>Max Per Match</th> 
            <th>Casino Table Limit</th>               
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="text-center">No record found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    }

}

if($action == 'excel'){
    $search = $_POST['ser'];
    $a = "";
    if($search != ''){  
        $a .= " AND (UserName like '%$search%') ";
    }  
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND AID='{$agentid}' ";
    }     
    $sql = "select * from tblagent 
    where AID is not null ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $fileName = "AgentReportList_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="5" align="center"><h3>Agent Report Lists</h3></td>
            </tr>
            <tr><td colspan="5"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Agent Name</th>  
                <th style="border: 1px solid ;">Currency</th>  
                <th style="border: 1px solid ;">Player Count</th>
                <th style="border: 1px solid ;">Status</th>
            </tr>';
    if(mysqli_num_rows($result) > 0){
        $no=0;
        while($row = mysqli_fetch_array($result)){
            $no = $no + 1;
            $statuscolor = "background-color: green; color: white;";
            if($row["Status"] == "Suspend"){
                $statuscolor = "background-color: orange; color: white;";
            }
            if($row["Status"] == "Closed"){
                $statuscolor = "background-color: red; color: white;";
            }
            $player_cnt = GetInt("SELECT COUNT(AID) FROM tblplayer WHERE AgentID = ?", [$row['AID']]);
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Currency"].'</td>  
                    <td style="border: 1px solid ;">'.customNumberFormat($player_cnt).'</td>       
                    <td style="border: 1px solid ;">'.$row["Status"].'</td>                            
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="5" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf'){
    $search = $_POST['ser'];
    $a = "";
    if($search != ''){  
        $a .= " AND (UserName like '%$search%') ";
    }  
    // check admin or agent
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " AND AID='{$agentid}' ";
    }     
    $sql = "select * from tblagent 
    where AID is not null ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $fileName = "AgentRateList_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Agent Report Lists</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Agent Name</th>  
                <th style="border: 1px solid ;">Currency</th>  
                <th style="border: 1px solid ;">Player Count</th>
                <th style="border: 1px solid ;">Status</th>
            </tr>';
    if(mysqli_num_rows($result) > 0){
        $no=0;
        while($row = mysqli_fetch_array($result)){
            $no = $no + 1;
            $statuscolor = "background-color: green; color: white;";
            if($row["Status"] == "Suspend"){
                $statuscolor = "background-color: orange; color: white;";
            }
            if($row["Status"] == "Closed"){
                $statuscolor = "background-color: red; color: white;";
            }
            $player_cnt = GetInt("SELECT COUNT(AID) FROM tblplayer WHERE AgentID = ?", [$row['AID']]);
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Currency"].'</td>  
                    <td style="border: 1px solid ;">'.customNumberFormat($player_cnt).'</td>       
                    <td style="border: 1px solid ;">'.$row["Status"].'</td>                            
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="5" align="center">No data found</td>   
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
    $file = 'AgentReportList_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}

///////////////////////////////////
// ////// for detail ////////////
///////////////////////////////////
if($action == "go_detail"){
    $agentid = $_POST['agentid'];
    $_SESSION["go_detail_agentid"] = $agentid;
    echo 1;
}

if($action == 'show_detail'){          
    $limit_per_page = ""; 
    if($_POST['entryvalue'] == ""){
        $limit_per_page = 10; 
    } else{
        $limit_per_page = $_POST['entryvalue']; 
    }

    $page = "";
    $no = 0;
    if(isset($_POST["page_no"])){
        $page=$_POST["page_no"];                
    }
    else{
        $page=1;                      
    }

    $offset = ($page-1) * $limit_per_page;     
    
    $out = "";
   
    $search = $_POST['search'];
    $a = "";
    if($search != ''){  
        $a .= " AND (UserName like '%$search%') ";
    }     
    
    $sql = "select * from tblplayer 
    ".$a." 
    order by AID desc limit {$offset},{$limit_per_page}";
    $sql = "SELECT * FROM tblplayer 
    WHERE AgentID = '{$_SESSION["go_detail_agentid"]}' ".$a." 
    ORDER BY AID DESC LIMIT {$offset},{$limit_per_page}";
    $result = $con->query($sql);
    if(!$result){
        die("SQL a Query: " . $con->error);
    }
    if($result->num_rows > 0){
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr> 
            <th width="7%;">No</th>
            <th>Agent Name</th>
            <th>Player Name</th>
            <th>Player Balance</th>  
            <th class="text-center">Status</th>    
        </tr>
        </thead>
        <tbody>
        ';
        $no = (($page - 1) * $limit_per_page);
        while($row = $result->fetch_assoc()){
            $no = $no + 1;
            $statuscolor = "bg-success";
            if($row["Status"] == "Suspend"){
                $statuscolor = "bg-warning";
            }
            if($row["Status"] == "Closed"){
                $statuscolor = "bg-danger";
            }
            $out.="<tr>
                <td>{$no}</td>
                <td>{$row["AgentName"]}</td>
                <td>{$row["UserName"]}</td>
                <td class='text-danger'>".customNumberFormat($row["Balance"])."</td>
                <td class='".$statuscolor." text-white text-center'>{$row["Status"]}</td>
            </tr>";
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "SELECT COUNT(AID) AS total FROM tblplayer 
        WHERE AgentID = '{$_SESSION["go_detail_agentid"]}' ".$a." 
        ORDER BY AID DESC";
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

        echo $out; 
    }else{
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr>
            <th width="7%;">No</th>
            <th>Agent Name</th>
            <th>Player Name</th>
            <th>Player Balance</th>  
            <th class="text-center">Status</th>              
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="text-center">No player found.</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    }
}






?>