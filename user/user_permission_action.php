<?php
include('../config.php');

$action = $_POST["action"];

if($action == 'save_permission'){  
    $aid = $_POST['aid'];
    $username = GetString("SELECT UserName FROM tbluser WHERE AID = ?", [$aid]);

    $P1 = isset($_POST["P1"])?1:0;
    $P2 = isset($_POST["P2"])?1:0;

    $M1 = isset($_POST["M1"])?1:0;
    if($M1 == 1){
        $M1P1 = isset($_POST["M1P1"])?1:0;
        $M1P2 = isset($_POST["M1P2"])?1:0;
        $M1P3 = isset($_POST["M1P3"])?1:0;
    }else{
        $M1P1 = 0;
        $M1P2 = 0;
        $M1P3 = 0;
    }  

    $M2 = isset($_POST["M2"])?1:0;
    if($M2 == 1){
        $M2P1 = isset($_POST["M2P1"])?1:0;
        $M2P2 = isset($_POST["M2P2"])?1:0;
        $M2P3 = isset($_POST["M2P3"])?1:0;
    }else{
        $M2P1 = 0;
        $M2P2 = 0;
        $M2P3 = 0;
    }  

    $M3 = isset($_POST["M3"])?1:0;
    if($M3 == 1){
        $M3P1 = isset($_POST["M3P1"])?1:0;
        $M3P2 = isset($_POST["M3P2"])?1:0;
        $M3P3 = isset($_POST["M3P3"])?1:0;
    }else{
        $M3P1 = 0;
        $M3P2 = 0;
        $M3P3 = 0;
    } 

    $M4 = isset($_POST["M4"])?1:0;
    if($M4 == 1){
        $M4P1 = isset($_POST["M4P1"])?1:0;
        $M4P2 = isset($_POST["M4P2"])?1:0;
        $M4P3 = isset($_POST["M4P3"])?1:0;
        $M4P4 = isset($_POST["M4P4"])?1:0;
    }else{
        $M4P1 = 0;
        $M4P2 = 0;
        $M4P3 = 0;
        $M4P4 = 0;
    }

    $M5 = isset($_POST["M5"])?1:0;
    if($M5 == 1){
        $M5P1 = isset($_POST["M5P1"])?1:0;
        $M5P2 = isset($_POST["M5P2"])?1:0;
        $M5P3 = isset($_POST["M5P3"])?1:0;
    }else{
        $M5P1 = 0;
        $M5P2 = 0;
        $M5P3 = 0;
    } 

    $sql = "UPDATE tbluser SET P1=?, P2=?, 
    M1=?, M1P1=?, M1P2=?, M1P3=?,
    M2=?, M2P1=?, M2P2=?, M2P3=?, 
    M3=?, M3P1=?, M3P2=?, M3P3=?, 
    M4=?, M4P1=?, M4P2=?, M4P3=?, M4P4=?, 
    M5=?, M5P1=?, M5P2=?, M5P3=?  
    WHERE AID=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("iiiiiiiiiiiiiiiiiiiiiiii", $P1, $P2, 
            $M1, $M1P1, $M1P2, $M1P3, 
            $M2, $M2P1, $M2P2, $M2P3,  
            $M3, $M3P1, $M3P2, $M3P3,  
            $M4, $M4P1, $M4P2, $M4P3, $M4P4,   
            $M5, $M5P1, $M5P2, $M5P3,  
            $aid);
    if($stmt->execute()){
        save_log($_SESSION["esport_admin_username"]." သည် user (".$username.") ၏ Permission အား update သွားသည်။");
        echo 1;
    }else{
         error_log("Edit error in user action ".$stmt->error."\n", 3, root."user/my_log_file.log");
        echo 0;
    }
}



?>