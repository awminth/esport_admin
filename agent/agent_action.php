<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];

if($action == 'show'){      
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
        $a = " where (UserName like '%$search%') ";
    }      
    $sql = "select * from tblagent ".$a." 
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
            <th>MinBet</th> 
            <th>MaxBet</th> 
            <th>Max Per Match</th> 
            <th>Casino Table Limit</th> 
            <th>Status</th> 
            <th width="10%;" class="text-center">Action</th>      
        </tr>
        </thead>
        <tbody>
        ';
        $no = (($page - 1) * $limit_per_page);
        while($row = mysqli_fetch_array($result)){
            $no=$no+1;
            $statuscolor = "bg-success";
            if($row["Status"] == "Suspend"){
                $statuscolor = "bg-warning";
            }
            if($row["Status"] == "Closed"){
                $statuscolor = "bg-danger";
            }
            $out.="<tr class='".$statuscolor." text-white'>
                <td>{$no}</td>
                <td>{$row["UserName"]}</td>
                <td>{$row["Currency"]}</td>
                <td>".number_format($row["MinBet"])."</td>
                <td>".number_format($row["MaxBet"])."</td>
                <td>".number_format($row["MaxPerMatch"])."</td>
                <td>{$row["CasinoTableLimit"]}</td>
                <td>{$row["Status"]}</td>
                <td class='text-center' >
                    <a data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <span class='text-primary' style='cursor:pointer;'>o o o</span>
                    </a>
                    <div class='dropdown-menu'>";
                    if($row["Status"] != "Active"){
                    $out.="
                        <a href='#' id='btnactive' class='dropdown-item'
                            data-aid='{$row['AID']}'
                            data-serverid='{$row['ServerID']}'
                            data-username='{$row['UserName']}'
                            data-status='Active'>
                            <i class='la la-edit text-primary'></i>
                            Active</a>
                        <div class='dropdown-divider'></div>";
                    }
                    if($row["Status"] != "Suspend"){
                    $out.="
                        <a href='#' id='btnsuspend' class='dropdown-item'
                            data-aid='{$row['AID']}'
                            data-serverid='{$row['ServerID']}'
                            data-username='{$row['UserName']}'
                            data-status='Suspend'>
                            <i class='la la-sort-amount-asc text-info'></i>
                            Suspend</a>
                        <div class='dropdown-divider'></div>";
                    }
                    if($row["Status"] != "Closed"){
                    $out.="
                        <a href='#' id='btnclose' class='dropdown-item'
                            data-aid='{$row['AID']}'
                            data-serverid='{$row['ServerID']}'
                            data-username='{$row['UserName']}'
                            data-status='Closed'>
                            <i class='la la-power-off text-danger'></i>
                            Closed</a>";
                    }
                    $out.="
                    </div>
                </td>
            </tr>";
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "select AID from tblagent ".$a." 
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
                <td colspan="5" class="text-center">ထည့်သွင်းထားသော Agentမရှိသေးပါ။ New Buttonကိုနှိပ်၍အသစ်သွင်းပါ။</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    }

}

if($action == 'save'){
    $companykey = "E258AF866BB444E6A116C52B24DAB7C5";
    $username = $_POST["username"];
    $password = $_POST["password"];
    $currency = $_POST["currency"];
    $max = $_POST["max"];
    $min = $_POST["min"];
    $maxpermatch = $_POST["maxpermatch"];
    $casinotablelimit = $_POST["casinotablelimit"];
    $dt = date("Y-m-d H:i:s");
    $serverid = date("YmdHis");
    //Send data to API
    $url = "https://ex-api-demo-yy.568win.com/web-root/restricted/agent/register-agent.aspx"; // Replace with your API URL

    $data = [
        "CompanyKey" => $companykey,
        "ServerId" => $serverid,
        "Username" => $username,
        "Password" => $password,
        "Currency" => $currency,
        "Max" => $max,
        "Min" => $min,
        "MaxPerMatch" => $maxpermatch,
        "CasinoTableLimit" => $casinotablelimit,
    ];

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Send JSON data
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Set the appropriate Content-Type for JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    // for local test
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for dev
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Only for dev

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Decode JSON
    $data = json_decode($response, true);

    // Close cURL session
    curl_close($ch);

    // Check for 'msg'
    if (isset($data['error']['msg'])) {
        if($data['error']['msg']=="No Error" && $data['error']['id']==0){
            //insert local database
            $sql = "insert into tblagent (CompanyKey,ServerID,UserName,Password,Currency,MinBet,MaxBet,
            MaxPerMatch,CasinoTableLimit,DateTime) 
            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sssssdddis", $companykey, $serverid, $username, $password, $currency, $min, 
            $max, $maxpermatch, $casinotablelimit, $dt);
            if($stmt->execute()){
                save_log($_SESSION["esport_admin_username"]." သည် agent (".$username.") အား အသစ်သွင်းသွားသည်။");
                echo 1;
            }else{
                error_log("Save data error echo 1 in agent_action.php", 3, root."agent/my_log_file.log");
                echo 0;
            }
        }
        else{
            echo $data['error']['msg'];
        }       
    } 
    else {
        error_log("Save data error echo 2 in agent_action.php", 3, root."agent/my_log_file.log");
        echo 2;
    }   
}

if($action == "editagent"){
    $aid = $_POST["aid"];
    $serverid = $_POST["serverid"];
    $username = $_POST["username"];
    $status = $_POST["status"];

    //Send data to API
    $url = "https://ex-api-demo-yy.568win.com/web-root/restricted/agent/update-agent-status.aspx"; // Replace with your API URL

    $data = [
        "CompanyKey" => $companykey,
        "ServerId" => $serverid,
        "Username" => $username,
        "Status" => $status,
    ];

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Send JSON data
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Set the appropriate Content-Type for JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    // for local test
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for dev
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Only for dev

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Decode JSON
    $data = json_decode($response, true);

    // Close cURL session
    curl_close($ch);

    // Check for 'msg'
    if (isset($data['error']['msg'])) {
        if($data['error']['msg']=="No Error" && $data['error']['id']==0){
            //insert local database
            $sql = "UPDATE tblagent SET Status = ? WHERE AID = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $status, $aid);
            if($stmt->execute()){
                save_log($_SESSION["esport_admin_username"]." သည် agent (".$username.") ၏ Statusအား ".$status."သို့ပြောင်းသွားသည်။");
                echo 1;
            }else{
                error_log("Update Status data error echo 0 in agent_action.php", 3, root."agent/my_log_file.log");
                echo 0;
            }
        }
        else{
            echo $data['error']['msg'];
        }       
    } 
    else {
        error_log("Update Status data error echo 2 in agent_action.php", 3, root."agent/my_log_file.log");
        echo 2;
    }   
}

if($action == 'excel'){
    $search = $_POST['ser'];
    $a = "";
    if($search != ''){  
        $a = " where (UserName like '%$search%') ";
    }      
    $sql = "select * from tblagent ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $fileName = "AgentList_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="8" align="center"><h3>Agent Lists</h3></td>
            </tr>
            <tr><td colspan="8"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Agent Name</th>  
                <th style="border: 1px solid ;">Currency</th>  
                <th style="border: 1px solid ;">Min Bet</th>
                <th style="border: 1px solid ;">Max Bet</th>
                <th style="border: 1px solid ;">Max Per Match</th>
                <th style="border: 1px solid ;">Casino Table Limit</th>
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
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Currency"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["MinBet"]).'</td>       
                    <td style="border: 1px solid ;">'.number_format($row["MaxBet"]).'</td>   
                    <td style="border: 1px solid ;">'.number_format($row["MaxPerMatch"]).'</td>   
                    <td style="border: 1px solid ;">'.$row["CasinoTableLimit"].'</td>    
                    <td style="border: 1px solid ; '.$statuscolor.'">'.$row["Status"].'</td>                            
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="8" align="center">No data found</td>   
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
        $a = " where (UserName like '%$search%') ";
    }      
    $sql = "select * from tblagent ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $fileName = "AgentList_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Agent Lists</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Agent Name</th>  
                <th style="border: 1px solid ;">Currency</th>  
                <th style="border: 1px solid ;">Min Bet</th>
                <th style="border: 1px solid ;">Max Bet</th>
                <th style="border: 1px solid ;">Max Per Match</th>
                <th style="border: 1px solid ;">Casino Table Limit</th>
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
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Currency"].'</td>  
                    <td style="border: 1px solid ;">'.number_format($row["MinBet"]).'</td>       
                    <td style="border: 1px solid ;">'.number_format($row["MaxBet"]).'</td>   
                    <td style="border: 1px solid ;">'.number_format($row["MaxPerMatch"]).'</td>   
                    <td style="border: 1px solid ;">'.$row["CasinoTableLimit"].'</td>   
                    <td style="border: 1px solid ;'.$statuscolor.'">'.$row["Status"].'</td>              
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="8" align="center">No data found</td>   
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
    $file = 'AgentList_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}




?>