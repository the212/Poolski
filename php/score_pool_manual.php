<?php

include_once "inc/loggedin_check.php";
include_once "inc/constants.inc.php";
$pageTitle = "Score Pool";
include_once "inc/header.php";


if(isset($_GET['pool_id'])) { //if a pool ID is specified in URL:
    include_once 'inc/class.pool.inc.php';
    include_once 'inc/class.users.inc.php';
    $pool = new Pool(); //new instance of the Pool class
    $user = new SiteUser();
    $pool_id = $_GET['pool_id']; //get pool ID from URL
    $pool_fetch_result = $pool->GetPoolData($pool_id);
    $pool_leader_id = $pool->GetUserIDFromEmail($pool_fetch_result['Leader ID']);
    $current_user_id = $pool->GetUserIDFromEmail($_SESSION['Username']);
    if($pool_leader_id !== $current_user_id){
        header("Location: home.php");
    }
    $pool_category_fetch = $pool->GetPoolCategoryData($pool_id); //get pool category data
    $pool_members_id_array = $pool->GetPoolMembers($pool_id); //store all of the user_id's of the pool member's in pool_member_id_array
    $pool_members_id_array_keys = array_keys($pool_members_id_array); //store pool member id's into the pool_members_id_array_keys array
}
else {
    //if no pool ID is specified in URL, return the user to the homepage:
    header("Location: home.php");
}
    if($pool_fetch_result['Multiple Choice?'] == 0){

//*************************************BEGIN NON-MULTIPLE CHOICE SECTION*********************************
?>
        <span id="pool_id_span" style="display:none"><?php echo $pool_id; ?></span>
        <h2 style="text-decoration:underline">Tally Pool Score</h2> 
        <p>Choose either "Correct" or "Incorrect" for each participant's picks in order to tally the scores.  Picks that were not made by a participant are automatically counted as "Incorrect"</p>
        <br>
        <div class="row">
            <div class="col-md-5">
                <h4 style="text-decoration:underline"><?php echo $pool_fetch_result['Overall Question']; ?></h4> 
            </div>
            <div class="col-md-7">
<?php
        if($pool_fetch_result['Pool ended?'] == 1){ //only show "finish and calculate" button if pool has ended
?>
                <h4><input type="button" id="score_pool_button" class="btn btn-warning btn-lg" value="Finish and Calculate Pool Score" onclick="JAVASCRIPT:CalculatePoolScoreValidate(<?php echo $pool_id; ?>, 1);" /></h4>
<?php
        }
?>
            </div>
        </div>
        <br>

        <!--BEGIN TIE BREAKER INPUT-->
        <div class="well well-sm">
            <h3 style="margin-left:50px; text-decoration:underline">Set correct tie breaker value here::</h3>
            <p style="margin-left:50px"><?php echo $template_fetch_result['Tie Breaker Question']; ?></p> 
            <div id="tie-breaker" style="margin-left:50px;">
<?php
    $custom_pool_tie_breaker_answer = $pool->GetCustomPoolTieBreakerAnswer($pool_id);
    if(isset($custom_pool_tie_breaker_answer)){
        $tie_breaker_answer_display = $custom_pool_tie_breaker_answer;
    }
    else{
        $tie_breaker_answer_display = "**Enter correct tie-breaker value here**";
    }
?>
                <h3 style="margin-left:50px;"><span class="label label-primary"><span id="tie_breaker_input" class="edit_custom_pool_tie_breaker" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
            </div>
        </div>
        <br>
        <!--END TIE BREAKER INPUT-->

<?php
        foreach ($pool_members_id_array_keys as $each_user => $user_id){ //generate list of user picks for each user in the pool ($each_user is the key of the pool_members_id_array_keys array and starts at 0)
            $user_info = $user->GetUserInfo($user_id); //get the given user's username and email address and store them in the user_info array
            if($pool_members_id_array[$user_id]['Nickname'] == "no_nickname"){ //if user does not have a nickname for the pool
                $nickname = $user_info['Email Address']; //set user's display name to be their email address is they have no nickname
            }
            else{ //if user does have a nickname for the pool
                $nickname = $pool_members_id_array[$user_id]['Nickname']; 
            }
            $user_picks_fetch = $pool->GetUserPicks($user_id, $pool_id); //get the given user's picks
            $category_counter = 1;
?>
            <h3><?php echo $nickname; ?>'s Picks:</h3>
<?php
            foreach($pool_category_fetch as $category_id => $category_info){ //generate list of categories for pool and populate the given user's picks for each category
                $incorrect_by_default = 0; //reset incorrect_by_default variable if it was previously equal to 1
                if(isset($user_picks_fetch[$category_id])) { //if a pick already exists for given category, we store it in the pick_display_value variable
                    $user_pick_explode_array = explode('|', $user_picks_fetch[$category_id]);
                    $pick_display_value = $user_pick_explode_array[0];
                    $answer_correct = $pool->GetUserPickCorrectStatus($category_id, $user_id, $pool_id);
                }
                else{
                    //if no pick exists for the given category:
                    $pick_display_value = "**No Pick made**";
                    $incorrect_by_default = 1; //set incorrect_by_default variable to 1 - this signifies that we are marking the user's lack of answer as "incorrect"
                    $answer_correct = 0;
                    $category_div_background_color = "#d9534f"; //set div background color to be red by default if no pick was made
                    //$pool->ScorePickManually($category_id, $user_id, $pool_id, 0);
                }

                //BEGIN CODE FOR CORRECT VS. INCORRECT DISPLAYS ON PAGE
                if(isset($answer_correct)) { //if the pick has already been scored by the leader:
                    if($answer_correct == 0) { //if pick has been marked as wrong:
                        $category_div_background_color = "#d9534f"; //set row background color to red
                        $correct_button_span_class = "label label-default"; //correct button
                        $incorrect_button_span_class = "label label-danger"; //incorrect button
                        $display_pick_span_class = "label label-danger"; //user pick
                    } 
                    else { //if pick has been marked as correct:
                        $category_div_background_color = "#5cb85c"; //set row background color to green
                        $correct_button_span_class = "label label-success";
                        $incorrect_button_span_class = "label label-default";
                        $display_pick_span_class = "label label-success";
                    } 
                } 
                else { //if pick has NOT already been scored by the leader:
                    $category_div_background_color = ""; //set background color to none
                    $correct_button_span_class = "label label-success";
                    $incorrect_button_span_class = "label label-danger";
                    $display_pick_span_class = "label label-primary";
                }
                //END CODE FOR CORRECT VS. INCORRECT DISPLAYS ON PAGE

?>          
                <div class="well well-sm" id="category_div_<?php echo $category_info['Category ID']; ?>_<?php echo $user_id; ?>" style="background-color:<?php echo $category_div_background_color; ?>">
                     <div class="row">
                        <div class="col-md-2">
                            <h4 id="category_n_span<?php echo $category_info['Category ID']; ?>"> <?php echo $category_info['Category Name']; ?> &nbsp; 
                            </h4>
                        </div>
                        <div class="col-md-1">
                            <h5><span id="category_p_span<?php echo $category_info['Category ID']; ?>">Point Value: <?php echo $category_info['Category Point Value']; ?></span>
                            </h5>
                        </div>
                        <div class="col-md-1">
                            <h5><span style="font-weight:bold;">Pick:</span>
                            </h5>
                        </div>
                        <div class="col-md-4">

                            <h2><span class="<?php echo $display_pick_span_class; ?>" id="pick_<?php echo $category_info['Category ID']; ?>_<?php echo $user_id; ?>"><span class="display_pick" id="user<?php echo $user_id; ?>_pick_for_category_<?php echo $category_info['Category ID']; ?>" style="font-weight:bold; white-space:pre-line;"><?php echo $pick_display_value ?></span></span>
                            </h2>

                        </div>
                        <div class="col-md-4" id="score_<?php echo $category_info['Category ID']; ?>_<?php echo $user_id; ?>"> <!--ID for this div is "score_" + category ID followed by user ID-->
<?php
                        if($incorrect_by_default == 1) { //if user did not make a pick:
?>
                            <h3><span class="label label-danger">Incorrect</span>
                            </h3>
<?php
                        }
                        else { //if user did make a pick:
?>
                            <h3><a class="<?php echo $correct_button_span_class; ?>" id="correct_<?php echo $category_info['Category ID']; ?>_<?php echo $user_id; ?>" href="JAVASCRIPT:manual_score(<?php echo $category_info['Category ID']; ?>, <?php echo $user_id; ?>, <?php echo $pool_fetch_result["Pool ID"]; ?>, 1);">Correct</a>&nbsp; <a class="<?php echo $incorrect_button_span_class; ?>" id="incorrect_<?php echo $category_info['Category ID']; ?>_<?php echo $user_id; ?>" href="JAVASCRIPT:manual_score(<?php echo $category_info['Category ID']; ?>, <?php echo $user_id; ?>, <?php echo $pool_fetch_result["Pool ID"]; ?>, 0);">Incorrect</a>
                            </h3>
<?php
                        }
?>
                        </div>
                    </div>
                </div>  
<?php 
            $category_counter++;
            } //END OF FOREACH STATEMENT WHICH POPULATES GIVEN USER'S PICKS




            /* PROBABLY DON'T NEED THE TIE BREAKER PICK TO BE DISPLAYED AT THIS POINT (ONLY MAKE IT APPEAR IF THERE IS A TIE) SO I'VE COMMENTED IT OUT (12/18/13)
            //BEGIN TIE BREAKER DISPLAY
    ?>
        <div class="well well-sm">
            <h3 style="margin-left:50px; text-decoration:underline">Tie breaker question:</h3>
            <p style="margin-left:50px"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></p> 
            <div id="tie-breaker" style="margin-left:50px;">
    <?php
                $tie_breaker_answer = $pool->GetTieBreakerAnswer($user_id, $pool_id);
                echo $GetTieBreakerAnswer;
                if(isset($tie_breaker_answer)){
                    $tie_breaker_answer_display = $tie_breaker_answer;
                }
                else{
                    $tie_breaker_answer_display = "**No choice made**";
                }
    ?>
                <h3 style="margin-left:50px;"><span class="label label-primary"><span id="tie_breaker_input" class="display_pick" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
            </div>
        </div>
        <br><hr><br>
    <?php
        //END TIE BREAKER DISPLAY
        */
?>
        <br><br><br>
<?php
        } //END OF FOREACH STATEMENT FOR ALL POOL USERS

//************************************END NON-MULTIPLE CHOICE SECTION*********************************
    }
    else{ //if pool is multiple choice:

//************************************BEGIN MULTIPLE CHOICE SECTION***********************************

?>
        <span id="pool_id_span" style="display:none"><?php echo $pool_id; ?></span>
        <h2 style="text-decoration:underline">Tally Pool Score</h2> 
        <h3>Choose the correct picks below.  Users will only receive points for correct picks.</h3>
        <br>
        <div class="row">
            <div class="col-md-5">
                <h4 style="text-decoration:underline"><?php echo $pool_fetch_result['Overall Question']; ?></h4> 
            </div>
            <div class="col-md-7">
                <h4><input type="button" id="score_pool_button" class="btn btn-warning btn-lg" value="Finish and Calculate Pool Score" onclick="JAVASCRIPT:CalculatePoolScoreValidate(<?php echo $pool_id; ?>, 1);" /></h4>
            </div>
        </div>
        <br>
<?php
        foreach($pool_category_fetch as $category_id => $category_info){
            $category_choices = $pool->GetCategoryChoices($category_id); //store all of the multiple choices for given category in $category_choices array
            $correct_response = $pool->GetCorrectChoiceForTemplateCategory($category_id);
            if($correct_response == '0'){ //if the category has not yet been marked as correct, display the first value of the category_choices array:
                //$display_choice = reset($category_choices);
                $display_choice = '000NA000'; //if we pass this value to the ScoreTemplateChoice method, it will reset the category to be unscored
            }
            else{ //if category has been marked correct, display the saved correct value:
                $display_choice = key($correct_response);
            }
            echo "<h4>".$category_info['Category Name']."</h4>";
?>
            <div class="bfh-selectbox" id="TEMPLATE99_<?php echo $category_info['Category ID']; ?>" data-name="selectbox1" data-value="<?php echo $display_choice; ?>" data-filter="true">
                <div data-value='000NA000'>**No answer chosen**</div> <!--default drop down choice when no answer has been previously selected-->
<?php
            foreach($category_choices as $choice_id => $choice){ //put all of the given category's multiple choices in the bfh-selectbox dropdown menu
?>
                <div data-value='<?php echo $choice_id; ?>'><?php echo $choice; ?></div> <!--display category choices in a drop down-->
<?php
            }
?>
            </div>
            <br><br>
<?php
        }
?>
            <div class="well well-sm">
            <h2 style="margin-left:50px; text-decoration:underline">Tie breaker question:</h2>
            <h4 style="margin-left:50px"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></h4> 
            <div id="tie-breaker" style="margin-left:50px;">
    <?php
                $tie_breaker_answer = $pool->GetCustomPoolTieBreakerAnswer($pool_id);
                if(isset($tie_breaker_answer)){
                    $tie_breaker_answer_display = $tie_breaker_answer;
                }
                else{
                    $tie_breaker_answer_display = "**Enter tie-breaker value here**";
                }
    ?>
                <h3 style="margin-left:50px;"><span class="label label-info"><span id="tie_breaker_input" class="edit_custom_pool_tie_breaker" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
            </div>
        </div>
        <br><hr><br>

<?php
//************************************END MULTIPLE CHOICE SECTION***********************************
    }
?>

<br>

