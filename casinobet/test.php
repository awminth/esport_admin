<?php
include('../config.php');

function formatCardData($input) {
    // Extract data
    preg_match('/\[TableID:(\d+)\]/', $input, $matches);
    $tableId = $matches[1] ?? '';
    
    preg_match('/Banker:([^ ]+)/', $input, $matches);
    $bankerCards = $matches[1] ?? '';
    
    preg_match('/Player:([^ ]+)/', $input, $matches);
    $playerCards = $matches[1] ?? '';
    
    // Format cards with colors and styling
    function formatCards($cardString) {
        $cards = preg_split('/([♠♣♥♦][A-Z0-9]+)/u', $cardString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $formatted = '';
        
        foreach ($cards as $card) {
            $suit = mb_substr($card, 0, 1, 'UTF-8');
            $value = mb_substr($card, 1, null, 'UTF-8');
            
            $color = '';
            $suitClass = '';
            switch ($suit) {
                case '♥':
                    $color = '#ff0000';
                    $suitClass = 'heart';
                    break;
                case '♦':
                    $color = '#ff0000';
                    $suitClass = 'diamond';
                    break;
                case '♣':
                    $color = '#000000';
                    $suitClass = 'club';
                    break;
                case '♠':
                    $color = '#000000';
                    $suitClass = 'spade';
                    break;
            }
            
            $formatted .= "<span class='card $suitClass' style='color: $color;'>$suit$value</span>";
        }
        
        return $formatted;
    }
    
    // HTML output with CSS
    $output = "
    <style>
        .card-table {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Arial', sans-serif;
            max-width: 300px;
            background: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-table h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .card-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .card-label {
            font-weight: bold;
            min-width: 60px;
            color: #555;
        }
        .card {
            font-weight: bold;
            font-size: 18px;
            margin-right: 5px;
            display: inline-block;
            width: 24px;
            text-align: center;
        }
        .heart, .diamond {
            color: #e74c3c;
        }
        .club, .spade {
            color: #2c3e50;
        }
    </style>
    
    <div class='card-table'>
        <h3>Table #$tableId</h3>
        <div class='card-row'>
            <div class='card-label'>Banker:</div>
            <div class='cards'>" . formatCards($bankerCards) . "</div>
        </div>
        <div class='card-row'>
            <div class='card-label'>Player:</div>
            <div class='cards'>" . formatCards($playerCards) . "</div>
        </div>
    </div>";
    
    return $output;
}

// Database query and result display
$sql = "SELECT GameResult FROM tblsettlebet WHERE ProductType = 9 ORDER BY AID DESC";
$result = $con->query($sql);

// Debugging and error handling
if (!$result) {
    echo "<div class='alert alert-danger'>Query failed: " . $con->error . "</div>";
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $databaseString = $row["GameResult"];
            echo formatCardData($databaseString);
        }
    } else {
        echo "<div class='alert alert-info'>No results found</div>";
    }
}

// Close connection if needed
$con->close();
?>