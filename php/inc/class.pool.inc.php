<?php  
/*Class to handle pool actions within app*/
/*By Evan Paul October 2013*/

// Include the Autoloader for MailGun(see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

include_once "constants.inc.php";
date_default_timezone_set('America/New_York'); //set timezone for getting the current time to be EST
include_once 'inc/class.db_queries.inc.php';


class Pool {

	private $cxn;

	public function __construct() { //this is the constructor - called whenever class is created  
	    //We connect to the database for the class
	    $this->cxn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME)
			or die("Could not connect to the server"); 
	}  //END OF CONSTRUCTOR METHOD FOR NEW INSTANCE OF USER CLASS
   
    //CREATE NEW POOL METHOD
    //Required arguments: pool leader's ID, pool title, overall question 
    //Optional arguments: pool description, tie breaker question
    //Returns array consisting of a result code, result message, and the new Pool ID number if pool creation was successful
    public function CreateNewPool($Leader_ID, $Pool_Title, $Overall_Question, $Description = NULL, $Tie_question = NULL, $pool_public_status = NULL, $template_id = NULL, $pool_category_type = NULL) {
        //give pool settings variables default values of 0:
        $multiple_choice = 0;
        $public_private_variable = 0;
        if($pool_public_status == "private"){ //if pool is private:
            $public_private_variable = 1;
        }
        if($pool_category_type == "MC"){ //if pool is to have multiple choice answers:
            $multiple_choice = 1;
        }
        //ESCAPE BAD CHARACTERS FROM STRING INPUTS:
            $escaped_title = $this->escapeBadCharacters($Pool_Title); 
            $escaped_overall_question = $this->escapeBadCharacters($Overall_Question); 
            $escaped_description = $this->escapeBadCharacters($Description); 
            $escaped_tie_question = $this->escapeBadCharacters($Tie_question); 
        $pool_query = "INSERT INTO `Pool` (`Title`, `Leader ID`, `Description`, `Overall Question`, `Tie-Breaker Question`, `Multiple Choice?`, `Private?`) VALUES ('$escaped_title', '$Leader_ID', '$escaped_description', '$escaped_overall_question', '$escaped_tie_question', '$multiple_choice', '$public_private_variable');";
        if($result = mysqli_query($this->cxn, $pool_query)){
            //get ID from Pool table of Pool that was just created:
            $new_pool_id = mysqli_insert_id($this->cxn);

            //fetch User ID from User table corresponding to pool leader's username:
            $user_ID_query = "SELECT `User ID` FROM `User` WHERE `Email Address` = '$Leader_ID'";
            $result2 = mysqli_query($this->cxn, $user_ID_query);
            $user_ID_result = mysqli_fetch_assoc($result2); 
            $user_ID = $user_ID_result['User ID'];

            //add a new entry to pool membership table for leader in newly created pool
            $pool_membership_query = "INSERT INTO `Pool Membership` (`User ID`, `Pool ID`) VALUES ('$user_ID', '$new_pool_id');";
            $result3 = mysqli_query($this->cxn, $pool_membership_query);

            if(isset($template_id)){ //if we get a $template_id variable, add that to the pool's multiple choice field:
                //note - as of 12/09 - we force any new pool created from a template to be multiple choice
                $template_info_query = $this->GetBasicTemplateInfo($template_id);
                $start_time = $template_info_query['Start Time'];
                $end_time = $template_info_query['End Time'];
                $set_template_query = "UPDATE  `Pool` SET  `Template ID`='$template_id', `Multiple Choice?`='1', `Start Time`='$start_time', `End Time` = '$end_time' WHERE  `Pool ID` ='$new_pool_id';";
                mysqli_query($this->cxn, $set_template_query);
            }

            $return_variable = array(2, "<p>Pool created successfully!</p>", $new_pool_id);
        }
        else{
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
        return $return_variable;
    } //END OF Create new pool METHOD 


    //DELETE POOL METHOD (ONLY ACCESSIBLE BY ADMINS - SEE HOME.PHP AND DELETE_POOL.PHP FILES FOR LOGIC)
    public function DeletePool($pool_id){
        //DELETE ENTRY FROM POOL TABLE:
            $delete_pool_query = "DELETE FROM `Pool` WHERE `Pool ID` = '$pool_id'";
            $result1 = mysqli_query($this->cxn, $delete_pool_query);
        //DELETE ENTRIES FROM POOL MEMBERSHIP TABLE:
            $delete_pool_membership_query = "DELETE FROM `Pool Membership` WHERE `Pool ID` = '$pool_id'";
            $result2 = mysqli_query($this->cxn, $delete_pool_membership_query);
        //DELETE ENTRIES FROM THE POOL CATEGORIES TABLE (ENTRIES WILL ONLY EXIST IF THE POOL IS NOT A TEMPLATE)
            $delete_pool_categories_query = "DELETE FROM `Pool Categories` WHERE `Pool ID` = '$pool_id'";
            $result3 = mysqli_query($this->cxn, $delete_pool_categories_query);
        //DELETE ENTRIES FROM THE USER PICKS TABLE (ENTRIES WILL ONLY EXIST IF AT LEAST ONE PICK BY A POOL MEMBER HAS BEEN MADE)
            $delete_user_picks_query = "DELETE FROM `User Picks` WHERE `Pool ID` = '$pool_id'";
            $result4 = mysqli_query($this->cxn, $delete_user_picks_query);
        //DELETE ENTRIES FROM CATEGORY CHOICES TABLE (ENTRIES WILL ONLY BE DELETED IF A POOL IS NOT A TEMPLATE)
            $delete_category_choices_query = "DELETE FROM `Category Choices` WHERE `Pool ID` = '$pool_id';";
            $result5 = mysqli_query($this->cxn, $delete_category_choices_query);
    } //END OF DELETE POOL METHOD
    

    //GET POOL DATA METHOD
    //accepts the pool ID as the input
    //if pool id is valid, the method returns an array with all of the fields from the given pool's row in Pool table in database
    //if pool id is not valid, method returns 0
    public function GetPoolData($pool_id){
        $query = "SELECT * FROM  `Pool` WHERE `Pool ID` = '$pool_id';";
        if($result = mysqli_query($this->cxn, $query)){
            $pool_data = mysqli_fetch_assoc($result);
            if(!is_null($pool_data['Start Time'])){ //only define pool_start_time variable if a pool start time is non-null in DB
                $pool_start_time = strtotime($pool_data['Start Time']); //convert pool start timestamp to unix timestamp
            }
            if(!is_null($pool_data['End Time'])){ //only define pool_end_time variable if a pool end time is non-null in DB
                $pool_end_time = strtotime($pool_data['End Time']); //convert pool end timestamp to unix timestamp
            }
            $current_time = time(); //get current time (unix timestamp) - this should be based on the timezone specified for the date_default_timezone_set() function at the top of this file
            if($pool_data['Ready for invites?'] == 1){ //if we are past edit_pool.php:
                if(isset($pool_start_time) AND ($current_time-$pool_start_time) > 0 AND $pool_data['Live?'] <> 1) { //if it is past the pool start time and pool is ready for invites:
                    $pool_start_query = "UPDATE  `Pool` SET `Live?` =  '1' WHERE  `Pool ID` ='$pool_id';"; //make pool live
                    $pool_start_result = mysqli_query($this->cxn, $pool_start_query);    
                }
                if(isset($pool_end_time) AND ($current_time-$pool_end_time) > 0 AND $pool_data['Pool Ended?'] <> 1) { //if pool is not already over and it is past the pool end time:
                    $pool_end_query = "UPDATE  `Pool` SET `Pool Ended?` =  '1' WHERE  `Pool ID` ='$pool_id';"; //end pool
                    $pool_end_result = mysqli_query($this->cxn, $pool_end_query);
                }
            }
            return $pool_data;
        }
        else {
            //return zero if the database query failed
            return 0;
        }
    }


    /*UPDATEPOOLSETTINGS METHOD
    **
    **ACCEPTS POOL ID, THE SETTING TO BE UPDATED, AND THE NEW SETTING VALUE TO UPDATE
    **CHECKS INPUT START/END INFO TO MAKE SURE THAT START TIMES ARE NOT BEFORE END TIMES AND VICE VERSA.  ERRORS OUT IF SO.
    **DOES NOT RETURN ANYTHING IF QUERY WAS SUCCESSFUL
    **RETURNS A COMMA DELIMITED STRING IF THERE IS AN ERROR.  
    **  STRING CONTAINS AN ERROR MESSAGE, THE HTML ID OF THE EDITED SETTING, AND THE ORIGINAL VALUE OF THE EDITED SETTING BEFORE THE EDIT OCCURRED
    **  THIS DELIMITED STRING IS CONVERTED TO A JAVASCRIPT ARRAY IN THE BROWSER AFTER AN AJAX CALL  - SEE EDIT_POOL.PHP AND MY_JAVASCRIPT.JS 
    */
    public function UpdatePoolSettings($pool_id, $setting_to_be_updated, $new_setting_value){
        $template_check = substr($pool_id, 0, 8);
        if($template_check == "template"){ //if we are updating a template's settings
            $template_id = substr($pool_id, 9);
            $pool_fetch_result = $this->GetBasicTemplateInfo($template_id);
            $db_table_to_be_updated = "Templates";
            $db_id_field = "Template ID";
            $update_id = $template_id;
        }
        else{ //if we are updating a regular pool's settings:
            $pool_fetch_result = $this->GetPoolData($pool_id);
            $db_table_to_be_updated = "Pool";
            $db_id_field = "Pool ID";
            $update_id = $pool_id;
        }
        $error_message = 0; //set error_message to 0.  error_message will only be changed if there is a problem
        switch ($setting_to_be_updated) {
            case 'SD': //if we are editing start date:
                $new_start_timestamp = substr_replace($pool_fetch_result['Start Time'], $new_setting_value, 0, 10);
                if(!is_null($pool_fetch_result['End Time']) && $new_start_timestamp > $pool_fetch_result['End Time']){ //if input start info is AFTER existing end info and ending info exists:
                    $error_message = "Error: Start Date cannot be after End Date! Please check your settings.";
                    $original_value = substr($pool_fetch_result['Start Time'], 0, 10); //get original value for edited setting
                    return $error_message.",edit_start_date,".$original_value;
                }
                else {
                    $query = "UPDATE  `$db_table_to_be_updated` SET  `Start Time` =  '$new_start_timestamp' WHERE  `$db_id_field` ='$update_id';";
                }
                break;
            case 'ST': //if we are editing start time:
                $new_start_timestamp = substr_replace($pool_fetch_result['Start Time'], $new_setting_value, 11);
                if(!is_null($pool_fetch_result['End Time']) && $new_start_timestamp > $pool_fetch_result['End Time']){ //if input start info is AFTER existing end info and ending info exists:
                    $error_message = "Error: Start Date cannot be after End Date! Please check your settings.";
                    $original_value = substr($pool_fetch_result['Start Time'], 11);
                    return $error_message.",edit_start_time,".$original_value; 
                }
                else {
                    $query = "UPDATE  `$db_table_to_be_updated` SET  `Start Time` =  '$new_start_timestamp' WHERE  `$db_id_field` ='$update_id';";
                }
                break;
            case 'ED': //if we are editing end date:
                if($pool_fetch_result['End Time'] == NULL){ //if end date is not previously set:
                    $new_end_timestamp = $new_setting_value." 00:00:00"; //we add this here so that setting a start and end date on the same day without setting any end time does not result in an error
                }
                else{ //if an end date already exists in DB:
                    $new_end_timestamp = substr_replace($pool_fetch_result['End Time'], $new_setting_value, 0, 10);
                }
                if($new_end_timestamp < $pool_fetch_result['Start Time']){ //if input end info is BEFORE existing start info:
                    $error_message = "Error: End Date cannot be before Start Date! Please check your settings.";
                    $original_value = substr($pool_fetch_result['End Time'], 0, 10);
                    return $error_message.",edit_end_date,".$original_value;
                }
                else{
                    $query = "UPDATE  `$db_table_to_be_updated` SET  `End Time` =  '$new_end_timestamp' WHERE  `$db_id_field` ='$update_id';";
                }
                break;
            case 'ET': //if we are editing end time:
                $new_end_timestamp = substr_replace($pool_fetch_result['End Time'], $new_setting_value, 11);
                if($new_end_timestamp < $pool_fetch_result['Start Time']){ //if input end info is BEFORE existing start info:
                    $error_message = "Error: End Date cannot be before Start Date! Please check your settings.";
                    $original_value = substr($pool_fetch_result['End Time'], 11);
                    return $error_message.",edit_end_time,".$original_value; 
                }
                else {
                    $query = "UPDATE  `$db_table_to_be_updated` SET  `End Time` =  '$new_end_timestamp' WHERE  `$db_id_field` ='$update_id';";
                }
                break;
            case 'public_private': //if we are changing whether the pool is public or private:
                if($new_setting_value == "0") {
                    //value of zero means pool is public:
                    $query = "UPDATE  `Pool` SET  `Private?` =  '0' WHERE  `Pool ID` ='$pool_id';";
                }
                else {
                    //value of 1 means pool is private:
                    $query = "UPDATE  `Pool` SET  `Private?` =  '1' WHERE  `Pool ID` ='$pool_id';";
                }
                break;
        }
        if($error_message == 0){ //if switch statement did not give an error, run the MySQL query:
            mysqli_query($this->cxn, $query);
        }
    }


    /*GET POOL CATEGORY DATA METHOD
    **accepts the pool ID as the input
    **checks to see whether the given pool is multiple choice or not
        **if so, we check to see if the given pool is a template or user-defined (as of 12/3/13, only templates are supported for multiple choice pools)
            **if the pool is a template, we run the DB query for all of the categories in the Pool Categories table that have the pool's template ID
        **if pool is not multiple choice, we run the DB query for all of the categories in the Pool Categories table that have the given pool ID
    **returns an array of arrays of pool category info for the given pool id if first query was successful, returns zero otherwise
    */
    public function GetPoolCategoryData($pool_id){
        $mult_choice_check_query = "SELECT `Multiple Choice?`,`Template ID` FROM  `Pool` WHERE `Pool ID` = '$pool_id';";
        $mult_choice_check_result = mysqli_query($this->cxn, $mult_choice_check_query);
        $mult_choice_check_array = mysqli_fetch_assoc($mult_choice_check_result);
        if($mult_choice_check_array['Multiple Choice?'] == 0){ //if pool is NOT multiple choice:
            //get all category ID's associated with given pool
            $query = "SELECT `Category ID` FROM  `Pool Categories` WHERE `Pool ID` = '$pool_id'";
        }
        else{ //if pool IS multiple choice:
            if($mult_choice_check_array['Template ID'] > 0){ //if pool is a template:
                $template_id = $mult_choice_check_array['Template ID']; 
                //get all category ID's associated with given template ID:
                $query = "SELECT `Category ID` FROM  `Pool Categories` WHERE `Template ID` = '$template_id'";
            }
            else{ //if pool is NOT a template (user-defined categories with multiple choice responses):
                //3/24/14 - NOTE, THE BELOW QUERY CAN BE CONSOLIDATED WITH THE MULTIPLE CHOICE == 0 PATH ABOVE
                $query = "SELECT `Category ID` FROM  `Pool Categories` WHERE `Pool ID` = '$pool_id'";
            }
        }
        //below code executes the query and returns us an array of arrays of pool category info
        if($result = mysqli_query($this->cxn, $query)){ //result here is all the category IDs associated with the given pool ID
            $category_array = array(); //create blank array to store category IDs
            while($row = mysqli_fetch_assoc($result)){
                $category_id = $row['Category ID'];
                //get category info for the given category:
                $category_query = "SELECT * FROM  `Pool Categories` WHERE `Category ID` = '$category_id'";
                $result2 = mysqli_query($this->cxn, $category_query); //result here is all of the info in the table for a given category ID
                $result2_array = mysqli_fetch_assoc($result2); //store given category's info as result2_array
                $category_array[$category_id] = $result2_array; //store array of given category's info in $category_array ($cateogry_array is an array of arrays)
            }
            return $category_array;
        }
        else {
            //return zero if the database query failed
            return 0;
        }
    }
        
    
    //ADD CATEGORY CHOICE METHOD
    public function AddCategoryChoice($pool_id, $category_id, $category_choice_name){
        $escaped_name_input = $this->escapeBadCharacters($category_choice_name); //strip out bad characters from input
        if(is_null($pool_id)){ //if we are adding a category for a template (pool_id is null)
            $query = "INSERT INTO `Category Choices` (`Category ID`, `Choice`) VALUES ('$category_id', '$escaped_name_input');";
        }
        else{ //if we are adding a category for a non-template pool:
            $query = "INSERT INTO `Category Choices` (`Category ID`, `Choice`, `Pool ID`) VALUES ('$category_id', '$escaped_name_input', '$pool_id');";
        }
        $result = mysqli_query($this->cxn, $query);
    }


    public function UpdateCategoryChoice($choice_id, $new_category_choice_name){
        $escaped_name_input = $this->escapeBadCharacters($new_category_choice_name); //strip out bad characters from input
        $query = "UPDATE `Category Choices` SET `Choice` = '$escaped_name_input' WHERE `Choice ID` = '$choice_id';";
        $result = mysqli_query($this->cxn, $query);
    }

    //REMOVE CATEGORY CHOICE METHOD
    public function RemoveCategoryChoice($choice_id){
        $query = "DELETE FROM `Category Choices` WHERE `Choice ID` = '$choice_id'";
        $result = mysqli_query($this->cxn, $query);
    }


    //GET CATEGORY CHOICES METHOD
    //ACCEPTS CATEGORY ID FOR WHICH WE ARE FETCHING CORRESPONDING MULTIPLE CHOICES
    //RETURNS AN ARRAY OF ALL OF THE MULTIPLE CHOICE VALUES WITH THEIR CHOICE ID SET AS THE ARRAY KEYS
    public function GetCategoryChoices($category_id) {
        $query = "SELECT * FROM  `Category Choices` WHERE `Category ID` = '$category_id';";
        $result = mysqli_query($this->cxn, $query); //get category choice table daya for given category id
        $category_choice_array = array();
        while($row = mysqli_fetch_assoc($result)){
            $category_choice = $row['Choice']; //get each choice in resulting row array
            $category_choice_array[$row['Choice ID']] = $category_choice; //store all choices in $category_choice_array array
        }
        return $category_choice_array;
    }


    //UPDATE POOL DATA METHOD
    //accepts the pool ID, the given item to be updated, and the update value as the inputs
    public function UpdatePoolData($pool_id, $pool_item, $input){
        /*
        POOL_ITEM VARIABLE: The pool_item variable will be the ID of the HTML element containing the editable field for the given pool item (e.g., category name, category point value, etc)
            This ID will be in the form of "category_X_span###" where the "X" tells us what type of category item is to be edited:
                If X = "n" we want to update the category name
                If X = "p" we want to update the category point value
            The number ("###") at the end of the ID tells us which category ID we are editing
        */
        $escaped_input = $this->escapeBadCharacters($input); //strip out bad characters from input
        $category_check = substr_compare(substr($pool_item,0,8),"category",0,8); //get first 8 characters of pool item id and check to see if they are "category" - if so, we are updating a category or a category's point value and need to write to the 'Pool Category' table
        if($category_check == 0){ 
            //if we are writing to the 'Pool Category' table:
            $category_id = substr($pool_item,15); //get the number at the end of the category_span ID which is the category ID in the table
            $category_item = $pool_item[9]; //9th position in the $pool_item string tells us which column in the pool categories table we are updating 
            switch ($category_item) {
                case "n"; //if we're updating the category name:
                    $query = "UPDATE  `Pool Categories` SET  `Category Name` =  '$escaped_input' WHERE  `Category ID` ='$category_id' AND `Pool ID` ='$pool_id';";
                    break;
                case "p"; //if we're updating the category point value:
                    $query = "UPDATE  `Pool Categories` SET  `Category Point Value` =  '$escaped_input' WHERE  `Category ID` ='$category_id' AND `Pool ID` ='$pool_id';";
                    break;   
            }
            $result = mysqli_query($this->cxn, $query);
        }
        else{
            //if the pool item is anything but a category or a category's point value, we write to the 'Pool' table
            $query = "UPDATE  `Pool` SET  `$pool_item` =  '$escaped_input' WHERE  `Pool ID` ='$pool_id'";
            $result = mysqli_query($this->cxn, $query);
        }
    }


    //ADD CATEGORY METHOD
    //RETURNS CATEGORY ID OF THE NEW CATEGORY
    public function AddCategory($pool_id, $category_name, $category_pt_value, $multiple_choice){
        $escaped_name_input = $this->escapeBadCharacters($category_name); //strip out bad characters from input
        $escaped_point_input = $this->escapeBadCharacters($category_pt_value); //strip out bad characters from input
        $query = "INSERT INTO `Pool Categories` (`Pool ID`, `Category Name`, `Category Point Value`, `Category Multiple Choice?`) VALUES ('$pool_id', '$escaped_name_input', '$escaped_point_input', '$multiple_choice');";
        $result = mysqli_query($this->cxn, $query);
        return mysqli_insert_id($this->cxn);
    }


    //REMOVE CATEGORY METHOD
    public function RemoveCategory($category_id){
        $delete_category_query = "DELETE FROM `Pool Categories` WHERE `Category ID` = '$category_id'";
        $result = mysqli_query($this->cxn, $delete_category_query);
        $delete_category_choices_query = "DELETE FROM `Category Choices` WHERE `Category ID` = '$category_id';";
        $result2 = mysqli_query($this->cxn, $delete_category_choices_query);
    }


    //MAKE POOL READY FOR INVITEES
    //ACCEPTS POOL ID
    //IF "READY FOR INVITES" FIELD IS 0, WE CHANGE "READY FOR INVITES?" FIELD FROM 0 TO 1 FOR GIVEN POOL AND WE RETURN THE NUMBER 1.  
    //IF "READY FOR INVITES?" IS ALREADY 1, WE RETURN THE NUMBER 0
    public function MakePoolReadyForInvitees($pool_id){
        $query = "SELECT `Ready for invites?` FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
        $result = mysqli_query($this->cxn, $query); 
        $result_array = mysqli_fetch_assoc($result); //GET THE "READY FOR INVITES?" FIELD VALUE FOR GIVEN POOL ID
        if($result_array['Ready for invites?']==1){
            //IF POOL IS ALREADY READY FOR INVITES, WE RETURN A 0:
            return "0";
        }
        else{
            //IF POOL WAS NOT PREVIOUSLY READY FOR INVITES, LET'S MAKE IT READY FOR INVITES AND RETURN A 1
            $query2 = "UPDATE  `Pool` SET  `Ready for invites?` =  '1' WHERE  `Pool ID` ='$pool_id';";
            $result2 = mysqli_query($this->cxn, $query2);
            return "1";
        }
    }

    //MAKE POOL LIVE
    //ACCEPTS POOL ID
    //SAME RESULT AS MAKEPOOLREADYFORINVITEES METHOD ABOVE
    public function MakePoolLive($pool_id) {
        $query = "SELECT `Live?` FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
        $result = mysqli_query($this->cxn, $query); 
        $result_array = mysqli_fetch_assoc($result); //GET THE "Live?" FIELD VALUE FOR GIVEN POOL ID
        if($result_array['Live?']==1){
            //IF POOL IS ALREADY Live, WE RETURN A 0:
            return "0";
        }
        else{
            //IF POOL WAS NOT PREVIOUSLY LIVE, LET'S MAKE IT READY FOR INVITES AND RETURN A 1
            $query2 = "UPDATE  `Pool` SET  `Live?` =  '1' WHERE  `Pool ID` ='$pool_id';";
            $result2 = mysqli_query($this->cxn, $query2);
            return "1";
        }
    }


    public function EndPool($pool_id) {
        $query = "SELECT `Pool Ended?` FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
        $result = mysqli_query($this->cxn, $query); 
        $result_array = mysqli_fetch_assoc($result); //GET THE "Live?" FIELD VALUE FOR GIVEN POOL ID
        if($result_array['Pool Ended?']==1){
            //IF POOL IS ALREADY ENDED, WE RETURN A 0:
            return "0";
        }
        else{
            //IF POOL HAS NOT ENDED, WE END IT AND RETURN A 1
            $query2 = "UPDATE  `Pool` SET  `Pool Ended?` =  '1' WHERE  `Pool ID` ='$pool_id';";
            $result2 = mysqli_query($this->cxn, $query2);
            return "1";
        }
    }


    //GET INACTIVE POOL METHOD
    //ACCEPTS USER'S ID AS INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF A USER'S INACTIVE POOLS AS ARRAY KEYS AND ARRAYS CONSISTING OF THE POOL'S INFO AS VALUES
    //INACTIVE POOLS ARE POOLS WHERE "READY FOR INVITES" EQUALS 0 I.E., THE LEADER IS STILL EDITING THE POOL CATEGORIES AND SETTINGS
    public function GetInactivePools($user_id){
        //get the IDs of all of the current user's inactive pools
        $pool_query = "SELECT `Pool Membership`.`Pool ID` FROM  `Pool Membership` INNER JOIN `Pool` ON `Pool Membership`.`Pool ID`=`Pool`.`Pool ID` WHERE `Pool Membership`.`User ID` = '$user_id' AND `Pool`.`Ready for invites?` = '0';";
        $result2 = mysqli_query($this->cxn, $pool_query);
        //create blank array to store the pool ID(s)
        $inactive_pools = array();
        //store all of the found pool's info in inactive_pools array:
        while($row = mysqli_fetch_assoc($result2)){
            $pool_id = $row['Pool ID'];
            //get info of pool for given pool id
            $pool_title_query = "SELECT * FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
            $result3 = mysqli_query($this->cxn, $pool_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the inactive_pools array
            $inactive_pools[$pool_id] = $result3_array;
        }
        return $inactive_pools;
    }

    //GET ACTIVE POOL METHOD
    //ACCEPTS USER'S EMAIL ADDRESS AS INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF A USER'S ACTIVE POOLS AS ARRAY KEYS AND ARRAYS CONSISTING OF THE POOL'S INFO AS VALUES
    //ACTIVE POOLS ARE POOLS WHERE "READY FOR INVITES" EQUALS 1 BUT "POOL ENDED?" EQUALS 0
    public function GetActivePool($user_id){
        //get the IDs of all of the current user's active pools
        $pool_query = "SELECT `Pool Membership`.`Pool ID` FROM  `Pool Membership` INNER JOIN `Pool` ON `Pool Membership`.`Pool ID`=`Pool`.`Pool ID` WHERE `Pool Membership`.`User ID` = '$user_id' AND `Pool`.`Ready for invites?` = '1' AND `Pool`.`Pool ended?` = '0';";
        $result2 = mysqli_query($this->cxn, $pool_query);
        //create blank array to store the pool ID(s)
        $active_pools = array();
        //store all of the found pool's info in active_pools array:
        while($row = mysqli_fetch_assoc($result2)){
            $pool_id = $row['Pool ID'];
            //get info of pool for given pool id
            $pool_title_query = "SELECT * FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
            $result3 = mysqli_query($this->cxn, $pool_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the active_pools array
            $active_pools[$pool_id] = $result3_array;
        }
        return $active_pools;
    }


    //GET COMPLETED POOLS METHOD
    //ACCEPTS USER'S ID AS INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF A USER'S COMPLETED POOLS AS ARRAY KEYS AND ARRAYS CONSISTING OF THE POOL'S INFO AS VALUES
    //COMPLETED POOLS ARE POOLS WHERE "POOL ENDED?" EQUALS 1
    public function GetCompletedPools($user_id){
        //get the IDs of all of the current user's completed pools
        $pool_query = "SELECT `Pool Membership`.`Pool ID` FROM  `Pool Membership` INNER JOIN `Pool` ON `Pool Membership`.`Pool ID`=`Pool`.`Pool ID` WHERE `Pool Membership`.`User ID` = '$user_id' AND `Pool`.`Pool ended?` = '1';";
        $result2 = mysqli_query($this->cxn, $pool_query);
        //create blank array to store the pool ID(s)
        $completed_pools = array();
        //store all of the found pool's info in completed_pools array:
        while($row = mysqli_fetch_assoc($result2)){
            $pool_id = $row['Pool ID'];
            //get info of pool for given pool id
            $pool_title_query = "SELECT * FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
            $result3 = mysqli_query($this->cxn, $pool_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the completed_pools array
            $completed_pools[$pool_id] = $result3_array;
        }
        return $completed_pools;
    }


    //GET ALL POOLS METHOD
    //ACCEPTS USER'S EMAIL ADDRESS AS INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF ALL OF A USER'S POOLS (INCLUDING INACTIVE POOLS THAT ARE SAVED AS DRAFTS IF USER IS A LEADER OF THEM) AS ARRAY KEYS AND ARRAYS CONSISTING OF THE POOL'S INFO AS VALUES
    public function GetAllPools($email){
        //find user id of current user
        $user_id = $this->GetUserIDFromEmail($email);
        //get the IDs of all of the current user's active pools
        $pool_query = "SELECT `Pool ID` FROM  `Pool Membership` WHERE `User ID` = '$user_id'";
        $result2 = mysqli_query($this->cxn, $pool_query);
        //create blank array to store the pool ID(s)
        $active_pools = array();
        //store all of the found pool's info in active_pools array:
        while($row = mysqli_fetch_assoc($result2)){
            $pool_id = $row['Pool ID'];
            //get info of pool for given pool id
            $pool_title_query = "SELECT * FROM  `Pool` WHERE `Pool ID` = '$pool_id'";
            $result3 = mysqli_query($this->cxn, $pool_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the active_pools array
            $active_pools[$pool_id] = $result3_array;
        }
        return $active_pools;
    }

    //GET LEADER POOLS METHOD
    //ACCEPTS USER'S EMAIL AS INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF ALL OF THE POOLS FOR WHICH THE GIVEN USER IS THE LEADER AS ARRAY KEYS AND ARRAYS CONSISTING OF THE POOL'S INFO AS VALUES
    public function GetLeaderPools($email){
        //find user id of current user
        $user_id = $this->GetUserIDFromEmail($email);
        //get the IDs of all of the current user's active pools
        $pool_query = "SELECT * FROM  `Pool` WHERE `Leader ID` = '$email'";
        $result = mysqli_query($this->cxn, $pool_query);
        //create blank array to store the pool ID(s)
        $leader_pools = array();
        while($pool_row = mysqli_fetch_assoc($result)){
            $pool_id = $pool_row['Pool ID'];
            //store pool info in leader_pools array with pool id as the array key
            $leader_pools[$pool_id] = $pool_row;
        }
        return $leader_pools;
    }



//*************************************************************************************

    /*
    POOL MEMBERSHIP METHODS ARE BELOW - SHOULD THESE BE IN THEIR OWN FILE?
    */

    /*
    GET POOL MEMBERS FUNCTION
    ACCEPTS POOL ID
    RETURNS AN ARRAY WHERE THE KEYS ARE THE USER_IDS OF THE POOL MEMBERS AND THE VALUES OF EACH KEY ARE THE POOL NICKNAME AND TIE BREAKER ANSWER OF EACH USER
    */
    public function GetPoolMembers($pool_id){
        $query = "SELECT * FROM `Pool Membership` WHERE `Pool ID` = '$pool_id'";
        $result = mysqli_query($this->cxn, $query);
        $pool_members_array = array();
        while($row = mysqli_fetch_assoc($result)){
            $user_id = $row['User ID'];
            if(!isset($row['Pool Nickname'])){
                $email_query = "SELECT `Email Address` FROM `User` WHERE `User ID` = '$user_id'";
                $email_result = mysqli_query($this->cxn, $email_query);
                $email_array = mysqli_fetch_assoc($email_result);
                $nickname = $email_array['Email Address'];
            }
            else{
                $nickname = $row['Pool Nickname'];
            }
                $tie_breaker_answer = $row['Tie-Breaker Answer'];
                $given_user_array = array();
                //store nickname and tie breaker answer for user in given_user_array
                $given_user_array['Nickname']=$nickname;
                $given_user_array['Tie-breaker Answer'] = $tie_breaker_answer;
                //store given_user_array into pool_members_array with the proper user_id as its respective key
                $pool_members_array[$user_id] = $given_user_array;
        }
        return $pool_members_array;
    }

    /*ADD USER TO POOL MEMBERSHIP METHOD
    ACCEPTS USER ID AND POOL ID
    ADDS GIVEN USER TO GIVEN POOL MEMBERSHIP TABLE
    */
    public function AddUserToPoolMembership($user_id, $pool_id){
        $pool_membership_query = "INSERT INTO `Pool Membership` (`User ID`, `Pool ID`) VALUES ('$user_id', '$pool_id');";
        $result3 = mysqli_query($this->cxn, $pool_membership_query);
    }


    /*GETNICKNAME METHOD
    **ACCEPTS USER ID AND POOL ID AS INPUTS
    **RETURNS GIVEN USER'S NICKNAME IN GIVEN POOL
    */
    public function GetNickname($user_id_input, $pool_id){
        if(!is_numeric($user_id_input)){ //if user_id_input is not numeric, it means we are receiving the user's email address
            //find the given user's user id based on their email address:
            $user_id = $this->GetUserIDFromEmail($user_id_input);
        }
        else{ //if we are receiving the user's user id number:
            $user_id = $user_id_input;
        }
        $query = new DB_Queries(); 
        $select_array = $this->CreateArrayFromDB_QueryInputs(
            'Pool Nickname',
            'TABLE:',
            'Pool Membership',
            'User ID', $user_id,
            'Pool ID', $pool_id
        );
        $nickname_result_array = $query->SelectFromDB($select_array);
        if(isset($nickname_result_array['Pool Nickname'])) {
            return $nickname_result_array['Pool Nickname'];
        }
        else{
            $select_array2 = $this->CreateArrayFromDB_QueryInputs(
                'Email Address',
                'TABLE:',
                'User',
                'User ID', $user_id
            );
            $no_nickname_result_array = $query->SelectFromDB($select_array2);
            return $no_nickname_result_array['Email Address'];
        }
    }


    /*UPDATE NICKNAME METHOD
    **CALLED FROM EDIT_NICKNAME CLASS (EDIT IN PLACE FUNCTIONALITY)
    **ACCETPS USER ID INPUT (EITHER EMAIL OR USER ID #), POOL ID, AND NEW NICKNAME VALUE
    **ESCAPES NICKNAME INPUT AND UPDATES POOL MEMBERSHIP TABLE WITH NEW NICKNAME.  DOES NOT RETURN ANYTHING
    */
    public function UpdateNickname($user_id_input, $pool_id, $new_nickname) {
        if(!is_numeric($user_id_input)){ //if user_id_input is not numeric, it means we are receiving the user's email address
            //find the given user's user id based on their email address:
            $user_id = $this->GetUserIDFromEmail($user_id_input);
        }
        else{ //if we are receiving the user's user id number:
            $user_id = $user_id_input;
        }
        $escaped_nickname_input = $this->escapeBadCharacters($new_nickname); //escape the input for special characters
        $nickname_query = "UPDATE  `Pool Membership` SET  `Pool Nickname` =  '$escaped_nickname_input' WHERE  `User ID` = '$user_id' AND  `Pool ID` = '$pool_id';";
        $nickname_result = mysqli_query($this->cxn, $nickname_query);
    }


    /*
    END OF POOL MEMBERSHIP METHODS
    */

//*************************************************************************************

    /*
    USER PICK METHODS ARE BELOW - SHOULD THESE BE IN THEIR OWN FILE?
    */

    /*GET USER PICKS METHOD
    **RETURNS ARRAY WHERE KEY IS CATEGORY ID AND ARRAY VALUES ARE:
    **    -IF POOL HAS NOT BEEN SCORED:  USER'S PICK FOR GIVEN CATEGORY
    **    -IF POOL HAS BEEN SCORED:  USER'S PICK AND 1 OR 0 SEPARATED BY COMMAS (1=CORRECT, 0=INCORRECT)
    **          E.G., WE WOULD RETURN "WINS, 1" AS AN ARRAY VALUE IF THE PICK IS "WINS" AND IT WAS MARKED AS CORRECT
    */
    public function GetUserPicks($user_id_input, $pool_id){
        if(!is_numeric($user_id_input)){ //if user_id_input is not numeric, it means we are receiving the user's email address
            //find the given user's user id based on their email address:
            $user_id = $this->GetUserIDFromEmail($user_id_input);
        }
        else{ //if we are receiving the user's user id number:
            $user_id = $user_id_input;
        }
        $query = "SELECT * FROM  `User Picks` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id'";
        $categories_result = mysqli_query($this->cxn, $query);
        $user_picks_array = array();
        while($row = mysqli_fetch_assoc($categories_result)){
            $category_id = $row['Category ID'];
            $pick = $row['Answer for main category'];
            $pick_correct = $this->GetUserPickCorrectStatus($category_id, $user_id, $pool_id);
            if(isset($pick_correct)) { //if the pick has been marked as "correct" or "incorrect" (meaning pool has finished and has been scored)
                $user_picks_array[$category_id] = $pick."|".$pick_correct;
            }
            else { //if pick has not yet been marketed as correct, we just return the pick by itself:
                $user_picks_array[$category_id] = $pick;
            }
        }
        return $user_picks_array;
    }

    /*UPDATEUSERPICK METHOD
    ACCEPTS USER'S EMAIL, POOL ID, CATEGORY ID, AND PICK INPUT VALUE AS MANDATORY ARGUMENTS
    RETURNS THE FOLLOWING RESULTS:
        2 - IF CATEGORY DID NOT ALREADY EXIST AND DB INSERT WAS SUCCESSFUL
    */
    public function UpdateUserPick($user_email, $pool_id, $category_id, $pick_value){
        $escaped_pick_input = $this->escapeBadCharacters($pick_value); //strip out bad characters from pick input
        //find the given user's user id based on their email address:
        $user_id = $this->GetUserIDFromEmail($user_email);  
        //QUERY TO SEE IF CATEGORY PICK ALREADY EXISTS
        $check_query = "SELECT * FROM  `User Picks` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id' AND `Category ID` = '$category_id'";
        $check_result = mysqli_query($this->cxn, $check_query);
        $check_result_array = mysqli_fetch_assoc($check_result);
        //CHECK IF PICK ALREADY EXISTS IN DB:
        if (isset($check_result_array)) {
            //if pick already exists in DB for this user:
            $existing_pick_query = "UPDATE  `User Picks` SET  `Answer for main category` =  '$escaped_pick_input' WHERE  `User ID` = '$user_id' AND  `Pool ID` = '$pool_id' AND  `Category ID` = '$category_id';";
            $result = mysqli_query($this->cxn, $existing_pick_query);
        }
        else{
            //if pick does not already exist in DB for this user:
            $new_pick_query = "INSERT INTO `User Picks` (`User ID`, `Pool ID`, `Category ID`, `Answer for main category`) VALUES ('$user_id', '$pool_id', '$category_id', '$escaped_pick_input');";
            $result = mysqli_query($this->cxn, $new_pick_query);
        }
        return $pick_value; //NOTE - AS OF 3/22/14, THIS RETURN VALUE ONLY GOES TO THE EDITINPLACE FORMS FOR USER PICKS FOR DISPLAY PURPOSES.  THIS VALUE IS NOT ESCAPED!
    }

    /*GetTieBreakerAnswer Method
    **Accepts user's email or ID and pool ID.  Returns the user's tie breaker answer for the given pool
    */
    public function GetTieBreakerAnswer($user_id_input, $pool_id){
        if(!is_numeric($user_id_input)){ //if user_id_input is not numeric, it means we are receiving the user's email address
            //find the given user's user id based on their email address:
            $user_id = $this->GetUserIDFromEmail($user_id_input);
        }
        else{ //if we are receiving the user's user id number:
            $user_id = $user_id_input;
        }
        $tie_breaker_query = "SELECT `Tie-breaker Answer` FROM  `Pool Membership` WHERE `User Id` = '$user_id' AND `Pool ID` = '$pool_id'";
        $result = mysqli_query($this->cxn, $tie_breaker_query);
        $tie_breaker_answer_array = mysqli_fetch_assoc($result);
        $tie_breaker_answer = $tie_breaker_answer_array['Tie-breaker Answer'];
        return $tie_breaker_answer;
    }

    public function UpdateTieBreakerAnswer($user_email, $pool_id, $input_value){
        $escaped_input_value = $this->escapeBadCharacters($input_value); //strip out bad characters from tie breaker input
        //find the given user's user id based on their email address:
        $user_id = $this->GetUserIDFromEmail($user_email);
        $tie_break_query = "UPDATE  `Pool Membership` SET  `Tie-Breaker Answer` = '$escaped_input_value' WHERE `User ID` = '$user_id' AND  `Pool ID` = '$pool_id';";
        $result = mysqli_query($this->cxn, $tie_break_query);
        return $escaped_input_value;
    }


    /*
    SCORE POOL METHODS ARE BELOW*************************************************
    */

    /*
    GetUserPickCorrectStatus method
    **Accetps category ID, user ID, and pool_id
    **Returns the "Answer Correct?" value from the table for the given pick
    */
    public function GetUserPickCorrectStatus($category_id, $user_id, $pool_id){
        $query = "SELECT * FROM  `User Picks` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id' AND `Category ID` = '$category_id'";
        $result = mysqli_query($this->cxn, $query);
        $answer_correct = mysqli_fetch_assoc($result);
        return $answer_correct['Answer Correct?']; 
    }

    /*
    **ScorePickManually method
    **Accetps category ID, user ID, pool ID, and the binary correct variable (1 for correct, 0 for incorrect)
    **Updates User_Picks table with correct/incorrect mark for given user pick.  Returns nothing.
    */
    public function ScorePickManually($category_id, $user_id, $pool_id, $correct) {
        if($correct == 1) { //if pick is being marked as correct:
            $query = "UPDATE  `User Picks` SET  `Answer Correct?` =  '1' WHERE  `User ID` = '$user_id' AND `Pool ID` = '$pool_id' AND `Category ID` = '$category_id';";
        }
        if($correct == 0) { //if pick is being marked as incorrect:
            $query = "UPDATE  `User Picks` SET  `Answer Correct?` =  '0' WHERE  `User ID` = '$user_id' AND `Pool ID` = '$pool_id' AND `Category ID` = '$category_id';";
        }
        $result = mysqli_query($this->cxn, $query);
    }

    public function UpdateCustomPoolTieBreakerAnswer($pool_id, $correct_tie_breaker_answer){
        $escaped_tie_breaker_input = $this->escapeBadCharacters($correct_tie_breaker_answer); //strip out bad characters from correct tie breaker input
        $update_tie_breaker_query = "UPDATE  `Pool` SET  `Tie-Breaker Correct Answer` = '$escaped_tie_breaker_input' WHERE  `Pool ID` = '$pool_id';";
        $update_tie_breaker_result = mysqli_query($this->cxn, $update_tie_breaker_query);
        return "Tie breaker answer successfully stored!";
    }

    public function GetCustomPoolTieBreakerAnswer($pool_id){
        $get_custom_pool_tie_breaker_query = "SELECT `Tie-Breaker Correct Answer` FROM `Pool` WHERE `Pool ID` = '$pool_id';";
        $result = mysqli_query($this->cxn, $get_custom_pool_tie_breaker_query);
        $result_array = mysqli_fetch_assoc($result);
        return $result_array['Tie-Breaker Correct Answer'];
    }


    /*GetCorrectChoiceForPoolCategory method
    **Accepts category ID
    **Returns the correct answer for the category as stored in the Pool Categories table
    */
    public function GetCorrectChoiceForPoolCategory($category_id){
        $query = "SELECT `Category Correct Answer` FROM `Pool Categories` WHERE `Category ID` = '$category_id';";
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result);
        return $result_array['Category Correct Answer'];
    }

    /*ScoreTemplateChoice method
    **This method is called when an Admin is scoring the choices for a pre-canned template
    **Accepts category id and choice id of correct choice as inputs
    **Updates "Category Choices" table so that "Correct?" field has "1" value for the given correct choice
    **Changes any "Correct?" values to 0's for category choices that are not being marked as correct

    **HOW TO UNDO A TEMPLATE CATEGORY SCORE ONCE IT HAS BEEN DONE:
        Change "Correct" fields for category ID in "Category Choices" table
        Change "Category Correct Answer" field in "Pool Categories" Table  for category ID (FOR ALL POOLS with given category)
        Change "Answer Correct?" field in "User Picks" table for category ID (for ALL USERS with given category ID)
    */
    public function ScoreTemplateChoice($category_id, $correct_choice_id){
        $reset_query = "UPDATE `Category Choices` SET `Correct?` = '0' WHERE `Category ID` = '$category_id';";
        $reset_result = mysqli_query($this->cxn, $reset_query);
        if($correct_choice_id == "000NA000"){ //if we are resetting the category being correct/incorrect:
            unset($correct_answer);
        }
        else{ //if we are marking an actual choice as correct for the category:
            $correct_query = "UPDATE `Category Choices` SET `Correct?` = '1' WHERE `Choice ID` = $correct_choice_id;";
            $result2 = mysqli_query($this->cxn, $correct_query);
            $correct_answer_query = "SELECT `Choice` FROM `Category Choices` WHERE `Choice ID` = $correct_choice_id";
            $correct_answer_result = mysqli_query($this->cxn, $correct_answer_query);
            $correct_answer_array = mysqli_fetch_assoc($correct_answer_result);
            $correct_answer = $correct_answer_array['Choice'];
        }
        //UPDATE POOL CATEGORIES TABLE:
            if(!isset($correct_answer)) { //if we are resetting the category:
                $mark_category_correct_query = "UPDATE `Pool Categories` SET `Category Correct Answer` = NULL WHERE `Category ID` = $category_id";
            }
            else{ //if we are marking a choice correct:
                $mark_category_correct_query = "UPDATE `Pool Categories` SET `Category Correct Answer` = '$correct_answer' WHERE `Category ID` = $category_id";
            }
            $result1 = mysqli_query($this->cxn, $mark_category_correct_query);
        //UPDATE USER PICKS TABLE:
            $answer_check_query = "SELECT `User ID`, `Answer for Main Category` FROM `User Picks` WHERE `Category ID` = '$category_id'";
            $answer_check_result = mysqli_query($this->cxn, $answer_check_query);
            while($row = mysqli_fetch_assoc($answer_check_result)) {
                $user_id = $row['User ID'];
                if(!isset($correct_answer)){ //if we are resetting the category
                    $mark_user_pick_query = "UPDATE `User Picks` SET  `Answer Correct?` = NULL WHERE  `User ID` = '$user_id' AND  `Category ID` = '$category_id';";
                }
                elseif($correct_answer == $row['Answer for Main Category']) { //if we are not resetting the category and the user's choice is correct
                    $mark_user_pick_query = "UPDATE `User Picks` SET `Answer Correct?` = '1' WHERE `User ID` = '$user_id' AND `Category ID` = '$category_id';";
                }
                else{ //if the user's choice is not correct
                    $mark_user_pick_query = "UPDATE `User Picks` SET `Answer Correct?` = '0' WHERE `User ID` = '$user_id' AND `Category ID` = '$category_id';";
                }
                $mark_user_pick_result = mysqli_query($this->cxn, $mark_user_pick_query);
            }
        return $correct_choice_id;
    }

    public function GetTemplateTieBreakerAnswer($template_id){
        $get_template_tie_breaker_query = "SELECT `Tie-Breaker Correct Answer` FROM `Templates` WHERE `Template ID` = '$template_id';";
        $result = mysqli_query($this->cxn, $get_template_tie_breaker_query);
        $result_array = mysqli_fetch_assoc($result);
        return $result_array['Tie-Breaker Correct Answer'];
    }


    public function UpdateTemplateTieBreakerAnswer($template_id, $correct_tie_breaker_answer){
        $escaped_tie_breaker_input = $this->escapeBadCharacters($correct_tie_breaker_answer); //strip out bad characters from correct tie breaker input
        $update_tie_breaker_query = "UPDATE `Templates` SET `Tie-Breaker Correct Answer` = '$escaped_tie_breaker_input' WHERE `Template ID` = '$template_id';";
        $update_tie_breaker_result = mysqli_query($this->cxn, $update_tie_breaker_query);
        return "Tie breaker answer successfully stored!";
    }


    /*GetCorrectChoiceForTemplateCategory Method
    **Accepts category ID number as only input
    **Returns an array where key is choice id and value is the choice marked as correct for the given category if one exists
    **Or, if no choice has been marked as correct, the method returns 0
    */
    public function GetCorrectChoiceForTemplateCategory($category_id){
        $query = "SELECT * FROM `Category Choices` WHERE `Category ID` = '$category_id' AND `Correct?` = '1';";
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result);
        if(isset($result_array)){
            $return_array = array();
            $return_array[$result_array['Choice ID']] = $result_array['Choice'];
            return $return_array;
        }
        else{
            return "0";
        }
    }


    /*FinalizeTemplateScores Method
    **Accepts the template ID which we are finalizing and an option no_email variable
    **If the no_email variable is not null, then we tell the CalculatePoolScore method to not send any emails to pool members
    **Checks to make sure all of the template's categories have correct answers marked, if not, we display an error and exit
    **If all template categories have correct answers, we update the "answer correct?" field in the Pool Categories table with the correct answer for the given category
    **We then update the User Picks table with 1's for each user pick that was correct and 0's for each user pick that was incorrect for the given categories
    **NOTE - THIS METHOD CAN BE CALLED REPEATEDLY FOR THE SAME TEMPLATE ID - IT WILL RESET ALL PREVIOUS RECORDS WITH ANY NEW ONES
    **Returns an array containing the correct answers as values and the appropriate category IDs as the array keys ($template_correct_array)
    */
    public function FinalizeTemplateScores($template_id, $no_email = NULL){
        $template_category_array = $this->GetTemplateCategories($template_id);
        $template_correct_array = array();
        foreach($template_category_array as $category_id => $category_info) { //this FOREACH statement checks to make sure each category has a correct answer marked
            $template_category_correct_status = $this->GetCorrectChoiceForTemplateCategory($category_id);
            if($template_category_correct_status == 0) { //if one or more categories from the template has not yet been marked as correct:
                return "<p style='color:red'>Not all choices marked as correct!<p>";
                exit();
            }
            else{ //if category has a marked correct answer, store it in the template_correct_array with the given category id as the array key:
                $template_correct_array[$category_id] = reset($template_category_correct_status);
            }
        }
        foreach($template_correct_array as $category_id => $correct_answer) { //assuming we haven't exited the method yet, we now store the correct answer for each category in the proper DB tables:
            
            //UPDATE POOL CATEGORIES TABLE:
            $mark_category_correct_query = "UPDATE `Pool Categories` SET `Category Correct Answer` = '$correct_answer' WHERE `Category ID` = $category_id";
            $result1 = mysqli_query($this->cxn, $mark_category_correct_query);

            //UPDATE USER PICKS TABLE:
            $answer_check_query = "SELECT `User ID`, `Answer for Main Category` FROM `User Picks` WHERE `Category ID` = '$category_id'";
            $answer_check_result = mysqli_query($this->cxn, $answer_check_query);
            while($row = mysqli_fetch_assoc($answer_check_result)) {
                $user_id = $row['User ID'];
                if($correct_answer == $row['Answer for Main Category']) {
                    $mark_user_pick_query = "UPDATE `User Picks` SET `Answer Correct?` = '1' WHERE `User ID` = '$user_id' AND `Category ID` = '$category_id';";
                }
                else{
                    $mark_user_pick_query = "UPDATE `User Picks` SET `Answer Correct?` = '0' WHERE `User ID` = '$user_id' AND `Category ID` = '$category_id';";
                }
                $mark_user_pick_result = mysqli_query($this->cxn, $mark_user_pick_query);
            }
        }
        //Get correct tie breaker answer for template from Templates table:
            $get_correct_template_tie_breaker_answer_query = "SELECT `Tie-Breaker Correct Answer` FROM `Templates` WHERE `Template ID` = '$template_id'";
            $get_correct_template_tie_breaker_answer_result = mysqli_query($this->cxn, $get_correct_template_tie_breaker_answer_query);
            $correct_tie_breaker_array = mysqli_fetch_assoc($get_correct_template_tie_breaker_answer_result);
        //Store correct tie breaker answer from Templates table in $correct_tie_breaker_answer:
            $correct_tie_breaker_answer = $correct_tie_breaker_array['Tie-Breaker Correct Answer'];
        //CLOSE OUT ALL POOLS ASSOCIATED WITH THE TEMPLATE ID BY RUNNING THE CALCULATEPOOLSCORE METHOD WITH THE "FINALIZE" VARIABLE SET
        //THIS SETS "USER'S FINAL SCORE" FIELDS IN POOL MEMBERSHIP TABLE AND THE "POOL WINNER"/"TIE BREAKER CORRECT ANSWER" FIELDS IN POOL TABLE WHICH EFFECTIVELY CLOSE OUT THE POOL
            $close_pool_query = "SELECT `Pool ID` FROM `Pool` WHERE `Template ID` = '$template_id' AND `Live?` = '1';";
            $close_pool_result = mysqli_query($this->cxn, $close_pool_query);
        //For each Pool
            while($row = mysqli_fetch_assoc($close_pool_result)) {
                $pool_id = $row['Pool ID'];
                //UPDATE TIE BREAKER ANSWERS IN ALL ASSOCIATED POOLS:
                $update_pool_tie_breaker_answer_query = "UPDATE `Pool` SET `Tie-Breaker Correct Answer` = '$correct_tie_breaker_answer' WHERE `Pool ID` = '$pool_id';";
                mysqli_query($this->cxn, $update_pool_tie_breaker_answer_query);
                if(is_null($no_email)){ //IF WE WANT TO SEND POOL ENDING EMAILS
                    $this->CalculatePoolScore($pool_id, 1); //finalize variable set to 1 means that we are closing out the pool (THIS SENDS POOL ENDING EMAILS)
                }
                else{ //IF WE DO NOT WANT TO SEND POOL ENDING EMAILS
                    $this->CalculatePoolScore($pool_id, 1, 1); //no_email variable set to 1 means that we are closing out the pool (THIS DOES NOT SEND POOL ENDING EMAILS)
                }
            }     
        //END POOL-CLOSE-OUT CODE
        return $template_correct_array;
    }


    /*
    **CalculatePoolScore method
    **Accepts Pool ID and the optional Finalize and No_email variables.  
    **If Finalize variable is set and equal to 1, it means we are calculating the final pool score 
    **If No_email is set at all (i.e., not null), we will not send out any emails to the pool members
    **Calculates scores and stores them in the User Picks table
    **Calculates a Pool winner and stores the result in the Pool Table if there is no tie for the pool
    **Sends out emails to pool members telling them that the pool is over and results are in (if no_email is set to null)
    **Returns an $users_points_array where array key is user ID's and array values are each user's final point values
    */
    public function CalculatePoolScore($pool_id, $finalize = NULL, $no_email = NULL){
        $pool_members_array = $this->GetPoolMembers($pool_id); //get list of members for given pool
        foreach($pool_members_array as $user_id => $user_info){ //run through array of users and calculate their point values for the pool:
            $get_user_correct_answers_query = "SELECT * FROM  `User Picks` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id' AND `Answer Correct?` = '1';";
            $correct_picks_result = mysqli_query($this->cxn, $get_user_correct_answers_query); //get list of categories for which the user picked correctly
            $correct_categories_for_user_array = array();
            while($row = mysqli_fetch_assoc($correct_picks_result)) {
                //store each correct category for the given user id in an array:
                $correct_categories_for_user_array[] = $row['Category ID'];
            }
            $user_points = 0; //set user's point value to 0 to start
            foreach($correct_categories_for_user_array as $category_id) { //run through list of categories that the given user got correct
                $points_for_correct_category_query = "SELECT `Category Point Value` FROM `Pool Categories` WHERE `Category ID` = '$category_id';";
                $points_for_correct_category_result = mysqli_query($this->cxn, $points_for_correct_category_query);
                //get point value for given correct category:
                $points_for_correct_category = mysqli_fetch_assoc($points_for_correct_category_result); 
                //add the point value for given correct category to user's overall point value:
                $user_points = $user_points + $points_for_correct_category['Category Point Value'];
            }
            if(isset($finalize)) { //if we are calculating the final score:
                $set_user_final_score_query = "UPDATE  `Pool Membership` SET  `User's Final Score` =  '$user_points' WHERE  `User ID` = '$user_id' AND `Pool ID` ='$pool_id';";
                $store_final_score_result = mysqli_query($this->cxn, $set_user_final_score_query); //store final point value in pool membership table for user/pool
            }
            $users_points_array[$user_id] = $user_points;
        }
        arsort($users_points_array); //sort user points array in descending order
        //reset pointer so that we are pointing to the first element in the array.  after the arsort function above, this will correspond to the pool winner
        reset($users_points_array); 
        if(isset($finalize)) {
            $highest_score = max($users_points_array); //find the highest score value amongst pool participants
            $highest_scoring_users_array = array_keys($users_points_array, $highest_score); //find all participants which had the highest score value
            $number_of_top_scorers = count($highest_scoring_users_array); //count how many participants in the pool had the highest score value
            if($number_of_top_scorers <> 1) { //if more than one participant had the highest score, it means there is a tie:
                $pool_winner_id = $this->TieBreaker($pool_id, $highest_scoring_users_array);
            }
            else{ //if there was only one participant with the highest score value, there was no tie:
                $pool_winner_id = key($users_points_array); //get user id of pool winner - it will automatically be the first value in the sorted users_points_array array
            }
            $store_pool_winner_query = "UPDATE  `Pool` SET  `Pool Winner` =  '$pool_winner_id' WHERE  `Pool ID` ='$pool_id';";
            $store_winner_result = mysqli_query($this->cxn, $store_pool_winner_query); //store user ID of pool winner in pool table
            if(is_null($no_email)){ //IF WE WANT TO SEND POOL ENDING EMAILS
                $this->SendPoolEndingEmails($pool_id); //send pool ending emails
            }
        }
        //The following code makes sure that the actual pool winner's ID is first in the user_points_array in the event of a tie
        //This ensures that the actual pool winner's name appears first in a pool member table after a pool has ended
        $this_pool_data = $this->GetPoolData($pool_id); //get pool data
        if(isset($this_pool_data['Pool Winner'])){ //if a pool winner is set:
            $pool_winner_id = $this_pool_data['Pool Winner'];
            //move pool winner ID to beginning of array
            //found this technique here: http://stackoverflow.com/questions/11276313/php-move-associative-array-element-to-beginning-of-array
            $users_points_array = array($pool_winner_id => $users_points_array[$pool_winner_id]) + $users_points_array;
        }
        //End of code for making sure actual pool winner's ID is at beginning of user_points_array 
        return $users_points_array; 
    }


    /*
    **TIEBREAKER METHOD
    **ACCEPTS POOL ID AND AN ARRAY CONTAINING THE POOL MEMBERS WHO TIED FOR THE HIGHEST SCORE
    **CHOOSES A WINNER AND RETURNS THE WINNER'S USER ID
    */
    public function TieBreaker($pool_id, $highest_scoring_users_array){
        $correct_tie_breaker_answer_query = "SELECT `Tie-Breaker Correct Answer` FROM `Pool` WHERE `Pool ID` = '$pool_id';";
        $correct_tie_breaker_answer_result = mysqli_query($this->cxn, $correct_tie_breaker_answer_query);
        $correct_tie_breaker_answer_array = mysqli_fetch_assoc($correct_tie_breaker_answer_result);
        $correct_tie_breaker_answer = $correct_tie_breaker_answer_array['Tie-Breaker Correct Answer'];
        $tie_breaker_result_array = array(); //this will be the array that contains each user's tie breaker result
            //tie breaker results values are either the difference between the user's tie breaker answer and the correct one (if the tie breaker is numeric) or a 1 if the user's answer is wrong or a 0 if the user's answer is right (if the tie breaker is not numeric)
        foreach($highest_scoring_users_array as $index => $user_id){
            $user_tie_breaker_answer_query = "SELECT `Tie-Breaker Answer` FROM  `Pool Membership` WHERE `Pool ID` = '$pool_id' AND `User ID` = '$user_id';";
            $user_tie_breaker_answer_result = mysqli_query($this->cxn, $user_tie_breaker_answer_query);
            $user_tie_breaker_answer_array = mysqli_fetch_assoc($user_tie_breaker_answer_result);
            $user_tie_breaker_answer = $user_tie_breaker_answer_array['Tie-Breaker Answer'];
            if(is_numeric($correct_tie_breaker_answer)){ //if tie breaker answer is numeric:
                $difference = abs($correct_tie_breaker_answer - $user_tie_breaker_answer);
                $tie_breaker_result_array[$user_id] = $difference; 
            }
            else{ //if tie breaker answer is not numeric:
                if($correct_tie_breaker_answer == $user_tie_breaker_answer){ //if user's tie breaker answer was correct:
                    $tie_breaker_result_array[$user_id] = 0; //better to have a lower tie breaker number
                }
                else{ //if user's tie breaker answer was incorrect:
                    $tie_breaker_result_array[$user_id] = 1;
                }
            }
        }
        asort($tie_breaker_result_array); ////sort tie breaker result array in ascending order (lowest value is the winner)
        reset($tie_breaker_result_array);
        return key($tie_breaker_result_array);
    }

    /*
    **GET FINAL POOL RANKINGS FUNCTION
    **ACCEPTS POOL ID AS INPUT
    **CHECKS TO MAKE SURE 'Pool Winner' IS SET IN POOL TABLE (IF NOT, THEN POOL HAS NOT BEEN SCORED)
    **RETURNS AN ASSOCIATE ARRAY WHERE KEYS ARE USER IDS AND KEY VALUES ARE THE USER'S POINT VALUES FOR POOL
    **RETURN ARRAY IS SORTED FROM HIGHEST TO LOWEST POINT VALUE
    */
    public function GetFinalPoolRankings($pool_id){
        $pool_winner_check_query = "SELECT `Pool Winner` FROM `Pool` WHERE `Pool ID` ='$pool_id';";
        $pool_winner_check_result = mysqli_query($this->cxn, $pool_winner_check_query);
        $pool_winner_check_array = mysqli_fetch_assoc($pool_winner_check_result); 
        if(isset($pool_winner_check_array['Pool Winner'])) { //if pool winner is set and pool has been completed and scored:
            $pool_members_array = $this->GetPoolMembers($pool_id); //get list of members for given pool
            $pool_rankings_array = array();
            foreach($pool_members_array as $user_id => $user_info){ //run through array of users
                $get_user_final_score_query = "SELECT `User's Final Score` FROM  `Pool Membership` WHERE  `User ID` = '$user_id' AND `Pool ID` ='$pool_id';";
                $get_user_final_score_result = mysqli_query($this->cxn, $get_user_final_score_query);
                $get_user_final_score_array = mysqli_fetch_assoc($get_user_final_score_result); 
                $pool_rankings_array[$user_id] = $get_user_final_score_array["User's Final Score"]; //store user's nickname and final score in pool_rankings_array
            }
            arsort($pool_rankings_array); //sort pool_rankings_array by point value
            //put pool winner at the beginning of the pool_rankings_array (necessary in the case of ties)
            $pool_rankings_array = array($pool_winner_check_array['Pool Winner'] => $pool_rankings_array[$pool_winner_check_array['Pool Winner']]) + $pool_rankings_array;
            return $pool_rankings_array;
        }
        else { //if pool has NOT been completed and scored:
            return "Pool Winner is not set!"; 
        }
    }


    /*
    END OF SCORE POOL METHODS*************************************************
    */


    /*GetUserIDFromEmail method
    **Accepts an email and returns the user ID associated with the email from DB
    */
    public function GetUserIDFromEmail($user_email){
        //find the given user's user id based on their email address:
        $user_id_query = "SELECT `User ID` FROM  `User` WHERE `Email Address` = '$user_email'";
        $user_id_result = mysqli_query($this->cxn, $user_id_query);
        $user_id_result_array = mysqli_fetch_assoc($user_id_result);
        $user_id = $user_id_result_array['User ID'];
        return $user_id;
    }

    /* END OF USER PICK METHODS
    */


//*************************************************************************************


    /* POOL TEMPLATE METHODS
    */

    public function CreateNewTemplate($template_title, $overall_question, $description=NULL, $tie_question=NULL){
        //ESCAPE BAD CHARACTERS FROM STRING INPUTS:
            $escaped_title = $this->escapeBadCharacters($template_title); 
            $escaped_overall_question = $this->escapeBadCharacters($overall_question); 
            $escaped_description = $this->escapeBadCharacters($description); 
            $escaped_tie_question = $this->escapeBadCharacters($tie_question); 
        $create_template_query = "INSERT INTO `Templates` (`Template Name`, `Template Description`, `Overall Question`, `Tie Breaker Question`) VALUES ('$escaped_title', '$escaped_description', '$escaped_overall_question', '$escaped_tie_question');";
        if($result = mysqli_query($this->cxn, $create_template_query)){
            $new_template_id = mysqli_insert_id($this->cxn);
            $return_variable = array(2, "<p>Template created successfully!</p>", $new_template_id);
        }
        else{
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
        return $return_variable;
    }


    public function GetBasicTemplateInfo($template_id){
        $query = "SELECT * FROM `Templates` WHERE `Template ID` = '$template_id';";
        $result = mysqli_query($this->cxn, $query);
        $template_info = mysqli_fetch_assoc($result);
        return $template_info;
    }


    //GET ALL TEMPLATES METHOD
    //NO INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF TEMPLATES AS ARRAY KEYS AND ARRAYS CONSISTING OF THE TEMPLATE'S INFO AS VALUES
    //AS OF 4/1/14, THIS IS ONLY CALLED FROM HOME.PHP WHEN THE USER IS AN ADMIN
    public function GetAllTemplates(){
        //get all templates:
        $templates_query = "SELECT * FROM  `Templates`;";
        $result = mysqli_query($this->cxn, $templates_query);
        //create blank array to store the pool ID(s)
        $list_of_templates = array();
        //store all of the found pool's info in list_of_templates array:
        while($row = mysqli_fetch_assoc($result)){
            $template_id = $row['Template ID'];
            //get info of pool for given pool id
            $template_title_query = "SELECT * FROM  `Templates` WHERE `Template ID` = '$template_id'";
            $result3 = mysqli_query($this->cxn, $template_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the list_of_templates array
            $list_of_templates[$template_id] = $result3_array;
        }
        return $list_of_templates;
    }

    //GET PUBLISHED TEMPLATES METHOD
    //NO INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF PUBLISHED TEMPLATES AS ARRAY KEYS AND ARRAYS CONSISTING OF THE TEMPLATE'S INFO AS VALUES
    //AS OF 4/1/14, THIS IS ONLY CALLED FROM BROWSE_TEMPLATES.PHP 
    public function GetPublishedTemplates(){
        //get all templates:
        $templates_query = "SELECT * FROM  `Templates` WHERE `Live?` = '1';";
        $result = mysqli_query($this->cxn, $templates_query);
        //create blank array to store the pool ID(s)
        $list_of_templates = array();
        //store all of the found pool's info in list_of_templates array:
        while($row = mysqli_fetch_assoc($result)){
            $template_id = $row['Template ID'];
            //get info of pool for given pool id
            $template_title_query = "SELECT * FROM  `Templates` WHERE `Template ID` = '$template_id'";
            $result3 = mysqli_query($this->cxn, $template_title_query);
            $result3_array = mysqli_fetch_assoc($result3);
            //store pool info into the list_of_templates array
            $list_of_templates[$template_id] = $result3_array;
        }
        return $list_of_templates;
    }



    /*GetTemplateCategories method
    **Accepts a template ID as the input
    **Returns an array where keys are category IDs and array values contain all fields from "Pool Categories" DB table for each category
    */
    public function GetTemplateCategories($template_id){
        $query = "SELECT `Category ID` FROM  `Pool Categories` WHERE `Template ID` = '$template_id'";
        if($result = mysqli_query($this->cxn, $query)){ //result here is all the category IDs associated with the given template ID
            $category_array = array(); //create blank array to store category IDs
            while($row = mysqli_fetch_assoc($result)){
                $category_id = $row['Category ID'];
                //get category info for the given category:
                $category_query = "SELECT * FROM  `Pool Categories` WHERE `Category ID` = '$category_id'";
                $result2 = mysqli_query($this->cxn, $category_query); //result here is all of the info in the table for a given category ID
                $result2_array = mysqli_fetch_assoc($result2); //store given category's info as result2_array
                $category_array[$category_id] = $result2_array; //store array of given category's info in $category_array ($cateogry_array is an array of arrays)
            }
            return $category_array;
        }
        else {
            //return zero if the database query failed
            return 0;
        }
    }

    //ADD TEMPLATE CATEGORY METHOD
    //RETURNS CATEGORY ID OF THE NEW CATEGORY
    public function AddTemplateCategory($template_id, $category_name, $category_pt_value, $multiple_choice){
        $escaped_name_input = $this->escapeBadCharacters($category_name); //strip out bad characters from input
        $escaped_point_input = $this->escapeBadCharacters($category_pt_value); //strip out bad characters from input
        $query = "INSERT INTO `Pool Categories` (`Template ID`, `Category Name`, `Category Point Value`, `Category Multiple Choice?`) VALUES ('$template_id', '$escaped_name_input', '$escaped_point_input', '$multiple_choice');";
        $result = mysqli_query($this->cxn, $query);
        return mysqli_insert_id($this->cxn);
    }


    public function UpdateTemplateData($template_id, $template_item, $input){
        /*
        TEMPLATE_ITEM VARIABLE: The pool_item variable will be the ID of the HTML element containing the editable field for the given pool item (e.g., category name, category point value, etc)
            This ID will be in the form of "category_X_span###" where the "X" tells us what type of category item is to be edited:
                If X = "n" we want to update the category name
                If X = "p" we want to update the category point value
            The number ("###") at the end of the ID tells us which category ID we are editing
        */
        $escaped_input = $this->escapeBadCharacters($input); //strip out bad characters from input
        $category_check = substr_compare(substr($template_item,0,8),"category",0,8); //get first 8 characters of pool item id and check to see if they are "category" - if so, we are updating a category or a category's point value and need to write to the 'Pool Category' table
        if($category_check == 0){ 
            //if we are writing to the 'Pool Category' table:
            $category_id = substr($template_item,15); //get the number at the end of the category_span ID which is the category ID in the table
            $category_item = $template_item[9]; //9th position in the $template_item string tells us which column in the pool categories table we are updating 
            switch ($category_item) {
                case "n"; //if we're updating the category name:
                    $query = "UPDATE  `Pool Categories` SET  `Category Name` =  '$escaped_input' WHERE  `Category ID` ='$category_id' AND `Template ID` ='$template_id';";
                    break;
                case "p"; //if we're updating the category point value:
                    $query = "UPDATE  `Pool Categories` SET  `Category Point Value` =  '$escaped_input' WHERE  `Category ID` ='$category_id' AND `Template ID` ='$template_id';";
                    break;   
            }
            $result = mysqli_query($this->cxn, $query);
        }
        else{
            //if the pool item is anything but a category or a category's point value, we write to the 'Pool' table
            $query = "UPDATE  `Templates` SET  `$template_item` =  '$escaped_input' WHERE  `Template ID` ='$template_id';";
            $result = mysqli_query($this->cxn, $query);
            return $escaped_input;
        }
    }

    /*
    **ChangeTemplateLiveVariable function
    **Accepts template id and an action variable (as of 4/1/14, only send_pool_data.php is sending us this info/calling this function)
    **Action variable is a 1 if we are publishing a template and 0 if we are retiring a template
    **Does not return anything
    */
    public function ChangeTemplateLiveVariable($template_id, $action){
        $query = "UPDATE  `Templates` SET  `Live?` =  '$action' WHERE  `Template ID` ='$template_id';";
        $result = mysqli_query($this->cxn, $query);
    }


    //DELETE TEMPLATE METHOD (ONLY ACCESSIBLE BY ADMINS - SEE HOME.PHP AND DELETE_TEMPLATE_OR_SERIES.PHP FILES FOR LOGIC)
    public function DeleteTemplate($template_id){
        //DELETE ENTRY FROM POOL TABLE:
            $delete_template_id = "DELETE FROM `Templates` WHERE `Template ID` = '$template_id'";
            $result1 = mysqli_query($this->cxn, $delete_template_id);
        //DELETE ENTRIES FROM CATEGORY CHOICES TABLE 
            $get_template_category_ids_query = "SELECT `Category ID` FROM `Pool Categories` WHERE `Template ID` = '$template_id';";
            $result2 = mysqli_query($this->cxn, $get_template_category_ids_query);
            while($row = mysqli_fetch_assoc($result2)){
                $category_id = $row['Category ID'];
                $delete_category_choices_query = "DELETE FROM `Category Choices` WHERE `Category ID` = '$category_id';";
                $result3 = mysqli_query($this->cxn, $delete_category_choices_query);
            }
        //DELETE ENTRIES FROM THE POOL CATEGORIES TABLE 
            $delete_template_categories_query = "DELETE FROM `Pool Categories` WHERE `Template ID` = '$template_id'";
            $result4 = mysqli_query($this->cxn, $delete_template_categories_query);
    } //END OF DELETE TEMPLATE METHOD


/* END POOL TEMPLATE METHODS
*/

//*************************************************************************************


/*
**GETPOOLWINNERS FUNCTION
**Takes no input
**Returns an array where keys are pool ID and only value is the pool nickname of the winner
**If no nickname was chosen, a NULL value is returned
*/

    public function GetPoolWinners() {
        $number_to_return = 5; //SET THE NUMBER OF WINNERS TO BE RETURNED BY THIS FUNCTION HERE
        //GET RECENT WINNERS FROM POOL TABLE
            $recent_winners_query = "SELECT * FROM  `Pool` ORDER BY `Pool ID` DESC LIMIT 0,$number_to_return;";
            $result1 = mysqli_query($this->cxn, $recent_winners_query);
            //$recent_pools_array = mysqli_fetch_assoc($result1);
            $pool_winnder_array = array();
            while($row = mysqli_fetch_assoc($result1)){
                $pool_id = $row['Pool ID'];
                $winner_user_id = $row['Pool Winner'];
                $nickname_query = "SELECT `Pool Nickname` FROM  `Pool Membership` WHERE `User ID` = '$winner_user_id' AND `Pool ID` = '$pool_id';";
                $result2 = mysqli_query($this->cxn, $nickname_query);
                $nickname_array = mysqli_fetch_assoc($result2);
                $pool_winnder_array[$pool_id] = $nickname_array['Pool Nickname'];
            }
            return $pool_winnder_array;
    }

    


    public function SendPoolEndingEmails($pool_id) {
        //get pool title:
            $pool_title_query = "SELECT `Title` FROM  `Pool` WHERE `Pool ID` = '$pool_id';";
            $title_result = mysqli_query($this->cxn, $pool_title_query);
            $title_result_array = mysqli_fetch_assoc($title_result);
            $pool_title = $title_result_array['Title'];
        include 'send_mail.php'; //include email file
        $pool_members_array = $this->GetPoolMembers($pool_id);
        foreach($pool_members_array as $user_id => $user_info){
            $email_query = "SELECT `Email Address` FROM  `User` WHERE  `User ID` =  '$user_id'";
            $email_result = mysqli_query($this->cxn, $email_query);
            $email_result_array = mysqli_fetch_assoc($email_result);
            $email = $email_result_array['Email Address'];
            SendEmail($email, "Pool has ended on ".BRAND_NAME.".com!", 
                "The pool ".$pool_title." has ended and the results are in!  
                \n\nGo to ".DOMAIN." to see the results!"
                );
        }
    }


//*************************************************************************************

/* POOL SERIES METHODS
*/


    public function CreateNewPoolSeries($Leader , $pool_series_title , $overall_topic, $pool_series_description, $public_status){
        $query = new DB_Queries(); //new instance of the DB_Queries object
        $public_private_variable = 0;
        if($public_status == "private"){ //if pool is private:
            $public_private_variable = 1;
        }
        $Leader = 1; //JUST FOR TESTING PURPOSES
        //prepare insert array:
        $insert_array = $this->CreateArrayFromDB_QueryInputs(
            'Pool Series',
            'Title', $pool_series_title, 
            'Description', $pool_series_description, 
            'Leader ID', $Leader, 
            'Overall Topic', $overall_topic, 
            'Private?', $public_private_variable
        );
        //insert new pool series info into DB:
        $return_variable = $query->InsertIntoDB($insert_array);
        return $return_variable;
    }

    //GET POOL SERIES DATA METHOD
    //accepts the pool series ID as the input
    //if pool series id is valid, the method returns an array with all of the fields from the given pool series row in Pool Series table in database
    //if pool series id is not valid, method returns 0
    public function GetPoolSeriesData($pool_series_id){
        $query = new DB_Queries(); //new instance of the DB_Queries object
        $select_array = $this->CreateArrayFromDB_QueryInputs(
            '*',
            'TABLE:',
            'Pool Series',
            'Series ID', $pool_series_id
        );
        $return_variable = $query->SelectFromDB($select_array);
        if($return_variable[0] == 2){ //if we did not find a pool series for the given series id:
            $return_variable = array(2, "No Pool Series Found with that ID.");
            return $return_variable;
        }
        else{ //if we did find a pool series:
            return $return_variable;
        }
    }


    /*UpdateSeriesData METHOD
    //
    */
    public function UpdateSeriesData($series_id, $series_item, $input) {
        $query = new DB_Queries();
        $update_array = $this->CreateArrayFromDB_QueryInputs(
        //sample update query:   
        //UPDATE  `Pool Series` SET  `Title` = 'Pool Series Test 1a' WHERE `Series ID` =15;
            'Pool Series',
            $series_item, $input,
            'WHERE:',
            'Series ID', $series_id
        );
        $return_variable = $query->UpdateDB($update_array);
        return $return_variable;
    }
    

    //GET ALL SERIES METHOD
    //NO INPUT
    //RETURNS AN ARRAY CONTAINING THE ID'S OF SERIES AS ARRAY KEYS AND ARRAYS CONSISTING OF THE SERIES'S INFO AS VALUES
    //AS OF 9/1/14, THIS IS ONLY CALLED FROM HOME.PHP WHEN THE USER IS AN ADMIN
    public function GetAllSeries(){
        $query = new DB_Queries(); //new instance of the DB_Queries object
        $select_array = $this->CreateArrayFromDB_QueryInputs(
            '*',
            'TABLE:',
            'Pool Series'
        );
        $result = $query->SelectFromDB($select_array);
        //create blank array to store the series IDs and info:
        $list_of_series = array();
        //***BEGIN CHECK TO SEE HOW MANY DB RECORDS ARE IN THE RESULT ARRAY***
        //If there is more than one DB record in the result array, we need to run the while loop (in "else" part of IF statement below)
        $result_type = gettype($result);
        if($result_type == "array"){ //if only one pool series is found (meaning $result is just an associative array for the single pool series and not a mysqli object)
            $list_of_series[$result['Series ID']] = $result; //store pool series data in list_of_series variable with the series ID as the array key
        }
        else{ //if more than one pool series is found (meaning $result is a mysqli object, not an associative array)
            //store all of the found series info in list_of_series array:
            while($row = mysqli_fetch_assoc($result)) {
                $series_id = $row['Series ID'];
                //get info of pool for given series id:
                $select_series_info_array = $this->CreateArrayFromDB_QueryInputs(
                    '*',
                    'TABLE:',
                    'Pool Series',
                    'Series ID', $series_id
                );
                $result2 = $query->SelectFromDB($select_series_info_array);
                $list_of_series[$series_id] = $result2; //store series info into the list_of_series array
            }
        }
        //END OF RESULT ARRAY CHECK
        return $list_of_series;
    }


    /*
    **ChangeSeriesLiveVariable function
    **Accepts series id and an action variable (as of 9/7/14, only send_pool_data.php is sending us this info/calling this function)
    **Action variable is a 1 if we are publishing a series and 0 if we are retiring a series
    **Does not return anything
    */
    public function ChangeSeriesLiveVariable($series_id, $action){
        $query = new DB_Queries();
        $update_array = $this->CreateArrayFromDB_QueryInputs(
            'Pool Series',
            'Live?', $action,
            'WHERE:',
            'Series ID', $series_id
        );
        $return_variable = $query->UpdateDB($update_array);
    }

    //DELETE SERIES METHOD (ONLY ACCESSIBLE BY ADMINS - SEE HOME.PHP AND DELETE_TEMPLATE_OR_SERIES.PHP FILES FOR LOGIC)
    public function DeleteSeries($series_id){
        //DELETE ENTRY FROM POOL SERIES TABLE:
        $query = new DB_Queries();
        $delete_array = $this->CreateArrayFromDB_QueryInputs(
            'Pool Series',
            'Series ID', $series_id
        );
        $return_variable = $query->DeleteFromDB($delete_array);
        return $return_variable;
        //NOTE, MAY WANT TO WRITE ADDITIONAL CODE IN THIS METHOD TO DELETE POOLS ASSOCIATED WITH SERIES?  MAY NOT WANT TO AS WELL...  LEAVING THIS FOR NOW (9/7/14)

    } //END OF DELETE SERIES METHOD


/* END POOL SERIES METHODS
*/

//*************************************************************************************


    /* MISCELLANEOUS TOOLS (UNRELEATED TO POOLS)
    */


    /*timestampTo12HourConversion method
    **ACCEPTS A TIME STAMP IN THE FORM OF HH:MM:SS WHERE HH IS IN 24 HOUR TIME
    **CONVERTS 24 HOURS TO 12 HOURS
    **APPENDS AM OR PM AS APPROPRIATE
    */
    public function timestampTo12HourConversion($timestamp){
        $hour = substr($timestamp, 0, 2);
        if($hour > 11){ //if the time is in PM
            if($hour > 12) { //only convert to 12 hour time if hour is not equal to noon
                $hour = $hour - 12; //convert to 12 hr time
            }
            $timestamp = substr_replace($timestamp, $hour, 0, 2); //replace hour with 12 hr time
            $timestamp = substr_replace($timestamp, " PM", -3); //append PM and get rid of the seconds
        }
        else {
            if($hour < 1){
                $hour = $hour + 12; //convert 00:00 AM into 12:00 AM
            }
            else {
                $hour = $hour + 12 - 12; //get rid of the extra zero in front of the hour if there is one - i.e., 01:00 AM becomes 1:00 AM
            }
            $timestamp = substr_replace($timestamp, $hour, 0, 2); //replace hour with 12 hr time
            $timestamp = substr_replace($timestamp, " AM", -3); //append AM and get rid of the seconds
        }
        return $timestamp;
    }


    /*escapeBadCharacters method
    **ACCEPTS A STRING INPUT AND REMOVES BAD CHARACTERS FROM IT
    **RETURNS STRING WITH GOOD CHARACTERS
    */
    public function escapeBadCharacters($string) {
        $new_string1 = preg_replace('~[\\\\/:*?"<>|]~',"", $string); //filter #1
        $new_string2 = str_replace(array("%", "\\", "#", " - ", "&", "$", "^", "(", ")"),"", $new_string1); //filter #2
        $new_string3 = addcslashes($new_string2, "'");
        return $new_string3;
    }


    /*CreateArrayFromDB_QueryInputs method
    **Accepts any number of inputs to be sent to an MYSQL method in DB_Query class file
    **Returns an array with each input as an element
    **E.g., for an insert query: array(TABLE, field1, value1, field2, value2, field3, value3, ... )
    */
    public function CreateArrayFromDB_QueryInputs(){
        $return_array = array();
        $arg_list = func_get_args();
        $numargs = func_num_args();
        for ($i = 0; $i < $numargs; $i++) {
            $return_array[$i] = $arg_list[$i];
        }
        return $return_array;
    }

}


?>