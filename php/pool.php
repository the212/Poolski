<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    

    /*
    TO DO AS OF 8:20 pm 11/20/13:
        -Add a check so that a user can only view the page if they are a member of the pool
            -NOTE - THIS SHOULD PROBABLY BE A SEPARATE PHP FILE SO WE CAN INCLUDE IT IN OTHER FILES (SUCH AS INVITE_FRIENDS.PHP)
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
        //Get pool start/end times and dates from pool_fetch_result array:
        $pool_start_date = substr($pool_fetch_result['Start Time'], 0, 10);
        $pool_start_time = substr($pool_fetch_result['Start Time'], 11); //pool start time will be in 24 hour time
        $pool_start_time = $pool->timestampTo12HourConversion($pool_start_time); //convert pool start time into appropriate time
        $pool_end_date = substr($pool_fetch_result['End Time'], 0, 10);
        $pool_end_time = substr($pool_fetch_result['End Time'], 11);
        $pool_end_time = $pool->timestampTo12HourConversion($pool_end_time); //convert pool start time into appropriate time
        $user_is_leader = 0; //set user is leader variable to 0 initially
    }

    $pageTitle = $pool_fetch_result['Title'];
    include_once "inc/header.php";

    if($pool_fetch_result==0 or $pool_fetch_result['Ready for invites?']==0):
    //if the pool id passed thru url does not exist in database OR the pool has not yet been finalized by Leader::
?>
        <h3>Error: pool does not exist</h3>
        <h4><a href="home.php">Click here to return to home page</a></h3>
<?php
    else:
        $pool_category_fetch = $pool->GetPoolCategoryData($pool_id); //get pool category data
        if($pool_fetch_result['Leader ID'] == $_SESSION['Username']){
            $user_is_leader = 1; //if user is the leader of the pool we set user is leader variable to 1
        }
?>
    <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span>
    <span id="user_id_span" style="display:none"><?php echo $_SESSION['Username']; ?></span>
    <div class="row">
        <div class="col-md-5" id="pool_title">
            <h1><?php echo $pool_fetch_result['Title']; ?></h1>
        </div>
        <div class="col-md-7" id="pool_status_message">
            <!--BEGIN POOL LIVE STATUS MESSAGE-->
            <h1 style="position:relative;">
<?php
            if($pool_fetch_result['Live?']==0){ //if pool is not yet live:
                echo " <span class='label label-warning'>Pool is unlocked - make your picks!</span>";
            }
            elseif($pool_fetch_result['Pool ended?']==0) { //if pool is live:
                echo " <span class='label label-success'>Live! Picks are Locked</span>";
            }
            else{ //if pool has ended:
                $pool_members_id_array = $pool->GetPoolMembers($pool_id);
                $pool_winner_nickmane = $pool_members_id_array[$pool_fetch_result['Pool Winner']]['Nickname'];
                if(!isset($pool_winner_nickmane)){
                    echo " <span class='label label-info'>Pool Ended.</span>";
                }
                else{
                    echo " <span class='label label-primary'>Pool Winner: ".$pool_winner_nickmane." </span>";
                }
            }
?>
            </h1>
            <!--END POOL LIVE STATUS MESSAGE--> 
         </div> 
             
    </div>

    <!--POOL UPDATED SUCCESS MESSAGE: -->
    <span class="alert alert-success alert-dismissable" id="edit_pool_success" style="display:none; padding:8px; width:250px; float:right">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Pool successfully updated!.
    </span>
    <br>

<div id="content">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active"><a href="#summary" data-toggle="tab">Pool Summary</a></li>
        <li><a href="#my_picks" data-toggle="tab">My Picks</a></li>
        <!--<li><a href="#pool_members" data-toggle="tab">Pool Members</a></li>
        <li><a href="#message_board" data-toggle="tab">Message Board</a></li>-->
    </ul>
    <div id="pool_tab_content" class="tab-content">


<!--********POOL SUMMARY TAB********-->


        <div class="tab-pane fade in active" id="summary">
            <div class="row" style="padding-left:15px;">   
                <h3>&#8220;<?php echo $pool_fetch_result['Description']; ?>&#8221;</h3>
            </div>
                    
<?php
            $invite_people_button = "<h4><form method='post' action='invite_people.php?pool_id=".$pool_id."'><input type='submit' value='Click here to invite people to the pool'></form></h4>";
            
            /* BEGIN INVITE BUTTON LOGIC*/
            if($user_is_leader == 1) { //if user is the leader of the pool:
                if($pool_fetch_result['Live?']==0){ //if pool is not let live, let leader invite new people:
                    echo $invite_people_button;
                }
            } 
            else{ //if user is NOT the leader of the pool:
                if($pool_fetch_result['Private?'] == 0 && $pool_fetch_result['Live?']==0){ //if pool is public and is not yet Live, allow a non-leader user to invite others:
                    echo $invite_people_button; 
                }
            }
            /*END INVITE BUTTON LOGIC*/

?>
            <div id="pool_summary_container">
<?php
            if($pool_fetch_result['Pool ended?']== 0) { //only display the start/end times and end pool button if pool has not ended:
                
                /*BEGIN START TIME DISPLAY AND START POOL BUTTON LOGIC*/
                if($pool_fetch_result['Start Time']!== NULL && $pool_fetch_result['Live?']==0) {  //if start time is set and pool is not yet live:
?>
                    
                    <div class="row" style="width:55%">
                        <div class="col-md-6">
                            <h4>Picks will be locked in at: </h4>
                        </div>
                        <div class="col-md-6">
                            <h4><?php echo $pool_start_time; ?> EST on <?php echo $pool_start_date; ?> </h4>
                        </div>
                    </div>
<?php 
                }
                else { //if pool does not have any start date defined, we allow the leader to start the pool manually:
                    if($user_is_leader == 1 && $pool_fetch_result['Live?']==0) { //if no start date defined, and user is leader, and the pool is NOT live:
                        echo "<span style='margin-left:30px'><form method='post' action='JAVASCRIPT:makePoolLive($pool_id);'><input type='submit' value='Click here to make pool live!'></form></span>";
                    }
                }
        /*END START TIME DISPLAY AND START POOL BUTTON LOGIC*/

        /*BEGIN END TIME DISPLAY AND END POOL BUTTON LOGIC*/
        
                if($pool_fetch_result['End Time']!== NULL && $pool_fetch_result['Live?']==1) {  //if end time is set and the pool is live:
?>
                    <br>
                    <div class="row" style="width:55%">
                        <div class="col-md-6">
                            <h4>The pool will end at: </h4>
                        </div>
                        <div class="col-md-6">
                            <h4><?php echo $pool_end_time; ?> EST on <?php echo $pool_end_date; ?></h4>
                        </div>
                    </div>
<?php
                }
                else { //if pool does not have any end date defined, we allow the leader to end the pool manually:
                    if($user_is_leader == 1 && $pool_fetch_result['Live?']==1) { //if no end date defined, and user is leader, and the pool is live:
                        echo "<br><div style='padding-left:25px'><h4><form method='post' action='JAVASCRIPT:endPool($pool_id);'><input type='submit' value='No end date set - click here when pool is finished'></form></h4></div>";
                    }
                }
        /*END END TIME DISPLAY AND END POOL BUTTON LOGIC*/
                include_once "inc/pool_members.php";
            } //END "IF POOL HAS NOT ENDED" LOGIC

        /*BEGIN "IF POOL HAS ENDED" LOGIC*/
            else {                
                if(!isset($pool_fetch_result['Pool Winner'])) { //if pool has NOT yet been scored:
                    if($pool_fetch_result['Multiple Choice?'] == 0) { //if pool is NOT multiple choice:
                            echo "<h3>Pool has ended.  Waiting on pool leader to tally the score</h3>";
                        if($user_is_leader == 1) {
                            echo "<br><h4><a href='score_pool_manual.php?pool_id=".$pool_id."'>Click here to tally the pool's score</a></h4>";
                        }
                    }
                    else { //if pool was multiple choice:
                        if(isset($pool_fetch_result['Template ID'])) { //if pool was a pre-canned template:
                            echo "<h4>Pool results are being calculated.  Please check back again soon.</h4>";
                            //**BEGIN ADMIN TEMPLATE SCORE LINK (ONLY FOR USER_ID=1**)
                            include_once 'inc/class.users.inc.php';
                            $user = new SiteUser();
                            $current_user_id = $user->GetUserIDFromEmail($_SESSION['Username']);
                            if($current_user_id == 1){ 
                                echo "<h4><a href='score_pool_manual.php?pool_id=".$pool_id."'>Click here to mark the correct answers (INTERNAL)</a></h4>";
                            }
                            //**END OF ADMIN TEMPLATE SCORE LINK**
                        }
                        else { //if pool was NOT a pre-canned template, the leader needs to mark the correct picks manually:
                            echo "<h3>Pool has ended.  Waiting on pool leader to tally the score</h3>";
                            if($user_is_leader == 1) {
                                echo "<br><h4><a href='score_pool_manual.php?pool_id=".$pool_id."'>Click here to mark the correct answers</a></h4>";
                            }
                        }
                    }
                }
                else { //if pool HAS been scored:
                    $pool_rankings_array = $pool->GetFinalPoolRankings($pool_id); //generate pool rankings array
                    //include_once "inc/final_pool_rankings.php"; //show pool ranking list
                    include_once "inc/pool_members.php";
                }  
            }
        /*END "IF POOL HAS ENDED" LOGIC*/
?>
            </div> 
        </div>
        <!--END POOL SUMMARY TAB-->

        <!--***********************************************************************-->

        <div class="tab-pane fade" id="my_picks">
<?php 
            if($pool_fetch_result['Multiple Choice?'] == 0){
                include_once "inc/my_picks_nonMC.php"; //include multiple choice picks file
            }
            else {
                include_once "inc/my_picks_MC.php"; //include non multiple choice picks file
            }
?>
        </div>
        <!--
        <div class="tab-pane fade" id="pool_members">
            <?php //include "inc/pool_members.php"; ?>
        </div>
        
        <div class="tab-pane fade" id="message_board">
            <h1>Message Board</h1>
            <p>Message Board here</p>
        </div>
        -->
    </div> <!--END OF pool_tab_content DIV-->
</div>  <!--END OF content DIV-->

<?php 
    /*DISPLAY CURRENT TIME IN TIMESTAMP FORM (ONLY FOR TESTING)
    $current_time = time();
    $current_time = gmdate("Y-m-dH:i:s", $current_time);
    echo $current_time;
    */
    endif;
    //include_once 'inc/close.php';
?>
