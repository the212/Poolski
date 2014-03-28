<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "New Pool";
    include_once "inc/header.php";

    if(!empty($_POST['pool_title']) && !empty($_POST['pool_question']) && !empty($_POST['tie_question'])):
        //IF WE ARE ABLE TO CREATE THE POOL FROM THE USER'S INPUTS:
        //if page loads and all required inputs have been given:
        include_once 'inc/class.pool.inc.php';
        $pool = new Pool(); //new instance of the Pool class
        $pool_title = $_POST['pool_title']; //get pool title from input
        $pool_description = $_POST['pool_description']; //get pool description from input
        $tie_question = $_POST['tie_question']; //get tie breaker question from input
        $pool_question = $_POST['pool_question']; //get overall question from input
        $pool_public_status = $_POST['public_private']; //get pool public/private status
        $pool_category_type = $_POST['category_type']; //get pool public/private status
        //create new pool in database with inputs:
        $new_pool_result = $pool->CreateNewPool($_SESSION['Username'] , $pool_title , $pool_question, $pool_description, $tie_question, $pool_public_status, NULL, $pool_category_type);
        //send user to newly created pool page automatically:
        header("Location: edit_pool.php?pool_id=$new_pool_result[2]");
    else:
        
    //display below HTML if page loads without any input:
?>
    <!--Jquery script for adding and removing new categories to form-->
    <script>
        var counter = 2;
        function add_category(){
            $("#category_space").append('<div id="category_'+counter+'"><li><input type="text" name="category'+counter+'" id="category'+counter+'" size="75"> <label  style="margin-left:20px" for="category'+counter+'_points">Point Value</label> <input type="number" name="category'+counter+'_points" id="category'+counter+'_points" size="4"> <input type="button" onclick="remove_category('+counter+')" value="Remove category"><br><br></li></div>');
            counter = ++counter;
        }
        function remove_category(div_number){
            //make sure that there is more than one category present on page (we don't want to remove the only category):
            if(counter>2){
                //set number variable to div_number argument that was passed
                var number = div_number;
                var div_id = "category_"+number;
                $('#'+div_id).remove();
                counter = --counter;
            }   
        }
    </script> 

    <h3>Create a new pool:</h3>
    <br>
    <form method="post" action="create_new.php" name="new_pool_form" id="new_pool_form">
        <div>
            <label for="pool_title">Pool Title</label>
            <span class="field_label">Give your pool a name.</span>
            <br>
            <input type="text" name="pool_title" id="pool_title" size="100"/>
            <br><br>
            <label for="pool_title">Pool Description (optional)</label>
            <br>
            <input type="text" name="pool_description" id="pool_description" size="100"/>
            <br><br>
            <label for="pool_question">Overall Pool Topic</label>
            <span class="field_label">What are we betting on? &nbsp; E.g., "who will win each Academy Award?", "what will happen on the new episode of Game of Thrones?", etc.</span>
            <br>
            <input type="text" name="pool_question" id="pool_question" size="100"/>
            <br><br>
            <label for="tie_question">Tie-Breaker Question</label>
            <span class="field_label">The answer to this question will be used if more than one person has the highest score.</span>
            <br>
            <input type="text" name="tie_question" id="tie_question" size="100"/>
            <br>
            <br>
            <label for="public_private">Public or Private?</label>
            <br>
            <input type="radio" name="public_private" value="public" checked="checked"> Make Pool Public (Default - anyone can invite others)<br>
            <input type="radio" name="public_private" value="private"> Make Pool Private (only you can invite others)<br>
            <br>
            <label for="category_type">Questions in pool should be:</label>
            <br>
            <input type="radio" name="category_type" value="MC" checked="checked">Multiple Choice (participants choose from a list of choices)<br>
            <input type="radio" name="category_type" value="non_MC">Open Ended (participants answer however they want)<br>
            <br>

            <input type="submit" name="new_pool_button" id="new_pool_button" value="Create New Pool" class="button" />

            <br>
            <br>

            <input type="hidden" name="form_sent" value="TEST"/>
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
