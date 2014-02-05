<?php

/*
Pool Members page
This page is included when user navigates to the "pool members" tab on the pool.php page
All of the necessary php variables are defined on the pool.php page

TO DO as of 5:00 pm 11/22/13:
    -CREATE FUNCTIONALITY TO ALLOW USERS TO VIEW OTHER USERS' PICKS ONCE POOL IS LIVE
*/

$pool_members_id_array = $pool->GetPoolMembers($pool_id); //store all of the user_id's of the pool member's in pool_member_id_array
include_once 'inc/class.users.inc.php';
$user = new SiteUser();
$pool_members_array = array();
?>


<div class="pool_members_container" style="position: absolute;">
    <h1>Pool Members</h1>
    <br>
    <table style="margin-left:20px">
        <tr>
            <th class="pool_member_table_nickname" style="width:20%; text-decoration:underline">Pool Nickname</th>
            <th class="pool_member_table_email" style="width:25%; text-decoration:underline;">Email Address</th> <!--DO WE NEED EMAIL ADDRESS HERE?-->
            <th class="pool_member_table_tie" style="width:20%; text-decoration:underline">Tie-breaker choice</th>
            <th class="pool_member_table_picks" style="width:20%; text-decoration:underline">User's picks</th>
            <th class="pool_member_table_score" style="width:15%; text-decoration:underline">Score</th>
        </tr>
<?php
    foreach($pool_members_id_array as $user_id => $tie_breaker_answer){
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
            $pool_scores_result = $pool->CalculatePoolScore($pool_id); //generate current pool scores
            if(isset($pool_fetch_result['Pool Winner'])) { //if pool has ended and has been scored, display user's score:
            echo $pool_rankings_array[$user_id];
            }
            else { //if pool has not yet ended, show the user's current score:
                echo $pool_scores_result[$user_id];
            }
?>
            </td>
        </tr>
<?php 
    } 
?>
    </table>
</div> <!--END OF POOL MEMBERS CONTAINER-->
<br>

<!--****************************************************-->

<!--USER PICKS DIV - THIS WILL ANIMATE ONTO SCREEN WHEN USER CLICKS THE "SHOW PICKS" LINK FOR A GIVEN USER-->

<?php
    if($pool_fetch_result['Live?']==1) { //only create the "user_picks_container" div if pool is live (we don't want to show user picks otherwise)
?>
<div class="user_picks_container" style="position: relative; right:-100%; display:none;">
    <h4><a href='JAVASCRIPT:hideUserPicks();'>Back</a></h4>
    <h3><span class="user_for_user_picks"></span>'s Picks:</h3>
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
                <span class="label label-primary" style="white-space:normal;"><?php echo $category_correct_answer; ?></span>
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

