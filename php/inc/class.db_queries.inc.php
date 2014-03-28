<?php  

// Include the Autoloader for MailGun(see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

include_once "constants.inc.php";
date_default_timezone_set('America/New_York'); //set timezone for getting the current time to be EST

class DB_Queries {

	private $cxn;

	public function __construct() { //this is the constructor - called whenever class is created  
	    //We connect to the database for the class
	    $this->cxn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME)
			or die("Could not connect to the server"); 
	}  //END OF CONSTRUCTOR METHOD FOR NEW INSTANCE OF USER CLASS

	public function SelectFromDB($selected_field, $table, $where_field1, $where_value1, $where_field2 = NULL, $where_value2 = NULL, $where_field3 = NULL, $where_value3 = NULL) {
		if(!isset($where_field2)){
			$query = "SELECT `$selected_field` FROM  `$table` WHERE  `$where_field1` =  '$where_value1'";
		}
		else {
			if(!isset($where_field3)){
				$query = "SELECT `$selected_field` FROM  `$table` WHERE  `$where_field1` =  '$where_value1' AND `$where_field2` =  '$where_value2'";
			}
			else {
				$query = "SELECT `$selected_field` FROM  `$table` WHERE  `$where_field1` =  '$where_value1' AND `$where_field2` =  '$where_value2' AND `$where_field3` =  '$where_value3'";
			}
		}
		$result = mysqli_query($this->cxn, $query);
        $output_array = mysqli_fetch_assoc($result);
        return $output_array;
	}

	public function InsertIntoDB($insert_table, $insert_field1, $insert_value1, $insert_field2 = NULL, $insert_value2 = NULL){
		$query = "INSERT INTO `$insert_table` (`$insert_field1`, `$insert_field2`) VALUES ('$insert_value1, '$insert_value2');";
		$result = mysqli_query($this->cxn, $query);
		return $result;
	}

}


?>