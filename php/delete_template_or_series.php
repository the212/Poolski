<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Delete Template or Series (INTERNAL)";
    $current_user = $_SESSION['Username']; 
    $current_user_id = $user->GetUserIDFromEmail($current_user);

    if((!isset($_GET['template_id']) && !isset($_GET['series_id'])) || $current_user_id <> 1){
        //if no template or series ID is specified in URL OR the current user is not an admin, return the user to the homepage:
        header("Location: home.php");
    }
    else { //if the page loaded with the ID properly defined and the current user is an admin:
        if(isset($_GET['delete'])) { //if we are ready to delete:
            if($_GET['delete'] == 5668){ //if delete variable is the proper value (meaning the "delete button was clicked from this page")
                if(isset($_GET['template_id'])){ //if we are deleting a template:
                    $pool->DeleteTemplate($_GET['template_id']); //delete all entries associated with the template
                }
                elseif(isset($_GET['series_id'])){ //if we are deleting a series:
                    $pool->DeleteSeries($_GET['series_id']); //delete all entries associated with the series
                }
                header("Location: home.php"); //return user to the home page once pool is deleted
            }
        }
        else{ //if we are not ready to delete and are just loading the page for the first time:
            if(isset($_GET['template_id'])){ //if we are deleting a template:
                $template_id = $_GET['template_id']; //get template ID from URL
                $fetch_result = $pool->GetBasicTemplateInfo($template_id); 
                $confirm_delete_url = "delete_template_or_series.php?template_id=$template_id&delete=5668";
                $name = $fetch_result['Template Name'];
            }
            elseif(isset($_GET['series_id'])){ //if we are deleting a series:
                $series_id = $_GET['series_id']; //get series ID from URL
                $fetch_result = $pool->GetPoolSeriesData($series_id); 
                $confirm_delete_url = "delete_template_or_series.php?series_id=$series_id&delete=5668";
                $name = $fetch_result['Title'];
            }
        }
    }
    include_once "inc/header.php";
?>
<br>
<div style="text-align: center; padding-left:10px; padding-right:10px;">
    <h1 style="color:red">WARNING</h1>
    <h1>Are you sure you want to delete <?php echo $name; ?>?</h1>
    <br>
    <h3>All associated data will be erased</h3>
<?php
    if(isset($_GET['template_id'])){ //only display below text if we are deleting a template:
?>
    <h3>This includes categories and category choices for historical pools!</h3>
<?php
    }
?>
</div>

<div class="row" style="padding:5%;">
        <div class="col-sm-6 col-md-6">
            <div class="thumbnail">
                <div class="caption">
                    <p><a href=<?php echo $confirm_delete_url; ?> class="btn btn-lg btn-danger center-block" role="button">DELETE</a></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6">
            <div class="thumbnail">
                <div class="caption">
                    <p><a href="home.php" class="btn btn-lg btn-primary center-block" role="button">CANCEL</a> </p>
                </div>
            </div>
        </div>
    </div>

<?php
    include_once 'inc/close.php';
?>