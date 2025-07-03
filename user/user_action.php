<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];

if($action == 'show'){  
    unset($_SESSION["go_sport_permission_aid"]);
    unset($_SESSION["go_sport_permission_name"]);
    
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
        $a = " where (UserName like '%$search%' or UserType like '%$search%') ";
    }      
    $sql = "select * from tbluser ".$a." 
    order by AID desc limit {$offset},{$limit_per_page}";
        
    $result=mysqli_query($con,$sql) or die("SQL a Query");
    $out="";
    if(mysqli_num_rows($result) > 0){
        $out.='
        <table class="table table-hover table-bordered mb-0">
        <thead>
        <tr> 
            <th width="7%;">No</th>
            <th>User Name</th>
            <th>User Type</th>  
            <th>Agent Name</th>  
            <th width="10%;" class="text-center">Action</th>      
        </tr>
        </thead>
        <tbody>
        ';
        $no = (($page - 1) * $limit_per_page);
        while($row = mysqli_fetch_array($result)){
            $no=$no+1;
            $agentname = GetString("SELECT UserName FROM tblagent WHERE AID = ?", [$row["AgentID"]]);
            $out.="<tr>
                <td>{$no}</td>
                <td>{$row["UserName"]}</td>
                <td>{$row["UserType"]}</td>
                <td>{$agentname}</td>
                <td class='text-center'>
                    <a data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <span class='text-primary' style='cursor:pointer;'>o o o</span>
                    </a>
                    <div class='dropdown-menu'>
					";
					if($_SESSION["esport_admin_usertype"] == "Admin"){
                        $out.="
                        <a href='#' id='btnpermission' class='dropdown-item'
                            data-aid='{$row['AID']}' 
                            data-username='{$row['UserName']}' >
                            <i class='ft-grid text-success'></i>
                            Permission</a>
                            <div class='dropdown-divider'></div>
                            ";
					}
					$out.="
                        <a href='#' id='btnedit' class='dropdown-item'
                            data-aid='{$row['AID']}' 
                            data-username='{$row['UserName']}' 
                            data-password='{$row['Password']}' 
                            data-usertype='{$row['UserType']}' 
                            data-agentid='{$row['AgentID']}' >
                            <i class='la la-edit text-primary'></i>
                            Edit</a>
                        <div class='dropdown-divider'></div>
                        <a href='#' id='btndelete' class='dropdown-item'
                            data-aid='{$row['AID']}' 
                            data-username='{$row['UserName']}' >
                            <i class='la la-trash text-danger'></i>
                            Delete</a>
                    </div>
                </td>
            </tr>";
        }
        $out.="</tbody>";
        $out.="</table><br><br>";

        $sql_total = "select AID from tbluser ".$a." 
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
            <th>User Name</th>
            <th>User Type</th>  
            <th width="10%;" class="text-center">Action</th>               
        </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" class="text-center">ထည့်သွင်းထားသော Userမရှိသေးပါ။ New Buttonကိုနှိပ်၍အသစ်သွင်းပါ။</td>
            </tr>
        </tbody>
        </table>
        ';
        echo $out;
    }

}

if($action == 'save'){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $usertype = $_POST["usertype"];
    $agentid = 0;
    if($usertype == "Agent"){
        $agentid = $_POST["agentid"];
    }
    $sql = "insert into tbluser (UserName,Password,UserType,AgentID) 
    values (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt -> bind_param('sssi',$username, $password, $usertype, $agentid);
    if($stmt->execute()){
        save_log($_SESSION["esport_admin_username"]." သည် user (".$username.") အား အသစ်သွင်းသွားသည်။");
        echo 1;
    }else{
         error_log("Save error in user action, ".$stmt->error."\n", 3, root."user/my_log_file.log");
        echo 0;
    }
}

if($action == 'edit'){
    $aid = $_POST["eaid"];
    $username = $_POST["eusername"];
    $password = $_POST["epassword"];
    $usertype = $_POST["eusertype"];  
    $check = true;
    $agentid = 0;
    if($usertype == "Agent"){
        $agentid = (isset($_POST["eagentid"])?$_POST["eagentid"]:"");
        if($agentid == "" || $agentid == NULL){
            $check = false;
        }
    } 
    if($check){
        $sql = "UPDATE tbluser SET UserName=?, Password=?, UserType=?, AgentID=? 
        WHERE AID=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssii", $username, $password, $usertype, $agentid, $aid);
        if($stmt->execute()){
            save_log($_SESSION["esport_admin_username"]." သည် user (".$username.") အား update သွားသည်။");
            echo 1;
        }else{
            error_log("Edit error in user action, ".$stmt->error."\n", 3, root."user/my_log_file.log");
            echo 0;
        }
    }else{
        echo 2;
    }
    
}

if($action == 'delete'){
    $aid = $_POST["aid"];
    $username = $_POST["username"];
    $sql = "delete from tbluser where AID=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $aid);
    if($stmt->execute()){
        save_log($_SESSION["esport_admin_username"]." သည် user (".$username.") အား delete သွားသည်။");
        echo 1;
    }else{
        error_log("Delete error in user action, ", 3, root."user/my_log_file.log");
        echo 0;
    }   
}

if($action == 'excel'){
    $search = $_POST['ser'];
    $a = "";
    if($search != ''){  
        $a = " where (UserName like '%$search%' or UserType like '%$search%') ";
    }      
    $sql = "select * from tbluser ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $fileName = "UserControl_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="4" align="center"><h3>User Control</h3></td>
            </tr>
            <tr><td colspan="4"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">Password</th>  
                <th style="border: 1px solid ;">User Type</th>
       
            </tr>';
    if(mysqli_num_rows($result) > 0){
        $no=0;
        while($row = mysqli_fetch_array($result)){
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Password"].'</td>  
                    <td style="border: 1px solid ;">'.$row["UserType"].'</td>                  
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="4" align="center">No data found</td>   
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
        $a = " where (UserName like '%$search%' or UserType like '%$search%') ";
    }      
    $sql = "select * from tbluser ".$a." 
    order by AID desc";
    $result = mysqli_query($con,$sql);
    $out="";
    $out .= '<h3 align="center">User Control</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">UserName</th>  
                <th style="border: 1px solid ;">Password</th>  
                <th style="border: 1px solid ;">User Type</th>
       
            </tr>';
    if(mysqli_num_rows($result) > 0){
        $no=0;
        while($row = mysqli_fetch_array($result)){
            $no = $no + 1;
            $out .= '
                <tr>  
                    <td style="border: 1px solid ;">'.$no.'</td>  
                    <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                    <td style="border: 1px solid ;">'.$row["Password"].'</td>  
                    <td style="border: 1px solid ;">'.$row["UserType"].'</td>                  
                </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="4" align="center">No data found</td>   
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
    $file = 'UserControl_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}

if($action == "go_permission"){
    $aid = $_POST["aid"];
    $username = $_POST["username"];
    $_SESSION["go_sport_permission_aid"] = $aid;
    $_SESSION["go_sport_permission_name"] = $username;
    echo 1;
}

if($action == "changeprofile"){
    $aid = $_SESSION['esport_admin_userid'];
    $password = $_POST["password"];
    $img = $_POST["img"];
    $sql = "update tbluser set Password='{$password}',Img='{$img}' where AID='{$aid}'";
    if(mysqli_query($con,$sql)){
        $_SESSION['esport_admin_userpassword'] = $password;
        $_SESSION['esport_admin_img'] = $img;
        save_log($_SESSION['esport_admin_username']." Password change သွားသည်");
        echo 1;
    }else{
        echo 0;
    }
}



?>