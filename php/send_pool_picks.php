<?php

/*
SEND_POOL_PICKS PHP FILE
BY EVAN PAUL, NOVEMBER 13, 2013
AS OF 11/13/13, THIS FILE WORKS EXCLUSIVELY WITH THE POOL.PHP PAGE FOR THE PURPOSES OF A USER SETTING THEIR PICKS FOR A POOL
*/

include_once "inc/constants.inc.php";
include_once 'inc/class.pool.inc.php';

//IF _POST['element_id'] IS PASSED VIA POST, IT MEANS THAT THE EDIT IN PLACE FUNCTION IS BEING CALLED AND WE ARE UPDATING AN ALREADY SAVED POOL VALUE:
//BELOW FUNCTION IS ONLY FOR OPEN-ENDED INPUTS!
if(isset($_POST['element_id'])){ 
    $pool = new Pool();
    $input_value = $_POST['update_value']; //get input value from page - this is the pick that the user has input
    $pool_id = $_POST['pool_id']; //get the pool ID from the page
    $pick_original_value = $_POST['original_html']; //get the value that was previously set as the pick value before user's latest input 
    $user_email = $_POST['user_id']; //get the user's ID from the page (as of 2/19/14, we expect this to be in email form)
    if($_POST['element_id'] == "tie_breaker_input"){ //if user is entering in their tie breaker input:
        if(isset($_POST['template']) && !is_numeric($input_value)) { //IF THE THIS IS A TEMPLATE AND THE TIE BREAKER INPUT VALUE IS NOT NUMERIC:
            echo $original_value." <span style='font-style:italic; word-wrap:break-word; font-size:80%;'>Input must be a number - please click here to re-enter your choice</span>";
            exit(); //AS OF 2/19/14, WE ONLY ALLOW NUMERIC TIE BREAKER INPUTS
        }
        else{ //we update the user's tie breaker input:
            $update_tie_breaker_answer = $pool->UpdateTieBreakerAnswer($user_email, $pool_id, $input_value);
            echo $update_tie_breaker_answer;
        }
    }
    else{
        $category_id = substr($_POST['element_id'],18); //get last characters of element ID (element ID will be of the form:  category_n_span_## where ## is the category ID in the DB)
        $update_category_result = $pool->UpdateUserPick($user_email, $pool_id, $category_id, $input_value);
        echo $update_category_result;
    }
}

//MULTIPLE CHOICE INPUT - updates user pick for a multiple choice pool (called from ajax for my_picks_MC.php)
if($_POST['multiple_choice'] == 1) {
    $pool = new Pool();
    $pool->UpdateUserPick($_POST['user_id'], $_POST['pool_id'], $_POST['category_id'], $_POST['new_value']);
}



//UPDATE CATEGORY LIST FUNCTION
//ACCEPTS POOL_CATEGORIES ARRAY AS PARAMETER (ARRAY CONTAINS ALL DATA FOR A SUBSET OF POOL CATEGORIES)
//RETURNS HTML MARKUP FOR THE ORDERED LIST OF SAVED CATEGORIES FOR THE GIVEN POOL
function Update_Category_List($pool_categories){
    $category_counter = 1;
    $return_array = array();
    foreach($pool_categories as $category_id => $category_info){
        //store desired html in return_array for each category
        $return_array[$category_counter] = <<<HTML
            <div id="category_{$category_counter}">
                <li style="margin-left:50px"> 
                    <p style="margin-left:30px">Category name:<span class="edit_pool_field" id="category_n_span{$category_info['Category ID']}">{$category_info['Category Name']}</span></p>
                    <div style="margin-left:30px">
                        <p>Point Value: <span style="background-color:#FFFF66; margin-left:62px">&nbsp;<span class="edit_pool_field" id="category_p_span{$category_info['Category ID']}" style="margin-left:0px">{$category_info['Category Point Value']}</span>&nbsp;</span></p>
                        <input type="button" onclick="remove_category({$category_counter}, {$category_info['Category ID']})" value="Remove category"> 
                    </div> 
                </li>
            </div>
            <br><br>
HTML;
        $category_counter++;
        }
        foreach($return_array as $category_number => $category_html_content){
            //concatenate each category's html section together into one long string of html text
            $return_value = $return_value.$category_html_content;
        }
        return $return_value;
}

?> 



