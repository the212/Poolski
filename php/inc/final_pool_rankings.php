<?php

/*
Final Pool Rankings page
This page is included on the pool.php page when pool has ended and a pool winner has been entered in the "Pool" table
*/

    $pool_members_id_array = $pool->GetPoolMembers($pool_id); //store all of the user_id's of the pool member's in pool_member_id_array
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser();
    $pool_members_array = array();
?>

<div class="pool_members_container">
    <br>
    <h3 style="text-decoration:underline;">Pool Results</h3>
    <table border="1" style="margin-left:20px">
        <tr>
            <th class="pool_member_table_rank" style="width:10%; text-decoration:underline">Rank</th>
            <th class="pool_member_table_nickname" style="width:30%; text-decoration:underline">Pool Nickname</th>
            <th class="pool_member_table_username" style="width:20%; text-decoration:underline">Username</th>
            <th class="pool_member_table_score" style="width:20%; text-decoration:underline">Pool Score</th>
            <th class="pool_member_table_tie_breaker" style="width:20%; text-decoration:underline">Tie Breaker: <?php echo $pool_fetch_result['Tie-Breaker Question']; ?></th>
        </tr>
<?php
    $counter = 0; //define this so we know which iteration of the below foreach statement we are in
    foreach($pool_rankings_array as $user_id => $user_score){
        $user_info = $user->GetUserInfo($user_id); //get the given user's username and email address and store them in the user_info array
        $user_tie_breaker = $pool->GetTieBreakerAnswer($user_id, $pool_id); //get user's tie breaker answer
        //BEGIN NICK NAME LOGIC
        if($pool_members_id_array[$user_id]['Nickname'] == "no_nickname"){
            $nickname = $user_info['Username'];
        }
        else{
            $nickname = $pool_members_id_array[$user_id]['Nickname'];
        }
        //END NICKNAME LOGIC
        $pool_members_array[$user_id]=$user_info; //stores user's username and email address in pool_members_array (array key is user id).  THIS CAN BE USED OUTSIDE OF FOREACH LOOP
?>
        <tr>
            <td class="pool_member_table_rank"><?php echo $counter+1; ?></td>
            <td class="pool_member_table_nickname"><?php echo $nickname; ?></td>
            <td class="pool_member_table_username"><?php echo $user_info['Email Address'] /*change this from email address to username at some point?*/; ?></td>
            <td class="pool_member_table_score"><?php echo $user_score; ?></td>
            <td class="pool_member_table_tie_breaker"><?php echo $user_tie_breaker; ?></td>
        </tr>
    <?php 
        $counter++;
    } //END OF FOREACH STATEMENT
?>
    </table>
    <br><br><br>
    <h3>Tie Breaker Correct Answer: </h3>
    <h4><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></h4>
    <h4 style="margin-left:50px;">Correct answer: <span class="label label-info" style="font-size:110%;"><?php echo $pool_fetch_result['Tie-Breaker Correct Answer']; ?></span></h4>
<?php
    include 'inc/close.php';
?>
</div> <!--END OF POOL MEMBERS CONTAINER-->


<!--****************************************************-->

