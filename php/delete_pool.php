<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Delete Pool (INTERNAL)";
    $current_user = $_SESSION['Username'];

    if(!isset($_GET['pool_id'])){
        //if no pool ID is specified in URL, return the user to the homepage:
        header("Location: home.php");
    }
    else {
        include_once 'inc/class.users.inc.php';
        $user = new SiteUser(); 
        $current_user_id = $user->GetUserIDFromEmail($current_user);
        if($current_user_id <> 1){ //make sure user is a site admin, if not, return them to the home page
            header("Location: home.php");
        }
        else { //if we successfully got the pool id from the URL and the user is a site ADMIN:
            include_once 'inc/class.pool.inc.php';
            $pool = new Pool(); //new instance of the Pool class
            $pool_id = $_GET['pool_id']; //get pool ID from URL
            if(isset($_GET['delete'])){ //if delete variable is set
                if($_GET['delete'] == 5668){ //if delete variable is the proper value (meaning the "delete button was clicked from this page")
                    $pool->DeletePool($pool_id); //delete all entries associated with the pool
                    header("Location: home.php"); //return user to the home page once pool is deleted
                }
            }
            else {
                $pool_fetch_result = $pool->GetPoolData($pool_id); 
                $confirm_delete_url = "delete_pool.php?pool_id=$pool_id&delete=5668";
            }
        }
    }
    include_once "inc/header.php";
?>
<br>
<div style="text-align: center">
    <h1 style="color:red">WARNING</h1>
    <h1>Are you sure you want to delete <?php echo $pool_fetch_result['Title']; ?>?</h1>
    <br>
    <h3>All Pool data and picks will be erased</h3>
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