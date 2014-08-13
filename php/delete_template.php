<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Delete Template (INTERNAL)";
    $current_user = $_SESSION['Username'];
    $user = new SiteUser(); 
    $current_user_id = $user->GetUserIDFromEmail($current_user);

    if(!isset($_GET['template_id']) || $current_user_id <> 1){
        //if no pool ID is specified in URL or the current user is not an admin, return the user to the homepage:
        header("Location: home.php");
    }
    else {
        include_once 'inc/class.pool.inc.php';
        $pool = new Pool(); //new instance of the Pool class
        $template_id = $_GET['template_id']; //get pool ID from URL
        if(isset($_GET['delete'])){ //if delete variable is set
            if($_GET['delete'] == 5668){ //if delete variable is the proper value (meaning the "delete button was clicked from this page")
                $pool->DeleteTemplate($template_id); //delete all entries associated with the template
                header("Location: home.php"); //return user to the home page once pool is deleted
            }
        }
        else {
            $template_fetch_result = $pool->GetBasicTemplateInfo($template_id); 
            $confirm_delete_url = "delete_template.php?template_id=$template_id&delete=5668";
        }
    }
    include_once "inc/header.php";
?>
<br>
<div style="text-align: center">
    <h1 style="color:red">WARNING</h1>
    <h1>Are you sure you want to delete <?php echo $template_fetch_result['Template Name']; ?>?</h1>
    <br>
    <h3>All Template data and picks will be erased</h3>
    <h3>This includes categories and category choices for historical pools!</h3>
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