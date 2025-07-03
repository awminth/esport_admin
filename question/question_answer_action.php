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
    // check admin or agent
    if($usertype == "Agent"){
        $userid = GetString("SELECT AID FROM tbluser WHERE AgentID = ? ", [$_SESSION["esport_admin_agentid"]]);
        $a .= " AND l.UserID='{$userid}' ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(l.DT)>='{$from}' AND Date(l.DT)<='{$to}' ";
    } 
    $out = '';
    $sql = "SELECT l.*,u.UserName FROM tblquestion l,tbluser u 
    WHERE l.UserID=u.AID " .$a . " 
    ORDER BY AID DESC LIMIT $offset, $limit_per_page";
    $result = $con->query($sql);
    if (!$result) {
        die("Query failed: " . $con->error);
    }
    if ($result->num_rows > 0) {
        $out.='<div class="row mb-5">';
        $no = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // title header
            $title_str = $row["Question"];
            if(strlen($row["Question"]) > 28){
                $title_str = substr($row["Question"], 0, 28).' ...';
            }
            $out .= '            
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <strong>'.$no.'. '.$title_str.'</strong>
                        </h4>
                        <div class="heading-elements">
                            <a data-toggle="collapse" href="#headingid'.$no.'" 
                                        aria-expanded="false" aria-controls="headingid'.$no.'" class="text-info">
                                        <i class="ft-minus"></i>
                                    </a>
                            <a class="text-primary" href="#" 
                                id="btnedit" 
                                data-aid="'.$row["AID"].'" 
                                data-question="'.$row["Question"].'" 
                                data-answera="'.$row["ChoiceA"].'" 
                                data-answerb="'.$row["ChoiceB"].'" 
                                data-answerc="'.$row["ChoiceC"].'" 
                                data-answerd="'.$row["ChoiceD"].'" 
                                data-correctanswer="'.$row["CorrectAnswer"].'" 
                                data-mark="'.$row["Mark"].'" >
                                <i class="ft-edit"></i>
                            </a>
                            <a class="text-danger" href="#" 
                                id="btndelete" 
                                data-aid="'.$row["AID"].'" ><i class="ft-trash-2"></i></a>
                        </div>
                    </div>
                    <div class="card-content collapse" id="headingid'.$no.'">
                        <div class="card-body">
                            <div class="bs-callout-primary callout-border-left p-1">
                                <strong class="text-danger">Question ?</strong>
                                <span class="float-right">
                                    <i class="la la-check-circle text-success"></i> '.$row["Mark"].' Points
                                </span>
                                <p>'.$row["Question"].'</p>
                                <strong class=" text-primary mr-1">
                                <i class="la la-cloud-upload"></i>&nbsp;'.$row["UserName"].',</strong> 
                                <small> Created  '.enDate($row["DT"]).' '.enTime($row["DT"]).'</small>
                            </div>
                            <div class="row mt-2">
                                <fieldset class="checkboxsas col-6">
                                    <label>
                                        <input type="checkbox" '.(($row["CorrectAnswer"]=="A")?'checked':'disabled').' >
                                        '.$row["ChoiceA"].'
                                    </label>
                                </fieldset>
                                <fieldset class="checkboxsas col-6">
                                    <label>
                                        <input type="checkbox" '.(($row["CorrectAnswer"]=="B")?'checked':'disabled').' >
                                        '.$row["ChoiceB"].'
                                    </label>
                                </fieldset>
                                <fieldset class="checkboxsas col-6">
                                    <label>
                                        <input type="checkbox" '.(($row["CorrectAnswer"]=="C")?'checked':'disabled').' >
                                        '.$row["ChoiceC"].'
                                    </label>
                                </fieldset>
                                <fieldset class="checkboxsas col-6">
                                    <label>
                                        <input type="checkbox" '.(($row["CorrectAnswer"]=="D")?'checked':'disabled').' >
                                        '.$row["ChoiceD"].'
                                    </label>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            ';            
            $no++;
        }
        $out.="</div>";

        $sql_total = "SELECT COUNT(l.AID) as total 
        FROM tblquestion l,tbluser u 
        WHERE l.UserID=u.AID ".$a." ORDER BY l.AID DESC";
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
        <div class="container bg-secondary">
            <div class="p-5 text-center">
                <h5 class=" text-white">There is no record found.</h5>
            </div>
        </div>
        ';
        echo $out;
    } 
}

if($action == "save"){
    $question = $_POST["question"];
    $answer_a = $_POST["answer_a"];
    $answer_b = $_POST["answer_b"];
    $answer_c = $_POST["answer_c"];
    $answer_d = $_POST["answer_d"];
    $correct_answer = $_POST["correct_answer"];
    $mark = $_POST["mark"];
    $userid = $_SESSION["esport_admin_userid"];
    $dt = date("Y-m-d H:i:s");
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);
    try{
        $sql = "INSERT INTO tblquestion (Question, ChoiceA, ChoiceB, ChoiceC, ChoiceD, 
        CorrectAnswer, DT, UserID, Mark) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssssssid", $question, $answer_a, $answer_b, $answer_c, $answer_d,
        $correct_answer, $dt, $userid, $mark);
        if($stmt->execute()){
            mysqli_commit($con);
            save_log($_SESSION["esport_admin_username"]." သည် Q & A အား အသစ် သွင်းသွားသည်။");
            echo 1;
        }else{
           mysqli_rollback($con);
           error_log("Save error in question action, ".$stmt->error."\n", 3, root."question/my_log_file.log");
           echo 0; 
        }
    }catch(mysqli_sql_exception $e){
        mysqli_rollback($con);
        echo "Error: " . $e->getMessage();
    }
}

if($action == "edit"){
    $aid = $_POST["eaid"];
    $question = $_POST["equestion"];
    $answer_a = $_POST["eanswer_a"];
    $answer_b = $_POST["eanswer_b"];
    $answer_c = $_POST["eanswer_c"];
    $answer_d = $_POST["eanswer_d"];
    $correct_answer = $_POST["ecorrect_answer"];
    $mark = $_POST["emark"];
    $userid = $_SESSION["esport_admin_userid"];
    $dt = date("Y-m-d H:i:s");
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);
    try{
        $sql = "UPDATE tblquestion SET Question=?, ChoiceA=?, ChoiceB=?, ChoiceC=?, ChoiceD=?, 
        CorrectAnswer=?, DT=?, UserID=?, Mark=? WHERE AID=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssssssidi", $question, $answer_a, $answer_b, $answer_c, $answer_d,
        $correct_answer, $dt, $userid, $mark, $aid);
        if($stmt->execute()){
            mysqli_commit($con);
            save_log($_SESSION["esport_admin_username"]." သည် Q & A အား edit သွားသည်။");
            echo 1;
        }else{
           mysqli_rollback($con);
           error_log("Edit error in question action, ".$stmt->error."\n", 3, root."question/my_log_file.log");
           echo 0; 
        }
    }catch(mysqli_sql_exception $e){
        mysqli_rollback($con);
        echo "Error: " . $e->getMessage();
    }
}

if($action == "delete"){
    $aid = $_POST["aid"];
    mysqli_begin_transaction($con);
    try{
        $sql = "DELETE FROM tblquestion WHERE AID = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $aid);
        if($stmt->execute()){
            mysqli_commit($con);
            save_log($_SESSION["esport_admin_username"]." သည် Q & A အား delete သွားသည်။");
            echo 1;
        }else{
            mysqli_rollback($con);
            error_log("Delete error in question action, ".$stmt->error."\n", 3, root."question/my_log_file.log");
            echo 0; 
        }
    }catch(mysqli_sql_exception $e){
        mysqli_rollback($con);
        echo "Error: " . $e->getMessage();
    }
}

if($action == 'excel'){
    $a = '';
    // check admin or agent
    if($usertype == "Agent"){
        $userid = GetString("SELECT AID FROM tbluser WHERE AgentID = ? ", [$_SESSION["esport_admin_agentid"]]);
        $a .= " AND l.UserID='{$userid}' ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(l.DT)>='{$from}' AND Date(l.DT)<='{$to}' ";
    } 
    $out = '';
    $sql = "SELECT l.*,u.UserName FROM tblquestion l,tbluser u 
    WHERE l.UserID=u.AID " .$a . " 
    ORDER BY AID DESC";
    $out = '';
    $result = $con->query($sql);
    $fileName = "QuestionAndAnswer_".date('d-m-Y').".xls";
    $out .= '<head><meta charset="UTF-8"></head>
        <table >  
            <tr>
                <td colspan="10" align="center"><h3>Question and Answer Report</h3></td>
            </tr>
            <tr><td colspan="10"><td></tr>
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Question</th>  
                <th style="border: 1px solid ;">Answer A</th> 
                <th style="border: 1px solid ;">Answer B</th>  
                <th style="border: 1px solid ;">Answer C</th>  
                <th style="border: 1px solid ;">Answer D</th>  
                <th style="border: 1px solid ;">Correct Answer</th>  
                <th style="border: 1px solid ;">Point</th>  
                <th style="border: 1px solid ;">UserName</th>   
                <th style="border: 1px solid ;">Date / Time</th>
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
            <tr>  
                <td style="border: 1px solid ;">'.$no.'</td>  
                <td style="border: 1px solid ;">'.$row["Question"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceA"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceB"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceC"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceD"].'</td>  
                <td style="border: 1px solid ;">'.$row["CorrectAnswer"].'</td>  
                <td style="border: 1px solid ;">'.$row["Mark"].'</td>  
                <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                <td style="border: 1px solid ;">'.enDate($row["DT"]).' / '.enTime($row["DT"]).'</td>                
            </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="10" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    echo $out;
}

if($action == 'pdf'){
    $a = '';
    // check admin or agent
    if($usertype == "Agent"){
        $userid = GetString("SELECT AID FROM tbluser WHERE AgentID = ? ", [$_SESSION["esport_admin_agentid"]]);
        $a .= " AND l.UserID='{$userid}' ";
    }
    // date search
    $from = $_POST['dtfrom'];
    $to = $_POST['dtto'];
    if($from != "" || $to != ""){
        $a .= " AND Date(l.DT)>='{$from}' AND Date(l.DT)<='{$to}' ";
    } 
    $out = '';
    $sql = "SELECT l.*,u.UserName FROM tblquestion l,tbluser u 
    WHERE l.UserID=u.AID " .$a . " 
    ORDER BY AID DESC";
    $out = '';
    $result = $con->query($sql);
    $fileName = "QuestionAndAnswer_".date('d-m-Y').".xls";
    $out .= '<h3 align="center">Question and Answer Report</h3>
    <head><meta charset="UTF-8"></head>
        <table >  
            <tr>  
                <th style="border: 1px solid ;">No</th>  
                <th style="border: 1px solid ;">Question</th>  
                <th style="border: 1px solid ;">Answer A</th> 
                <th style="border: 1px solid ;">Answer B</th>  
                <th style="border: 1px solid ;">Answer C</th>  
                <th style="border: 1px solid ;">Answer D</th>  
                <th style="border: 1px solid ;">Correct Answer</th>  
                <th style="border: 1px solid ;">Point</th>  
                <th style="border: 1px solid ;">UserName</th>   
                <th style="border: 1px solid ;">Date / Time</th>
       
            </tr>';
    if ($result->num_rows > 0) {
        $no = 0;
        while ($row = $result->fetch_assoc()) {
            $no = $no + 1;
            $out .= '
            <tr>  
                <td style="border: 1px solid ;">'.$no.'</td>  
                <td style="border: 1px solid ;">'.$row["Question"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceA"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceB"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceC"].'</td>  
                <td style="border: 1px solid ;">'.$row["ChoiceD"].'</td>  
                <td style="border: 1px solid ;">'.$row["CorrectAnswer"].'</td>  
                <td style="border: 1px solid ;">'.$row["Mark"].'</td>  
                <td style="border: 1px solid ;">'.$row["UserName"].'</td>  
                <td style="border: 1px solid ;">'.enDate($row["DT"]).' / '.enTime($row["DT"]).'</td>                
            </tr>';
        }          
    }else{
        $out .= '
            <tr>
                <td style="border: 1px solid ;" colspan="10" align="center">No data found</td>   
            </tr>';
        
    }
    $out .= '</table>';
    // $mpdf = new \Mpdf\Mpdf();
    $mpdf = new \Mpdf\Mpdf(['orientation' => 'L']); // Set to landscape
    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont   = true;  
    $stylesheet = file_get_contents(roothtml.'lib/mypdfcss.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);  
    $mpdf->WriteHTML($out,2);
    $file = 'QuestionAndAnswer_'.date("d_m_Y").'.pdf';
    $mpdf->output($file,'D');
    
}




?>