<?php
include("config.php");

$CompanyKey = $data["CompanyKey"];
$Username = $data["Username"];
$TransferCode = $data["TransferCode"];
$ProductType = $data["ProductType"];
$GameType = $data["GameType"];
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
    error_log("Product Type error in rollback");
    echo json_encode(
        array(
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error producttype"
        ));
}

// for function sport and virtual sport
function fun_for_sport() {
    global $con;

    global $CompanyKey;
    global $Username;
    global $TransferCode;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Deduct ထဲမှာ ရှိ/မရှိ စစ်မယ်
        $chk_Deduct = GetInt("SELECT AID FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        // deduct မှာ ရှိတဲ့ အခြေအနေဆိုရင်
        if ($chk_Deduct > 0) {
            // rollback လုပ်ထားပီးသားလား / မလုပ်ရသေးလား စစ်မယ်
            $check = GetString("SELECT TransferCode FROM tblrollback WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
            // rollback မလုပ်ထားရသေးတဲ့ အခြေအနေ
            if ($check != $TransferCode) {
                // Check if this is from settlebet or just deduct
                $sql_chk = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                $stmt_chk = $con->prepare($sql_chk);
                $stmt_chk->bind_param("s", $TransferCode);
                $stmt_chk->execute();
                $res_chk = $stmt_chk->get_result();
                
                if ($res_chk->num_rows > 0) {
                    $data_settle = $res_chk->fetch_assoc();
                    
                    // Insert into rollback table
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // Get player balance with locking
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
                    
                    // Handle different cancel statuses
                    if ($data_settle["CancelStatus"] == "no") {
                        $updatePlayerBalance = $player_balance - $data_settle["WinLoss"];
                    } else {
                        $deductBalance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                        $updatePlayerBalance = $player_balance - $deductBalance;
                    }
                    
                    // Update player balance
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerUpdate);
                    $stmt_update->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // Get final balance
                    $finalPlayerBalance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ?", 
                                                [$Username, $CompanyKey]);
                    
                    // Delete related records
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_delSettle = $con->prepare($sql_delSettle);
                    $stmt_delSettle->bind_param("s", $TransferCode);
                    $stmt_delSettle->execute();
                    
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_delBalancein = $con->prepare($sql_delBalancein);
                    $stmt_delBalancein->bind_param("s", $TransferCode);
                    $stmt_delBalancein->execute();
                    
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_updateDeduct = $con->prepare($sql_updateDeduct);
                    $stmt_updateDeduct->bind_param("s", $TransferCode);
                    $stmt_updateDeduct->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $finalPlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } else {
                    // Handle case where it's just a deduct rollback
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // Update deduct status
                    $sql_updateStatus = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateStatus);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Get and update player balance
                    $deductBalance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                    $player_Balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    $updatePlayerBalance = $player_Balance - $deductBalance;
                    
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_player = $con->prepare($sql_playerUpdate);
                    $stmt_player->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_player->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $updatePlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                }
            } 
            // rollback လုပ်ထားပီးသား အခြေအနေဆိုရင်
            else {
                // settle လုပ်ထားပြီးလား / မလုပ်ထားလား စစ်မယ်
                $rollbackSettle = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                // settle လုပ်ပီးသား  ဆိုရင်
                if ($rollbackSettle == $TransferCode) {
                    // player balance ကို ရှာမယ်
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    // settle ထဲက winloss တန်ဖိုးကို ရှာမယ်
                    $settleBalance = GetInt("SELECT WinLoss FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                    // Player Balance ကို Update လုပ်
                    $final_rollbackSettle = $player_balance - $settleBalance;
                    
                    $sql_rollbackSettleUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_rollbackSettleUpdate);
                    $stmt_update->bind_param("dss", $final_rollbackSettle, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // settlebet ကို ဖျက်
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delSettle);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // balancein ကိုဖျက်၊ 
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delBalancein);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // Deduct Statusကို Update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_rollbackSettle,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } 
                // settle မလုပ်ရသေးဘူး ဆိုရင်
                else {
                    // Rollback transaction as it's already processed
                    mysqli_rollback($con);
                    
                    echo json_encode([
                        "ErrorCode" => 2003,
                        "ErrorMessage" => "Bet Already Rollback",
                        "Balance" => $player_balance
                    ]);
                }
            }
        } 
        // deduct မှာ မရှိတဲ့ အခြေအနေဆိုရင် 
        else {
            // Rollback transaction as bet doesn't exist
            mysqli_rollback($con);
            
            echo json_encode([
                "Balance" => $player_balance,
                "ErrorCode" => 6,
                "ErrorMessage" => "Bet not exists"
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2003,
                "ErrorMessage" => "Bet Already Rollback",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in rollback fun_for_sport() " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error: " . $e->getCode()
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        
        error_log("System error in rollback fun_for_sport(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// for function casino and sbo rng
function fun_for_casino() {
    global $con;

    global $CompanyKey;
    global $Username;
    global $TransferCode;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Deduct ထဲမှာ ရှိ/မရှိ စစ်မယ်
        $chk_Deduct = GetInt("SELECT AID FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        // deduct မှာ ရှိတဲ့ အခြေအနေဆိုရင်
        if ($chk_Deduct > 0) {
            // rollback လုပ်ထားပီးသားလား / မလုပ်ရသေးလား စစ်မယ်
            $check = GetString("SELECT TransferCode FROM tblrollback WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
            // rollback မလုပ်ထားရသေးတဲ့ အခြေအနေ
            if ($check != $TransferCode) {
                // rollback မလုပ်ထားတဲ့ အခြေအနေ condition 2 ခုရှိမယ်
                // 1- settle ကနေ rollback လုပ်တာ
                // 2- deduct ကနေ rollback လုပ်တာ
                
                // 1- Settle bet ကနေ Rollback လုပ်တာ
                $sql_chk = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                $stmt_chk = $con->prepare($sql_chk);
                $stmt_chk->bind_param("s", $TransferCode);
                $stmt_chk->execute();
                $res_chk = $stmt_chk->get_result();
                
                if ($res_chk->num_rows > 0) {
                    $data_settle = $res_chk->fetch_assoc();
                    
                    // Insert into rollback table
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // player balance ကို ယူမယ်
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
                    
                    // settle ကနေ cancel မလုပ်ရသေးဘူးဆိုရင် winloss ကို ပြန်နှုတ်ပေး
                    if ($data_settle["CancelStatus"] == "no") {
                        $updatePlayerBalance = $player_balance - $data_settle["WinLoss"];
                    }
                    // settle ကနေ cancel လုပ်ပီးသားဆိုရင် မူလလောင်းထားတဲ့ amount ကိုပဲ ပြန်နှုတ်ပေး
                    else {
                        $deductBalance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                        $updatePlayerBalance = $player_balance - $deductBalance;
                    }
                    
                    // Update player balance
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerUpdate);
                    $stmt_update->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // နောက်ဆုံး player balance
                    $finalPlayerBalance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ?", 
                                              [$Username, $CompanyKey]);
                    
                    // settle bet ကို ဖျက်
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_delSettle = $con->prepare($sql_delSettle);
                    $stmt_delSettle->bind_param("s", $TransferCode);
                    $stmt_delSettle->execute();
                    // balance in ကို ဖျက်
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_delBalancein = $con->prepare($sql_delBalancein);
                    $stmt_delBalancein->bind_param("s", $TransferCode);
                    $stmt_delBalancein->execute();
                    // deduct status ကို update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_updateDeduct = $con->prepare($sql_updateDeduct);
                    $stmt_updateDeduct->bind_param("s", $TransferCode);
                    $stmt_updateDeduct->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $finalPlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } 
                // 2- deduct ကနေ rollback လုပ်တာ
                else {
                    // insert into rollback
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // deduct status ကို update ရိုက်
                    $sql_updateStatus = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateStatus);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Get and update player balance
                    $deductBalance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                    $player_Balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    $updatePlayerBalance = $player_Balance - $deductBalance;
                    
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_player = $con->prepare($sql_playerUpdate);
                    $stmt_player->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_player->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $updatePlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                }
            } 
            // rollback လုပ်ထားပီးသား အခြေအနေဆိုရင်
            else {
                // settle လုပ်ထားပြီးလား / မလုပ်ထားလား စစ်မယ်
                $rollbackSettle = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                // settle လုပ်ပီးသား  ဆိုရင်
                if ($rollbackSettle == $TransferCode) {
                    // player balance ကို ရှာမယ်
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    // settle ထဲက winloss တန်ဖိုးကိုရှာမယ်
                    $settleBalance = GetInt("SELECT WinLoss FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                    // player balance ကို update ရိုက်
                    $final_rollbackSettle = $player_balance - $settleBalance;
                    
                    $sql_rollbackSettleUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_rollbackSettleUpdate);
                    $stmt_update->bind_param("dss", $final_rollbackSettle, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // settle bet ကို ဖျက် 
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delSettle);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // balance in ကို ဖျက်
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delBalancein);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // Deduct Statusကို Update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_rollbackSettle,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } 
                // settle မလုပ်ရသေးဘူး ဆိုရင်
                else {
                    // Rollback transaction as it's already processed
                    mysqli_rollback($con);
                    
                    echo json_encode([
                        "ErrorCode" => 2003,
                        "ErrorMessage" => "Bet Already Rollback",
                        "Balance" => $player_balance
                    ]);
                }
            }
        } 
        // deduct မှာ မရှိတဲ့ အခြေအနေဆိုရင် 
        else {
            // Rollback transaction as bet doesn't exist
            mysqli_rollback($con);
            
            echo json_encode([
                "Balance" => $player_balance,
                "ErrorCode" => 6,
                "ErrorMessage" => "Bet not exists"
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2003,
                "ErrorMessage" => "Bet Already Rollback",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in rollback fun_for_casino(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error: " . $e->getCode()
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        
        error_log("System error in rollback fun_for_casino(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// for function 3rd wan mei
function fun_for_3rdwanmei() {
    global $con;

    global $CompanyKey;
    global $Username;
    global $TransferCode;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Deduct ထဲမှာ ရှိ/မရှိ စစ်မယ်
        $chk_Deduct = GetInt("SELECT AID FROM tbldeduct WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        // deduct မှာ ရှိတဲ့ အခြေအနေဆိုရင်
        if ($chk_Deduct > 0) {
            // rollback လုပ်ထားပီးသားလား / မလုပ်ရသေးလား စစ်မယ်
            $check = GetString("SELECT TransferCode FROM tblrollback WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
            // rollback မလုပ်ထားရသေးတဲ့ အခြေအနေ
            if ($check != $TransferCode) {
                // rollback မလုပ်ထားတဲ့ အခြေအနေ condition 2 ခုရှိမယ်
                // 1- settle ကနေ rollback လုပ်တာ
                // 2- deduct ကနေ rollback လုပ်တာ

                // 1- Settle bet ကနေ Rollback လုပ်တာ
                $sql_chk = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                $stmt_chk = $con->prepare($sql_chk);
                $stmt_chk->bind_param("s", $TransferCode);
                $stmt_chk->execute();
                $res_chk = $stmt_chk->get_result();
                
                if ($res_chk->num_rows > 0) {
                    $data_settle = $res_chk->fetch_assoc();
                    
                    // Insert into rollback table
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // Get player balance 
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
                    
                    // settle ကနေ cancel မလုပ်ရသေးဘူးဆိုရင် winloss ကို ပြန်နှုတ်ပေး
                    if ($data_settle["CancelStatus"] == "no") {
                        $updatePlayerBalance = $player_balance - $data_settle["WinLoss"];
                    } 
                    // settle ကနေ cancel လုပ်ပီးသားဆိုရင် မူလလောင်းထားတဲ့ amount ကိုပဲ ပြန်နှုတ်ပေး
                    else {
                        $deductBalance = GetInt("SELECT SUM(Amount) FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                        $updatePlayerBalance = $player_balance - $deductBalance;
                    }
                    
                    // Update player balance
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_playerUpdate);
                    $stmt_update->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // နောက်ဆုံး player balance
                    $finalPlayerBalance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ?", 
                                              [$Username, $CompanyKey]);
                    
                    // Delete settle records
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_delSettle = $con->prepare($sql_delSettle);
                    $stmt_delSettle->bind_param("s", $TransferCode);
                    $stmt_delSettle->execute();
                    // delete balance in
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_delBalancein = $con->prepare($sql_delBalancein);
                    $stmt_delBalancein->bind_param("s", $TransferCode);
                    $stmt_delBalancein->execute();
                    // deduct status ကို update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_updateDeduct = $con->prepare($sql_updateDeduct);
                    $stmt_updateDeduct->bind_param("s", $TransferCode);
                    $stmt_updateDeduct->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $finalPlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } 
                // 2- deduct ကနေ rollback လုပ်တာ
                else {
                    // insert rollback table
                    $sql = "INSERT INTO tblrollback (CompanyKey, Username, TransferCode, ProductType, GameType, GpID, ExtraInfo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("sssiiis", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, $Gpid, $jsonExtraInfo);
                    $stmt->execute();
                    
                    // deduct status ကို update ရိုက်
                    $sql_updateStatus = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateStatus);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Get and update player balance
                    $deductBalance = GetInt("SELECT SUM(Amount) FROM tbldeduct WHERE TransferCode = ?", [$TransferCode]);
                    $player_Balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    $updatePlayerBalance = $player_Balance - $deductBalance;
                    
                    $sql_playerUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_player = $con->prepare($sql_playerUpdate);
                    $stmt_player->bind_param("dss", $updatePlayerBalance, $Username, $CompanyKey);
                    $stmt_player->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $updatePlayerBalance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                }
            } 
            // rollback လုပ်ထားပီးသား အခြေအနေဆိုရင်
            else {
                // settle လုပ်ထားပြီးလား / မလုပ်ထားလား စစ်မယ်
                $rollbackSettle = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                // settle လုပ်ပီးသား  ဆိုရင်
                if ($rollbackSettle == $TransferCode) {
                    // Get player balances
                    $player_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                          [$Username, $CompanyKey]);
                    // get winloss 
                    $settleBalance = GetInt("SELECT WinLoss FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
                    
                    $final_rollbackSettle = $player_balance - $settleBalance;
                    // player balance ကို update လုပ်
                    $sql_rollbackSettleUpdate = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                    $stmt_update = $con->prepare($sql_rollbackSettleUpdate);
                    $stmt_update->bind_param("dss", $final_rollbackSettle, $Username, $CompanyKey);
                    $stmt_update->execute();
                    
                    // Delete settle records
                    $sql_delSettle = "DELETE FROM tblsettlebet WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delSettle);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // delete balance in record
                    $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                    $stmt_del = $con->prepare($sql_delBalancein);
                    $stmt_del->bind_param("s", $TransferCode);
                    $stmt_del->execute();
                    // deduct status ကို update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'no' WHERE TransferCode = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("s", $TransferCode);
                    $stmt_update->execute();
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_rollbackSettle,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                } 
                // settle မလုပ်ရသေးဘူး ဆိုရင်
                else {
                    // Rollback transaction as it's already processed
                    mysqli_rollback($con);
                    
                    echo json_encode([
                        "ErrorCode" => 2003,
                        "ErrorMessage" => "Bet Already Rollback",
                        "Balance" => $player_balance
                    ]);
                }
            }
        } 
        // deduct မှာ မရှိတဲ့ အခြေအနေဆိုရင် 
        else {
            // Rollback transaction as bet doesn't exist
            mysqli_rollback($con);
            
            echo json_encode([
                "Balance" => $player_balance,
                "ErrorCode" => 6,
                "ErrorMessage" => "Bet not exists"
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2003,
                "ErrorMessage" => "Bet Already Rollback",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in rollback fun_for_3rdwanmei(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error: " . $e->getCode()
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        
        error_log("System error in rollback fun_for_3rdwanmei(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}


?>