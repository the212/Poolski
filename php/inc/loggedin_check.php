<?php
    //include_once "constants.inc.php";
    session_start();

if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==999){ 
	//if user is logged in:
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser(); 
    include_once 'inc/class.pool.inc.php';
    $pool = new Pool(); 
    $current_user = $_SESSION['Username'];
    $current_user_id = $user->GetUserIDFromEmail($current_user);
    $pool_invites_result_pre = $user->CheckPoolInvites($current_user); //get initial pool invites for a user if they exist - these may include pools which are live that we don't want the user to join, so we do a check for live pool invites below and remove the invite if the pool is live
    $admin = $user->CheckAdmin($current_user_id); //$ADMIN variable is a 1 if user is an admin and 0 if not
}
elseif($on_login_page !== 1){
	//if user is not logged in, redirect to login page:
    header("Location: login.php");
}

?>