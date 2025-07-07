<?php

include("config.php");

//GetBalance of Player	
$secret=$data["secret"];
$agent=$data["agent"];
$userName=$data["userName"];

// Set transaction isolation level and start transaction
mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
mysqli_begin_transaction($con);

try{
    $chk_secret = GetInt("SELECT AID FROM tblplayer WHERE secretID= ?",[$secret]);
    if($chk_secret > 0){
        //Usernameရှိမရှိစစ်
        $sql="SELECT * FROM tblplayer WHERE UserName= ? and secretID= ? FOR UPDATE";
        $stmt = $con -> prepare($sql);
        $stmt -> bind_param("ss",$userName,$secret);
        $stmt -> execute();
        $res = $stmt -> get_result();

        if($res -> num_rows > 0){
            $data = $res -> fetch_assoc(); 
            // Commit transaction
            mysqli_commit($con);                         
            echo json_encode(
                array(
                    "errorCode"=>"",
                    "balance"=>$data["Balance"]
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