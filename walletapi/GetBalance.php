<?php

include("config.php");

//GetBalance of Player
	
$CompanyKey=$data["CompanyKey"];
$Username=$data["Username"];
$ProductType=$data["ProductType"];
$GameType=$data["GameType"];
$Gpid=$data["Gpid"];

// Set transaction isolation level and start transaction
mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
mysqli_begin_transaction($con);

try{
    $chk_companykey = GetInt("SELECT AID FROM tblplayer WHERE CompanyKey= ?",[$CompanyKey]);
    if($chk_companykey > 0){
        //Usernameရှိမရှိစစ်
        $sql="SELECT * FROM tblplayer WHERE UserName= ? and CompanyKey= ? FOR UPDATE";
        $stmt = $con -> prepare($sql);
        $stmt -> bind_param("ss",$Username,$CompanyKey);
        $stmt -> execute();
        $res = $stmt -> get_result();

        if($res -> num_rows > 0){
            $data = $res -> fetch_assoc(); 
            // Commit transaction
            mysqli_commit($con);                         
            echo json_encode(
                array(
                    "AccountName"=>$data["UserName"],
                    "Balance"=>$data["Balance"],
                    "ErrorCode"=>0,
                    "ErrorMessage"=>"No Error"
                ));
            
        }
        else{
            // Rollback if bet doesn't exist
            mysqli_rollback($con);
            echo json_encode(
                array(
                    "Balance"=>0,
                    "ErrorCode"=>1,
                    "ErrorMessage"=>"Member not exist"
                ));
        }
    }
    else{
        mysqli_rollback($con);
        echo json_encode(
            array(
                "Balance"=>0,
                "ErrorCode"=>4,
                "ErrorMessage"=>"CompanyKey Error"
            ));
    }
    
}
catch(mysqli_sql_exception $e){
    mysqli_rollback($con);
    error_log("Database error in GetBalance: " . $e->getMessage());
    echo json_encode(
        array(
            "Balance"=>0,
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error"
        ));
}
catch(Exception $e){
    mysqli_rollback($con);
    error_log("System error in GetBalance: " . $e->getMessage());
    echo json_encode(
        array(
            "Balance"=>0,
            "ErrorCode"=>7,
            "ErrorMessage"=>"System Error"
        ));
}

?>