<?php

include_once "inc/loggedin_check.php";
include_once "inc/constants.inc.php";
$pageTitle = "Score Template (INTERNAL)";
include_once "inc/header.php";
include_once 'inc/class.users.inc.php';
$user = new SiteUser();
$current_user_id = $user->GetUserIDFromEmail($_SESSION['Username']); //get user ID of user who is trying to access this page

include_once 'inc/class.pool.inc.php';
$pool = new Pool(); //new instance of the Pool class
$template_id = $_GET['template_id']; //get pool ID from URL
$template_fetch_result = $pool->GetBasicTemplateInfo($template_id);
if(isset($_GET['template_id']) && isset($template_fetch_result) && $current_user_id == 1){ //if template is specified, exists, and current user is admin
    if($_GET['finalize_template'] == 998) {
        if($current_user_id == 1){ 
            include_once 'inc/class.pool.inc.php';
            $pool = new Pool(); //new instance of the Pool class
            if($_GET['no_email'] == 1){
                $finalize_template_result = $pool->FinalizeTemplateScores($_GET['template_id'], 1);
            }
            else {
                $finalize_template_result = $pool->FinalizeTemplateScores($_GET['template_id']);
            }
            echo "<h2>Pool Results Stored.</h2><br>";
        }
        else{ //IF USER IS ANYONE BESIDES USER ID #1
            header("Location: home.php");
        }
    }
}
else{
    header("Location: home.php");
}
   
$template_category_fetch = $pool->GetTemplateCategories($template_id); //get template category data 

//************************************BEGIN MULTIPLE CHOICE SECTION***********************************
        

    if($current_user_id == 1){ 
        /*12/29/13 - FOR NOW, WE ONLY LET USER ID 1 TALLY THE SCORE OF A MULTIPLE CHOICE POOL
        **THIS IS JUST AN INTERNAL INTERFACE FOR MARKING TEMPLATES AS CORRECT
        */
?>
        <span id="template_id_span" style="display:none"><?php echo $template_id; ?></span>

        <h2 style="text-decoration:underline">Tally Template Score (INTERNAL)</h2> 
        <p>Choose the correct answer to the template categories below.  When you are finished, click the submit button.</p>
        <br>
        <div class="row">
            <div class="col-md-5">
                <h4 style="text-decoration:underline"><?php echo $template_fetch_result['Overall Question']; ?></h4> 
            </div>
            <div class="col-md-7">
<?php
                if($_GET['finalize_template'] <> 998) {
?>
                <h4><input type="button" id="score_pool_button" class="btn btn-warning btn-lg" value="Submit" onclick="JAVASCRIPT:FinalizeTemplateScores(<?php echo $template_id; ?>);" /> Click here once all correct answers have been chosen</h4>
                <p>Submitting will mark all user picks correct/incorrect for this template.</p>
<?php
                }
?>
            </div>
        </div>
        <br>
<?php
        foreach($template_category_fetch as $category_id => $category_info){
            $category_choices = $pool->GetCategoryChoices($category_id); //store all of the multiple choices for given category in $category_choices array
            $correct_response = $pool->GetCorrectChoiceForTemplateCategory($category_id);
            if($correct_response == '0'){ //if the category has not yet been marked as correct:
                $display_choice = '000NA000'; //if we pass this value to the ScoreTemplateChoice method, it will reset the category to be unscored
            }
            else{ //if category has been marked correct, display the saved correct value:
                $display_choice = key($correct_response);
            }
            echo $category_info['Category Name'];
?>
            <div class="bfh-selectbox" id="TEMPLATE99_<?php echo $category_info['Category ID']; ?>" data-name="selectbox1" data-value="<?php echo $display_choice; ?>" data-filter="true">
                <div data-value='000NA000'>**No answer chosen**</div> <!--default drop down choice when no answer has been previously selected-->
<?php
            foreach($category_choices as $choice_number => $choice){ //put all of the given category's multiple choices in the bfh-selectbox dropdown menu
?>
                <div data-value='<?php echo $choice_number; ?>'><?php echo $choice; ?></div> <!--display category choices in a drop down-->
<?php
            }
            echo "</div>";
            echo "<br><br>";
        }
?>

        <div class="well well-sm">
            <h3 style="margin-left:50px; text-decoration:underline">Tie breaker question:</h3>
            <p style="margin-left:50px"><?php echo $template_fetch_result['Tie Breaker Question']; ?></p> 
            <div id="tie-breaker" style="margin-left:50px;">
    <?php
                $template_tie_breaker_answer = $pool->GetTemplateTieBreakerAnswer($template_id);
                if(isset($template_tie_breaker_answer)){
                    $tie_breaker_answer_display = $template_tie_breaker_answer;
                }
                else{
                    $tie_breaker_answer_display = "**Enter tie-breaker value here**";
                }
    ?>
                <h3 style="margin-left:50px;"><span class="label label-primary"><span id="tie_breaker_input" class="edit_template_tie_breaker" style="font-weight:bold;"><?php echo $tie_breaker_answer_display; ?></span></span></h3>
            </div>
        </div>
        <br><hr><br>

<?php
    }
    else{ //IF USER IS ANYONE BESIDES USER ID #1
        header("Location: home.php");
    }
//************************************END MULTIPLE CHOICE SECTION***********************************
    
?>

<br>

