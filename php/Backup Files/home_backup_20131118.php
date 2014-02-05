<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Home";
    include_once "inc/header.php";


    include_once 'inc/class.pool.inc.php';
    $current_user = $_SESSION['Username'];
    $pool = new Pool(); //new instance of the Pool class
    $active_pools = $pool->GetActivePool($current_user); //get pool data based on pool ID that is passed in URL
    $leader_pools = $pool->GetAllPools($current_user);
    //get the number of active pools for the user:
    $number_of_active_pools = count($active_pools);
    $number_of_leader_pools = count($leader_pools);
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
<?php
    
?>
        <p>You are currently <strong>logged in.</strong></p>
        <h3><a href="create_new.php">Click here to create a new pool</a></h3>
        <br>
        <div id="test1"></div>
           
<?php
    if($number_of_leader_pools==0){
        //if user doesn't have any pools:
        echo "<p></p>";
    }
    else{
        //if user does have pools:
?>
        <h3 style="text-decoration:underline">You are the leader of the below pools which still need to be finalized:</h3>
        <table border="1">
            <tr>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Pool Name</td>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Status</td>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Action</td>
            </tr>
<?php
        //create a table of pools that the given user is the leader of
        foreach($leader_pools as $pool_id => $pool_info){
            if($pool_info['Leader ID'] == $current_user){
                //check if pool is live - if not, instruct the user to make their picks
                if($pool_info['Ready for invites?']==0){
                    //if pool has not yet been submitted, we offer a link to the edit_pool page for the given pool:
                    $pool_url = "edit_pool.php?pool_id=$pool_id";
                    $live_variable = "Pool has not yet been submitted - click here to edit pool settings";
?>
                <tr>
                <td class="pool_row" style="padding:10px"><?php echo $pool_info['Title']; ?></td>
                <td class="pool_row" style="padding:10px"><a href=<?php echo $pool_url; ?>><?php echo $live_variable; ?></a></td>
                <td class="pool_row" style="padding:10px"><a href="javascript:Delete_Pool(<?php echo $pool_id; ?>)">Delete Pool</a></td>
                </tr>
<?php
                }  
            }
        }
?>
        </table>
<?php
    }

    if($number_of_active_pools==0){
        //if user doesn't have any pools:
        echo "<br><h3 style='text-decoration:underline'>You do not currently have any active pools</h3>";
        echo "<p>Perhaps you would like to <a href='create_new.php'>create a new one?</a></p>";
    }
    else{
        //if user does have pools:

?>
        </table>
        <br>
        <h3 style="text-decoration:underline">Your currently active pools</h3>
        <table border="1">
            <tr>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Pool Name</td>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Description</td>
                <td class="pool_top_row" style="padding:10px; font-weight:bold">Status</td>
            </tr>
<?php
        //create table of active pools for user by walking through active_pools array:
        foreach($active_pools as $pool_id => $pool_info){
            //create URL to use for each pool's link:
            $pool_url = "'pool.php?pool_id=$pool_id'";
            //check if pool is live - if not, instruct the user to make their picks
            if($pool_info['Live?']==0){
                $live_variable = "Pool is not live - Click to make your picks!";
            }
            else{
                $live_variable = "Pool is live - Click to view pool";
            }
?>
                <tr>
                <td class="pool_row" style="padding:10px"><?php echo $pool_info['Title']; ?></td>
                <td class="pool_row" style="padding:10px"><?php echo $pool_info['Description']; ?></td>
                <td class="pool_row" style="padding:10px"><a href=<?php echo $pool_url; ?>><?php echo $live_variable; ?></a></td>
                </tr>
<?php 
        }
    
?>
        </table>

<?php
    }
   
    //include_once 'common/close.php';
?>
