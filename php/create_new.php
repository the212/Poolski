<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";

    if(!empty($_POST['title']) && !empty($_POST['question']) && !empty($_POST['tie_question'])):
        //if page loads and all required inputs have been given:
        $title = $_POST['title']; //get title from input
        $description = $_POST['description']; //get description from input
        $tie_question = $_POST['tie_question']; //get tie breaker question from input
        $question = $_POST['question']; //get overall question from input
        $public_status = $_POST['public_private']; //get public/private status

        if($_GET['template'] == 1){ //if we are trying to create a new template (note this GET variable instance comes from the $url_variable defined below and sent via the form action HTML attribute)
            if($admin == 1){ //make sure user is an admin before creating new template:
                $new_template_result = $pool->CreateNewTemplate($title , $question, $description, $tie_question);
                header("Location: edit_template.php?template_id=$new_template_result[2]");
            }
        }
        elseif($_GET['series'] == 1){ //if we are trying to create a new pool series:
            $new_pool_series_result = $pool->CreateNewPoolSeries($_SESSION['Username'] , $title , $question, $description, $public_status);
            header("Location: edit_series.php?series_id=$new_pool_series_result[2]");
        }
        else{ //if we are just creating a regular pool:
            $pool_category_type = $_POST['category_type']; //get pool category type (multiple choice or non-multiple choice)
            //create new pool in database with inputs:
            $new_pool_result = $pool->CreateNewPool($_SESSION['Username'] , $title , $question, $description, $tie_question, $public_status, NULL, $pool_category_type);
            //send user to newly created pool page automatically:
            header("Location: edit_pool.php?pool_id=$new_pool_result[2]");
        }
    else:

        /*$new_type variable is used to determine what type of item we are creating 
        1 = a new custom pool
        2 = a new template (internal admin only)
        3 = a new pool series (as of 8/16/14, this is internal admin only but may be released in the future)
        */
        if($_GET['template'] == 1){ //if we are creating a template:
            $new_type = 2; 
            $pool_label_variable = "template";
            $url_variable = "?template=1"; //we define this for form submission purposes - see form action HTML attribute below
            $pageTitle = "New Template";
        }
        elseif($_GET['series'] == 1){ //if we are creating a new pool series:
            $new_type = 3;
            $pool_label_variable = "pool series";
            $url_variable = "?series=1"; //we define this for form submission purposes - see form action HTML attribute below
            $pageTitle = "New Pool Series";
            $tie_open_html_comment = '<!--';
            $tie_close_html_comment = '--><input type="hidden" name="tie_question" id="tie_question" value="dummy"/>';
        }
        else{ //if we are just creating a regular custom pool
            $new_type = 1;
            $pool_label_variable = "pool";
            $url_variable = ""; //we define this for form submission purposes - see form action HTML attribute below
            $pageTitle = "New Pool";
        }
    
        //put pool_label_variable into proper case:
        $words = explode(' ', $pool_label_variable);
        for ($i=0; $i<count($words); $i++) {
            $s = strtolower($words[$i]);
            $s = substr_replace($s, strtoupper(substr($s, 0, 1)), 0, 1);
            $result .= $s.' ';
        }
        $proper_pool_label_variable = trim($result);
        
        include_once "inc/header.php";
        
        if($_POST['form_sent'] == 1){  //if form was sent but did not have all the required fields filled out:
            $error_message = "<span style='color:red'>Please fill out all required fields</span>";
            echo $error_message; //display error on page prompting user to fill out all required fields
        }

        //display below HTML if page loads without any input:
?>

    <h3>Create a new <?php echo $proper_pool_label_variable; ?>:</h3>
    <br>
    <form method="post" action="create_new.php<?php echo $url_variable; ?>" name="new_pool_form" id="new_pool_form">
        <div id="create_new_pool_form">
            <label for="title"><?php echo $proper_pool_label_variable; ?> Title</label>
            <span class="field_label">Give your <?php echo $pool_label_variable; ?> a name.</span>
            <br>
            <input type="text" name="title" id="title" size="100"/>
            <br><br>
            <label for="description"><?php echo $proper_pool_label_variable; ?> Description (optional)</label>
            <br>
            <input type="text" name="description" id="description" size="100"/>
            <br><br>
            <label for="question">Overall <?php echo $proper_pool_label_variable; ?> Topic</label>
            <span class="field_label">What are we betting on? &nbsp; E.g., "who will win each Academy Award?", "what will happen on the new episode of Game of Thrones?", etc.</span>
            <br>
            <input type="text" name="question" id="question" size="100"/>
            <br><br>
<?php
        echo $tie_open_html_comment; //this is an open HTML comment <!-- if this we are creating a new pool series since we don't have tie questions for series
?>
            <label for="tie_question">Tie-Breaker Question</label>
            <span class="field_label">The answer to this question will be used if more than one person has the highest score.</span>
            <br>
            <input type="text" name="tie_question" id="tie_question" size="100"/>
            <br><br>
<?php
        echo $tie_close_html_comment; //this is a closed HTML comment --> if this we are creating a new pool series since we don't have tie questions for series

        if($new_type !== 2){ //if we are NOT creating a new template, we include the public/private input:
?>
            <label for="public_private">Public or Private?</label>
            <br>
            <input type="radio" name="public_private" value="public" checked="checked"> Make <?php echo $proper_pool_label_variable; ?> Public (Default - anyone can invite others)<br>
            <input type="radio" name="public_private" value="private"> Make <?php echo $proper_pool_label_variable; ?> Private (only you can invite others)<br>
            <br>
<?php
            if($new_type !== 3){ //if we are NOT creating a pool series, we include the MC/non-MC input:
?>
            <label for="category_type">Questions in pool should be:</label>
            <br>
            <input type="radio" name="category_type" value="MC" checked="checked">Multiple Choice (participants choose from a list of choices that you create)<br>
            <input type="radio" name="category_type" value="non_MC">Open Ended (participants answer however they want)<br>
            <br>
<?php
            }
        }
?>
            <input type="submit" name="new_pool_button" id="new_pool_button" value="Create New <?php echo $proper_pool_label_variable; ?>" class="button" />
            <br>
            <br>

            <input type="hidden" name="form_sent" id="form_sent" value="1"/>
        </div>
    </form>
    <br>
<?php
    endif;
    /*
    NEED TO EDIT THE BELOW FUNCTION - IT WILL DISPLAY THE ERROR MESSAGE EVEN IF POOL WAS SUBMITTED WITH ALL FIELDS FILLED IN
    if(isset($_POST['form_sent'])){
        //if form was sent without all required fields:
        echo "<p style='color:red'>Please fill in all required fields.</p>";
    }*/
    //include_once 'common/close.php';
?>
