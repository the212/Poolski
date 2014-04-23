<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Home";
    include_once "inc/header.php";

    $all_pools = $pool->GetAllPools($current_user); 
    $active_pools = $pool->GetActivePool($current_user_id); //this stores all of a user's pools that are "ready for invites" but have not yet ended
    $inactive_pools = $pool->GetInactivePools($current_user_id); //this stores all of a user's pools where "ready for invites" equals 0
    $completed_pools = $pool->GetCompletedPools($current_user_id);  //this stores all of a user's pools where "pool ended?" equals 1
    //get the total number of all pools that a user is in, active or inactive:
    $number_of_total_pools = count($all_pools);

    //BEGIN CHECK TO SEE IF ANY OF THE USER'S INVITE POOLS ARE LIVE ALREADY:
    foreach($pool_invites_result_pre as $index => $pool_id){ //for each pool ID in the user's invite list:
        if($pool_id !== ""){ //if we have NOT yet reached the end of the pool invite list for the given user:
            $given_pool_data = $pool->GetPoolData($pool_id); //get given pool data
            if($given_pool_data == 0 OR $given_pool_data['Live?'] == 1){ //if pool id doesn't exist in DB or the pool is already live:
                $user->RemoveInvite($current_user_id, $pool_id); //remove pool invite entry if pool doesn't exist
            }
        }
    }
    //END CHECK TO SEE IF ANY OF THE USER'S INVITE POOLS ARE LIVE ALREADY
    $pool_invites_result = $user->CheckPoolInvites($current_user); //get pool invites for a user if they exist after "Live?" check - this ensures that we are only fetching pool invites that are not live for a user
?>

<!--BEGIN MAIN CONTAINER OF PAGE******************************************************-->


<div class="main_container">
    <div class="row">

<!--BEGIN 1ST COLUMN ****************************************************************-->

        <div class="col-md-6">

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Your current pools:</h3>
                </div>
                <div class="panel-body">

<?php
    //BEGIN CHECK FOR POOL INVITES IF STATEMENT
    if($pool_invites_result <> "0"){ //if user has pool invites pending:
?>
                    <h3 style='word-wrap:break-word;'><span class='label label-primary'>** You have been invited to a new pool! **</span></h3>
                    <h5 style="margin-left: 20px;"><a id="show_invites_link" style="cursor:pointer;">Click here to see your list of invites.</a></h5>
                    <br>
                    <div id='pool_invite_list' style='display:none'>
                        <h4 style='text-decoration:underline'>Pool invites:</h4>
<?php
        foreach($pool_invites_result as $index => $pool_id){
            $given_pool_data = $pool->GetPoolData($pool_id);
            if($given_pool_data == 0){ //if pool id doesn't exist in DB:
                echo "";
            }
            else{ //if we are able to find a pool for the given pool id:
?>
                        <span id="pool_span_<?php echo $pool_id; ?>"style="margin-left:30px; font-weight:bold;"><?php echo $given_pool_data['Title']; ?></span>
                            <input class="accept_invite_button" type='button' onclick="accept_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Join Pool'>
                            <input type='button' onclick="decline_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Decline Invite'>
                        </span>
                        <br>
<?php
            }
        } //END OF POOL INVITE LIST FOREACH STATEMENT
?>
                        <br>
                    </div> <!--END OF POOL INVITE LIST DIV-->
<?php
    } //END OF CHECK FOR POOL INVITES IF STATEMENT

    //BEGIN ACTIVE POOLS CHECK

        if(count($active_pools) > 0){ //if the user has active pools
            rsort($active_pools); //sort the array so that most recently created pools come first
            foreach($active_pools as $pool_id => $pool_info){
                $pool_id = $pool_info['Pool ID'];
                $active_pool_data = $pool->GetPoolData($pool_id); //this is only here so that the pool will begin if it is past the start date (GetPoolData method has a check to see if current time is past pool start time)
                $pool_url = "pool.php?pool_id=$pool_id"; //set pool URL - this is the default and will be overridden if necessary depending on what state the pool is in
                if($pool_info['Ready for invites?']==1 && $pool_info['Live?']==0){
                    //if pool has been submitted, but is not live yet:
                    $live_variable = "Pool has not started yet - Click to make your picks!"; 
                    $status_styling = "color:#f0ad4e";
                    $rectangle_color = "#f0ad4e";
                }
                if($pool_info['Live?']==1 && $pool_info['Pool ended?']==0){
                    //if pool is ready for invites and is live also:
                    $live_variable = "Pool is live - Click to view pool!"; 
                    $status_styling = "color:#5cb85c;";
                    $rectangle_color = "#5cb85c";
                }
                if($current_user == $pool_info['Leader ID']){
                    $leader_variable = "You";
                }
                else {
                    $leader_variable = $pool_info['Leader ID'];
                }
?>
                        <div class="row active_pool_list_item" id="active_pool_id_<?php echo $pool_id; ?>">
                            <div class="homepage_pool_list_rectangle" style="background-color:<?php echo $rectangle_color; ?>;"></div>
                            <div class="active_pool_list_item_title">
                                <a href=<?php echo $pool_url; ?>><?php echo $pool_info['Title']; ?></a>
                            </div>
                            <div class="active_pool_list_item_content">
                                <a href=<?php echo $pool_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a>
<?php
                if($admin == 1){ //if user is admin:
                    $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <br>
                            <a href=<?php echo $delete_pool_url; ?>>Delete Pool</a>
<?php
                }
?>
                            </div>
                        </div>
<?php
            } //END OF ALLPOOLS FOREACH STATEMENT
?>
                   
                    <br>
<?php
        }
        else { //if user does not have any active pools:
            echo "<h3>You do not currently have any pools</h3>";
            echo "<h4>Perhaps you would like to <a href='new.php'>create a new one?</a></h4>";
        }
?>

                </div><!--END ACTIVE POOLS PANEL-BODY DIV-->
            </div><!--END ACTIVE POOLS PANEL DIV-->

<!--
             <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">News</h3>
                </div>
                <div class="panel-body" id="news_panel_body">
                    <div id="news_panel_content">
                        <h3 id="homepage_news_headline">Coming soon:</h3>
                        <h4 class="homepage_news_line">FIFA World Cup <span class="homepage_news_float_right">June 2014</span></h4>
                    </div>
                </div>
            </div>
-->

<!--END ACTIVE POOLS SECTION************************************************************************************-->


<!--BEGIN RECENT POOL WINNERS SECTION*****************************************************************-->

        <!--
        <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Recent Pool Winners</h3>
                </div>
                <div class="panel-body" id="recent_winners_panel_body">
                    <div id="recent_winners_panel_content">
                        <h4 class="recent_winners_line">Our hats off to:</h4>
                        <div id="recent_pool_winners_list">
<?php
                /*$recent_pool_winners_array = $pool->GetPoolWinners();
                foreach($recent_pool_winners_array as $pool_id => $winner_nickname){
                    if(is_null($winner_nickname)){ //if no nickname specified for pool winner
                        $winner_nickname = "";
                    }
?>
                    <p><?php echo $winner_nickname; ?></p>
<?php

                }*/
?>
                        </div>
                    </div>
                </div>
            </div>
        
    -->

<!--END RECENT POOL WINNERS SECTION*****************************************************************-->





<!--BEGIN INACTIVE POOLS SECTION*****************************************************************-->

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Your works in progress:</h3>
                </div>
                <div class="panel-body">
<?php
        if(count($inactive_pools) > 0){ //if the user has inactive pools
            rsort($inactive_pools); //sort the array so that most recently created pools come first
            foreach($inactive_pools as $pool_id => $pool_info){
                $pool_id = $pool_info['Pool ID'];
                $pool_url = "pool.php?pool_id=$pool_id"; //set pool URL - this is the default and will be overridden if necessary depending on what state the pool is in
                if($pool_info['Ready for invites?']==0){ //if pool is still in edit mode and hasn't been finalized:
                    if($current_user == $pool_info['Leader ID']){ //check to make sure user is leader of the pool:
                        $pool_url = "edit_pool.php?pool_id=$pool_id";
                        $live_variable = "INACTIVE - Edit Pool";
                        $status_styling = "color:#d9534f";
                        $rectangle_color = "#d9534f";
                        $leader_variable = "You";
                    }
                }
?>

                    <div class="row active_pool_list_item" id="active_pool_id_<?php echo $pool_id; ?>">
                        <div class="homepage_pool_list_rectangle" style="background-color:<?php echo $rectangle_color; ?>;">
                        </div>
                        <div class="active_pool_list_item_title">
                            <a href=<?php echo $pool_url; ?>><?php echo $pool_info['Title']; ?></a>
                        </div>
                        <div class="active_pool_list_item_content">
                            <a href=<?php echo $pool_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a>
<?php
                if($admin == 1){ //if user is admin:
                    $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <br>
                            <a href=<?php echo $delete_pool_url; ?>>Delete Pool</a>
<?php
                }
?>
                        </div>
                    </div>
<?php
            } //END OF INACTIVE POOLS FOREACH STATEMENT
        } //END OF INACTIVE POOLS IF STATEMENT
        else{ //if user does not have any inactive pools:
            echo "<h4>You don't have any pools that you are editing</h4>";
        }
?>
                </div>
            </div>

<!--END INACTIVE POOLS SECTION******************************************************************--> 

        </div> 





<!--END 1ST COLUMN******************************************************************************-->







<!--BEGIN CREATE NEW POOL BUTTON ****************************************************************-->


        <div class="col-md-6" id="homepage_pools"> 
        <!--
            <div class="panel panel-info" id="create_new_pool_button">
                <div class="panel-body" id="home_create_new_pool_body">
                    <div id="home_create_new_pool_content">
                        <div>
                            <h3><a href="new.php"><span class="home_create_new_pool_text">Create new pool</span></a></h3>
                        </div>
                    </div>
                </div>
            </div>
            <br>
        -->
        

<!--END CREATE NEW POOL BUTTON *******************************************************-->





<!--BEGIN PAST POOLS SECTION *******************************************************-->


            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Your past pools:</h3>
                </div>
                <div class="panel-body">
<?php
        if(count($completed_pools) > 0){ //if the user has completed pools
            rsort($completed_pools); //sort the array so that most recently created pools come first
            foreach($completed_pools as $pool_id => $pool_info){
                $pool_id = $pool_info['Pool ID'];
                $pool_url = "pool.php?pool_id=$pool_id"; //set pool URL - this is the default and will be overridden if necessary depending on what state the pool is in
                if($pool_info['Live?']==1 && $pool_info['Pool ended?'] ==1){
                    //if pool has ended:
                    $live_variable = "Pool has ended - Click to view pool results"; 
                    $status_styling = "color:black";
                    $rectangle_color = "black";
                }
                if($current_user == $pool_info['Leader ID']){
                    $leader_variable = "You";
                }
                else {
                    $leader_variable = $pool_info['Leader ID'];
                }
?>
                    <div class="row past_pool_list_item" id="past_pool_id_<?php echo $pool_id; ?>">
                        <div class="homepage_pool_list_rectangle" style="background-color:<?php echo $rectangle_color; ?>;"></div>
                        <div class="past_pool_list_item_title">
                            <a href=<?php echo $pool_url; ?>><?php echo $pool_info['Title']; ?></a>
                        </div>
                        <div class="past_pool_list_item_content">
                            <a href=<?php echo $pool_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a>
<?php
                if($admin == 1){ //if user is admin:
                    $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <br>
                            <a href=<?php echo $delete_pool_url; ?>>Delete Pool</a>
<?php
                }
?>
                            </div>
                        </div>
<?php
            } //END OF COMPLETED POOLS FOREACH STATEMENT
?>
                    <br>
<?php
        } //END OF COMPLETED POOLS IF STATEMENT
        else { //if use does not have any past pools
            echo "<h4>You don't have any past pools</h4>";
        }
?>
                </div>
            </div>


<!-- END PAST POOLS SECTION *******************************************************-->



<?php /*BEGIN INTERNAL TEMPLATE SECTION***********************************************/
    if($admin == 1){ //if user is an admin:
        $list_of_templates = $pool->GetAllTemplates();
?>
<!--************BEGIN TEMPLATES SECTION***********************************************-->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Templates (Internal - Admin only)</h3>
                </div>
                <div class="panel-body">
                    <h3 style="text-decoration:underline">Templates</h3>
                        <table border="1" style="width:95%">
                            <tr>
                                <th class="pool_top_row pool_column">Template</th>
                                <th class="pool_top_row pool_status_column">Status</th>
                                <th class="pool_top_row pool_leader_column">Score Template</th>
                                <th class="pool_top_row pool_leader_column">Delete Template</th>
                            </tr>
<?php
        foreach($list_of_templates as $template_id => $template_info){
            $edit_template_url = "edit_template.php?template_id=$template_id"; 

            if($template_info['Live?']==1){
                //if template has been published and is live:
                $live_variable = "Template is Live"; 
                $status_styling = "color:#5cb85c";
            }
            else{
                //if template has been not yet been published and is NOT live:
                $live_variable = "Template is not yet Live"; 
                $status_styling = "color:black";
            }
?>
                    <tr>
                        <td class="pool_row"><a href=<?php echo $edit_template_url; ?>><?php echo $template_info['Template Name']; ?></a></td>
                        <td class="pool_status"><a href=<?php echo $edit_template_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a></td>
<?php
            $score_template_url = "score_template_choices.php?template_id=".$template_info['Template ID'];
            $delete_template_url = "delete_template.php?template_id=$template_id";
?>
                        <td>
<?php
            if(isset($template_info['Template ID'])){ //display score_template_choices link 
?>                            
                            <a href=<?php echo $score_template_url; ?>>Score Template ID <?php echo $template_info['Template ID']; ?></a>
                            &nbsp;
<?php
            } 
?>
                        </td>
                        <td>
                            <a href=<?php echo $delete_template_url; ?>>Delete</a>
                        </td>
                    </tr>
<?php
            } //END OF TEMPLATES FOREACH STATEMENT
?>
                </table> 
            </div>
        </div>
                <br>

    <!--************END TEMPLATES SECTION***********************************************-->
<?php
    } //END OF "IF USER IS ADMIN" IF STATEMENT
?>






        </div>
<!--END 2ND COLUMN******************************************************************************-->





    </div> <!--END OF ROW DIV-->

</div><!--END OF MAIN_CONTAINER DIV-->


<?php
    include_once 'inc/close.php';
?>


<?php
/*

   <!--************BEGIN INACTIVE POOLS SECTION***********************************************-->
<?php
        if(count($inactive_pools) > 0){ //if the user has inactive pools
?>
                    <h3 style="text-decoration:underline">Your pools that still need to be finalized</h3>
                    <table border="1" style="width:95%">
                        <tr>
                            <th class="pool_top_row pool_column">Pool</th>
                            <th class="pool_top_row pool_status_column">Status</th>
                            <th class="pool_top_row pool_leader_column">Pool Leader</th>
                        </tr>
<?php
            foreach($inactive_pools as $pool_id => $pool_info){
                $pool_url = "pool.php?pool_id=$pool_id"; //set pool URL - this is the default and will be overridden if necessary depending on what state the pool is in
                if($pool_info['Ready for invites?']==0){ //if pool is still in edit mode and hasn't been finalized:
                    if($current_user == $pool_info['Leader ID']){ //check to make sure user is leader of the pool:
                        $pool_url = "edit_pool.php?pool_id=$pool_id";
                        $live_variable = "INACTIVE - Edit Pool";
                        $status_styling = "color:#d9534f";
                        $leader_variable = "You";
                    }
                }
?>
                        <tr>
                            <td class="pool_row"><a href=<?php echo $pool_url; ?>><?php echo $pool_info['Title']; ?></a></td>
                            <td class="pool_status"><a href=<?php echo $pool_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a></td>
                            <td class="pool_row"><?php echo $leader_variable; ?></td>
<?php
                if($current_user_id == 1){ //if user is user #1 (admin):
                    $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <td>
                                <a href=<?php echo $delete_pool_url; ?>>Delete</a>
                            </td>
<?php
                }
?>
                        </tr>
<?php
            } //END OF INACTIVE POOLS FOREACH STATEMENT
?>
                    </table> 
                    <br>
<?php
        } //END OF INACTIVE POOLS IF STATEMENT
?>
    <!--************END INACTIVE POOLS SECTION****************************************************--> 

*/
?>
