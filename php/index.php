<?php
    session_start(); //need this here so that we can redirect user to homepage if they are logged in
    if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username']) && $_SESSION['LoggedIn']==999): 
        //if user is already properly logged in:
        header("Location: home.php");
    else:
        include_once "inc/constants.inc.php";
        $pageTitle = "Welcome to Poolski!";
        include_once "inc/header.php";
?>  

<body>
<div id="full_container">
    <div id="body">

        <div id="page-wrap">
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
				<li>NFL Season 2014</li>
				<li>And much more...</li>
			</ul>
		</div>
		<br>
		<h1><a href="home.php"><span class='label label-warning' style="color:white;">Click here to try the beta version</span></a></h1>
	</div>
</div>

<?php
    endif;
?>


