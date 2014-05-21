<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <title>Poolski| <?php echo $pageTitle ?></title>   

<!--GET JQUERY FROM ONLINE SITE-->
<!-- JQUERY COMMENTED OUT FOR SPEED REASONS 12/12/13<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>-->

<!--GET JQUERY FROM FILE IN CASE WE CAN'T REACH ABOVE SITE-->
<script src="inc/jquery.js"></script>

<script src="my_javascript.js"></script>

<!--GET EDITINPLACE JQUERY PLUGIN-->
<script type="text/javascript" src="inc/jquery.editinplace.js"></script>
<script type="text/javascript" src="form_edit.js"></script>

<!--BOOTSTRAP JQUERY ADD ONS-->
<script src="inc/bootstrap_addons//bootstrap/js/bootstrap.min.js"></script> 
<script src="inc/bootstrap_addons/bootstrap-formhelpers.min.js"></script>

<!--Bootstrap Form Helpers -->
<link href="inc/bootstrap_addons/bootstrap-formhelpers.min.css" rel="stylesheet" media="screen">

<!--Bootstrap Plugins-->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="inc/bootstrap_addons/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!--GET STYLESHEET-->
<link rel="stylesheet" href="inc/styles.css" type="text/css" media="screen" title="no title" />

</head>

<body>
<div id="full_container">
    <div id="body">

        <div id="page-wrap">
            <div id="header">
                <div id="control">
<?php
	session_start();
?>
                <nav class="navbar navbar-default" role="navigation">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="home.php"><?php echo BRAND_NAME; ?></a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
<?php

	if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==999): //if user is logged in:
        include_once "inc/loggedin_check.php";
        include_once "inc/constants.inc.php";
        include_once 'inc/class.users.inc.php';
        $user = new SiteUser(); 
        include_once 'inc/class.pool.inc.php';
        $pool = new Pool(); 
        $current_user = $_SESSION['Username'];
        $current_user_id = $user->GetUserIDFromEmail($current_user);
        $pool_invites_result_pre = $user->CheckPoolInvites($current_user); //get initial pool invites for a user if they exist - these may include pools which are live that we don't want the user to join, so we do a check for live pool invites below and remove the invite if the pool is live
        $admin = $user->CheckAdmin($current_user_id); //$ADMIN variable is a 1 if user is an admin and 0 if not
?>
                  <!-- Collect the nav links, forms, and other content for toggling -->
                  
                        <ul class="nav navbar-nav">
                            <li><a href="home.php">Your Pools</a></li>
                            <li><a href="new.php">Create New Pool</a></li>
                            <!--<li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#">Action</a></li>
                                    <li><a href="#">Another action</a></li>
                                    <li><a href="#">Something else here</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#">Separated link</a></li>
                                    <li class="divider"></li>
                                    <li><a href="#">One more separated link</a></li>
                                </ul>
                            </li> -->
                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            <li><a><?php echo $_SESSION['Username']; ?></a></li>
                            <!--<li><a href="mailto:contact@poolski.com?Subject=Hello">Contact us</a></li>-->
                            <li><a href="logout.php">Log out</a></li>
                        </ul>
<?php else: //if logged out: ?>
                        <ul class="nav navbar-nav">
                            <li><a href="login.php">Log in</a></li>
                            <li><a href="signup.php">Sign up</a></li>
                        </ul>
<?php endif; ?>
                    </div><!-- /.navbar-collapse -->
                </nav>
            </div>
        </div>


