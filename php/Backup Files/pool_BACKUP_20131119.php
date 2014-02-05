<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Pool";
    include_once "inc/header.php";

    /*
    TO DO AS OF 11:30 AM ON 11/17/13:
    -EDIT THIS PAGE SO THAT THE USER CAN INPUT A TIE BREAKER VALUE USING EDITINPLACE FUNCTIONALITY
    -WRITE A "TIE BREAKER PICK" METHOD IN POOL CLASS FILE TO ALLOW USER TO INPUT THEIR TIE BREAKER RESPONSE
    -CREATE A "SAVE PICKS" BUTTON THAT WILL BRING THE USER BACK TO THE HOME PAGE ONCE THEY'RE DONE EDITING
    */

    //IT IS EXPECTED THAT THIS PAGE WILL BE LOADED WITH THE POOL_ID VARIABLE SET IN THE URL

    if(!isset($_GET['pool_id'])){
        //if no pool ID is specified in URL, return the user to the homepage:
        header("Location: home.php");
    }
    else {
        //if we successfully got the pool id from the URL:
        include_once 'inc/class.pool.inc.php';
        $pool_id = $_GET['pool_id']; //get pool ID from URL
        $pool = new Pool(); //new instance of the Pool class
        //Below functions fetch the necessary pool data for the user:
        $pool_fetch_result = $pool->GetPoolData($pool_id); 
        $pool_category_fetch = $pool->GetPoolCategoryData($pool_id);
        $user_picks_fetch = $pool->GetUserPicks($_SESSION['Username'], $pool_id);
        $tie_breaker_answer = $pool->GetTieBreakerAnswer($_SESSION['Username'], $pool_id);
        if(isset($tie_breaker_answer)){
            $tie_breaker_answer_display = $tie_breaker_answer;
        }
        else{
            $tie_breaker_answer_display = "**Enter your tie-breaker answer here!**";
        }
    }

    if($pool_fetch_result==0):
    //if the pool id passed thru url does not exist in database:
?>
        <p>Error: pool does not exist</p>
        <p><a href="home.php">Click here to return to home page</a></p>
<?php
    else:
?>
    <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span>
    <span id="user_id_span" style="display:none"><?php echo $_SESSION['Username']; ?></span>
    <h1 style="text-decoration:underline"><?php echo $pool_fetch_result['Title']; ?></h1>
    <h3><?php echo $pool_fetch_result['Description']; ?></h3> 
    <br>

<div id="content">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active"><a href="#summary" data-toggle="tab">Pool Summary</a></li>
        <li><a href="#my_picks" data-toggle="tab">My Picks</a></li>
        <li><a href="#pool_members" data-toggle="tab">Pool Members</a></li>
        <li><a href="#message_board" data-toggle="tab">Message Board</a></li>
    </ul>
    <div id="pool_tab_content" class="tab-content">
        <div class="tab-pane fade in active" id="summary">
            <h1>Pool Summary</h1>
            <p>Pool Summary</p>
        </div>
        <div class="tab-pane fade" id="my_picks">
            <h1>My Picks</h1>
            <p>My Picks here</p>
        </div>
        <div class="tab-pane fade" id="pool_members">
            <h1>Pool Members</h1>
            <p>Pool Members here</p>
        </div>
        <div class="tab-pane fade" id="message_board">
            <h1>Message Board</h1>
            <p>Message Board here</p>
        </div>
    </div>
</div>   


<?php 
    if($pool_fetch_result['Live?']==0){
        echo "<p>This pool is not live yet.  Make your picks below!</p>";
    }
    else{
        echo "<p>This pool is live.  Picks are locked</p>";
    }

    if($pool_fetch_result['Leader ID'] == $_SESSION['Username']){
        echo "<p style='font-weight:bold'>You are the leader of this pool</p>";
    }
?>

    <br>
    <h3 style="text-decoration:underline"><?php echo $pool_fetch_result['Overall Question']; ?></h3> 
    <br>
    <ol>

<?php
        $category_counter = 1;
        //create list of saved pool categories and user's picks for given pool by walking through pool_categories array:
        foreach($pool_category_fetch as $category_id => $category_info){
            if(isset($user_picks_fetch[$category_id])) {
                //if a pick already exists for given category, we store it in the pick_display_value variable
                $pick_display_value = $user_picks_fetch[$category_id];
            }
            else{
                //if no pick exists for the given category, we instruct the user to make one:
                $pick_display_value = "**Pick not set - click here to make your pick! **";
            }
?>          
            <div id="category_<?php echo $category_counter; ?>">
                <li style="margin-left:50px"> 
                    <p id="category_n_span<?php echo $category_info['Category ID']; ?>" style="margin-left:30px"> <?php echo $category_info['Category Name']; ?> &nbsp; <span id="category_p_span<?php echo $category_info['Category ID']; ?>" style="position:absolute; left:30%">Point Value: <?php echo $category_info['Category Point Value']; ?></span>
                        <span style="left:50%; position:absolute; font-weight:bold;">Your pick: <span class="<?php if($pool_fetch_result['Live?']==0){echo "edit_pick";}else{echo "display_pick";}?>" id="pick_for_category_<?php echo $category_info['Category ID']; ?>" style="margin-left:50px; font-weight:bold; color:blue"><?php echo $pick_display_value ?></span></span>
                    </p>
                </li>
            </div>
            <br>
<?php 
        $category_counter++;
        }
?>
    </ol>
    <h3 style="margin-left:50px; text-decoration:underline">Tie breaker question</h3>
    <p style="margin-left:50px"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></p> 
    <div id="tie-breaker" style="margin-left:50px;">
        <p style="margin-left:50px;"><span id="tie_breaker_input" class="edit_pick" style="font-weight:bold; color:blue;"><?php echo $tie_breaker_answer_display; ?></span></p>
    </div>
        
<?php
    endif;
    //include_once 'common/close.php';
?>
