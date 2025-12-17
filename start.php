<?php
session_start();
$error_msg="";

    if($_SERVER['REQUEST_METHOD']== 'POST'){

        $username = htmlspecialchars(trim($_POST["username"]));
       
        if($username!=""){ 
             //process username after submit
            $_SESSION['username']= $username;

            header('Location: difficulty.php'); //Redirect to difficulty.php after validating username
    } else{
        $error_msg= "Please enter a valid username";
    }
    
}
?>
<!-- Form to ask username -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./styles/main.css"> 
</head>
<body>
    <!-- display form -->
     <div class="container">
    <h1>Welcome to the Hangman </h1>
    <form action="start.php" method="post">
        <!-- take username -->
         <label for="username">Username:</label>
         <input type="text" id="username" name="username" required>
        <!-- show eeror if username is invalid-->
         <span class="error <?= empty($error_msg)? 'hidden' :''?>">
            <?= $error_msg?>
         </span>
         <!-- submit button -->
         <button type= "submit">Start Game</button>

    </form>
</div>
</body>
</html>