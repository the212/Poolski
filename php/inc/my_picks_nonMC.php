<?php

/*
MY_PICKS NON MULTIPLE CHOICE PAGE
This page is included when user navigates to the "my picks" tab on the pool.php page
All of the necessary php variables are defined on the pool.php page
    EXCEPT for $user_picks_fetch and $tie_breaker_answer which are both run below when this page is included:

*/
    $user_picks_fetch = $pool->GetUserPicks($_SESSION['Username'], $pool_id);
    $tie_breaker_answer = $pool->GetTieBreakerAnswer($_SESSION['Username'], $pool_id);
        if(isset($tie_breaker_answer)){
            $tie_breaker_answer_display = $tie_breaker_answer;
        }
        else{
            $tie_breaker_answer_display = "**Enter your tie-breaker answer here!**";
        }
?>

    <h3 style="text-decoration:underline"><?php echo $pool_fetch_result['Overall Question']; ?></h3> 
    <br>
<?php
        if(isset($pool_fetch_result['Pool Winner'])) {
            $user_id = $user->GetUserIDFromEmail($_SESSION['Username']); //get current user id from session variable
?>
    <h3>Your point total is: <span class='label label-primary'><?php echo $pool_rankings_array[$user_id]; ?></span> 
    </h3><br>
<?php
        }

        $category_counter = 1;
        //create list of saved pool categories and user's picks for given pool by walking through pool_categories array:
        foreach($pool_category_fetch as $category_id => $category_info){
            if(isset($user_picks_fetch[$category_id])) {
                $pick_label_class = "label label-primary";
                //if a pick already exists for given category, we store it in the pick_display_value variable
                $pick_display_array = explode('|', $user_picks_fetch[$category_id]); //separate out correct/incorrect status from pick if status is set (separated from pick value by | delimiter)
                $pick_display_value = $pick_display_array[0];
                if($pick_display_array[1] == 1){
                    //PICK IS CORRECT:
                    $category_background_color = "#5cb85c";
                    $pick_label_class = "label label-success";
                }
                elseif(isset($pick_display_array[1])) { //PHP will think that it is equal to zero even if it is not set, so we need to use isset here - if it is set, it means that it is equal to zero since we made it past the 1st if statement
                    //PICK IS INCORRECT:
                    $category_background_color = "#d9534f";
                    $pick_label_class = "label label-danger";
                }
            }
            else{
                if($pool_fetch_result['Live?'] == 1) {
                    //if pool is live and pick is not set, we do not instruct the user to edit their pick:
                    $pick_display_value = "**Pick not set**";
                }
                else {
                    //if no pick exists for the given category and pool is not yet live, we instruct the user to make one:
                    $pick_display_value = "**Pick not set - click here to make your pick! **";
                }
            }
?>          
            <div class="well well-sm" id="category_<?php echo $category_counter; ?>" style="background-color:<?php echo $category_background_color; ?>;">
                 <div class="row">
                    <div class="col-md-3">
                        <h4 id="category_n_span<?php echo $category_info['Category ID']; ?>"> <?php echo $category_info['Category Name']; ?> &nbsp; 
                        </h4>
                    </div>
                    <div class="col-md-2">
                        <h4><span id="category_p_span<?php echo $category_info['Category ID']; ?>">Point Value: <?php echo $category_info['Category Point Value']; ?></span>
                        </h4>
                    </div>
                    <div class="col-md-2">
                        <h4><span style="font-weight:bold;">Your pick <?php if($pool_fetch_result['Live?']==0) {/*only display "click to edit" if pool is not yet live*/ echo "(click to edit):"; } ?>
                        </h4>
                    </div>
                    <div class="col-md-5">
<?php
                    if($pool_fetch_result['Live?']==0) { //if pool is not live, make the pick editable:
?>
                        <h3><span class="label label-info"><span class="edit_pick" id="pick_for_category_<?php echo $category_info['Category ID']; ?>" style="font-weight:bold;"><?php echo $pick_display_value ?></span></span></span></h3>
<?php
                    }
                    else { //if pool is live, do not display editable pick:
?>
                        <h2><span class="<?php echo $pick_label_class; ?>"><span class="display_pick" id="pick_for_category_<?php echo $category_info['Category ID']; ?>" style="font-weight:bold; white-space:pre-line;"><?php echo $pick_display_value ?></span></span></span></h2>
<?php
                    }
?>
                    </div>
                </div>
            </div>
<?php 
        $category_counter++;
        }
?>
    
    <br>
    <div class="well well-sm" style="width:60%;">
        <h3 style="margin-left:50px; text-decoration:underline">Tie breaker question:</h3>
        <p style="margin-left:50px"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></p> 
        <div id="tie-breaker" style="margin-left:50px;">
<?php
            if($pool_fetch_result['Live?']==0) {
?>
            <h3 style="margin-left:50px;"><span class="label label-info"><span id="tie_breaker_input" class="edit_pick" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
<?php
            }
            else {
?>
            <h3 style="margin-left:50px;"><span class="label label-primary"><span id="tie_breaker_input" class="display_pick" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
<?php
            }
?>
        </div>
    </div>
<?php
    //include 'inc/close.php';
?>

