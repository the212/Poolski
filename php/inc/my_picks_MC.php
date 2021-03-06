<?php

/*
MY_PICKS MULTIPLE CHOICE PAGE
This page is included when user navigates to the "my picks" tab on the pool.php page
This page is only displayed if the given pool is a multiple choice pool
All of the necessary php variables are defined on the pool.php page
    EXCEPT for $user_picks_fetch and $tie_breaker_answer which are both run below when this page is included:

*/

    $user_picks_fetch = $pool->GetUserPicks($_SESSION['Username'], $pool_id);
    $tie_breaker_answer = $pool->GetTieBreakerAnswer($_SESSION['Username'], $pool_id);
        if(isset($tie_breaker_answer)){
            $tie_breaker_answer_display = $tie_breaker_answer;
        }
        else{ //if no tie breaker pick has been made
            $tie_breaker_answer_display = 0; //as of 2/19/14 we assume that the tie breaker answers will be numeric for all pools
        }
?>
    <script>
        //make the page reload every 5 min so that it will eventually become live automatically when the start time passes (if start time is set)
        //this will prevent someone from sitting on the mypicks page and editing their picks after the pool goes live
        setInterval('window.location.reload()', 300000);
    </script>

    <h3 style="text-decoration:underline"><?php echo $pool_fetch_result['Overall Question']; ?></h3> 
    <br>

<?php
        $category_counter = 1;
        //create list of saved pool categories and user's picks for given pool by walking through pool_categories array:
        foreach($pool_category_fetch as $category_id => $category_info){
            $category_choices = $pool->GetCategoryChoices($category_id); //store all of the multiple choices for given category in $category_choices array
            $pick_label_class = "label label-primary"; 
            $category_background_color = ""; //reset background color if a previous pick had changed it
            if(isset($user_picks_fetch[$category_id])) {
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
                //if no pick exists for the given category:
                $pick_display_value = "No Pick";
            }
            $category_correct_answer = $pool->GetCorrectChoiceForPoolCategory($category_id); //get correct answer for category if one exists
?>          
            <div class="well well-sm" id="category_<?php echo $category_counter; ?>" style="background-color:<?php echo $category_background_color; ?>">
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
                        <p><span style="font-weight:bold;font-size:80%">Your pick <?php if($pool_fetch_result['Live?']==0) {/*only display "click to edit" if pool is not yet live*/ echo "(click to edit):"; } ?>
                        </p>
                    </div>
                    <div class="col-md-4">
<?php
            if($pool_fetch_result['Live?']==0) {
?>
                        <div class="bfh-selectbox" id="<?php echo $category_info['Category ID']; ?>" data-name="selectbox1" data-value="<?php echo $pick_display_value; ?>" data-filter="true">
                            <div data-value='No Pick'><!--default dropdown value if no pick has been made-->
                            </div> 
<?php
                foreach($category_choices as $choice_number => $choice){ //put all of the given category's multiple choices in the bfh-selectbox dropdown menu
?>
                            <!--NEED TO ADD A CHECK FOR POOL BEING LIVE -->
                            <div data-value='<?php echo $choice; ?>'><?php echo $choice; ?> <!--display category choices in a drop down-->
                            </div> 
                        
<?php
                }
?>
                        </div>
<?php
            }
            else{
?>
                            <h3><span class="<?php echo $pick_label_class; ?>"><span class="display_pick" id="pick_for_category_<?php echo $category_info['Category ID']; ?>" style="font-weight:bold; white-space: normal;"><?php echo $pick_display_value ?></span></span></h3>
<?php
            }
?>
                        
                    </div>
                     <div class="col-md-2">
                        <h5>Correct Answer: </h5>
                        <span class="label label-primary" style="font-size:100%; white-space:normal; padding-left:0px; padding-right:0px;"><?php echo $category_correct_answer; ?></span>
                    </div>
                </div><!--END OF ROW DIV FOR CATEGORY-->
            </div>
<?php 
            $category_counter++;
        } //END OF POOL_CATEGORY_FETCH FOREACH STATEMENT
?>
    
    <div class="well well-sm">
        <h2 style="margin-left:50px; text-decoration:underline">Tie breaker question:</h2>
        <h4 style="margin-left:50px"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></h4> 
        <div id="tie-breaker" style="margin-left:50px;">            <!--  edit_tie_breaker_choice_template  -->
            <h3><span class="label label-info" style="margin-left:62px">&nbsp;<span class="<?php if($pool_fetch_result['Live?']==0){echo 'edit_tie_breaker_choice_template'; } else {echo 'display_pick';} ?>" id="tie_breaker_input" style="margin-left:0px">&nbsp;<?php echo $tie_breaker_answer_display;; ?>&nbsp; </span>&nbsp;</span></h3>
        </div>
    </div>
<?php

    //include 'inc/close.php';


    /*
    BELOW IS OLD TIE BREAKER INPUT (EDIT IN PLACE FUNCTIONALITY) - I REMOVED IT ON 2/19/14 IN ORDER TO FORCE THE USER TO ENTER A NUMBERIC INPUT
    
    <h3 style="margin-left:50px;"><span class="label label-info"><span id="tie_breaker_input" class="<?php if($pool_fetch_result['Live?']==0){echo 'edit_pick'; } else {echo 'display_pick';} ?>" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
    
    */
?>
