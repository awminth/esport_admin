<?php
include("config.php");

$CompanyKey = $data["CompanyKey"];
$Username = $data["Username"];
$Amount = $data["Amount"];
$BonusTime = $data["BonusTime"];
$IsGameProviderPromotion = $data["IsGameProviderPromotion"];
$ProductType = $data["ProductType"];
$GameType = $data["GameType"];
$NewGameType = $data["NewGameType"] ?? null;
$TransferCode = $data["TransferCode"];
$TransactionId = $data["TransactionId"];
$GameId = $data["GameId"];
$Gpid = $data["Gpid"];
$BonusProvider = $data["BonusProvider"];

//SeamlessGameExtraInfo
$seamlessgameextrainfo = $data["SeamlessGameExtraInfo"] ?? null; // JSON string

$seamlessgameextrainfoToInsert = [
    $FeatureBuyStatus = $seamlessgameextrainfo["FeatureBuyStatus"] ?? null,
    $EndRoundStatus = $seamlessgameextrainfo["EndRoundStatus"] ?? null
];

$jsonSeamlessgameextrainfo = json_encode($seamlessgameextrainfoToInsert, JSON_UNESCAPED_UNICODE);

// get player balance
$current_balance = GetInt("SELECT Balance FROM tblplayer WHERE UserName = ? AND CompanyKey = ? FOR UPDATE", 
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
    fun_for_sport();
}
else if($ProductType == 9){
    fun_for_sport();
}
else{
    echo json_encode(
        array(
            "ErrorCode"=>7,
            "ErrorMessage"=>"Internal Error producttype"
        ));
}

function fun_for_sport() {
    global $con, $CompanyKey, $Username, $Amount, $BonusTime, $IsGameProviderPromotion, 
           $ProductType, $GameType, $NewGameType, $TransferCode, $TransactionId, $GameId, 
           $Gpid, $BonusProvider, $seamlessgameextrainfo, $jsonSeamlessgameextrainfo, 
           $current_balance, $playerID, $dt;

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        
        $safeTransferCode = mysqli_real_escape_string($con, $TransferCode);
        // bonus လုပ်ပီသားလား / မလုပ်ရသေးလား စစ်
        $check = GetString("SELECT TransferCode FROM tblbonus WHERE TransferCode = '$safeTransferCode'");
        
        // မလုပ်ရသေးတဲ့ အခြေအနေ
        if ($check != $TransferCode) {
            // Insert into tblbonus
            $sql = "INSERT INTO tblbonus (CompanyKey, Username, Amount, BonusTime, IsGameProviderPromotion,
                  ProductType, GameType, NewGameType, TransferCode, TransactionId, GameId, Gpid, BonusProvider, 
                  SeamlessGameExtraInfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssdssiiissiiss", $CompanyKey, $Username, $Amount, $BonusTime, $IsGameProviderPromotion,
                            $ProductType, $GameType, $NewGameType, $TransferCode, $TransactionId, $GameId, $Gpid, 
                            $BonusProvider, $jsonSeamlessgameextrainfo);

            if ($stmt->execute()) {
                // Player Balanceကို Bonus Amount ထပ်ပေါင်း
                $balance_query = mysqli_query($con, 
                    "SELECT Balance FROM tblplayer 
                     WHERE UserName = '$Username' AND CompanyKey = '$CompanyKey'
                     LIMIT 1 FOR UPDATE");
                
                if ($balance_query && mysqli_num_rows($balance_query) > 0) {
                    $balance_data = mysqli_fetch_assoc($balance_query);
                    $current_balance = $balance_data['Balance'];
                    $finalValue = $current_balance + $Amount;
                    
                    // Update player balance
                    $update_sql = "UPDATE tblplayer SET Balance = $finalValue 
                                 WHERE UserName = '$Username' AND CompanyKey = '$CompanyKey'";
                    if (mysqli_query($con, $update_sql)) {
                        // Insert into balancein
                        $sql_balancein = "INSERT INTO tblbalancein (PlayerID, Amount, TransferCode, 
                                            PrepaidStatus, WinLossStatus, DateTime) 
                                            VALUES (?, ?, ?, 'bonus', 'bonus', ?)";
                        $stmt_balancein = $con->prepare($sql_balancein);
                        $stmt_balancein->bind_param("idss", $playerID, $Amount, $TransferCode, $dt);
                        $stmt_balancein->execute();

                        // Commit transaction
                        mysqli_commit($con);
                        
                        echo json_encode([
                            "AccountName" => $Username,
                            "Balance" => $finalValue,
                            "ErrorCode" => 0,
                            "ErrorMessage" => "No Error"
                        ]);
                    } else {
                        throw new Exception("Failed to update player balance");
                    }
                } else {
                    throw new Exception("Player not found");
                }
            } else {
                throw new Exception("Failed to insert bonus record");
            }
        } 
        // လုပ်ထားပီးသား အခြေအနေ
        else {
            // Bonus already exists - check if it's a game provider promotion
            $IsGameProvider = GetString("SELECT IsGameProviderPromotion FROM tblbonus WHERE TransferCode = '$safeTransferCode'");
            
            // Rollback transaction
            mysqli_rollback($con);
            
            if ($IsGameProvider == "true") {
                echo json_encode([
                    "Balance" => $current_balance,
                    "ErrorCode" => 5003,
                    "ErrorMessage" => "Game Provider Bonus Already Exists"
                ]);
            } else {
                echo json_encode([
                    "Balance" => $current_balance,
                    "ErrorCode" => 5003,
                    "ErrorMessage" => "Bonus With Same RefNo Already Exists"
                ]);
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Rollback on any error
        mysqli_rollback($con);
        
        if ($e->getCode() == 1062) {
            echo json_encode([
                "ErrorCode" => 2002,
                "ErrorMessage" => "Bonus Already Processed",
                "Balance" => $current_balance
            ]);
        } else {
            error_log("Database error in bonus fun_for_sport (bonus): " . $e->getMessage());
            echo json_encode([
                "ErrorCode" => 7,
                "ErrorMessage" => "Internal Database Error"
            ]);
        }
    } catch (Exception $e) {
        // Rollback on any other error
        mysqli_rollback($con);
        error_log("System error in bonus fun_for_sport (bonus): " . $e->getMessage());
        echo json_encode([
            "ErrorCode" => 7,
            "ErrorMessage" => "Internal System Error"
        ]);
    }
}


?>