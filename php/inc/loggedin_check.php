<?php
    include_once "constants.inc.php";
    $pageTitle = "Logged in check";
    session_start();

    //check to see if the LoggedIn and Username $_SESSION variables are NOT set
    if(empty($_SESSION['LoggedIn']) && empty($_SESSION['Username'])){
        //if so, redirect to login page:
        header("Location: login.php");
    }
    
?>