<?php  

// Include the Autoloader for MailGun(see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

include_once "constants.inc.php";
include_once 'inc/class.pool.inc.php'; //as of 8/17/14, this is only here so that we have access to the escape bad characters function.  We should move that function into this class file once all pool requests call these methods
date_default_timezone_set('America/New_York'); //set timezone for getting the current time to be EST

class DB_Queries {

	private $cxn;

	public function __construct() { //this is the constructor - called whenever class is created  
	    //We connect to the database for the class
	    $this->cxn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME)
			or die("Could not connect to the server"); 
	}  //END OF CONSTRUCTOR METHOD FOR NEW INSTANCE OF USER CLASS


	/*SELECTFROM DB METHOD
	**ACCEPTS AN ARRAY OF THINGS TO SELECT.  THIS INPUT ARRAY MUST BE IN THIS FORMAT:
		field1, field2, ... , fieldn , TABLE:, table_name, where_field1, where_field_Value1, where_field2, where_field_Value2, ... 
		NOTES:
			field1, field2,...,fieldn can be replaced by * if we want to select all table columns
			TABLE: should be hardcoded in as its own array element - see method below for why
	**RETURNS THE RESULTING FETCHED ASSOCIATIVE ARRAY OR AN ERROR MESSAGE
	**ERROR CODES: 2 if no record found, 3 if DB connection failed
	*/
	public function SelectFromDB($select_array){
		$number_of_items_to_select = array_search('TABLE:', $select_array); //'TABLE:' is placed in select_array at the point in the array where array elements switch from being fields that we want to select to the table name that we want to select from
		$select_fields_array = array_slice($select_array,0,$number_of_items_to_select); //get the fields that we are selecting from the table
		if(count($select_fields_array) == 1 AND $select_fields_array[0] == '*'){ //if we are selecting all columns from a table:
			$field_component_of_select_statement = '*';
		}
		else{ //if we are selecting specific columns from a table:
			$field_component_of_select_statement = '`';
			foreach($select_fields_array as $index => $field_name){ //generate the "select" clause of the MYSQL statement
				$field_component_of_select_statement = $field_component_of_select_statement.$field_name.'`, `';
			}
			$field_component_of_select_statement = substr($field_component_of_select_statement, 0, -3); //remove last 3 characters of field list (which are '`, ')
		}
		$table = $select_array[$number_of_items_to_select+1]; //store table name that we are selecting from
		$where_array = array_slice($select_array,$number_of_items_to_select+2); //populate the WHERE clause of the MYSQL statement
		$size_of_where_array = count($where_array); //get the size of the where clause array.  divide this number by 2 to get the number of where parameters
		$where_component_of_select_statement = '`';
		for ($i = 0; $i < $size_of_where_array; $i++) { //generate the where clause of the MYSQL statement
		    $where_component_of_select_statement = $where_component_of_select_statement.$where_array[$i]."` = '".$where_array[$i+1]."' AND `";
		    $i++;
		}
		$where_component_of_select_statement = substr($where_component_of_select_statement, 0, -6); //remove last 6 characters of where clause (which are " AND `")
		$query = "SELECT $field_component_of_select_statement FROM  `$table` ";
		if($size_of_where_array > 0){ //only add the where clause if there is a WHERE clause specified in the original query
			$query = $query."WHERE  ".$where_component_of_select_statement;
		}
		if($result = mysqli_query($this->cxn, $query)) { //if we successfully run the select statement:
            if(mysqli_num_rows($result) == 0){ //if no records where found in select statement:
            	$return_variable = array(2, "No record found!", NULL);
            }
            else{ //if we found at least 1 record:
            	if(mysqli_num_rows($result) > 1){ //if there is more than one record returned from table:
            		$return_variable = $result; //we just return the mysqli object for the consumer to use/put into an associative array themselves
            	}
            	else{ //if there is only one record returned from table:
            		$return_variable = mysqli_fetch_assoc($result); //return an associative array for the returned record
            	}
            }
        }
        else{ //if there was a problem:
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
        return $return_variable;
        
	}

	public function InsertIntoDB($insert_array){
		//Insert Array should have the first element be the Table name into which we are inserting
		//Additional elements after the first should be field name/value pairs that we are inserting
		//Either returns success message and new series ID, or returns an error message if something went wrong

		$insert_value_array = array(); //this will be the array of values to be inserted into table
		$insert_field_array = array(); //this will be the array of fields in which to insert values
		$pool = new Pool(); //this is only here so that we can use the escape bad characters method below
		$value_counter = 0;
		$field_counter = 0;
		foreach($insert_array as $index => $input_value){ //for each element of the input array:
			if($index == 0){ //if this is the first array element (meaning that it is the table name)
				$table_name = $input_value;
			}
			elseif ($index % 2 == 0) { //if the array key is even (meaning it is a value being inserted)
  				$escaped_value = $pool->escapeBadCharacters($input_value); //escape bad characters
  				$insert_value_array[$value_counter] = $escaped_value; //insert escaped charcter into value array
  				$value_counter++;
			}
			else{ //if array key is odd (meaning it is a table field name into which we are inserting the value)
				$insert_field_array[$field_counter] = $input_value; //insert escaped charcter into field array
				$field_counter++; 
			}  
        }
        //create field component of insert SQL statement:
        $field_component_of_insert_statement = '`';
        foreach($insert_field_array as $index => $field_name){
        	$field_component_of_insert_statement = $field_component_of_insert_statement.$field_name.'`, `';
        }
        $field_component_of_insert_statement = substr($field_component_of_insert_statement, 0, -3); //remove last 3 characters of field list (which are '`, ')
        
        //create values component of insert SQL statement:
        $value_component_of_insert_statement = "'";
        foreach($insert_value_array as $index => $value_name){
        	$value_component_of_insert_statement = $value_component_of_insert_statement.$value_name."', '";
        }
        $value_component_of_insert_statement = substr($value_component_of_insert_statement, 0, -3); //remove last 3 characters of value list (which are "', "")

        $query = "INSERT INTO `$table_name` ($field_component_of_insert_statement) VALUES ($value_component_of_insert_statement);";
		if($result = mysqli_query($this->cxn, $query)) {
            $new_record_id = mysqli_insert_id($this->cxn);
            $return_variable = array(2, "<p>INSERT successful!</p>", $new_record_id);
        }
        else{
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
        return $return_variable;
	}


	public function UpdateDB($update_array){
		//Update Array should have the first element be the Table name in which we are updating
		//Additional elements after the first should be field name/value pairs that we are inserting
		//The list of field names/values should end with a hardcoded "WHERE:" element
		//Additional elements after the "WHERE:" should be field name/value pairs for the WHERE clause of the SQL statement
		//Either returns success message (and error code 1) or an error code and error message if something went wrong

		$pool = new Pool(); //this is only here so that we can use the escape bad characters method below
		$table = $update_array[0]; //store table name that we are selecting from
		$number_of_items_to_update = array_search('WHERE:', $update_array) - 1; //'WHERE:' is placed in update_array at the point in the array where array elements switch from being fields that we want to update to the WHERE clause
		$update_fields_array = array_slice($update_array,1,$number_of_items_to_update); //get the fields that we are updating in the table
		//Sample update statement:
		//UPDATE  `Pool Series` SET  `Title` = 'Pool Series Test 1', `Description` = 'Test Description 1' WHERE `Series ID` =15
		$input_component_of_update_statement = "`";

		for($i= 0; $i < $number_of_items_to_update; $i++){ //for each element of the update fields array:
			if ($i == 0 || $i % 2 == 0) { //if the update_fields_array key is zero OR even (meaning it is the field we want to update)
				$input_component_of_update_statement = $input_component_of_update_statement.$update_fields_array[$i]."` = '";
			}
			else{ //if array key is odd (meaning it is a value to be inserted into a field:
				$escaped_value = $pool->escapeBadCharacters($update_fields_array[$i]); //escape bad characters
  				$input_component_of_update_statement = $input_component_of_update_statement.$escaped_value."', `"; 
			}  
        }
		$input_component_of_update_statement = substr($input_component_of_update_statement, 0, -3); //remove last 3 characters of field list (which are '`, ')
		
		$where_array = array_slice($update_array, $number_of_items_to_update + 2); //populate the WHERE clause of the MYSQL statement
		$size_of_where_array = count($where_array); //get the size of the where clause array.  divide this number by 2 to get the number of where parameters
		$where_component_of_select_statement = '`';
		for ($i = 0; $i < $size_of_where_array; $i++) { //generate the where clause of the MYSQL statement
		    $where_component_of_select_statement = $where_component_of_select_statement.$where_array[$i]."` = '".$where_array[$i+1]."' AND `";
		    $i++;
		}
		$where_component_of_select_statement = substr($where_component_of_select_statement, 0, -6); //remove last 6 characters of where clause (which are " AND `")

        $query = "UPDATE  `$table` SET  $input_component_of_update_statement WHERE $where_component_of_select_statement";
		if($result = mysqli_query($this->cxn, $query)) {
            $return_variable = array(1, "<p>UPDATE successful!</p>");
        }
        else{
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
        
        return $return_variable;
	}


	public function DeleteFromDB($delete_array){
		//Delete Array should have the first element be the Table name from which we are deleting a record
		//Additional elements after the first should be field name/value pairs for the WHERE clause of the SQL statement
		//Either returns success message (and error code 1) or an error code and error message if something went wrong
		$table = $delete_array[0]; //table from which we are deleting an entry is first item in delete_array
		$delete_where_array = array_slice($delete_array,1); //get the fields for the WHERE part of the delete sql statement
		$size_of_where_array = count($delete_where_array);
		$where_component_of_delete_statement = '`';
		for ($i = 0; $i < $size_of_where_array; $i++) { //generate the where clause of the MYSQL delete statement
		    $where_component_of_delete_statement = $where_component_of_delete_statement.$delete_where_array[$i]."` = '".$delete_where_array[$i+1]."' AND `";
		    $i++;
		}
		$where_component_of_delete_statement = substr($where_component_of_delete_statement, 0, -6); //remove last 6 characters of where clause (which are " AND `")
		$query = "DELETE FROM `$table` WHERE $where_component_of_delete_statement";
		if($result = mysqli_query($this->cxn, $query)) {
            $return_variable = array(1, "<p>DELETE successful!</p>");
        }
        else{
            $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>", NULL);
        }
		return $return_variable;
	}

}


?>