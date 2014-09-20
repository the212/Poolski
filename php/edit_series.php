<?php

    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Edit Pool Series";

    if(!isset($_GET['series_id']) || $current_user_id <> 1) {
        //if no pool series ID is specified in URL or user is not an admin, return the user to the homepage:
        header("Location: home.php");
    }
    else {
        //if we successfully got the series id from the URL:
        $series_id = $_GET['series_id'];
    }
    include_once "inc/header.php";
    $series_fetch_result = $pool->GetPoolSeriesData($series_id);
    //print_r($series_fetch_result);
    if($series_fetch_result[0] == 2):     
?>
    <div class="error_message_div">
        <h3>Error: <?php echo $series_fetch_result[1]; //echo GetPoolSeriesData function error message ?></h3>
        <p><a href="home.php">Click here to return to home page</a></p>
    </div>
<?php    
    else:
?>
    
    <div id="edit_pool_header_container">
        <div style="text-align:center;">
            <h1>Edit Pool Series </h1>
            <h5>Configure the Pool Series settings.</h5>
        </div>

<?php
    if($series_fetch_result['Live?'] == 0){ //only display the publish series button if the given series is not already published:
?>
        <h3 style="margin-left:20px;"><span class="label label-warning">Series has not yet been published</span>&nbsp;&nbsp;<input type="button" onclick="JAVASCRIPT:change_series_live_variable(<?php echo $series_id; ?>, 1)" value="Publish Series"></h3>
<?php
    }
    else{ //if series has been published:
?>
        <h3 style="margin-left:20px;"><span class="label label-success">Series has been published and is live.</span>&nbsp;&nbsp;<input type="button" onclick="JAVASCRIPT:change_series_live_variable(<?php echo $series_id; ?>, 0)" value="Retire Series"></h3>
<?php
    }
?>

        <!--POOL UPDATE SUCCESS MESSAGE -->
        <span class="alert alert-success alert-dismissable" id="edit_pool_success" style="display:none; padding:8px; width:250px; float:right">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Series successfully updated!
        </span>

        <!--POOL UPDATE FAILURE MESSAGE -->
        <span class="alert alert-danger alert-dismissable" id="edit_pool_failure" style="display:none; padding:8px; width:250px; float:right">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span id="edit_pool_error_message">ERROR!</span>
        </span>

        <br>

        <span id="series_id_span" style="display:none"><?php echo $series_fetch_result["Series ID"]; ?></span> <!--This is here so that the form_edit.js function knows which series we are looking at.  That function passes the series ID to the server with each input-->

    </div>

    <div id="content">
    <!--EDIT POOL INFO DIV -->
        <br><h4 class="field_label">Click on an item to edit it</h4>
        <div id="series_info_container">
            <div class="well well-sm">
                <div class="row">
                    <div class="col-md-5">
                        <h3 class="edit_series_heading">Series Name:</h3>
                    </div>
                    <div class="col-md-7">
                        <h2><span class="label label-info"><span class="edit_series_field" id="series_Title"><?php echo $series_fetch_result["Title"]; ?> </span></span></h2>
                    </div>
                </div>
            </div>
            <div class="well well-sm">
                <div class="row">
                    <div class="col-md-5">
                        <h3 class="edit_series_heading">Series Description (optional): </h3>
                    </div>
                    <div class="col-md-7">
                        <h3><p class="label label-info" style="display:block; white-space:normal;"><span class="edit_series_field" id="series_Description"><?php echo $series_fetch_result['Description']; ?></span></p></h3>
                    </div>
                </div>
            </div> 
            <div class="well well-sm">
                <div class="row">
                    <div class="col-md-5">
                        <h3 class="edit_series_heading">Overall Series Topic: </h3> </div>
                    <div class="col-md-7">
                        <h3><span class="label label-info"><span class="edit_series_field" id="series_Overall Topic"><?php echo $series_fetch_result['Overall Topic']; ?></span></span></h3>
                    </div>
                </div>
            </div>
            <br>
        </div>
    </div> <!--END OF CONTENT DIV-->
    <br>
    <br>
<?php
    include_once 'inc/close.php';
    endif;
?>