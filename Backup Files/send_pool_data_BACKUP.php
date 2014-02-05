<?php

    include_once "inc/constants.inc.php";
    include_once 'inc/class.pool.inc.php';
    if(isset($_POST['element_id'])){ //IF EDIT IN PLACE IS BEING CALLED:
        //if element_id is set, it means we got here from an editinplace function, so execute the below code:
        //this assumes that we are updating an existing pool value
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

        $new_pool_result = $pool->UpdatePoolData($pool_id, $input_id, $input_value);
        echo $input_value;
    }
    else{ //IF EDIT IN PLACE IS NOT BEING CALLED:
        if(isset($_GET['remove_category'])){
            $removal_category = $_GET['remove_category'];
            $pool = new Pool();
            $pool->RemoveCategory($removal_category);
            $pool_categories = $pool->GetPoolCategoryData($pool_id);
            echo "TEST12345";
        }
    }
    

?> 



