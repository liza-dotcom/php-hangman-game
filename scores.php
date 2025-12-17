<?php
#include database library and connect to it 
require_once 'includes/library.php';
$pdo= connectdb();

#specify 3 levels 
$difficulties= ['easy', 'medium','hard'];

#function to show 10 latest score for each level 
function getScores($pdo, $difficulty){

    #prepare statement 
    $stmt = $pdo->prepare("SELECT username,result,word,score,play_date FROM cois3430_assn1_scores WHERE difficulty=? ORDER BY play_date DESC LIMIT 10");
    $stmt->execute([$difficulty]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>
<body>
    <div class="score-table">
        <?php

    foreach ($difficulties as $level) {
        $scores = getScores($pdo, $level); #call the function for suitable level
        echo "<h2>" . ucfirst($level) . " Scores</h2>";
        echo "<table>";
        echo "<tr><th>Username</th><th>Result</th><th>Word</th><th>Score</th><th>Date Played</th></tr>"; #table headings for each level
        foreach ($scores as $row) #show scores 
            {
                echo "<tr>  
                        <td>{$row['username']}</td>
                        <td>{$row['result']}</td>
                        <td>{$row['word']}</td>
                        <td>{$row['score']}</td>
                        <td>{$row['play_date']}</td>
                    </tr>";
            }
        echo "</table>"; #display table 
    }
?>
<!-- Start New Game Button -->
    <div class="new-game-btn">
        <form action="difficulty.php" method="get">
            <button type="submit">Start New Game</button>
        </form>
    </div>

    </div>
    
    
    
</body>
</html>