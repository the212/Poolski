<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    

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
        $pageTitle = $pool_fetch_result['Title'];
        include_once "inc/header.php";

        /*BEGIN CHECK TO SEE IF GIVEN USER IS A MEMBER OF THE POOL:
        **NOTE - THIS SHOULD PROBABLY BE A SEPARATE PHP FILE SO WE CAN INCLUDE IT IN OTHER FILES (SUCH AS INVITE_FRIENDS.PHP)
        */
        $pool_members_id_array = $pool->GetPoolMembers($pool_id); //generate array of pool members
        $user_is_pool_member_check = 0; //we use this variable to check whether the given user is a member of the pool 
        foreach($pool_members_id_array as $user_id => $user_info){ //run thru pool member array - if the given user's ID is present in the array, we make the is_user_pool_member variable equal to 1, if not it remains as 0
            if($user_id == $current_user_id){ //if current user is a member of the pool:
                $user_is_pool_member_check = 1;
            }
        }
        if($user_is_pool_member_check == 0){ //if user is not a pool member, return the user to the homepage:
            header("Location: home.php");
        }
        //END CHECK TO SEE IF GIVEN USER IS A MEMBER OF POOL

    }

    //BEGIN CHECK TO SEE IF POOL EXISTS OR IF POOL IS READY FOR INVITES:
    if($pool_fetch_result==0 or $pool_fetch_result['Ready for invites?']==0):
    //if the pool id passed thru url does not exist in database OR the pool has not yet been finalized by Leader::
?>

        <h3>Error: pool does not exist</h3>
        <h4><a href="home.php">Click here to return to home page</a></h3>

<?php
    else: //if pool exists and is ready for invites, load the rest of pool.php file:
        //Get pool category data
        $pool_category_fetch = $pool->GetPoolCategoryData($pool_id); 
        
        //Get pool start/end times and dates from pool_fetch_result array:
        $pool_start_date = substr($pool_fetch_result['Start Time'], 0, 10);
        $pool_start_time = substr($pool_fetch_result['Start Time'], 11); //pool start time will be in 24 hour time
        $pool_start_time = $pool->timestampTo12HourConversion($pool_start_time); //convert pool start time into appropriate time
        $pool_end_date = substr($pool_fetch_result['End Time'], 0, 10);
        $pool_end_time = substr($pool_fetch_result['End Time'], 11);
        $pool_end_time = $pool->timestampTo12HourConversion($pool_end_time); //convert pool start time into appropriate time
        
        //BEGIN CHECK TO SEE IF USER IS THE LEADER OF POOL:
        $user_is_leader = 0; //set user is leader variable to 0 initially
        if($pool_fetch_result['Leader ID'] == $_SESSION['Username']){
            $user_is_leader = 1; //if user is the leader of the pool we set user is leader variable to 1
        }
        //END CHECK TO SEE IF USER IS LEADER OF POOL

        //BEGIN GENERAL HTML:
?>

    <!--BEGIN HIDDEN SPANS (FOR AJAX)-->
    <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span>
    <span id="user_id_span" style="display:none"><?php echo $_SESSION['Username']; ?></span>
    <!--END HIDDEN SPANS-->
    

    <!--BEGIN TOP ROW DIV-->
    <div class="row" id="pool_page_top_row">
        <div class="col-md-5">
            <h1 id="pool_title_pool_page"><?php echo $pool_fetch_result['Title']; ?></h1>  
            <h4 id="pool_description_pool_page"><?php echo $pool_fetch_result['Description']; ?></h4>
        </div>
        <div class="col-md-7" id="pool_status_message">

            <!--BEGIN POOL LIVE STATUS MESSAGE-->
            <h1 style="position:relative; width:10%;">
<?php
            if($pool_fetch_result['Live?']==0){ //if pool is not yet live:
                echo "<span class='label label-warning'>Pool is unlocked - make your picks!</span>";
            }
            elseif($pool_fetch_result['Pool ended?']==0) { //if pool is live:
                echo " <span class='label label-success'>Live! Picks are Locked</span>";
            }
            else{ //if pool has ended:
                $pool_winner_nickmane = $pool_members_id_array[$pool_fetch_result['Pool Winner']]['Nickname'];
                echo " <span class='label label-primary'>Pool Ended</span>";
                
            }
?>
            </h1>
            <!--END POOL LIVE STATUS MESSAGE--> 
         </div> 
             
    </div>
    <!--END TOP ROW DIV-->


<!--*****************************************************************************************-->
<!--*****************************************************************************************-->    


    <!--BEGIN POOL UPDATED SUCCESS MESSAGE (FOR AJAX): -->
    <span class="alert alert-success alert-dismissable" id="edit_pool_success">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Pool successfully updated!
    </span>
    <!--END POOL UPDATED SUCCESS MESSAGE: -->

<!--*****************************************************************************************-->
<!--*****************************************************************************************-->

    
    <!--BEGIN MAIN CONTENT SECTION -->
    <br>
    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#summary" data-toggle="tab">Pool Summary</a></li>
            <li><a href="#my_picks" data-toggle="tab">My Picks</a></li>
            <li><a href="#invite_people_tab" data-toggle="tab">Invite Friends</a></li>
            <!--<li><a href="#message_board" data-toggle="tab">Message Board</a></li>-->
        </ul>
        <div id="pool_tab_content" class="tab-content">
            <!--TABS ARE BELOW:-->

<!--*****************************************************************************************-->
<!--*****************************************************************************************-->


    <!--BEGIN POOL SUMMARY TAB-->
            <div class="tab-pane fade in active" id="summary">

                <div id="pool_summary_container">
                    <br>
                    <div class="row"><!--BEGIN POOL PAGE SUB TABS ROW-->
                        

            <!--*****************************************************************************************-->
            <!--*****************************************************************************************-->


                        <div class="col-md-4 pool_page_sub_tabs_column" id="nickname_container">
                            <h4><span id="pool_nickname_glyph" class="glyphicon glyphicon-user"></span></h4>
<?php
                //BEGIN POOL NICKNAME LOGIC
                $user_nickname = $pool->GetNickname($current_user_id, $pool_id);
                if ($pool_fetch_result['Live?']==0) {
?>
                <h4>Choose your nickname for this pool: </h4>
                <h4>
                    <span class="label label-info"><span class='edit_nickname' id='update_nickname'><?php echo $user_nickname; ?></span></span><span style="margin-left:15px; font-style:italic; font-size:70%;">(Click to edit)</span>
                </h4>
<?php
                }
                else{
?>
                <h4>Your nickname for this pool is: </h4>
                <h4>
                    <span class="label label-primary"><?php echo $user_nickname; ?></span>
                </h4>
<?php
                }
            //END POOL NICKNAME LOGIC
?>
                        </div><!--End nickname_container-->


            <!--*****************************************************************************************-->
            <!--*****************************************************************************************-->



                        <div class="col-md-4 pool_page_sub_tabs_column" id="start_end_time_container">
<?php
                //icons to be displayed:
                $time_icon = "<h4><span class='glyphicon glyphicon-time' id='pool_time_glyph'></span></h4>";
                $calculate_icon = "<h4><span class='glyphicon glyphicon-tasks' id='pool_score_calculate_glyph'></span>";
                $exclamation_icon = "<h4><span class='glyphicon glyphicon-exclamation-sign' id='pool_end_glyph'></span></h4>";
                
                /*BEGIN START TIME DISPLAY AND START POOL BUTTON LOGIC*/

                if($pool_fetch_result['Live?']==0){ //if pool is NOT yet live
                    if($pool_fetch_result['Start Time']!== NULL) {  //if the pool has a start time defined:
                        echo $time_icon;
?>
                            <h4>Picks will be locked in at: </h4>
                            <h4><?php echo $pool_start_time; ?> EST on <?php echo $pool_start_date; ?> </h4>
<?php 
                    }
                    else { //if pool does NOT have a start date defined:
                        echo $exclamation_icon;
                        if($user_is_leader == 1) { //if user is the leader of the pool, they can start the pool manually themselves:
?>
                            <h3>
                                <a href='JAVASCRIPT:makePoolLive(<?php echo $pool_id; ?>);'>Click here to make pool live!</a>
                            </h3>
<?php
                        }
                        else{ //if user is not the leader:
?>
                            <h4>
                                The pool is unlocked.  
                                <br>
                                Make your picks now!
                            </h4>
<?php
                        }
                    }
                }
                /*END START TIME DISPLAY AND START POOL BUTTON LOGIC*/

                /*BEGIN END TIME DISPLAY AND END POOL BUTTON LOGIC*/
                
                elseif($pool_fetch_result['Pool ended?']== 0) { //if the pool is live:
                    if($pool_fetch_result['End Time']!== NULL) {  //if the pool has an end time defined:
                        echo $time_icon;
?>
                            <h4>This pool ends at: </h4>
                            <h4>
                                <?php echo $pool_end_time; ?> EST on 
                                <br>
                                <?php echo $pool_end_date; ?>
                            </h4>
<?php
                    }
                    else { //if pool does not have any end date defined:
                        echo $exclamation_icon;
                        if($user_is_leader == 1) { //if user is the leader of the pool, they can end the pool manually themselves:
?>
                            <h3>
                                <a href='JAVASCRIPT:endPool(<?php echo $pool_id; ?>);'>Click here to end the pool!</a>
                            </h3>
<?php
                        }
                        else{//if user is not the leader:
?>
                            <h4>
                                The pool is live! 
                                <br>
                                All picks are locked in.
                            </h4>
<?php
                        }
                    }
                }

                /*END END TIME DISPLAY AND END POOL BUTTON LOGIC*/

                /*BEGIN IF POOL HAS ENDED LOGIC (UNRELATED TO START AND END TIMES SINCE THOSE ARE IRRELEVANT IF POOL HAS ENDED)*/
                
                else{ //if the pool has ended:
                    if(!isset($pool_fetch_result['Pool Winner'])) { //if pool has NOT yet been scored:
                        echo $calculate_icon;
                        if(!isset($pool_fetch_result['Template ID'])) { //if pool is NOT a pre-canned template, we need to wait for the leader to score it:
                            if($user_is_leader == 1) {
                                $pool_leader_variable = "YOU";
                            }
                            else{
                                $pool_leader_variable = "pool leader";
                            }
?>
                            <h3>Pool has ended.  Waiting for <?php echo $pool_leader_variable; ?> to tally the score</h3>
<?php
                        }
                        else{ //if pool is a pre-canned template
                            if($admin == 1){ //if user is an admin:
                                $template_id = $pool_fetch_result['Template ID'];
?>
                            <h4><a href='score_template_choices.php?template_id=<?php echo $template_id; ?>'>Click here to mark the correct answers for the template (INTERNAL)</a></h4>
<?php
                            }
                            else{ //if user is not an admin and pool is from a template:
?>
                            <h3>Pool results are being calculated.  Please check back again soon.</h3>
<?php
                            }
                        }
                    }
                    else { //if pool HAS been scored and there is a pool winner set:
                        $pool_rankings_array = $pool->GetFinalPoolRankings($pool_id); //generate pool rankings array (for pool_members.php)
?>
                            <h4><span class="glyphicon glyphicon-star" id="pool_winner_glyph"></span></h4>
                            <h4>Pool Winner is:</h4>
                            <h2>
                                <span class='label label-warning'><?php echo $pool_winner_nickmane; ?></span>
                            </h2>
<?php
                    }  
                }

                /*END IF POOL HAS ENDED LOGIC*/
?>
                        </div><!--End start_end_time_container-->



            <!--*****************************************************************************************-->
            <!--*****************************************************************************************-->



                        <div class="col-md-4 pool_page_sub_tabs_column" id="leader_controls_container">
<?php
                if($user_is_leader == 1 && !isset($pool_fetch_result['Template ID']) && $pool_fetch_result['Live?'] == 1 && !isset($pool_fetch_result['Pool Winner'])) { 
                    /*If user is the pool leader, the pool is NOT a template, the pool is live, and a pool winner has NOT been set, let them score it manually:
                    **We don't let the user score the pool if a winner has already been calculated
                    **NOTE AS OF 5/20/14, IT'S DEBATEABLE AS TO WHETHER A USER SHOULD BE ABLE
                    **TO EDIT THE POOL'S SCORE AFTER IT HAS ENDED OR NOT
                    **FOR NOW, I AM DISABLING THIS BY ADDING A CHECK TO SEE IF A POOL WINNER IS SET IN THE ABOVE IF CONDITION
                    */
?>
                            <h4>
                                <span class='glyphicon glyphicon-pencil' id='pool_score_manual_glyph'></span>
                            </h4>
                            <h3>
                                <a href='score_pool_manual.php?pool_id=<?php echo $pool_id; ?>'>Click here to score picks</a>
                            </h3>
<?php
                }
                else { //if user is not leader, the pool IS a template, the pool is NOT live, OR there is a pool winner set:
                    //AS OF 5/20/14, WE ARE DISPLAYING THE TIE BREAKER ANSWER HERE
                    //THIS COULD BE CHANGED TO BE SOME OTHER CONTENT/FILLER
                    $user_tie_breaker_answer = $pool->GetTieBreakerAnswer($current_user_id, $pool_id);
                    if(is_null($user_tie_breaker_answer)){ //if there is no tie breaker answer chosen
                        $user_tie_breaker_answer_display = "<span style='font-style:italic'>**No answer chosen**</span>";
                    }
                    else{ //if there is a tie breaker answer chosen:
                        $user_tie_breaker_answer_display = "<span class='label label-primary'>".$user_tie_breaker_answer."</span>";
                    }
?>
                            <h4>
                                <span class="glyphicon glyphicon-certificate" id="pool_mypicks_link_glyph"></span>
                            </h4>
                            <h4>
                                Your Tie-breaker answer is: 
                            </h4>
                            <h3>
                                <?php echo $user_tie_breaker_answer_display; ?>
                            </h3>

<?php
                }
?>
                        </div><!--End leader_controls_container-->


        <!--*****************************************************************************************-->
        <!--*****************************************************************************************-->


                    </div><!--END POOL PAGE SUB TABS ROW (between tabs and pool standings table)-->
<?php
            include_once "inc/pool_members.php";
?>
                </div> <!--END POOL SUMMARY CONTAINER DIV-->
            </div>
    <!--END POOL SUMMARY TAB-->

<!--*****************************************************************************************-->
<!--*****************************************************************************************-->


    <!--BEGIN MY PICKS TAB-->
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
    <!--END MY PICKS TAB-->


<!--*****************************************************************************************-->
<!--*****************************************************************************************-->

            
            <div class="tab-pane fade" id="invite_people_tab">
<?php           
            /* BEGIN INVITE BUTTON LOGIC*/
            if($pool_fetch_result['Live?']==0){ //if pool is not yet live:
                if($user_is_leader == 1 || $pool_fetch_result['Private?'] == 0){ //if pool is public OR if the user is the leader:
                    include_once "invite_people.php"; 
                }
                else { //if user is not authorized to invite others (i.e., pool is private and user is not the leader)
?>
                <div id='no_invite_message'>
                    <h2>You cannot invite people because the pool is private</h2>
                    <h4>Only the pool leader can invite others</h4>
                </div>
<?php
                }
            }
            else{ //if pool is already live:
?>
                <div id='no_invite_message'>
                    <h2>You cannot invite people once the pool is live</h2>
                    <h4>Those who aren't here are missing out.</h4>
                </div>
<?php
            }
            /*END INVITE BUTTON LOGIC*/
?>
            </div>


        <!--        
            <div class="tab-pane fade" id="message_board">
                <h1>Message Board</h1>
                <p>Message Board here</p>
            </div>
        -->


<!--*****************************************************************************************-->
<!--*****************************************************************************************-->


        </div> <!--END OF pool_tab_content DIV-->
    </div>  
    <!--END MAIN CONTENT SECTION -->

<?php 
    /*DISPLAY CURRENT TIME IN TIMESTAMP FORM (ONLY FOR TESTING)
    $current_time = time();
    $current_time = gmdate("Y-m-dH:i:s", $current_time);
    echo $current_time;
    */
    endif;
    //include_once 'inc/close.php';
?>
