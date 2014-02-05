<?php  
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

//Include site constants
include_once "constants.inc.php";

// Start a PHP session
session_start();

//connects to the poolapptest1 database with credentials defined in constants file
$cxn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME)
	or die("Could not connect to the server"); 



//Below code runs a test to make sure we can actually query database.  The result of the test is displayed on screen

/*
$query = "SELECT  `Username` FROM  `User` WHERE `User ID`=1";
$result = mysqli_query($cxn, $query)
	or die("Could not execute query");

$currentfield = mysqli_fetch_assoc($result); //this converts $result into an object we can use and display on the page

echo "<br>This is the result of the mysqli_fetch_assoc function:<br><br>";
print_r($currentfield);

echo "<br><br>This is the result of the MySQL query: <br><br>";
echo $currentfield['Username'];

echo "<br><p>The following is defined as DB_HOST in constants file</p>";
echo DB_HOST;
echo "<br><br>End of File.<br>"; //displays "end of file" message
*/

?>  