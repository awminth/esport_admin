<?php
include("config.php");

$CompanyKey=$data["CompanyKey"];
$Username=$data["Username"];
$TransferCode=$data["TransferCode"];
$ProductType=$data["ProductType"];
$GameType=$data["GameType"];
$IsCancelAll=$data["IsCancelAll"];
$TransactionId=$data["TransactionId"];
$Gpid=$data["Gpid"];
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
// create vno
$vno = $TransferCode ."-". $TransactionId;

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
    error_log("Product Type error in cancel");
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
    global $IsCancelAll;
    global $TransactionId;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;
    global $vno;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Cancelလုပ်ထားပြီးသားဟာကို ထပ်ပြီး Cancelမလုပ်စေရန် စစ်မယ်
        $check = GetString("SELECT VNO FROM tblcancelbet WHERE TransferCode = ? AND TransactionID = ? FOR UPDATE", 
                          [$TransferCode, $TransactionId]);
        
        // cancel မလုပ်ရသေးတဲ့ အခြေအနေ
        if ($check != $vno) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'no' FOR UPDATE";
            $stmt_chk = $con->prepare($sql_chk);
            $stmt_chk->bind_param("s", $TransferCode);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if ($res_chk->num_rows > 0) {
                // Insert into cancelbet table
                $sql = "INSERT INTO tblcancelbet (CompanyKey, Username, TransferCode, ProductType, GameType, 
                       IsCancelAll, TransactionID, GpID, ExtraInfo, VNO) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("sssiississ", $CompanyKey, $Username, $TransferCode, $ProductType, 
                                 $GameType, $IsCancelAll, $TransactionId, $Gpid, $jsonExtraInfo, $vno);

                if ($stmt->execute()) {
                    // Update deduct cancel status
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'yes' 
                                       WHERE TransferCode = ? AND TransactionID = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("ss", $TransferCode, $TransactionId);
                    $stmt_update->execute();

                    // ပထမဆုံး settle လုပ်ထားပြီးသားလား / မလုပ်ထားရသေးလား ထပ်စစ်မယ်
                    $sql_settle = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                    $stmt_settle = $con->prepare($sql_settle);
                    $stmt_settle->bind_param("s", $TransferCode);
                    $stmt_settle->execute();
                    $res_settle = $stmt_settle->get_result();
                    
                    // get player current balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
                    // settle လုပ်ထားပြီးသား အခြေအနေဆိုရင်
                    if ($res_settle->num_rows > 0) {
                        $data_settle = $res_settle->fetch_assoc();
                        
                        // 1-settlebet ရဲ့ cancelstatus ကို yes ပေးမယ်
                        $sql_updateSettle = "UPDATE tblsettlebet SET CancelStatus = 'yes' WHERE TransferCode = ?";
                        $stmt_settle = $con->prepare($sql_updateSettle);
                        $stmt_settle->bind_param("s", $TransferCode);
                        $stmt_settle->execute();

                        // 2-လျော်ပြီးသား settle ဖြစ်တဲ့အတွက် balance in ကို ဖျတ်မယ် 
                        $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                        $stmt_del = $con->prepare($sql_delBalancein);
                        $stmt_del->bind_param("s", $TransferCode);
                        $stmt_del->execute();

                        // 3- Winloss တန်ဖိုးကို ရှာ
                        $winLoss = $data_settle['WinLoss'];
                        // လောင်းထားတဲ့ တန်ဖိုးကို ရှာ
                        $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                               [$TransferCode, $TransactionId]);
                        // ရလာတဲ့ amount ကို player ထဲကို ပြန်ထည့်ပေါင်းပေး
                        $originalValue = ($current_balance - $winLoss) + $deduct_balance;
                        
                        // 4. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $originalValue, $Username, $CompanyKey);
                        $stmt_player->execute();
                        
                        // Commit transaction
                        mysqli_commit($con);
                        
                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $originalValue,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    } 
                    // settle မလုပ်ထားရသေးတဲ့ အခြေအနေ
                    else {
                        // Case 2: Not settled yet
                        // deduct ကနေ cancel တိုက်ရိုက်လုပ်တဲ့အတွက် balanceout ကို ဖျက်တာ
                        $sql_delBalanceout = "DELETE FROM tblbalanceout WHERE TransferCode = ?";
                        $stmt_del = $con->prepare($sql_delBalanceout);
                        $stmt_del->bind_param("s", $TransferCode);
                        $stmt_del->execute();

                        // လောင်းထားပီးသား amount ကို player ထဲကို ပြန်ထည့်ပေါင်းပေး
                        $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                               [$TransferCode, $TransactionId]);
                        $final_balancededuct = $current_balance + $deduct_balance;
                        
                        // 3. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $final_balancededuct, $Username, $CompanyKey);
                        $stmt_player->execute();
                        
                        // Commit transaction
                        mysqli_commit($con);
                        
                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $final_balancededuct,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    }
                }
            } 
            // deduct ထဲမှာ မရှိတဲ့ အခြေအနေ
            else {
                // Rollback if bet doesn't exist
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 6,
                    "ErrorMessage" => "Bet not exists"
                ]);
            }
        } 
        // cancel လုပ်ထားပြီးသား အခြေအနေဆိုရင် already cancel ပြမယ်
        else {
            // Rollback if already canceled
            mysqli_rollback($con);
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
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
            error_log("Database error in cancel fun_for_sport(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in cancel fun_for_sport(): " . $e->getMessage());
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
    global $IsCancelAll;
    global $TransactionId;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;
    global $vno;

    // Set transaction isolation level and begin transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Cancelလုပ်ထားပြီးသားဟာကို ထပ်ပြီး Cancelမလုပ်စေရန် စစ်မယ်
        $check = GetString("SELECT VNO FROM tblcancelbet WHERE TransferCode = '{$TransferCode}' 
                            AND TransactionID = '{$TransactionId}'");
        // cancel မလုပ်ရသေးတဲ့ အခြေအနေ
        if ($check != $vno) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = '{$TransferCode}' AND CancelStatus = 'no'";
            $res_chk = mysqli_query($con, $sql_chk);
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if (mysqli_num_rows($res_chk) > 0) {
                // Insert into cancelbet table
                $sql = "INSERT INTO tblcancelbet (CompanyKey, Username, TransferCode, ProductType, GameType, 
                        IsCancelAll, TransactionID, GpID, ExtraInfo, VNO) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $con->prepare($sql);
                $stmt->bind_param("sssiississ", $CompanyKey, $Username, $TransferCode, $ProductType, $GameType, 
                                $IsCancelAll, $TransactionId, $Gpid, $jsonExtraInfo, $vno);
                
                if ($stmt->execute()) {
                    // Deduct Statusကို Update ရိုက်
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'yes' WHERE TransferCode = ? AND 
                    TransactionID = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("ss", $TransferCode, $TransactionId);
                    $stmt_update->execute();

                    // get player current balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);

                    // ပထမဆုံး settle လုပ်ထားပြီးသားလား / မလုပ်ထားရသေးလား ထပ်စစ်မယ်
                    $sql_settle = "SELECT * FROM tblsettlebet WHERE TransferCode = '{$TransferCode}'";
                    $res_settle = mysqli_query($con, $sql_settle);
                    // settle လုပ်ထားပြီးသား အခြေအနေဆိုရင်
                    if (mysqli_num_rows($res_settle) > 0) {
                        $data_settle = $res_settle->fetch_assoc();

                        // 1-settlebet ရဲ့ cancelstatus ကို yes ပေးမယ်
                        $sql_updateSettle = "UPDATE tblsettlebet SET CancelStatus = 'yes' WHERE TransferCode = ?";
                        $stmt_settle = $con->prepare($sql_updateSettle);
                        $stmt_settle->bind_param("s", $TransferCode);
                        $stmt_settle->execute();

                        // 2-လျော်ပြီးသား settle ဖြစ်တဲ့အတွက် balance in ကို ဖျတ်မယ် 
                        $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                        $stmt_del = $con->prepare($sql_delBalancein);
                        $stmt_del->bind_param("s", $TransferCode);
                        $stmt_del->execute();
                        
                        // 3- Winloss တန်ဖိုးကို ရှာ
                        $winLoss = $data_settle['WinLoss'];
                        // လောင်းထားတဲ့ တန်ဖိုးကို ရှာ
                        $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                               [$TransferCode, $TransactionId]);
                        // ရလာတဲ့ amount ကို player ထဲကို ပြန်ထည့်ပေါင်းပေး
                        $originalValue = ($current_balance - $winLoss) + $deduct_balance;
                        
                        // 4. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $originalValue, $Username, $CompanyKey);
                        $stmt_player->execute();

                        mysqli_commit($con);

                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $originalValue,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    } 
                    // settle မလုပ်ထားရသေးတဲ့ အခြေအနေ
                    else {
                        // deduct ကနေ cancel တိုက်ရိုက်လုပ်တဲ့အတွက် balanceout ကို ဖျက်တာ
                        $sql_delBalanceout = "DELETE FROM tblbalanceout WHERE TransferCode = ?";
                        $stmt_del = $con->prepare($sql_delBalanceout);
                        $stmt_del->bind_param("s", $TransferCode);
                        $stmt_del->execute();

                        // လောင်းထားပီးသား amount ကို player ထဲကို ပြန်ထည့်ပေါင်းပေး
                        $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                               [$TransferCode, $TransactionId]);
                        $final_balancededuct = $player_balance + $deduct_balance;

                        // 3. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $final_balancededuct, $Username, $CompanyKey);
                        $stmt_player->execute();

                        mysqli_commit($con);

                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $final_balancededuct,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    }
                }
            } 
            // deduct ထဲမှာ မရှိတဲ့ အခြေအနေ
            else {
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 6,
                    "ErrorMessage" => "Bet not exists"
                ]);
            }
        } 
        // cancel လုပ်ထားပြီးသား အခြေအနေဆိုရင် already cancel ပြမယ်
        else {
            mysqli_rollback($con);
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($con);
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in cancel fun_for_casino(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error: " . $e->getMessage()
            ]);
        }
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("System error in cancel fun_for_casino(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Transaction failed: " . $e->getMessage()
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
    global $IsCancelAll;
    global $TransactionId;
    global $Gpid;
    global $extraInfo;
    global $jsonExtraInfo;

    global $player_balance;
    global $playerID;
    global $dt;
    global $vno;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // Cancelလုပ်ထားပြီးသားဟာကို ထပ်ပြီး Cancelမလုပ်စေရန် စစ်မယ်
        $check = GetString("SELECT VNO FROM tblcancelbet WHERE TransferCode = ? AND TransactionID = ? FOR UPDATE", 
                          [$TransferCode, $TransactionId]);
        
        // cancel မလုပ်ရသေးတဲ့ အခြေအနေ
        if ($check != $vno) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'no' FOR UPDATE";
            $stmt_chk = $con->prepare($sql_chk);
            $stmt_chk->bind_param("s", $TransferCode);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if ($res_chk->num_rows > 0) {
                // Insert into cancelbet table
                $sql = "INSERT INTO tblcancelbet (CompanyKey, Username, TransferCode, ProductType, GameType, 
                       IsCancelAll, TransactionID, GpID, ExtraInfo, VNO) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("sssiississ", $CompanyKey, $Username, $TransferCode, $ProductType, 
                                 $GameType, $IsCancelAll, $TransactionId, $Gpid, $jsonExtraInfo, $vno);

                if ($stmt->execute()) {
                    // Update deduct cancel status
                    $sql_updateDeduct = "UPDATE tbldeduct SET CancelStatus = 'yes' 
                                       WHERE TransferCode = ? AND TransactionID = ?";
                    $stmt_update = $con->prepare($sql_updateDeduct);
                    $stmt_update->bind_param("ss", $TransferCode, $TransactionId);
                    $stmt_update->execute();

                    // ပထမဆုံး settle လုပ်ထားပြီးသားလား / မလုပ်ထားရသေးလား ထပ်စစ်မယ်
                    $sql_settle = "SELECT * FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE";
                    $stmt_settle = $con->prepare($sql_settle);
                    $stmt_settle->bind_param("s", $TransferCode);
                    $stmt_settle->execute();
                    $res_settle = $stmt_settle->get_result();
                    
                    // Get player balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                            [$Username, $CompanyKey]);
                    // settle လုပ်ထားပြီးသား အခြေအနေဆိုရင်
                    if ($res_settle->num_rows > 0) {
                        $data_settle = $res_settle->fetch_assoc();
                        
                        // 1-settlebet ရဲ့ cancelstatus ကို yes ပေးမယ်
                        $sql_updateSettle = "UPDATE tblsettlebet SET CancelStatus = 'yes' WHERE TransferCode = ?";
                        $stmt_settle = $con->prepare($sql_updateSettle);
                        $stmt_settle->bind_param("s", $TransferCode);
                        $stmt_settle->execute();

                        // 2-လျော်ပြီးသား settle ဖြစ်တဲ့အတွက် balance in ကို ဖျတ်မယ် 
                        $sql_delBalancein = "DELETE FROM tblbalancein WHERE TransferCode = ?";
                        $stmt_del = $con->prepare($sql_delBalancein);
                        $stmt_del->bind_param("s", $TransferCode);
                        $stmt_del->execute();

                        // 3- Winloss တန်ဖိုးကို ရှာ
                        $winLoss = $data_settle['WinLoss'];
                        // လောင်းထားတဲ့ တန်ဖိုးကို ရှာ  
                        if ($IsCancelAll) {
                            $deduct_balance = GetInt("SELECT SUM(Amount) FROM tbldeduct WHERE TransferCode = ?", 
                                                   [$TransferCode]);
                        } else {
                            $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                                   [$TransferCode, $TransactionId]);
                        }
                        $originalValue = ($current_balance - $winLoss) + $deduct_balance;
                        
                        // 4. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $originalValue, $Username, $CompanyKey);
                        $stmt_player->execute();
                        
                        // Commit transaction
                        mysqli_commit($con);
                        
                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $originalValue,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    } 
                    // settle မလုပ်ထားရသေးတဲ့ အခြေအနေ
                    else {
                        // Case 2: Not settled yet
                        // deduct ကနေ cancel တိုက်ရိုက်လုပ်တဲ့အတွက် balanceout ကို ဖျက်တာ
                        $sql_delBalanceout = "DELETE FROM tblbalanceout WHERE TransferCode = ? AND TransactionID = ?";
                        $stmt_del = $con->prepare($sql_delBalanceout);
                        $stmt_del->bind_param("ss", $TransferCode, $TransactionId);
                        $stmt_del->execute();

                        // လောင်းထားပီးသား amount ကို player ထဲကို ပြန်ထည့်ပေါင်းပေး
                        $deduct_balance = GetInt("SELECT Amount FROM tbldeduct WHERE TransferCode = ? AND TransactionID = ?", 
                                               [$TransferCode, $TransactionId]);
                        $final_balancededuct = $current_balance + $deduct_balance;
                        
                        // 3. Update player balance
                        $sql_updatePlayer = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                        $stmt_player = $con->prepare($sql_updatePlayer);
                        $stmt_player->bind_param("dss", $final_balancededuct, $Username, $CompanyKey);
                        $stmt_player->execute();
                        
                        // Commit transaction
                        mysqli_commit($con);
                        
                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $final_balancededuct,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    }
                }
            } 
            // deduct ထဲမှာ မရှိတဲ့ အခြေအနေ
            else {
                // Rollback if bet doesn't exist
                mysqli_rollback($con);
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 6,
                    "ErrorMessage" => "Bet not exists"
                ]);
            }
        } 
        // cancel လုပ်ထားပြီးသား အခြေအနေဆိုရင် already cancel ပြမယ်
        else {
            // Rollback if already canceled
            mysqli_rollback($con);
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bet Already Canceled",
                "Balance" => $player_balance
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
            error_log("Database error in cancel fun_for_3rdwanmei(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in cancel fun_for_3rdwanmei(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}


?>