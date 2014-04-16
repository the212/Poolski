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
                        <a class="navbar-brand" href="home.php">Poolski</a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
<?php

	if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==999):
		//IF LOGGED IN 
		header("Location: home.php");
	else: 

	?>
<!-- IF LOGGED OUT -->
                        <ul class="nav navbar-nav">
                            <li><a href="login.php">Log in</a></li>
                            <li><a href="signup.php">Sign up</a></li>
                        </ul>
<?php endif; ?>
<!-- END OF IF STATEMENT -->
                    </div><!-- /.navbar-collapse -->
                </nav>
            </div>
        </div>
<div id="landing_page_welcome">
	<h1 id="welcome_heading">Welcome to Poolski</h1>

    <div class="row" id="landing_page_rectangles_container">
        <div class="rectangle landing_page_rectangle bckgrd_light_blue"></div>
        <div class="rectangle landing_page_rectangle bckgrd_green"></div>
        <div class="rectangle landing_page_rectangle bckgrd_dark_blue"></div>
        <div class="rectangle landing_page_rectangle bckgrd_orange"></div>
        <div class="rectangle landing_page_rectangle bckgrd_red"></div>
        <div class="rectangle landing_page_rectangle bckgrd_light_blue" style="margin-right:0%;"></div>
    </div>
	
	<div id="landing_page_sub_text">
		<h3>Poolski allows you to create and manage "pick 'em" style betting pools with your friends.</h3>
		<br>
		<h3>Simply create a pool, invite your friends, and Poolski will take care of the rest.</h3>
		<br>
		<div id="landing_page_list">
			<ul>
				<li>Academy Awards</li>
				<li>World Cup 2014</li>
				<li>And much more...</li>
			</ul>
		</div>
		<br>
		<h1><a href="home.php"><span class='label label-warning' style="color:white;">Click here to try the beta version</span></a></h1>
	</div>
</div>


