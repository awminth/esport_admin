<?php

include("config.php");

$dt = date("Y-m-d H:i:s");
$CompanyKey=$data["CompanyKey"];
$Username=$data["Username"];
$ProductType=$data["ProductType"];
$GameType=$data["GameType"];
$TransferCode=$data["TransferCode"];
$TransactionId=$data["TransactionId"];
$Gpid=$data["Gpid"];


// Set transaction isolation level and start transaction
mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
mysqli_begin_transaction($con);

try{
    // Usernameရှိမရှိ စစ်ဆေးခြင်း
    $chk_username = GetInt("SELECT AID FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
    if($chk_username > 0){
        // check whether TransferCode exists in tbldeduct for status 
        $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ? FOR UPDATE";
        $stmt = $con -> prepare($sql_chk);
        $stmt -> bind_param("ss",$TransferCode, $TransactionId);
        $stmt -> execute();
        $res = $stmt->get_result();
        if($res -> num_rows >0){
            $data = $res -> fetch_assoc(); 
            // check CancelStatus for settle
            if($data["CancelStatus"] == "no"){
                //check settle for status settled
                $sql_chksettle = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                $stmt_chksettle = $con -> prepare($sql_chksettle);
                $stmt_chksettle -> bind_param("s",$TransferCode);
                $stmt_chksettle -> execute();
                $res_chksettle = $stmt_chksettle -> get_result();
                if($res_chksettle -> num_rows > 0){
                    $data_settle = $res_chksettle -> fetch_assoc();
                    mysqli_commit($con); 
                    echo json_encode(
                        array(
                            "TransferCode"=>$TransferCode,
                            "TransactionId"=>$TransactionId,
                            "Status"=>"settled",
                            "WinLoss"=>$data_settle["WinLoss"],
                            "Stake"=>$data["Amount"],
                            "ErrorCode"=>0,
                            "ErrorMessage"=>"No Error"
                        ));
                }
                else{
                    mysqli_rollback($con);
                    echo json_encode(
                        array(
                            "TransferCode"=>$TransferCode,
                            "TransactionId"=>$TransactionId,
                            "Status"=>"running",
                            "WinLoss"=>0,
                            "Stake"=>$data["Amount"],
                            "ErrorCode"=>0,
                            "ErrorMessage"=>"No Error"
                        ));
                } 
            }
            else{
                mysqli_rollback($con);
                echo json_encode(
                    array(
                        "TransferCode"=>$TransferCode,
                        "TransactionId"=>$TransactionId,
                        "Status"=>"void",
                        "ErrorCode"=>0,
                        "ErrorMessage"=>"No Error"
                    ));
            }
        }
        else{
            mysqli_rollback($con);
            echo json_encode(
                array(
                    "TransferCode"=>$TransferCode,
                    "TransactionId"=>$TransactionId,
                    "Status"=>"void",
                    "ErrorCode"=>6,
                    "ErrorMessage"=>"Bet not exists"
                ));
        }
    }
    else{
        mysqli_rollback($con);
        echo json_encode(
            array(
                "ErrorCode"=>3,
                "ErrorMessage"=>"Username empty"
            ));
    }
}
catch(mysqli_sql_exception $e){
    mysqli_rollback($con);
    error_log("Database error in GetBetStatus: " . $e->getMessage());
    echo json_encode(
        array(
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error"
        ));
}
catch(Exception $e){
    mysqli_rollback($con);
    error_log("System error in GetBetStatus: " . $e->getMessage());
    echo json_encode(
        array(
            "ErrorCode"=>3,
            "ErrorMessage"=>"Username empty"
        ));
}


?>