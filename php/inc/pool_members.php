<?php

/*
Pool Members page
This page is included when user navigates to the "pool members" tab on the pool.php page
All of the necessary php variables are defined on the pool.php page
*/

$pool_members_id_array = $pool->GetPoolMembers($pool_id); //store all of the user_id's of the pool member's in pool_member_id_array
include_once 'inc/class.users.inc.php';
$user = new SiteUser();
$pool_members_array = array();
if($pool_fetch_result['Pool ended?']==1){ //if pool has ended:
    $pool_members_array_for_table = $pool_rankings_array; //generate the pool members table using final rankings array (GetFinalPoolRankings method in pool class)
    $pool_member_table_rank_style = "text-decoration:underline; width:8%"; //show rank column in table
    $pool_member_table_score_style = "text-decoration:underline; width:7%;"; //adjust score column to share width with rank column 
}
else{ //if pool has not ended:
    $pool_members_array_for_table = $pool_members_id_array; 
    $pool_member_table_rank_style = "text-decoration:underline; width:8%"; //show rank column in table
    $pool_member_table_score_style = "text-decoration:underline; width:15%;"; //give score column in table full 15% width since it doesn't share with rank column
}
?>

<div class="pool_members_container">
    <br>
    <h1>Pool Members</h1>
<?php
            //BEGIN POOL NICKNAME LOGIC
            $user_nickname = $pool->GetNickname($current_user_id, $pool_id);
            if ($pool_fetch_result['Live?']==0) {
?>
                <h4>Choose your nickname for this pool: <span class="label label-info"><span class='edit_nickname' id='update_nickname'><?php echo $user_nickname; ?></span></span><span style="margin-left:15px; font-style:italic; font-size:70%;">(Click to edit)</span></h4>
<?php
            }
            //END POOL NICKNAME LOGIC
?>
    <br>
    <table border="1" style="margin-left:20px">
        <tr>
            <th class="pool_member_table_rank" style="<?php echo $pool_member_table_rank_style; ?>">Rank</th>
            <th class="pool_member_table_nickname" style="width:20%; text-decoration:underline">Pool Nickname</th>
            <th class="pool_member_table_email" style="width:25%; text-decoration:underline;">Email Address</th> <!--DO WE NEED EMAIL ADDRESS HERE?-->
            <th class="pool_member_table_tie" style="width:20%; text-decoration:underline">Tie-breaker choice</th>
            <th class="pool_member_table_picks" style="width:20%; text-decoration:underline">User's picks</th>
            <th class="pool_member_table_score" style="<?php echo $pool_member_table_score_style; ?>">Score</th>
        </tr>
<?php
    $pool_scores_result = $pool->CalculatePoolScore($pool_id); //generate current pool scores (this comes as sorted from highest scorer to lowest)
    $counter = 0; //define this so we know which iteration of the below foreach statement we are in 
    $counter_interval = 0; //we use this to keep track of the number of users tied in a given position.  
        //for instance, if 3 users all have the same score, this value will become 3 as we go thru each of the 3 tied users' ranks
        //this way, when we get to the next user after the 3 tied users, we can make that users rank 3 more than the tied users' rank
    $pool_sorting_array = array();
    //below foreach will sort the array of pool members according to their score:
    foreach($pool_scores_result as $user_id => $user_score){ 
        $pool_sorting_array[$user_id] = $pool_members_array_for_table[$user_id];
    }
    $pool_sorting_array_keys = array_keys($pool_sorting_array);
    //now that we have the pool members sorted based on their score, let's create the pool members table:
    foreach($pool_sorting_array as $user_id => $tie_breaker_answer){
        $user_info = $user->GetUserInfo($user_id); //get the given user's username and email address and store them in the user_info array
        if($pool_members_id_array[$user_id]['Nickname'] == "no_nickname"){
            $nickname = $user_info['Email Address'];
        }
        else{
            $nickname = $pool_members_id_array[$user_id]['Nickname'];
        }
        $pool_members_array[$user_id]=$user_info; //stores user's username and email address in pool_members_array (array key is user id).  THIS CAN BE USED OUTSIDE OF FOREACH LOOP
?>
        <tr>
            <td class="pool_member_table_rank" style="<?php echo $pool_member_table_rank_style; ?>"><?php echo $counter+1; ?></td>
            <td class="pool_member_table_nickname"><?php echo($nickname); ?></td>
            <td class="pool_member_table_email"><?php echo($user_info['Email Address']); ?></td> <!--DO WE NEED EMAIL ADDRESS HERE?-->
            <td class="pool_member_table_tie"> <!--Tie breaker-->
<?php 
        if($pool_fetch_result['Live?']==0){
            echo "<span>Answer hidden until pool begins</span>";
        }
        else {
            echo($pool_members_id_array[$user_id]['Tie-breaker Answer']); 
        }
?>
            </td>
            <td class="pool_member_table_picks">
<?php 
        if($pool_fetch_result['Live?']==0){
            echo "<span>Picks hidden until pool begins</span>";
        }
        else{
            echo("<a href='JAVASCRIPT:showUserPicks($user_id, $pool_id, &apos;$nickname&apos;);'>See picks</a>"); 
        }
?>
            </td>
            <td class="pool_member_table_score">
<?php      
            
            if($pool_fetch_result['Pool ended?'] == 1) { //if pool has ended and has been scored, display user's score:
                echo $pool_rankings_array[$user_id];
            }
            else { //if pool has not yet ended, show the user's current score:
                echo $pool_scores_result[$user_id];
            }
?>
            </td>
        </tr>
<?php
        //BEGIN LOGIC FOR CREATING NEXT RANK IN TABLE:
        $current_key = array_search($user_id, $pool_sorting_array_keys); //get the number in the pool_sorting_array_keys array associated with the given user ID
        $next_user_key = $current_key + 1; //add one to the number (this gives us the key for the next user in the pool_sorting_array)
        $next_user_id = $pool_sorting_array_keys[$next_user_key]; //get the user ID of the user who is next up in the foreach statement
        $next_user_score = $pool_scores_result[$next_user_id]; //get the score of the user who is next up in the foreach statement
        if($pool_scores_result[$user_id] == $next_user_score){ //if the score of the user who is next up in the foreach statement is equal to the current user's (meaning they are tied):
            if($counter_interval == 0){ //if this is the first user in a set of tying users:
                $counter_interval = $counter + 1; //e.g., if two people tie for 2nd, we make counter_interval equal to 3 for the foreach loop for the 2nd of the two tied users
            } 
            $counter_interval++;
                //e.g., if the 1st place user is tied with the 2nd place user, we make $counter_interval equal to 2 initially and then add 1 to it to get 3 when we go thru the 2nd user's foreach loop
                //if the 2nd place user is tied with the 3rd place user (meaning the top 3 users are all tied), $counter_interval becomes 4 when going thru the 3rd user's foreach loop
        }
        else{ //if the score of the user who is next up in the foreach array is NOT equal to the current user's score
            if($counter_interval <> 0){ //if we are coming off tying ranks, add the counter_interval to counter variable to get appropriate rank for next user:
                $counter = $counter_interval;
                $counter_interval = 0; //reset $counter_interval to 0 since we are done with the tying ranks for now
            }
            else{ //if we are not coming off tying ranks, simply increase the counter by 1:
                $counter++;
            }
        }
        //END LOGIC FOR CREATING NEXT RANK IN TABLE
    } 
?>
    </table>
<br>

<?php
        if($pool_fetch_result['Pool ended?'] == 1) { //only display tie breaker correct answer if pool has ended:
?>
    <h3 style="text-decoration:underline;">Tie-Breaker correct answer:</h3>
<?php
            echo "<h4>".$pool_fetch_result['Tie-Breaker Question']."</h4>";
            echo "<h4 style='margin-left:40px;'>Correct Answer: <span class='label label-info' style='font-size:110%;'>".$pool_fetch_result['Tie-Breaker Correct Answer']."</span></h4>";
        }
?>
<br><br>
</div> <!--END OF POOL MEMBERS CONTAINER-->

<!--****************************************************-->

<!--USER PICKS DIV - THIS WILL ANIMATE ONTO SCREEN WHEN USER CLICKS THE "SHOW PICKS" LINK FOR A GIVEN USER-->

<?php
    if($pool_fetch_result['Live?']==1) { //only create the "user_picks_container" div if pool is live (we don't want to show user picks otherwise)
?>

<div class="user_picks_container" style="position: relative; right:-100%; display:none; padding-left:80px; padding-right:25px;">
    <h2 id="see_user_picks_back_button"><a href='JAVASCRIPT:hideUserPicks();'>Back</a></h2>
    <h2><span class="user_for_user_picks"></span>'s Picks:</h2>
    <br>
    <h4><?php echo $pool_fetch_result['Overall Question']; ?></h4> 
<?php
        $category_counter = 1;
        //create list of saved pool categories and user's picks for given pool by walking through pool_categories array:
        foreach($pool_category_fetch as $category_id => $category_info){
        $category_correct_answer = $pool->GetCorrectChoiceForPoolCategory($category_id);     
?>          
    <div class="well well-sm" id="category_<?php echo $category_counter; ?>" style="background:transparent;">
         <div class="row">
            <div class="col-md-3">
                <h4 id="category_n_span<?php echo $category_info['Category ID']; ?>"> <?php echo $category_info['Category Name']; ?> &nbsp; 
                </h4>
            </div>
            <div class="col-md-2">
                <h4><span id="category_p_span<?php echo $category_info['Category ID']; ?>">Point Value: <?php echo $category_info['Category Point Value']; ?></span>
                </h4>
            </div>
            <div class="col-md-1">
                <h4><span style="font-weight:bold;">Pick:</span></h4>
            </div>
            <div class="col-md-4">
                <h3><span class="label label-primary"><span class="display_user_pick" id="display_user_pick_for_category_<?php echo $category_info['Category ID']; ?>" style="font-weight:bold; white-space: normal;"><?php echo "**No Pick**" ?></span></span></h3>
            </div>
<?php
            if(isset($pool_fetch_result['Template ID'])){
?>
            <div class="col-md-2">
                <h5>Correct Answer: </h5>
                <span class="label label-primary" style="white-space:normal; padding-left:0px; padding-right:0px"><?php echo $category_correct_answer; ?></span>
            </div>
<?php
            }
?>
        </div>
    </div>
<?php 
        $category_counter++;
        }
?>
    <br>
</div>
<!--END OF USER PICKS DIV-->

<?php
    }
?>

