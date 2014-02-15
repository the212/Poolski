<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "New Pool";
    include_once "inc/header.php";

?>
<br>
<div style="text-align: center">
    <h1>Create New Pool</h1>
    <h4>You are presented with a choice...</h4>
</div>

<div id="container">
    <div class="row" style="padding:5%;">
        <div class="col-sm-6 col-md-6">
            <div class="thumbnail">
                <div class="caption">
                    <h3>Create pool from existing template</h3>
                    <p>Choose one of our many pool templates.  We create all of the categories and mark answers correct so that you don't have to.</p>
                    <br>
                    <p><a href="browse_templates.php" class="btn btn-lg btn-primary center-block" role="button">Browse Templates</a> </p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="thumbnail">
                <div class="caption">
                    <h3>Create pool from scratch</h3>
                    <p>You can customize all of the pick 'em categories and assign point values yourself.  When the pool ends, you input the correct answers.</p>
                    <br>
                    <p><a href="create_new.php" class="btn btn-lg btn-primary center-block" role="button">Create from scratch</a> </p>
                </div>
            </div>
        </div>
    </div>
</div>