<?php 

session_start();

date_default_timezone_set("Asia/Rangoon");

define('server_name',$_SERVER['HTTP_HOST']);

if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
    $chk_link = "https";
}else{
    $chk_link = "http";
}

define('root',__DIR__.'/');

//Local
define('roothtml',$chk_link."://".server_name."/esport_admin/");
$con=new mysqli("localhost","root","root","esport");

define('curlink',basename($_SERVER['SCRIPT_NAME']));

//Online
//define('roothtml',$chk_link."://".server_name."/sportadmin/");
// $con=new mysqli("65.60.39.46","hitupkur_admin","kyoungunity*007*","hitupkur_esport");

mysqli_set_charset($con,"utf8");

$color="secondary";
$pay_type = array('KBZ Pay','Wave Pay');
$statussalary=array('Bonus','Cut');

$arr_gender = array('Male','Female');
$arr_national = array('ဗမာ','ကရင်','ချင်း','မွန်','ရခိုင်','ရှမ်း');
$arr_religion = array('ဗုဒ္ဓဘာသာ','ခရစ်ယာန်','ဟိနျူ','အခြား');
$arr_day = array('SUN','MON','TUE','WED','THU','FRI','SAT');
$arr_month = array('ဇန်နဝါရီလ','ဖေဖော်ဝါရီလ','မတ်လ','ဧပြီလ','မေလ','ဇွန်လ','ဇူလိုင်လ','ဩဂုတ်လ','စက်တင်ဘာလ','အောက်တိုဘာလ','နိုဝင်ဘာလ','ဒီဇင်ဘာလ');
$arr_montheng = array('January','February','March','April','May','June','July','August','September','October','November','December');
$arr_usertype = array("Admin","Agent");
$arr_correctanswer = array("A","B","C","D");
$arr_currency = array("MMK");
$companykey = "E258AF866BB444E6A116C52B24DAB7C5";
$secretID = "833633";


function load_player(){
    global $con;
    $usertype = (isset($_SESSION["esport_admin_usertype"])?$_SESSION["esport_admin_usertype"]:"Admin");
    // check admin or agent
    $a = "";
    if($usertype == "Agent"){
        $agentid = $_SESSION["esport_admin_agentid"];
        $a .= " WHERE AgentID='{$agentid}' ";
    }
    $sql = "SELECT AID, UserName FROM tblplayer ".$a." 
    ORDER BY AID DESC";
    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $out = "";
    while ($row = $result->fetch_assoc()) {
        $out .= "<option value='{$row["AID"]}'>{$row["UserName"]}</option>";
    }
    $stmt->close();
    return $out;
}

function load_agent(){
    global $con;
    $sql = "SELECT AID, UserName FROM tblagent 
    WHERE Status = 'Active' ORDER BY AID DESC";
    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $out = "";
    while ($row = $result->fetch_assoc()) {
        $out .= "<option value='{$row["AID"]}'>{$row["UserName"]}</option>";
    }
    $stmt->close();
    return $out;
}

function GetString($sql, $params = []) {
    global $con;
    $str = "";

    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }

    if (!empty($params)) {
        // Generate bind types automatically
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $stmt->bind_result($str);
    $stmt->fetch();
    $stmt->close();

    return $str;
}

function GetInt($sql, $params = []) {
    global $con;
    $value = 0;

    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }

    if (!empty($params)) {
        // Auto-generate parameter types
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();

    return $value;
}

function GetBool($sql){
    global $con;
    $str = false;  

    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $con->error);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $str = true;
    }

    $stmt->close();
    return $str;
}

function enDate($date){
    if($date!=NULL && $date!=''){
        $date = date_create($date);
        $date = date_format($date,"d-m-Y");
        return $date;
    }else{
        return "";
    }
   
}

function enDate1($date){
    if($date != NULL && $date != ''){
        $date = date_create($date);
        $date = date_format($date, "Y F d");
        return $date;
    }else{
        return "";
    }
   
}

function enTime1($date){
    if($date != NULL && $date != ''){
        $date = date_create($date);
        $date = date_format($date,"H:i A");
        return $date;
    }else{
        return "";
    }
   
}

function customNumberFormat($number, $decimals = 2) {
    // Format the number with specified decimals
    $formatted = number_format($number, $decimals, '.', '');
    // $formatted = number_format($number, $decimals);
    
    // Remove trailing zeros and possible decimal point if not needed
    $formatted = preg_replace('/(\.\d*?)0+$/', '$1', $formatted);
    $formatted = rtrim($formatted, '.');
    
    return $formatted;
}

function enTime($date){
    if($date!=NULL && $date!=''){
        $date = date_create($date);
        $date = date_format($date,"H:i:s");
        return $date;
    }else{
        return "";
    }
   
}

function save_log($des){
    global $con;
    $dt = date("Y-m-d H:i:s");
    $userid = $_SESSION['esport_admin_userid'];
    $sql_log = "insert into tbllog (Description,UserID,DateTime) 
    values (?, ?, ?)";
    $stmt_log = $con->prepare($sql_log);
    $stmt_log->bind_param("sis",$des, $userid, $dt);
    $stmt_log->execute(); 
}

function custom_calendar($dt){
    $ym = date('Y-m');
    if($dt != ""){
        $ym = $dt;
    }

    // Check format
    $timestamp = strtotime($ym . '-01');
    if ($timestamp === false) {
        $ym = date('Y-m');
        $timestamp = strtotime($ym . '-01');
    }

    // Today
    $today = date('Y-m-j', time());  

    // Number of days in the month
    $day_count = date('t', $timestamp);
 
    // 0:Sun 1:Mon 2:Tue ...
    $str = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));
    //$str = date('w', $timestamp);

    // Create Calendar!!
    $weeks = array();
    $week = '';

    // create Add empty cell
    $week .= str_repeat('<td class="td-height"></td>', $str);
    // userid
    $userid = $_SESSION['userid'];

    for ( $day = 1; $day <= $day_count; $day++, $str++) {     
        $date = $ym . '-' . $day;
        // search event count from tbltodolist 
        $txt = '';
        $sql = "select count(AID) as cnt from tbltodolist 
        where Date(StartEvent)='{$date}'";
        $res = GetInt($sql);
        if($res > 0){
            $txt = '<br><br><span class="badge badge-primary text-center">'.$res.'&nbsp;Events</span>';
        }
     
        if ($today == $date) {
            $week .= '<td class="td-height today" id="btnevent" data-dt="'.$date.'">'.$day.$txt.'</td>';
        } else {
            $week .= '<td class="td-height" id="btnevent" data-dt="'.$date.'">'.$day.$txt.'</td>';
        }
     
        // End of the week OR End of the month
        if ($str % 7 == 6 || $day == $day_count) {

            if ($day == $day_count) {
                // Add empty cell
                $week .= str_repeat('<td class="td-height"></td>', 6 - ($str % 7));
            }

            $weeks[] = '<tr>'.$week.'</tr>';

            // Prepare for new week
            $week = '';
        }
    }

    // show data
    foreach ($weeks as $week) {
        echo $week;
    }
}

function NumtoText($number){
    $array = [
        '1' => 'First',
        '2' => 'Second',
        '3' => 'Third',
        '4' => 'Four',
        '5' => 'Five',
        '6' => 'Six',
        '7' => 'Seven',
        '8' => 'Eight',
        '9' => 'Nine',
        '10' => 'Ten',
    ];
    return strtr($number, $array);
}

function toMyanmar($number){
    $array = [
        '0' => '၀',
        '1' => '၁',
        '2' => '၂',
        '3' => '၃',
        '4' => '၄',
        '5' => '၅',
        '6' => '၆',
        '7' => '၇',
        '8' => '၈',
        '9' => '၉',
    ];
    return strtr($number, $array);
}

function toEnglish($number){
    $array = [
        '၀' => '0',
        '၁' => '1',
        '၂' => '2',
        '၃' => '3',
        '၄' => '4',
        '၅' => '5',
        '၆' => '6',
        '၇' => '7',
        '၈' => '8',
        '၉' => '9',
    ];
    return strtr($number, $array);
}

function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . '  year(s) ago';
    if ($diff->m > 0) return $diff->m . '  month(s) ago';
    if ($diff->d > 0) return $diff->d . '  day(s) ago';
    if ($diff->h > 0) return $diff->h . '  hour(s) ago';
    if ($diff->i > 0) return $diff->i . '  minute(s) ago';
    
    return 'just now';
}



?>