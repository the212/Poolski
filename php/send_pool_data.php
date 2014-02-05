<?php

/*
SEND_POOL_DATA PHP FILE
BY EVAN PAUL, NOVEMBER 13, 2013
AS OF 11/13/13, THIS FILE WORKS EXCLUSIVELY WITH THE EDIT_POOL.PHP PAGE FOR THE PURPOSES OF EDITING A POOL AND ITS CATEGORIES.  WE ALSO PROVIDE A SUBMIT POOL FUNCTION FOR USE ONCE POOL SETTINGS ARE FINALIZED
THIS FUNCTION RECEIVES ARGUMENTS VIA EITHER POST OR GET.  DEPENDING ON THE ARGUMENTS RECEIVED, WE TAKE A CERTAIN ACTION
*/

include_once "inc/constants.inc.php";
include_once 'inc/class.pool.inc.php';

//IF _POST['element_id'] IS PASSED VIA POST, IT MEANS THAT THE EDIT IN PLACE FUNCTION IS BEING CALLED AND WE ARE UPDATING AN ALREADY SAVED POOL VALUE:
if(isset($_POST['element_id'])){ 
    $pool = new Pool();
    $input_value = $_POST['update_value']; //get input value from page
    $input_id = $_POST['element_id']; //get the element ID from the page.  The element IDs of each input on the page are the same as the fields in the DB
    $pool_id = $_POST['pool_id']; //get the pool ID from the page
    $original_value = $_POST['original_html'];
    
    $category_check = substr_compare(substr($input_id,0,8),"category",0,8); //get first 8 characters of pool item id and check to see if they are "category" - if so, we are updating a category or a category's point value and need to write to the 'Pool Category' table
    //BELOW SEQUENCE OF IF'S IS TO VALIDATE CATEGORY POINT INPUTS (THEY MUST BE NUMERIC IN DB)
    if($category_check == 0){ //if we have been sent a category related update:
        $category_item = $input_id[9]; //CHECK TO SEE WHAT CATEGORY ITEM IS BEING EDITED 
        if($category_item == "p") { //IF ITEM BEING EDITED IS CATEGORY POINT VALUE
            if(!is_numeric($input_value)){ //IF THE INPUT VALUE IS NOT NUMERIC:
                echo $original_value." <span style='font-style:italic'>&nbsp; &nbsp; &nbsp; Input must be a number</span>";
                exit();
            }
        }
    }
    if($input_id == "update_nickname") { //if we are updating a user's pool nickname:
        $new_nickname_result = $pool->UpdateNickname($_POST['user_id'], $pool_id, $input_value);
        echo $new_nickname_result;
    }
    else { //if we are not updating a user's pool nickname:
        $new_pool_result = $pool->UpdatePoolData($pool_id, $input_id, $input_value);
    }
    echo $input_value;
}

else{ //IF EDIT IN PLACE IS NOT BEING CALLED:

    $pool_id = $_GET['pool_id']; 

    //ADD A NEW SAVED CATEGORY
    if(isset($_GET['new_category'])){ 
        //if we are adding a new category to DB:
        $new_category = $_GET['new_category'];
        $new_category_points = $_GET['new_category_points'];
        $pool = new Pool();
        $pool->AddCategory($pool_id, $new_category, $new_category_points);
        $pool_categories = $pool->GetPoolCategoryData($pool_id);
        $number_of_saved_categories = count($pool_categories);
        if($number_of_saved_categories>0){ 
            $return_value = Update_Category_List($pool_categories);
            echo $return_value;
        }
    }

    //REMOVE A SAVED CATEGORY:
    if(isset($_GET['remove_category'])){ 
        //if we are removing a given category from DB:
        $removal_category = $_GET['remove_category'];
        $pool = new Pool();
        $pool->RemoveCategory($removal_category);
        $pool_categories = $pool->GetPoolCategoryData($pool_id);
        $number_of_saved_categories = count($pool_categories);
        if($number_of_saved_categories>0){ 
            $return_value = Update_Category_List($pool_categories);
            echo $return_value;
        }
    }

    //SUBMIT THE POOL - MAKE IT READY FOR INVITEES
    if(isset($_GET['submit_pool'])){ 
        $pool = new Pool();
        $result_array_test = $pool->MakePoolReadyForInvitees($pool_id);
        if($result_array_test == 0){
            //IF WE GET A 0 BACK FROM MakePoolReadyForInvitees FUNCTION, IT MEANS POOL IS ALREADY SUBMITTED
            echo "Pool is already submitted!"; //note, as of 11/13, this text will not be shown anywhere
        }
        else{
            //IF WE GET A 1 BACK FROM MakePoolReadyForInvitees FUNCTION, IT MEANS POOL WAS NOT PREVIOUSLY READY FOR INVITES
            echo "Pool has been submitted!"; //note, as of 11/13, this text will not be shown anywhere
        }
    }

    //DELETE POOL - IF POOL IS TO BE DELETED
    if(isset($_GET['delete_pool_id'])){
        $pool = new Pool();
        $pool_to_be_deleted = $_GET['delete_pool_id'];
        $pool->DeletePool($pool_to_be_deleted);
    }



    //IF WE ARE UPDATING POOL SETTINGS (START/END DATES OR START/END TIMES) VIA EDIT_POOL.PHP PAGE:
    if($_GET['edit_pool_settings'] == 1) {
        $pool = new Pool();
        $error_message = $pool->UpdatePoolSettings($_GET['pool_id'], $_GET['edit_item'], $_GET['edit_value']);
        echo $error_message;      
    }

    if($_GET['make_live']=='762'.$pool_id.'8354') { //if we are making the pool live 
        $pool = new Pool($pool_id);
        $make_pool_live_result = $pool->MakePoolLive($pool_id);
        header("Location: pool.php?pool_id=$pool_id"); //refresh the pool.php page once pool is live
    }

    if($_GET['end_pool']=='8341'.$pool_id.'8165') { //if we are ending the pool:
        $pool = new Pool($pool_id);
        $end_pool_result = $pool->EndPool($pool_id);
        header("Location: pool.php?pool_id=$pool_id"); //refresh the pool.php page once pool is live
    }

    //GET USER PICKS SO THAT WE CAN SHOW THEM ON PAGE:
    //CALLED FROM POOL_MEMBERS.PHP AND FINAL_POOL_RANKINGS.PHP PAGES AS OF 12/25/13
    if($_GET['show_user_picks'] == 1) { 
        $pool = new Pool();
        $user_picks_fetch = $pool->GetUserPicks($_GET['user_id'], $_GET['pool_id']);
        $user_picks_json = json_encode($user_picks_fetch);
        echo $user_picks_json;
    }
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
                <div style="margin-left:50px" class="well well-sm"> 
                    <div class="row">
                        <div class="col-md-5">
                            <h4> Category name: &nbsp; 
                                <span class="label label-info"><span class="edit_pool_field" id="category_n_span{$category_info['Category ID']}" style="margin-left:0px;">{$category_info['Category Name']}</span></span>
                            </h4>
                        </div>
                        <div class="col-md-7">
                            <h4>
                                <span style="margin-left:20px;">Point Value: <span class="label label-info" style="margin-left:62px">&nbsp;<span class="edit_pool_field" id="category_p_span{$category_info['Category ID']}" style="margin-left:0px">&nbsp;{$category_info['Category Point Value']}&nbsp;</span>&nbsp;</span></span>
                                <input type="button" onclick="remove_category({$category_counter}, {$category_info['Category ID']})" value="Remove category"> 
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
HTML;
        $category_counter++;
        }
        foreach($return_array as $category_number => $category_html_content){
            //concatenate each category's html section together into one long string of html text
            $return_value = $return_value.$category_html_content;
        }
        return $return_value;
}



//EDIT POOL SETTINGS SECTION
//THIS SECTION IS FOR THE "EDIT POOL SETTINGS" TAB THAT IS FOUND ON THE EDIT_POOL.PHP PAGE 
//E.G., START/END DATES AND TIMES, ETC.

//BELOW IS CALLED WHEN THE "change_date_time" AJAX FUNCTION IS RUN:



?> 



