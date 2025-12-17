<?php
#include library file and connect to database
require_once './includes/library.php';
$pdo = connectdb();
session_start();

// Initialize scoreSaved if not set
if(!isset($_SESSION['scoreSaved'])) {
    $_SESSION['scoreSaved'] = false;
}

$userName= $_SESSION['username'] ?? '';
$randomWord= trim($_SESSION['random_word'] ?? '');


#see if its a new game to reset the session variables 
if(!isset($_SESSION['currState']) || isset($_GET['new'])){
    $_SESSION['gameOver'] = false;   #to enable guess button and text input in a new game
    $_SESSION['scoreSaved'] = false;
}


#get difficulty level 
if (isset($_POST['difficulty'])) {
    $_SESSION['difficulty_level'] = $_POST['difficulty'];
}

# Read the current difficulty from session
$difficulty_level = $_SESSION['difficulty_level'] ?? 'easy';

if(!$randomWord){
    header("Location: difficulty.php");
    exit;
} 

#replace actual letters with _underscore/blank and display word
if(!isset($_SESSION['currState'])){
    $_SESSION['currState']= str_split(str_repeat('_', strlen($randomWord)));
}
# store remaining guess in session
if(!isset($_SESSION['remainGuess'])){
    $_SESSION['remainGuess'] = 6;  #set maximum number of guesses to 6 initially
}
$remainGuess = $_SESSION['remainGuess'];

# check the guessed letter input from user 
if(!isset($_SESSION['allGuesses'])){   # create an array to store all user input letters(for later on validation)
    $_SESSION['allGuesses']= [];
} 
if(!isset($_SESSION['correctGuess'])){   # create an array to store correctly guessed letters 
    $_SESSION['correctGuess']= [];

} 

$guess= $_POST['guessInp'] ?? '';
$guessType = $_POST['guess'] ?? '';

$message='';  #  message to display after a letter is guessed 

if($_SERVER['REQUEST_METHOD']==='POST'){

    #check if game is already over 

    if (!empty($_SESSION['gameOver']) && $_SESSION['gameOver'] === true) {
        $message = "Game is over. Start a new game!";
    }
    else{

        if($guess!==''){


            $guess= strtoupper($guess);
            if(in_array($guess , $_SESSION['allGuesses'])){
            $message = "You already guessed '$guess'. Try something else!"; #tell user they already tried the letter/word
            }
            # validate the input 
            elseif(!preg_match( "/^[a-zA-Z]+$/", $guess)){ #check for numbers and symbols
                $message= "Guess cannot contain symbols or characters :)";
            }
            elseif($guessType=="guess a letter" && strlen($guess)>1){  #check if letter guess has more than 1 character
                $message= "A letter guess cannot have more than 1 letter !!";
            }
            #once input is validated process to store it in session and move ahead
            else{
                $_SESSION['allGuesses'][]= $guess; # store letter in array for all guessed letters if not already in the array

                #handle the case if user guess whole word
                if($guessType== "guess a word"){

                    #user guessed correct
                    if(strcasecmp($randomWord, $guess)===0){
                        $_SESSION['currState']= str_split(strtoupper($randomWord));
                        $message="Bravo! you guessed the full word !!";
                    }
                    #user guessed wrong word
                    else{
                        $message=" Oops,'$guess' is not the word";
                        $remainGuess = $remainGuess-2; #reduce remaining guess by 2 for wrong word 
                        $_SESSION['remainGuess']= $remainGuess; #update the value of remaining guesses in the session

                    } 
                }
                #move on to check letter guesses
                else{
                    if(stripos($randomWord, $guess) !==false){
                        # check if letter is in word
                        if(!in_array($guess , $_SESSION['correctGuess'])){
                            $_SESSION['correctGuess'][]= $guess;  # store it in array for correct letters (if not alraedy stored)
                        }
                        $message= "Great! '$guess' is a correct guess!"; #tell user it was correct
                        

                        #also add letter to the displayed word (reveal all matching letters)
                        for($i=0; $i<strlen($randomWord); $i++){
                            if(strtoupper($randomWord[$i])=== $guess){
                                $_SESSION['currState'][$i] = $guess;
                            }
                        }
                        
                    }
                    else{              

                        $message = "oops! '$guess' is not in the word";  #tell user if guess is wrong.
                        $remainGuess--; #reduce the number of remaining guesses by 1 if the guess was wrong
                        $_SESSION['remainGuess']= $remainGuess; #update the value of remaining guesses in the session
                    } 
                }

                #check if user won 
                 if(!in_array('_', $_SESSION['currState'])){
                        $message= "YaaaY! You won the game!!";
                        $_SESSION['gameOver']= true;
                        $won= true;
                        # call helper method to add score to table 
                       
                        if(empty($_SESSION['scoreSaved'])) {
                        saveScore($pdo, $userName, $difficulty_level, $_SESSION['random_word'], $_SESSION['currState'], $_SESSION['remainGuess'],true);
                       }

                    }

                    #check for lose condition after updating remaining guesses
                    if($_SESSION['remainGuess']<=0){
                        $message= "GAME OVER!! The word was: ". strtoupper($_SESSION['random_word']);
                        $won =false;
                        $_SESSION['currState']= str_split(strtoupper($_SESSION['random_word']));  #reveal correct word
                        $_SESSION['gameOver'] = true;
                        #call method to store scores 
                        if(empty($_SESSION['scoreSaved'])) {
                        saveScore($pdo, $userName, $difficulty_level, $_SESSION['random_word'], $_SESSION['currState'], $_SESSION['remainGuess'],false);
                        }
                        
                    }

                   
            }    

        }
    }
}
 # function to calculate score and save it to table 

  function saveScore($pdo, $userName, $difficulty_level, $randomWord, $currState, $remainGuess , $won){


   #calculate number of letters revealed
   $letterRevealed=0;
    foreach($currState as $c){
        if($c !=='_'){
        $letterRevealed++;
       }
     }
      
      #calculate score 
     $score = ($letterRevealed * 5) + ($remainGuess * 10);
     if($won) $score += 15;
        #inser score in table after calculating it 
         $stmt = $pdo->prepare("
         INSERT INTO cois3430_assn1_scores
        (username, difficulty, result, word, score, play_date)
        VALUES (:username, :difficulty, :result, :word, :score, NOW())
         ");
     if($stmt->execute([
      ':username'   => $userName,
      ':difficulty' => $difficulty_level,
      ':result'     => $won ? 'Win' : 'Lose',
      ':word'       => strtoupper($randomWord),
      ':score'      => $score
     ])) {
     $_SESSION['scoreSaved'] = true;
     } else {
         $err = $stmt->errorInfo();
         echo "DB insert error: ".$err[2];
         
       } 
    }

               
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/play.css">
</head>
<body>
    <div class="game">
    <section> 
        <!--use a foreach loop to show word as letters on a gameboard -->
        <div class="board">
            <?php foreach($_SESSION['currState'] as $letter):?>
                <span class="letter-box"><?= htmlspecialchars($letter)?></span>
            <?php endforeach;?>    
        </div>
        <h2><?= htmlspecialchars($difficulty_level)?></h2> <!-- show difficulty level -->
    </section>
    
    <aside> 
        <!--use a foreach loop to insert guessed letters/words as list items -->
        <div class="guessed-letters">
            <?php foreach( $_SESSION['allGuesses'] as $guess): ?>
            <span><?= htmlspecialchars($guess) ?></span>
            <?php endforeach; ?>
            </div>
    </aside>
    <div class="lives">
        <p>Remaining Lives: <?= $remainGuess?></p>
        <?php for($i=0; $i<$remainGuess; $i++): ?> <!-- show remaining number of guesses -->
            <span>&hearts;</span>
            <?php endfor;?>
    </div>
    <main>
        <!-- form to take user input-->
         <?php if (!empty($message)): ?>
            <p class="feedback"><?= htmlspecialchars($message) ?></p> <!-- show the appropriate message to user -->
        <?php endif; ?>
        <?php $gameOver = $_SESSION['gameOver'] ?? false; ?>
        <form method="POST" action="play.php">
            <label for="guessLetter"> <!-- form to take guess from user -->
                <input type="radio" id="guessLetter" name="guess" value="guess a letter" checked/>
                Guess a Letter
            </label>
            <label for="guessWord">
                <input type="radio" id="guessWord" name="guess" value="guess a word"/>
                Guess a word
            </label>
            
            <label  for="guessinp">
                <!-- Do not take input if Game is Over -->
                <input type="text" id="guessinp" name="guessInp" placeholder="Enter your Guess" required <?= $gameOver ? 'disabled' : '' ?> />
            </label>
            <button type="submit" <?= $gameOver ? 'disabled' : '' ?>>GUESS</button> 
        </form>
         <div class="new-game">
    <form action="difficulty.php" method="get">
        <button type="submit" name="new" value="1">Start New Game</button>
    </form>
</div>
    </main>
    <footer>
        <p>Logged in as: <strong><?= htmlspecialchars($userName)?> </strong></p> <!-- show username -->
    </footer>
     </div>
        
    <?php if ($gameOver): ?>
    <div class="game-over-buttons">
        <!-- play again button and score button show when game is over -->
         <form action="difficulty.php" method="get">
            <button type="submit">PLAY AGAIN</button>
         </form>
         <form action="scores.php" method="get">
            <button type="submit">Go to ScoreBoard</button>
         </form>
    </div> 
    <?php endif; ?>
    
        
</body>
</html>