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
                <div class="row" style="padding-left:20px; padding-right:20px;">
<?php
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser(); 
    $current_user_id = $user->GetUserIDFromEmail($current_user);
    $pool_invites_result = $user->CheckPoolInvites($current_user);
    //BEGIN CHECK FOR POOL INVITES IF STATEMENT
    if($pool_invites_result <> "0"){ //if user has pool invites pending:
?>
                    <br><br>
                    <h2 style='text-decoration:underline; word-wrap:break-word;'>** You have been invited to a new pool! **</h2>
                    <h4 style="margin-left: 20px;"><span class='label label-warning'><a id="show_invites_link" style="cursor:pointer;">Click here to see your list of invites.</a></span></h4>
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
                        <span id="pool_span_<?php echo $pool_id; ?>"style="margin-left:30px; font-weight:bold;"><p><?php echo $given_pool_data['Title']; ?></p>
                            <input class="accept_invite_button" type='button' onclick="accept_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Join Pool'>
                            <input type='button' onclick="decline_invite(<?php echo $current_user_id.", ".$given_pool_data['Pool ID']; ?>)" value='Decline Invite'>
                            <br>
                        </span>
<?php
            }
        } //END OF POOL INVITE LIST FOREACH STATEMENT
?>
                    <br>
                    </div>
                    <br>
<?php
    } //END OF CHECK FOR POOL INVITES IF STATEMENT

    //BEGIN USER'S POOL LIST
    if($number_of_total_pools<>0){ //if user has pools:      
?>
                    <h2 style="text-decoration:underline">Your Active Pools</h2>
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
                $status_styling = "color:black";
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
                        if($current_user_id == 1){ //if user is user #1 (admin):
                            $score_template_url = "score_template_choices.php?template_id=".$pool_info['Template ID'];
                            $delete_pool_url = "delete_pool.php?pool_id=$pool_id";
?>
                            <td>
<?php
                            if(isset($pool_info['Template ID'])){ //display score_template_choices link only if given pool is a template:
?>                            
                                <a href=<?php echo $score_template_url; ?>>Score Template ID <?php echo $pool_info['Template ID']; ?></a>
                                &nbsp;
<?php
                            } 
?>
                                <a href=<?php echo $delete_pool_url; ?>>Delete</a>
                            </td>
<?php
                        }
?>
                        </tr>
<?php
        }
?>
                    </table>
                    <br>
                </div> <!--end of row div-->
<?php
    }
    else{
        //if user does NOT have any pools or pool invites pending:
        echo "<br><h2 style='text-decoration:underline'>You do not currently have any active pools</h2>";
        echo "<h4>Perhaps you would like to <a href='new.php'>create a new one?</a></h4>";
    } //END OF ACTIVE POOL LIST
?>
            </div> <!--END OF ACTIVE POOLS DIV-->
        </div><!--END OF ROW DIV-->
    </div>

<?php
    include_once 'inc/close.php';
?>
