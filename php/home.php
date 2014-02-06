<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Home";
    include_once "inc/header.php";


    include_once 'inc/class.pool.inc.php';
    $current_user = $_SESSION['Username'];
    $pool = new Pool(); 
    $active_pools = $pool->GetActivePool($current_user); 
    $all_pools = $pool->GetAllPools($current_user); 
    $leader_pools = $pool->GetLeaderPools($current_user);
    //get the number of active pools for the user:
    $number_of_active_pools = count($active_pools);
    //get the total number of all pools that a user is in, active or inactive:
    $number_of_total_pools = count($all_pools);
?>
    <script>
    function Delete_Pool(pool_id){
        if(confirm("Delete pool "+pool_id+"?")){
            $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {delete_pool_id: pool_id}
            })
                .done(function(){ //when ajax request completes
                    location.reload();
                });
        }
    }
    </script>
    <!--GET RID OF THIS AFTER TIMEZONE TESTING IS DONE<span style="margin-left:25px;">**Time zone is: <?php echo $_SESSION['time'] ?> !!**</span></p>-->
    <div class="container">
        <div class="row" style="border-style:none">
            <div>
                <div class="row">
<?php
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser(); 
    $current_user_id = $user->GetUserIDFromEmail($current_user);
    $pool_invites_result = $user->CheckPoolInvites($current_user);
    //BEGIN CHECK FOR POOL INVITES IF STATEMENT
    if($pool_invites_result == "0"){ //if user does not have any invites, we don't display anything
        echo ""; 
    } 
    else {
?>
                    <h3><a id="show_invites_link" style="cursor:pointer;">You have been invited to a new pool!  Click here to see your list of invites.</a></span></h3>
                    <br>
                    <div id='pool_invite_list' style='display:none'>
                        <h4>Pool invites:</h4>
<?php
        foreach($pool_invites_result as $index => $pool_id){
            $given_pool_data = $pool->GetPoolData($pool_id);
            if($given_pool_data == 0){ //if pool id doesn't exist in DB:
                echo "";
            }
            else{ //if we are able to find a pool for the given pool id:
?>
                        <span id="pool_span_<?php echo $pool_id; ?>"style="margin-left:30px; font-weight:bold;"><p><?php echo $given_pool_data['Title']; ?></p>
                            <input class="accept_invite_button" type='button' onclick="accept_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Accept'>
                            <input type='button' onclick="decline_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Decline'>
                            <br>
                        </span>
<?php
            }
        } //END OF POOL INVITE LIST FOREACH STATEMENT
?>
                    <br>
                    </div>
<?php
    } //END OF CHECK FOR POOL INVITES IF STATEMENT

    //BEGIN USER'S POOL LIST
    if($number_of_total_pools==0){
        //if user doesn't have any pools:
        echo "<br><h3 style='text-decoration:underline'>You do not currently have any active pools</h3>";
        echo "<p>Perhaps you would like to <a href='create_new.php'>create a new one?</a></p>";
    }
    else {
        //if user does have pools:       
?>
                    <h3 style="text-decoration:underline">Your Active Pools</h3>
                    <table border="1" style="width:95%">
                        <tr>
                            <th class="pool_top_row">Pool</th>
                            <th class="pool_top_row">Status</th>
                            <th class="pool_top_row">Pool Leader</th>
                        </tr>
<?php
        foreach($all_pools as $pool_id => $pool_info){
            $pool_url = "pool.php?pool_id=$pool_id"; //set pool URL - this is the default and will be overridden if necessary depending on what state the pool is in
            if($pool_info['Ready for invites?']==0){ //if pool is still in edit mode and hasn't been finalized:
                if($current_user == $pool_info['Leader ID']){ //check to make sure user is leader of the pool:
                    $pool_url = "edit_pool.php?pool_id=$pool_id";
                    $live_variable = "INACTIVE - Edit Pool";
                    $status_styling = "color:#d9534f";
                }
            }
            if($pool_info['Ready for invites?']==1 && $pool_info['Live?']==0){
                //if pool has been submitted, but is not live yet:
                $live_variable = "Pool has not started yet - Click to make your picks!"; 
                $status_styling = "color:#f0ad4e";
            }
            if($pool_info['Live?']==1 && $pool_info['Pool ended?']==0){
                //if pool is ready for invites and is live also:
                $live_variable = "Pool is live - Click to view pool!"; 
                $status_styling = "color:#5cb85c;";
            }
            if($pool_info['Live?']==1 && $pool_info['Pool ended?'] ==1){
                //if pool has ended:
                $live_variable = "Pool has ended - Click to view pool results"; 
                $status_styling = "color:#808080";
            }
            
            if($current_user == $pool_info['Leader ID']){
                $leader_variable = "You";
            }
            else {
                $leader_variable = $pool_info['Leader ID'];
            }
?>
                        <tr>
                            <td class="pool_row"><a href=<?php echo $pool_url; ?>><?php echo $pool_info['Title']; ?></a></td>
                            <td class="pool_status"><a href=<?php echo $pool_url; ?> style="<?php echo $status_styling; ?>"><?php echo $live_variable; ?></a></td>
                            <td class="pool_row"><?php echo $leader_variable; ?></td>
<?php
                        if($current_user_id == 1){
                            $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <td><a href=<?php echo $delete_pool_url; ?>>Delete</a></td>
<?php
                        }
?>
                        </tr>
<?php
            
        }
?>
                    </table>
                    <br>
                </div>
<?php
    } //END OF ACTIVE POOL LIST
?>
            </div> <!--END OF ACTIVE POOLS DIV-->
        </div><!--END OF ROW DIV-->
    </div>

<?php //following is only for testing email purposes
    if($current_user_id == 1){
        echo "<a href='send_mail_test.php'>Click here to test sending an email!</a>";
    }
?>
<?php
    include_once 'inc/close.php';
?>
