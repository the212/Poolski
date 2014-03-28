<?php
/*update_categories_list.php file
**By Evan Paul March 2014
**AS OF 3/26/14, THIS FILE IS INCLUDED FROM THE FOLLOWING FILES:
	edit_pool_categories_MC.php
	send_pool_data.php (so that category space and category choices spaces can be updated)
*/

//UPDATE CATEGORY LIST FUNCTION
//ACCEPTS POOL_CATEGORIES ARRAY AS PARAMETER (ARRAY CONTAINS ALL DATA FOR A SUBSET OF POOL CATEGORIES) AND MULTIPLE CHOICE VARIABLE
//IF MULTIPLE CHOICE VARIABLE IS 1, ALL CATEGORIES ARE ASSUMED TO BE MULTIPLE CHOICE (AS OF 3/26/14)
//RETURNS HTML MARKUP FOR THE ORDERED LIST OF SAVED CATEGORIES FOR THE GIVEN POOL
function Update_Category_List($pool_categories, $multiple_choice){
    $pool = new Pool();
    $category_counter = 1;
    $return_array = array();
    foreach($pool_categories as $category_id => $category_info){
        if($multiple_choice == 1){ //if this is a multiple choice pool (NOTE: AS OF 3/26/14, THIS UPDATE_CATEGORY_LIST FUNCTION CANNOT DIFFERENTIATE BETWEEN MC CATEGORIES AND NON MC CATEGORIES FOR THE SAME POOL - I.e., THE MC ARGUMENT IS STATIC ACROSS ALL CATEGORIES)
            $category_choices = $pool->GetCategoryChoices($category_id);
            $choice_list = Update_Category_Choice_List($category_choices, $category_id);
            $choice_return_value = <<<HTML
                <div id="category_choices_container">
                    <h4 class="edit_category_choices_label"> Category Choices: &nbsp; </h4>
                    <div id="category{$category_id}_choice_space">
                        {$choice_list}
                    </div>
                     <div id="new_category{$category_id}_choice_goes_here" class="new_category_choice_goes_here_div">
                        <h4><span id="add_choice_button_for_category_{$category_id}" class="add_category_choice_button"><input type="button" onclick="add_category_choice({$category_id})" value="Add new choice"></span></h4>
                    </div>
                </div> <!--END OF CATEGORY_CHOICE_CONTAINER DIV-->
HTML;
        }
        else{ //if given pool is NOT multiple choice, choice_return_value should just be blank
            $choice_return_value = "";
        }
        $return_array[$category_counter] = <<<HTML
            <div id="category_{$category_counter}">
                <div style="margin-left:50px" class="well well-sm"> 
                    <div class="row">
                        <div class="col-md-2">
                            <h4> 
                            	<span class="category_name_label">Category name: &nbsp; </span>
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <h3>
                                <span class="label label-info"><span class="edit_pool_field" id="category_n_span{$category_info['Category ID']}" style="margin-left:0px; white-space:normal;">{$category_info['Category Name']}</span></span>
                            </h3>
                        </div>
                        <div class="col-md-2">
                            <h4>
                                Point Value: <span class="label label-info">&nbsp;<span class="edit_pool_field" id="category_p_span{$category_info['Category ID']}">&nbsp;{$category_info['Category Point Value']}&nbsp; </span>&nbsp;</span>
                            </h4>  
                        </div>
                        <div class="col-md-2"> 
                            <h5> 
                                <span class="remove_category_button"><input type="button" onclick="remove_category({$category_counter}, {$category_info['Category ID']}, {$multiple_choice})" value=" Remove \nCategory"></span>
                            </h5>
                        </div>
                    </div>
                    {$choice_return_value}
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

function Update_Category_Choice_List($category_choices, $category_id){
    $choice_counter = 1;
    $return_array = array();
    foreach($category_choices as $choice_id => $choice){
        $return_array[$choice_counter] = <<<HTML
            <div id="category{$category_id}_choice_{$choice_counter}">
                <div class="col-md-2">
                    <h5>Choice {$choice_counter}: &nbsp; </h5>
                </div>
                <div class="col-md-9">
                    <h4>
                        <span class="label label-info"><span class="edit_pool_field" id="choice_span{$choice_id}" style="margin-left:0px; white-space:normal;">{$choice}</span></span>
                    </h4>
                </div>
                <div class="col-md-1"> 
                    <h5> 
                        <input type="button" onclick="remove_category_choice({$category_id}, {$choice_counter}, {$choice_id})" value="Remove choice"> 
                    </h5>
                </div>
            </div>
HTML;
        $choice_counter++;
    }
    foreach($return_array as $choice_number => $choice_html_content){
        //concatenate each choice's html section together into one long string of html text
        $return_value = $return_value.$choice_html_content;
    }
    return $return_value;
}


?>
