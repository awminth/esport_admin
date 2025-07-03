<?php
include("config.php");  

$Amount = $data["Amount"];
$TransferCode = $data["TransferCode"];
$TransactionId = $data["TransactionId"];
$BetTime = $data["BetTime"];
$GameRoundId = $data["GameRoundId"];
$GamePeriodId = $data["GamePeriodId"];
$OrderDetail = $data["OrderDetail"];
$PlayerIp = $data["PlayerIp"];
$GameTypeName = $data["GameTypeName"];
$CompanyKey = $data["CompanyKey"];
$Username = $data["Username"];
$ProductType = $data["ProductType"];
$GameType = $data["GameType"];
$GameId = $data["GameId"];
$Gpid = $data["Gpid"];
// ExtraInfo
$extraInfo = $data["ExtraInfo"] ?? null; // JSON string

$extraInfoToInsert = [
    "sportType" => $extraInfo["sportType"] ?? null,
    "marketType" => $extraInfo["marketType"] ?? null,
    "league" => $extraInfo["league"] ?? null,
    "match" => $extraInfo["match"] ?? null,
    "betOption" => $extraInfo["betOption"] ?? null,
    "kickoffTime" => $extraInfo["kickoffTime"] ?? null,
    "isHalfWonLose" => $extraInfo["isHalfWonLose"] ?? null,
    "winlostDate" => $extraInfo["winlostDate"] ?? null
];

$jsonExtraInfo = json_encode($extraInfoToInsert, JSON_UNESCAPED_UNICODE);

//SeamlessGameExtraInfo
$seamlessJson = $data["SeamlessGameExtraInfo"] ?? null; // JSON string

$seamlessgameextrainfoToInsert = [
    $FeatureBuyStatus = $seamlessJson["FeatureBuyStatus"] ?? null,
    $EndRoundStatus = $seamlessJson["EndRoundStatus"] ?? null
];

$jsonSeamlessgameextrainfo = json_encode($seamlessgameextrainfoToInsert, JSON_UNESCAPED_UNICODE);

// create vno
$vno = $TransferCode."-".$TransactionId;

// get player balance
$player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
// get player id
$playerID = GetInt("SELECT AID FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
// create dt
$dt = date("Y-m-d H:i:s");

// for Sport 1 and Virtual Sport 5
if($ProductType == 1 || $ProductType == 5){
    fun_for_sport();
}
// for SBO NRG 3 and Casino 7
else if($ProductType == 3 || $ProductType == 7){
    fun_for_casino();
}
// for 3rd Wan Mei
else if($ProductType == 9){
    fun_for_3rdwanmei();
}
else{
    error_log("Product Type error in deduct");
    echo json_encode(
        array(
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error producttype"
        ));
}

// create function for sport and virtual sport
function fun_for_sport() {
    global $con; 

    global $Amount;
    global $TransferCode;
    global $TransactionId;
    global $BetTime;
    global $GameRoundId;
    global $GamePeriodId;
    global $OrderDetail;
    global $PlayerIp;
    global $GameTypeName;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $GameId;
    global $Gpid;
    global $jsonExtraInfo;
    global $jsonSeamlessgameextrainfo;

    global $vno;
    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // deduct ထဲမှာ လောင်းပြီးသား ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT VNO FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // deduct ထဲမှာ မရှိသေးတဲ့ အခြေအနေဆိုရင် ပွဲကို အသစ်လောင်း (for deduct)
        if ($check != $vno) {
            // လောင်းကြေးငွေ လောက်/မလောက် စစ်
            $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                     [$Username, $CompanyKey]);
            
            if ($current_balance >= $Amount) {
                // Insert into deduct table
                $sql_insert = "INSERT INTO tbldeduct (Amount, TransferCode, TransactionId, BetTime, GameRoundId, 
                              GamePeriodId, OrderDetail, PlayerIp, GameTypeName, CompanyKey, Username, ProductType, 
                              GameType, GameID, GpID, ExtraInfo, SeamlessGameExtraInfo, VNO) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $con->prepare($sql_insert);
                $stmt->bind_param("dssssssssssiiiisss", $Amount, $TransferCode, $TransactionId, $BetTime, $GameRoundId, 
                                 $GamePeriodId, $OrderDetail, $PlayerIp, $GameTypeName, $CompanyKey, $Username, 
                                 $ProductType, $GameType, $GameId, $Gpid, $jsonExtraInfo, $jsonSeamlessgameextrainfo, $vno);
                
                if ($stmt->execute()) {
                    // Insert into balanceout table
                    $sql_balanceout = "INSERT INTO tblbalanceout (PlayerID, Amount, DateTime, TransferCode, TransactionID) 
                                       VALUES (?, ?, ?, ?, ?)";
                    $stmt_balanceout = $con->prepare($sql_balanceout);
                    $stmt_balanceout->bind_param("sdsss", $playerID, $Amount, $dt, $TransferCode, $TransactionId);
                    $stmt_balanceout->execute();
                    
                    // Update player balance
                    $update_balance = $current_balance - $Amount;
                    $sql_playerupdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerupdate);
                    $stmt_update->bind_param("dss", $update_balance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $update_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error",
                        "BetAmount" => $Amount
                    ]);
                }
            } else {
                // if not enough balance
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $current_balance,
                    "ErrorCode" => 5,
                    "ErrorMessage" => "Not enough balance"
                ]);
            }
        } 
        // deduct ထဲမှာ ရှိပြီးသား အခြေအနေဆိုရင် error ပြန်ပေးရမယ်
        else {
            // Rollback if bet already exists
            mysqli_rollback($con);
            echo json_encode([
                "Balance" => $player_balance,
                "ErrorCode" => 5003,
                "ErrorMessage" => "Bet With Same RefNo Exists"
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in deduct fun_for_sport(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in deduct fun_for_sport(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// create function for sbo nrg and casino
function fun_for_casino() {
    global $con; 

    global $Amount;
    global $TransferCode;
    global $TransactionId;
    global $BetTime;
    global $GameRoundId;
    global $GamePeriodId;
    global $OrderDetail;
    global $PlayerIp;
    global $GameTypeName;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $GameId;
    global $Gpid;
    global $jsonExtraInfo;
    global $jsonSeamlessgameextrainfo;

    global $vno;
    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // deduct ထဲမှာ လောင်းပြီးသား ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT VNO FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // deduct ထဲမှာ မရှိသေးတဲ့ အခြေအနေဆိုရင် ပွဲကို အသစ်လောင်း (for deduct)
        if ($check != $vno) {
            // Get player balance
            $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                     [$Username, $CompanyKey]);
            // လောင်းကြေးငွေ လောက်/မလောက် စစ်
            if ($current_balance >= $Amount) {
                // Insert into deduct table
                $sql_insert = "INSERT INTO tbldeduct (Amount, TransferCode, TransactionId, BetTime, GameRoundId, 
                              GamePeriodId, OrderDetail, PlayerIp, GameTypeName, CompanyKey, Username, ProductType, 
                              GameType, GameID, GpID, ExtraInfo, SeamlessGameExtraInfo, VNO) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $con->prepare($sql_insert);
                $stmt->bind_param("dssssssssssiiiisss", $Amount, $TransferCode, $TransactionId, $BetTime, $GameRoundId, 
                                 $GamePeriodId, $OrderDetail, $PlayerIp, $GameTypeName, $CompanyKey, $Username, 
                                 $ProductType, $GameType, $GameId, $Gpid, $jsonExtraInfo, $jsonSeamlessgameextrainfo, $vno);
                
                if ($stmt->execute()) {
                    // Insert into balanceout table
                    $sql_balanceout = "INSERT INTO tblbalanceout (PlayerID, Amount, DateTime, TransferCode, TransactionID) 
                                      VALUES (?, ?, ?, ?, ?)";
                    $stmt_balanceout = $con->prepare($sql_balanceout);
                    $stmt_balanceout->bind_param("sdsss", $playerID, $Amount, $dt, $TransferCode, $TransactionId);
                    $stmt_balanceout->execute();
                    
                    // Update player balance
                    $update_balance = $current_balance - $Amount;
                    $sql_playerupdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerupdate);
                    $stmt_update->bind_param("dss", $update_balance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $update_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error",
                        "BetAmount" => $Amount
                    ]);
                }
            } 
            // လောင်းကြေးငွေ မလောက်ခဲ့ဘူးဆိုရင် error ပြ
            else {
                // Rollback if not enough balance
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $current_balance,
                    "ErrorCode" => 5,
                    "ErrorMessage" => "Not enough balance"
                ]);
            }
        }
        // deduct ထဲမှာ ရှိပြီးသား အခြေအနေဆိုရင်    
        else {
            // ပထမဆုံး settle လုပ်ထားပြီးသားလား / မလုပ်ထားရသေးလား အရင်စစ်
            $chkTransCodeSettle = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
            
            // settle မလုပ်ထားရသေးတဲ့ အခြေအနေ
            if ($TransferCode != $chkTransCodeSettle) {
                // ဒုတိယအကြိမ်လောင်းငွေ သည် ပထမအကြိမ်ထက် များမှ လောင်းလို့ရတဲ့ အခြေအနေ
                $previousAmount = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
                
                if ($Amount > $previousAmount) {
                    
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                             [$Username, $CompanyKey]);
                    // လောင်းကြေးငွေ လောက်/မလောက် စစ်
                    if ($current_balance >= $Amount) {
                        // 1- ပထမအကြိမ်တုန်းက လောင်းထားတဲ့ amount ကို tblplayer ရဲ့ Balance ထဲကို အရင်ပြန်ပေါင်းပေး
                        $update_balanceFirst = $current_balance + $previousAmount;
                        $update_playerfirst = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_refund = $con->prepare($update_playerfirst);
                        $stmt_refund->bind_param("dss", $update_balanceFirst, $Username, $CompanyKey);
                        $stmt_refund->execute();
                        
                        // 2- ဒုတိယအကြိမ်လောင်းတာကို မထည့်ခင် ပထမလောင်းထားတဲ့ record ကို Deduct ကနေ အရင်ဖျက်
                        $deletesql_deduct = "DELETE FROM tbldeduct WHERE TransferCode = ?";
                        $stmt_del_deduct = $con->prepare($deletesql_deduct);
                        $stmt_del_deduct->bind_param("s", $TransferCode);
                        $stmt_del_deduct->execute();
                        
                        // 3- ဒုတိယအကြိမ်လောင်းတာကို မထည့်ခင် ပထမလောင်းထားတဲ့ record ကို balance out ကနေ ထပ်ဖျက်
                        $deletesql_out = "DELETE FROM tblbalanceout WHERE TransferCode = ? AND TransactionID = ?";
                        $stmt_del_out = $con->prepare($deletesql_out);
                        $stmt_del_out->bind_param("ss", $TransferCode, $TransactionId);
                        $stmt_del_out->execute();
                        
                        // Insert new deduct record
                        $sql_insert = "INSERT INTO tbldeduct (Amount, TransferCode, TransactionId, BetTime, GameRoundId, 
                                      GamePeriodId, OrderDetail, PlayerIp, GameTypeName, CompanyKey, Username, ProductType, 
                                      GameType, GameID, GpID, ExtraInfo, SeamlessGameExtraInfo, VNO) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
                        $stmt = $con->prepare($sql_insert);
                        $stmt->bind_param("dssssssssssiiiisss", $Amount, $TransferCode, $TransactionId, $BetTime, $GameRoundId, 
                                         $GamePeriodId, $OrderDetail, $PlayerIp, $GameTypeName, $CompanyKey, $Username, 
                                         $ProductType, $GameType, $GameId, $Gpid, $jsonExtraInfo, $jsonSeamlessgameextrainfo, $vno);
                        
                        if ($stmt->execute()) {
                            // Insert new balanceout record
                            $sql_balanceout = "INSERT INTO tblbalanceout (PlayerID, Amount, DateTime, TransferCode, TransactionID) 
                                              VALUES (?, ?, ?, ?, ?)";
                            $stmt_balanceout = $con->prepare($sql_balanceout);
                            $stmt_balanceout->bind_param("sdsss", $playerID, $Amount, $dt, $TransferCode, $TransactionId);
                            $stmt_balanceout->execute();
                            
                            // လက်ကျန်ငွေထဲကနေ လောင်းကြေးငွေကို နှုတ်
                            $update_balance = $update_balanceFirst - $Amount;
                            $sql_playerupdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_update = $con->prepare($sql_playerupdate);
                            $stmt_update->bind_param("dss", $update_balance, $Username, $CompanyKey);
                            $stmt_update->execute();
                            
                            // Commit transaction
                            mysqli_commit($con);
                            
                            echo json_encode([
                                "AccountName" => $Username,
                                "Balance" => $update_balance,
                                "ErrorCode" => 0,
                                "ErrorMessage" => "No Error",
                                "BetAmount" => $Amount
                            ]);
                        }
                    } 
                    // လောင်းကြေးငွေ မလောက်ခဲ့ဘူးဆိုရင် error ပြ
                    else {
                        // Rollback if not enough balance for increased bet
                        mysqli_rollback($con);
                        echo json_encode([
                            "Balance" => $current_balance,
                            "ErrorCode" => 5,
                            "ErrorMessage" => "Not enough balance"
                        ]);
                    }
                } 
                // ဒုတိယအကြိမ်လောင်းငွေ သည် ပထမအကြိမ်ထက် နည်းနေရင် error ပြ
                else {
                    // Rollback if new amount not greater than previous
                    mysqli_rollback($con);
                    echo json_encode([
                        "Balance" => $player_balance,
                        "ErrorCode" => 7,
                        "ErrorMessage" => "New bet amount must be greater than previous"
                    ]);
                }
            } 
            // settle လုပ်ပြီးသွားတဲ့ အခြေအနေ
            else {
                // Rollback if already settled
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 5003,
                    "ErrorMessage" => "Bet With Same RefNo Exists"
                ]);
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in deduct fun_for_casino(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in deduct fun_for_casino(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// create function for 3rd Wan Mei
function fun_for_3rdwanmei() {
    global $con; 

    global $Amount;
    global $TransferCode;
    global $TransactionId;
    global $BetTime;
    global $GameRoundId;
    global $GamePeriodId;
    global $OrderDetail;
    global $PlayerIp;
    global $GameTypeName;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $GameId;
    global $Gpid;
    global $jsonExtraInfo;
    global $jsonSeamlessgameextrainfo;

    global $vno;
    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // deduct ထဲမှာ လောင်းပြီးသား ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT VNO FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // deduct ထဲမှာ မရှိသေးတဲ့ အခြေအနေဆိုရင် ပွဲကို အသစ်လောင်း (for deduct)
        if ($check != $vno) {
            // Get player balance
            $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                     [$Username, $CompanyKey]);
            // လောင်းကြေးငွေ လောက်/မလောက် စစ်
            if ($current_balance >= $Amount) {
                // Insert into deduct table
                $sql_insert = "INSERT INTO tbldeduct (Amount, TransferCode, TransactionId, BetTime, GameRoundId, 
                              GamePeriodId, OrderDetail, PlayerIp, GameTypeName, CompanyKey, Username, ProductType, 
                              GameType, GameID, GpID, ExtraInfo, SeamlessGameExtraInfo, VNO) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $con->prepare($sql_insert);
                $stmt->bind_param("dssssssssssiiiisss", $Amount, $TransferCode, $TransactionId, $BetTime, $GameRoundId, 
                                 $GamePeriodId, $OrderDetail, $PlayerIp, $GameTypeName, $CompanyKey, $Username, 
                                 $ProductType, $GameType, $GameId, $Gpid, $jsonExtraInfo, $jsonSeamlessgameextrainfo, $vno);
                
                if ($stmt->execute()) {
                    // Insert into balanceout table
                    $sql_balanceout = "INSERT INTO tblbalanceout (PlayerID, Amount, DateTime, TransferCode, TransactionID) 
                                      VALUES (?, ?, ?, ?, ?)";
                    $stmt_balanceout = $con->prepare($sql_balanceout);
                    $stmt_balanceout->bind_param("sdsss", $playerID, $Amount, $dt, $TransferCode, $TransactionId);
                    $stmt_balanceout->execute();
                    
                    // Update player balance
                    $update_balance = $current_balance - $Amount;
                    $sql_playerupdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerupdate);
                    $stmt_update->bind_param("dss", $update_balance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $update_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error",
                        "BetAmount" => $Amount
                    ]);
                }
            } 
            // လောင်းကြေးငွေ မလောက်ခဲ့ ရင် error ပြ
            else {
                // Rollback if not enough balance
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $current_balance,
                    "ErrorCode" => 5,
                    "ErrorMessage" => "Not enough balance"
                ]);
            }
        } 
        // deduct ထဲမှာ ရှိပြီးသား အခြေအနေဆိုရင် error ပြန်ပေးရမယ်
        else {
            // Rollback if bet already exists
            mysqli_rollback($con);
            echo json_encode([
                "Balance" => $player_balance,
                "ErrorCode" => 5003,
                "ErrorMessage" => "Bet With Same RefNo Exists"
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 5003,
                "ErrorMessage" => "Bet With Same RefNo Exists",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in deduct fun_for_3rdwanmei(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in deduct fun_for_3rdwanmei(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}


?>