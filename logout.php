<?php

    session_start();

    unset($_SESSION['LoggedIn']);
    unset($_SESSION['Username']);
    unset($_SESSION['time']);

?>

<meta http-equiv="refresh" content="0;login.php">