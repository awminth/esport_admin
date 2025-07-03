<?php
include("config.php");

$TransferCode = $data["TransferCode"];
$WinLoss = $data["WinLoss"];
$ResultType = $data["ResultType"];
$ResultTime = $data["ResultTime"];
$CommissionStake = $data["CommissionStake"];
$GameResult = $data["GameResult"];
$CompanyKey = $data["CompanyKey"];
$Username = $data["Username"];
$ProductType = $data["ProductType"];
$GameType = $data["GameType"];
$Gpid = $data["Gpid"];
$IsCashOut = $data["IsCashOut"];
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
$seamlessgameextrainfo = $data["SeamlessGameExtraInfo"] ?? null; // JSON string

$seamlessgameextrainfoToInsert = [
    $FeatureBuyStatus = $seamlessgameextrainfo["FeatureBuyStatus"] ?? null,
    $EndRoundStatus = $seamlessgameextrainfo["EndRoundStatus"] ?? null
];

$jsonSeamlessgameextrainfo = json_encode($seamlessgameextrainfoToInsert, JSON_UNESCAPED_UNICODE);

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
    error_log("Product Type error in settle");
    echo json_encode(
        array(
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error producttype"
        ));
}

// create function for sport and virtual sport
function fun_for_sport() {
    global $con;

    global $TransferCode;
    global $WinLoss;
    global $ResultType;
    global $ResultTime;
    global $CommissionStake;
    global $GameResult;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $IsCashOut;
    global $extraInfo;
    global $jsonExtraInfo;
    global $seamlessgameextrainfo;
    global $jsonSeamlessgameextrainfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // settle ထဲမှာ အရင်ဆုံး ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // settle မရှိသေးတဲ့ အခြေအနေ
        if ($check != $TransferCode) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'no' FOR UPDATE";
            $stmt_chk = $con->prepare($sql_chk);
            $stmt_chk->bind_param("s", $TransferCode);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if ($res_chk->num_rows > 0) {
                $data = $res_chk->fetch_assoc();
                
                // cancel လုပ်ပြီးသားလား / မလုပ်ရသေးလား ထပ်စစ်
                // cancel လုပ်ထားပြီးသား အခြေအနေ ဆိုရင် already cancel ပြမယ်
                if ($data["CancelStatus"] == 'yes') {
                    mysqli_rollback($con);
                    echo json_encode([
                        "Balance" => $player_balance,
                        "ErrorCode" => 2002,
                        "ErrorMessage" => "Bet Already Canceled"
                    ]);
                    return;
                }
                // cancel မလုပ်ထားရသေးတဲ့ အခြေအနေ
                // Insert into settlebet table
                $sql = "INSERT INTO tblsettlebet (TransferCode, WinLoss, ResultType, ResultTime, CommissionStake,
                      GameResult, CompanyKey, Username, ProductType, GameType, GpID, IsCashOut, ExtraInfo, 
                      SeamlessGameExtraInfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("sdisdsssiiisss", $TransferCode, $WinLoss, $ResultType, $ResultTime, 
                    $CommissionStake, $GameResult, $CompanyKey, $Username, $ProductType, $GameType, $Gpid, 
                    $IsCashOut, $jsonExtraInfo, $jsonSeamlessgameextrainfo);
                
                if ($stmt->execute()) {
                    // Get player balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                             [$Username, $CompanyKey]);
                    
                    // Handle different result types
                    switch ($ResultType) {
                        // result type က 0 ဆိုရင် အနိုင် ဖြစ်မယ်
                        case 0: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'win', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                        // result type က 1 ဆိုရင် အရှုံး(အကုန်လုံး/တစ်ဝက်/တစ်ခုခု) ဖြစ်မယ်   
                        case 1: 
                            // lose (tblbalanceoutရဲ့ Winloss Statusကို Lossဆိုပြီးပြောင်းပေး)
                            $sql = "UPDATE tblbalanceout SET WinLossStatus = 'loss' 
                                   WHERE TransferCode = ? AND PlayerID = ?";
                            $stmt_update = $con->prepare($sql);
                            $stmt_update->bind_param("si", $TransferCode, $playerID);
                            $stmt_update->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                            
                        // result type က 2 ဆိုရင် သရေ ဖြစ်မယ်
                        case 2: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'draw', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                    }
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
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
        // settle ထဲမှာ ရှိပြီးသားအခြေအနေ
        else {
            // အကယ်၍ ရှိပြီးသား အခြေအနေမှာ ဒီဟာကို cancel လုပ်ထား/မထား ထပ်စစ်
            $cancelStatus = GetString("SELECT CancelStatus FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
            
            // Rollback with appropriate message
            mysqli_rollback($con);
            // cancel မလုပ်ရသေးရင် already settle ပြမယ်
            if ($cancelStatus == "no") {
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2001,
                    "ErrorMessage" => "Bet Already Settled"
                ]);
            }
            // cancel လုပ်ထားရင် already cancel ပြမယ် 
            else {                
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2002,
                    "ErrorMessage" => "Bet Already Canceled"
                ]);
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2001,
                "ErrorMessage" => "Bet Already Settled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in settle fun_for_sport(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in settle fun_for_sport(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// create function for casino and sbo nrg
function fun_for_casino() {
    global $con;

    global $TransferCode;
    global $WinLoss;
    global $ResultType;
    global $ResultTime;
    global $CommissionStake;
    global $GameResult;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $IsCashOut;
    global $extraInfo;
    global $jsonExtraInfo;
    global $seamlessgameextrainfo;
    global $jsonSeamlessgameextrainfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // settle ထဲမှာ အရင်ဆုံး ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // settle မရှိသေးတဲ့ အခြေအနေ
        if ($check != $TransferCode) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'no' FOR UPDATE";
            $stmt_chk = $con->prepare($sql_chk);
            $stmt_chk->bind_param("s", $TransferCode);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if ($res_chk->num_rows > 0) {
                $data = $res_chk->fetch_assoc();
                
                // cancel လုပ်ပြီးသားလား / မလုပ်ရသေးလား ထပ်စစ်
                // cancel လုပ်ထားပြီးသား အခြေအနေ ဆိုရင် already cancel ပြမယ်
                if ($data["CancelStatus"] == 'yes') {
                    mysqli_rollback($con);
                    echo json_encode([
                        "Balance" => $player_balance,
                        "ErrorCode" => 2002,
                        "ErrorMessage" => "Bet Already Canceled"
                    ]);
                    return;
                }
                // cancel မလုပ်ထားရသေးတဲ့ အခြေအနေ
                // Insert into settlebet table
                $sql = "INSERT INTO tblsettlebet (TransferCode, WinLoss, ResultType, ResultTime, CommissionStake,
                      GameResult, CompanyKey, Username, ProductType, GameType, GpID, IsCashOut, ExtraInfo, 
                      SeamlessGameExtraInfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("sdisdsssiiisss", $TransferCode, $WinLoss, $ResultType, $ResultTime, 
                    $CommissionStake, $GameResult, $CompanyKey, $Username, $ProductType, $GameType, $Gpid, 
                    $IsCashOut, $jsonExtraInfo, $jsonSeamlessgameextrainfo);
                
                if ($stmt->execute()) {
                    // Get player balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                             [$Username, $CompanyKey]);
                    
                    // Handle different result types
                    switch ($ResultType) {
                        // result type က 0 ဆိုရင် အနိုင် ဖြစ်မယ်
                        case 0: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'win', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                        // result type က 1 ဆိုရင် အရှုံး(အကုန်လုံး/တစ်ဝက်/တစ်ခုခု) ဖြစ်မယ်   
                        case 1: 
                            // lose (tblbalanceoutရဲ့ Winloss Statusကို Lossဆိုပြီးပြောင်းပေး)
                            $sql = "UPDATE tblbalanceout SET WinLossStatus = 'loss' 
                                   WHERE TransferCode = ? AND PlayerID = ?";
                            $stmt_update = $con->prepare($sql);
                            $stmt_update->bind_param("si", $TransferCode, $playerID);
                            $stmt_update->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                            
                        // result type က 2 ဆိုရင် သရေ ဖြစ်မယ်
                        case 2: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'draw', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                    }
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
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
        // settle ထဲမှာ ရှိပြီးသားအခြေအနေ
        else {
            // အကယ်၍ ရှိပြီးသား အခြေအနေမှာ ဒီဟာကို cancel လုပ်ထား/မထား ထပ်စစ်
            $cancelStatus = GetString("SELECT CancelStatus FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
            
            // Rollback with appropriate message
            mysqli_rollback($con);
            // cancel မလုပ်ရသေးရင် already settle ပြမယ်
            if ($cancelStatus == "no") {
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2001,
                    "ErrorMessage" => "Bet Already Settled"
                ]);
            }
            // cancel လုပ်ထားရင် already cancel ပြမယ် 
            else {                
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2002,
                    "ErrorMessage" => "Bet Already Canceled"
                ]);
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2001,
                "ErrorMessage" => "Bet Already Settled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in casino fun_for_sport(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in casino fun_for_sport(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

// create function for 3rd wan mei
function fun_for_3rdwanmei() {
    global $con;

    global $TransferCode;
    global $WinLoss;
    global $ResultType;
    global $ResultTime;
    global $CommissionStake;
    global $GameResult;
    global $CompanyKey;
    global $Username;
    global $ProductType;
    global $GameType;
    global $Gpid;
    global $IsCashOut;
    global $extraInfo;
    global $jsonExtraInfo;
    global $seamlessgameextrainfo;
    global $jsonSeamlessgameextrainfo;

    global $player_balance;
    global $playerID;
    global $dt;

    // Set transaction isolation level and start transaction
    mysqli_query($con, "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
    mysqli_begin_transaction($con);

    try {
        // settle ထဲမှာ အရင်ဆုံး ရှိ/မရှိ စစ်မယ်
        $check = GetString("SELECT TransferCode FROM tblsettlebet WHERE TransferCode = ? FOR UPDATE", [$TransferCode]);
        
        // settle မရှိသေးတဲ့ အခြေအနေ
        if ($check != $TransferCode) {
            // ပထမဆုံး တကယ် လောင်းထား/မထား ကို deduct မှာ အရင်စစ် 
            $sql_chk = "SELECT * FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'no' FOR UPDATE";
            $stmt_chk = $con->prepare($sql_chk);
            $stmt_chk->bind_param("s", $TransferCode);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            // deduct ထဲမှာ တကယ်လောင်းထားတဲ့ အခြေအနေ
            if ($res_chk->num_rows > 0) {
                $data = $res_chk->fetch_assoc();
                
                // cancel လုပ်ပြီးသားလား / မလုပ်ရသေးလား ထပ်စစ်
                // cancel လုပ်ထားပြီးသား အခြေအနေ ဆိုရင် already cancel ပြမယ်
                if ($data["CancelStatus"] == 'yes') {
                    mysqli_rollback($con);
                    echo json_encode([
                        "Balance" => $player_balance,
                        "ErrorCode" => 2002,
                        "ErrorMessage" => "Bet Already Canceled"
                    ]);
                    return;
                }
                // cancel မလုပ်ထားရသေးတဲ့ အခြေအနေ
                // Insert into settlebet table
                $sql = "INSERT INTO tblsettlebet (TransferCode, WinLoss, ResultType, ResultTime, CommissionStake,
                      GameResult, CompanyKey, Username, ProductType, GameType, GpID, IsCashOut, ExtraInfo, 
                      SeamlessGameExtraInfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("sdisdsssiiisss", $TransferCode, $WinLoss, $ResultType, $ResultTime, 
                    $CommissionStake, $GameResult, $CompanyKey, $Username, $ProductType, $GameType, $Gpid, 
                    $IsCashOut, $jsonExtraInfo, $jsonSeamlessgameextrainfo);
                
                if ($stmt->execute()) {
                    // Get player balance
                    $current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
                                             [$Username, $CompanyKey]);
                    
                    // Handle different result types
                    switch ($ResultType) {
                        // result type က 0 ဆိုရင် အနိုင် ဖြစ်မယ်
                        case 0: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'win', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                        // result type က 1 ဆိုရင် အရှုံး(အကုန်လုံး/တစ်ဝက်/တစ်ခုခု) ဖြစ်မယ်   
                        case 1: 
                            // lose (tblbalanceoutရဲ့ Winloss Statusကို Lossဆိုပြီးပြောင်းပေး)
                            $sql = "UPDATE tblbalanceout SET WinLossStatus = 'loss' 
                                   WHERE TransferCode = ? AND PlayerID = ?";
                            $stmt_update = $con->prepare($sql);
                            $stmt_update->bind_param("si", $TransferCode, $playerID);
                            $stmt_update->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                            
                        // result type က 2 ဆိုရင် သရေ ဖြစ်မယ်
                        case 2: 
                            // Insert into balancein
                            $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                             PrepaidStatus, WinLossStatus, DateTime) 
                                             VALUES (?, ?, ?, 'success', 'draw', ?)";
                            $stmt_balancein = $con->prepare($sql_balancein);
                            $stmt_balancein->bind_param("isss", $playerID, $WinLoss, $TransferCode, $dt);
                            $stmt_balancein->execute();
                            
                            // Update player balance
                            $final_balance = $current_balance + $WinLoss;
                            $sql_player = "UPDATE tblplayer SET Balance = ? WHERE UserName = ? AND CompanyKey = ?";
                            $stmt_player = $con->prepare($sql_player);
                            $stmt_player->bind_param("dss", $final_balance, $Username, $CompanyKey);
                            $stmt_player->execute();
                            break;
                    }
                    
                    // Commit transaction
                    mysqli_commit($con);
                    
                    echo json_encode([
                        "AccountName" => $Username,
                        "Balance" => $final_balance,
                        "ErrorCode" => 0,
                        "ErrorMessage" => "No Error"
                    ]);
                }
            } 
            // deduct ထဲမှာ မရှိတဲ့ အခြေအနေ
            else {
                $check_status = GetString("SELECT CancelStatus FROM tbldeduct WHERE TransferCode = ? AND CancelStatus = 'yes' FOR UPDATE", 
                          [$TransferCode]);
                // $check_status = GetString("select CancelStatus from tbldeduct where TransferCode = '{$TransferCode}' and CancelStatus='yes'");
                if($check_status == 'yes'){
                    // Rollback if bet doesn't exist
                    mysqli_rollback($con);
                    echo json_encode(
                        array(
                            "Balance"=>$player_balance,
                            "ErrorCode"=>2002,
                            "ErrorMessage"=>"Bet Already Canceled 3"
                        ));
                }
                else{
                    // Rollback if bet doesn't exist
                    mysqli_rollback($con);
                    echo json_encode(
                        array(
                            "Balance"=>$player_balance,
                            "ErrorCode"=>6,
                            "ErrorMessage"=>"Bet not exists"
                        ));
                }
            }
        } 
        // settle ထဲမှာ ရှိပြီးသားအခြေအနေ
        else {
            // အကယ်၍ ရှိပြီးသား အခြေအနေမှာ ဒီဟာကို cancel လုပ်ထား/မထား ထပ်စစ်
            $cancelStatus = GetString("SELECT CancelStatus FROM tblsettlebet WHERE TransferCode = ?", [$TransferCode]);
            
            // Rollback with appropriate message
            mysqli_rollback($con);
            // cancel မလုပ်ရသေးရင် already settle ပြမယ်
            if ($cancelStatus == "no") {
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2001,
                    "ErrorMessage" => "Bet Already Settled"
                ]);
            }
            // cancel လုပ်ထားရင် already cancel ပြမယ် 
            else {                
                echo json_encode([
                    "Balance" => $player_balance,
                    "ErrorCode" => 2002,
                    "ErrorMessage" => "Bet Already Canceled"
                ]);
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2001,
                "ErrorMessage" => "Bet Already Settled",
                "Balance" => $player_balance
            ]);
        } else {
            error_log("Database error in wan mei fun_for_sport(): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in wan mei fun_for_sport(): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}

?>