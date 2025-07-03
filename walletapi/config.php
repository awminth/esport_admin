<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type:application/json');

//$con=new mysqli("localhost","root","root","schoolms");
$con=new mysqli("65.60.39.46","hitupkur_admin","kyoungunity*007*","hitupkur_esport");

mysqli_set_charset($con,"utf8");

date_default_timezone_set("Asia/Rangoon");

$dir=dirname(__FILE__);
define('root',__DIR__.'/');

define('roothtml','http://hitupmm.com/walletapi/');

$jsondata="php://input";
$phpjson=file_get_contents($jsondata);
$data=json_decode($phpjson,true);

function NumtoText($number)
{
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


function toMyanmar($number)
{
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


function toEnglish($number)
{
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

function GetString_old($sql)
{
    global $con;
    $str="";   
    $result=mysqli_query($con,$sql) or die("Query Fail");
    if(mysqli_num_rows($result)>0){

        $row = mysqli_fetch_array($result);
       $str= $row[0];
    }
    return $str;
}


function GetInt_old($sql)
{
    global $con;
    $str=0;     
    $result=mysqli_query($con,$sql) or die("Query Fail");
    if(mysqli_num_rows($result)>0){
        $row = mysqli_fetch_array($result);
       $str= $row[0];
    }
    return $str;
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




?>