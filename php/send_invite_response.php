<?php

/*
SEND_INVITE_RESPONSE PHP FILE
BY EVAN PAUL, NOVEMBER 13, 2013
*/

include_once "inc/constants.inc.php";
include_once 'inc/class.pool.inc.php';
include_once 'inc/class.users.inc.php';


if(isset($_POST['response'])){ 
    $user = new SiteUser(); 
    $pool = new Pool(); 

    if($_POST['response'] == "a"){ //if invite was accepted:
        //add user to given pool's membership list:
        $pool->AddUserToPoolMembership($_POST['user_id'], $_POST['pool_id']);
        //remove given pool id from user's invite list:
        $user->RemoveInvite($_POST['user_id'], $_POST['pool_id']);
    }

    if($_POST['response'] == "r"){ //if invite was accepted:
        //remove given pool id from user's invite list:
        $user->RemoveInvite($_POST['user_id'], $_POST['pool_id']);
    }
}