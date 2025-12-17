
<?php
#include library file and connect to database
require_once './includes/library.php';
$pdo = connectdb();

session_start();



$difficulty_level= $_POST['difficulty']?? 'easy';
$random_word= $_SESSION['random_word']?? "";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    if(isset($_POST['difficulty'])) {                #get difficulty from session 
        $difficulty_level = $_POST['difficulty'];

        # Fetch word from database based on difficulty
        if($difficulty_level === 'easy'){
            $stmt = $pdo->prepare("SELECT word FROM cois3430_assn1_words WHERE difficulty = 'easy' ORDER BY RAND() LIMIT 1");
        } elseif($difficulty_level === 'medium'){
            $stmt = $pdo->prepare("SELECT word FROM cois3430_assn1_words WHERE difficulty = 'medium' ORDER BY RAND() LIMIT 1");
        } else { 
            $stmt = $pdo->prepare("SELECT word FROM cois3430_assn1_words WHERE difficulty = 'hard'  ORDER BY RAND() LIMIT 1");
        }
        #execute the prepared query
        $stmt->execute();
        $random_word = $stmt->fetchColumn(); #store word in a variable

        #reset the guess array and all other variables before redirecting to the game play page, to make sure a fresh word is added after changeing difficulty.
        $_SESSION['random_word'] = trim($random_word);
        $_SESSION['currState'] = str_split(str_repeat('_', strlen($random_word)));
        $_SESSION['allGuesses'] = [];
        $_SESSION['correctGuess'] = [];
        $_SESSION['remainGuess'] = 6;
        $_SESSION['difficulty_level'] = $difficulty_level;
        $_SESSION['gameOver']= false; 
        $_SESSION['scoreSaved'] = false;

        // Redirect to play page
        header('Location: ./play.php');
        exit();
    }
}

        
 

?>

<!--Html form  for difficulty level-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>
<body>
    <form class= "set-level" method="post" action="difficulty.php">
        <label> 
            <input type="radio" name="difficulty" value="easy" required/> Easy 
        </label>
        <label>
            <input type="radio" name="difficulty" value="medium" required/> Medium 
        </label>
        <label>
            <input type="radio" name="difficulty" value="hard" required/> Hard 
        </label>
        <button type="submit">Set Difficulty</button>

    </form>
</body>
</html>
