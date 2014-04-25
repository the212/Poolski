<?php  
/*Class to handle user interactions within app*/
/*By Evan Paul October 2013*/

// Include the Autoloader for MailGun(see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

//Include DB constants
include_once "constants.inc.php";
//Include DB_QUERIES class
include_once 'inc/class.db_queries.inc.php';

class SiteUser {

	private $cxn;

	public function __construct() { //this is the constructor - called whenever class is created{  
	    //We connect to the database for the class
	    $this->cxn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME)
			or die("Could not connect to the server"); 
	}  //END OF CONSTRUCTOR METHOD FOR NEW INSTANCE OF USER CLASS

    //below method is just for testing the DB connection
	public function GetDataTest() {  
    	//We get the username from the 1st entry in the "User" database
    	$query = "SELECT  `Username` FROM  `User` WHERE `User ID`=1";
		$result = mysqli_query($this->cxn, $query)
			or die("Could not execute query!");

		$currentfield = mysqli_fetch_assoc($result); //this converts $result into an object we can use and display on the page
		echo "<p>This is the result of the MySQL query: </p>";
		echo $currentfield['Username'];
		echo "<br>";
    } //END OF GETDATATEST METHOD 


    //GENERATE VERIFICATION METHOD
    //Accepts an email address.  RETURNS THE VERIFICATION CODE
    public function GenerateVerification($email){
    	//generate a new random verification code for the given email
	    $verification_code = substr(md5(uniqid(rand(), true)), 16, 16);
	    //add the new verification code to proper row in User database table.  the row corresponds to the email address passed to the function
	    $verification_query = "UPDATE  `User` SET  `Verification` = '$verification_code' WHERE  `Email Address` = '$email'";
    	mysqli_query($this->cxn,$verification_query)
    		or die("<br>Error: could not store verification code");
        return $verification_code;
    } //END OF GENERATEVERIFICATION EMAIL METHOD


    //ADD NEW USER METHOD
    //Accepts an email address.  Returns result code and result message in array
    /*Result codes: 
    **  1=Error - account for given email address already exists
    **  2=email was stored successfully
    **  3=database query error
    */
    public function addNewUser($email){
    	//check to see whether given email address already exists in database
    	$check_query = "SELECT * FROM `User` WHERE `Email Address`='$email' LIMIT 1";
    	$result = mysqli_query($this->cxn,$check_query)
    		or die("Error: Could not check database for duplicate");
    	$email_check_result = mysqli_fetch_assoc($result);
    	if (isset($email_check_result)) {
    		//if email already exists:
    		$return_variable = array (1, "<p style='color:red'>An account with this email address already exists!</p>");
    	}
        else {
    	    //if email does not already exist, add input value as email address for new row in database
    		$query = "INSERT INTO `User` (`Email Address`, `Date`) VALUES ('".$email."',CURRENT_TIMESTAMP);";
    		if($result = mysqli_query($this->cxn,$query)){
                //Get User ID for new user:
                $new_user_id = mysqli_insert_id($this->cxn);

                //call the GenerateVerification function in order to generate verification code for user to activate acct
                $ver_code = $this->GenerateVerification($email);

                //create the URL for user to verify account (this will be sent in the verification email)
                $verify_url = DOMAIN."accountverify.php?v=".$ver_code."&e=".$new_user_id;
                $return_variable = array(2, "<h3>Email was successfully stored!</h3>", $verify_url, $new_user_id);
                //display verification URL on screen (needs to eventually be email)
                //echo "<p><a href=".$verify_url.">Click here to verify your account</a></p>";
            }
            else {
                $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>");
            }
        }
        return $return_variable;
    } //END OF ADDNEWUSER METHOD


    //CHECK VERIFICATION CODE METHOD
    //Accepts verification code and user ID.  Returns result code and result message in array as $return_variable
    /*Result codes: 
    **  2=account is already activated but password is null
    **  3=account is already activated with a stored password
    **  4=given account does not exist in database
    **  5=database query error
    **  6=account successfully verified (account was previously unverified)
    */
    public function verifyAccount($ver_code, $user_id){
        $query = "SELECT * FROM `User` WHERE `Verification`='$ver_code' AND `User ID`='$user_id'"; //AND `Account activated`=0
        if($result = mysqli_query($this->cxn, $query)){
            //if database query was successful, store the selected acct data in $verification_result variable array:
            $verification_result = mysqli_fetch_assoc($result); //this converts $result into an object we can use and display on the page
            //check if the given user ID and verification code combination exist in DB
            if (isset($verification_result)) {
                //if we are able to find the user ID and verification code combination in DB:
                if($verification_result['Account activated']==0){
                    //if the given account is not already activated:
                    $activate_query = "UPDATE  `User` SET  `Account activated` =  '1' WHERE  `User ID` ='$user_id';";
                    mysqli_query($this->cxn,$activate_query)
                        or die("<br>Error: Could activate account");
                    $return_variable = array (6, "<h3>Verification successful!</h3>");
                }
                else{
                    //check to see if a password is set
                    if(is_null($verification_result['Password'])){
                        //if password is not set:
                        $return_variable = array (2, "<p>Password has not yet been set for this account.</p>");
                    }
                    else{
                        $return_variable = array (3, "<p style='color:red'>Password has already been set for this account.</p>");
                    }
                }
            }
            else {
                //if verification query did not find anything:
                //NOTE, NEED TO ADD SOME BETTER LOGIC HERE TO HANDLE VARIOUS FAILURE POSSIBILITIES
                //See http://www.copterlabs.com/blog/creating-an-app-from-scratch-part-5/ under "verifying the user's email and verification code" section
                $return_variable = array (4, "<p style='color:red'>The specified account does not exist</p>");
            }
        }
        else {
            //if database query was not successfull
            $return_variable = array (5, "<p style='color:red'>There was an error connecting to the database</p>");
        }
        return $return_variable;
    } //END OF VERIFY ACCOUNT METHOD


    /*UPDATE PASSWORD METHOD
    **NOTE: AS OF 2/10/14, THIS METHOD IS ONLY TO BE USED WHEN A USER IS CREATING THEIR PASSWORD FOR THE FIRST TIME
    ACCEPTS 1ST AND 2ND PASSWORD ENTRIES AND THE USER'S ID
    UPDATES PASSWORD IN USER TABLE WITH ENCRYPTED PASSWORD
    LOGS USER IN ONCE PASSWORD IS SUCCESSFULLY STORED
    */
    public function updatePassword($password_entry1, $password_entry2, $user_id) {
        $encrypt_password = md5($password_entry1);
        $query = "UPDATE  `User` SET  `Password` =  '$encrypt_password' WHERE  `User ID` ='$user_id';";
        if($result = mysqli_query($this->cxn, $query)) {
            //if connection to DB was successful:
            $return_variable = "<h3>Your new password has been successfully stored.</h3>";
            //Log the user in once their password is successfully stored:
            $user_info = $this->GetUserInfo($user_id);
            $_SESSION['Username'] = $user_info['Email Address'];
            $_SESSION['LoggedIn'] = 999;  //didn't want to make the LoggedIn session variable intuitive.  999 for logged in, 0 otherwise
        }
        else {
            //if connection to DB failed:
            $return_variable = "<p>There was an error connecting to the database<p>";
        }
        return $return_variable;
    } //END OF UPDATE PASSWORD METHOD


    /*RESET PASSWORD METHOD
    **ACCEPTS USER ID AND NEW VERIFICATION CODE FOR ACCOUNT
    **SETS GIVEN USER'S "ACCOUNT ACTIVATED?" FIELD IN USER TABLE TO 0
    **SENDS RESET PASSWORD EMAIL TO GIVEN USER
    */
    public function ResetPassword($user_id, $email){
        $unverify_query = "UPDATE  `User` SET  `Account activated` =  '0' WHERE  `User ID` ='$user_id';";
        $unverify_result = mysqli_query($this->cxn, $unverify_query);
        //generate new verification code for given account
        $unset_password_query = "UPDATE  `User` SET  `Password` =  NULL WHERE  `User ID` ='$user_id';";
        $unset_password_result = mysqli_query($this->cxn, $unset_password_query);
        $ver = $this->GenerateVerification($email);
        //Send email to invitee:
        include 'send_mail.php'; //include email file
        SendEmail($email, "Reset Password", 
            "Click the following link to reset your password: 
            \n\n".DOMAIN."resetpassword.php?v=".$ver."&user_id=".$user_id."
            \n\nIf clicking the link does not work, please copy and paste it into your browser."
            );
    }

    //UPDATE USERNAME METHOD
    //ACCEPTS EMAIL ADDRESS AND USERNAME.  CHANGES USERNAME IN DATABASE TO THE GIVEN USERNAME
    public function updateUsername($email, $new_username){
        $query = "UPDATE  `User` SET  `Username` =  '$new_username' WHERE  `Email Address` ='$email';";
        $result = mysqli_query($this->cxn, $query);
    }


    //ACCOUNT LOGIN METHOD
    //Accepts email address and password.  Returns boolean TRUE if login is successful and FALSE otherwise
    public function accountLogin($email, $password, $timezone) {
        $query = "SELECT `Account Activated` FROM `User` WHERE `Email Address` = '$email' AND `Password` = '$password' LIMIT 1";
        $result = mysqli_query($this->cxn, $query) 
            or die("Could not execute query!");
        $login_result = mysqli_fetch_assoc($result);
        //check if result of MYSQL query above equals 1 (pulls from the "Account Activated" field in DB thus ensuring only activated accts can log in):
        if ($login_result['Account Activated']==1) {
            //if account exists and is activated (login success):
            $_SESSION['Username'] = $email; //set session username to input email
            $_SESSION['time'] = $timezone; //set the user's timezone in the session['time'] variable
            //SET time_zone = '-8:00';
            $_SESSION['LoggedIn'] = 999; //user's session is logged in
            return TRUE;
        }
        else{
            //if login failed:
            return FALSE;
        }
    } //END OF ACCOUNT LOGIN METHOD


    
    /*GET USER INFO METHOD
    ACCEPTS A USER ID
    RETURNS THE GIVEN USER'S USERNAME AND EMAIL ADDRESS AS AN ASSOCIATIVE ARRAY
    */
    public function GetUserInfo($user_id){
        $query = "SELECT `Username`, `Email Address` FROM  `User` WHERE  `User ID` =  '$user_id'";
        $result = mysqli_query($this->cxn, $query) ;
        $user_info = mysqli_fetch_assoc($result);
        return $user_info;
    } //END OF GET USER INFO METHOD


    /*GET USER ID FROM EMAIL METHOD
    ACCEPTS USER EMAIL
    RETURNS THE GIVEN USER ID 
    */
    public function GetUserIDFromEmail($email){
        $query = "SELECT `User ID` FROM  `User` WHERE  `Email Address` =  '$email'";
        $result = mysqli_query($this->cxn, $query) ;
        $result_array = mysqli_fetch_assoc($result);
        return $result_array['User ID'];
    } //END OF GET USER ID FROM EMAIL METHOD
    


    /*
    CHECK FOR POOL INVITES METHOD
    ACCEPTS USER EMAIL
    IF USER HAS POOL INVITES, THE METHOD RETURNS AN ARRAY CONTAINING THE POOL IDS OF POOLS THAT THE GIVEN USER HAS BEEN INVITED TO
    OTHERWISE THE METHOD RETURNS ZERO INDICATING THAT THE USER HAS NO POOL INVITES
    */
    public function CheckPoolInvites($email){
        $query = "SELECT `Pool Invites` FROM  `User` WHERE `Email Address` = '$email'";
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result); 
        //store list of pool invites in $pool_invites variable (list will be in the form of poolID1, poolID2, poolID3, etc.)
        $pool_invites = $result_array['Pool Invites'];
        if(preg_match('/[^a-z0-9]/i', $pool_invites)){ //check to see whether Pool Invites field for given user has alpha-numeric values:
            //if so, it means they have invites:
            $pool_invites_array = explode(",", $pool_invites); //separate out each pool id into values in $pool_invites_array 
            return $pool_invites_array;
        }
        else{
            //if not, they don't have any invites, and we return 0:
            return 0;
        }
    }

    /*INVITE RECEIVE METHOD
    ACCEPTS THE ID OF THE USER TO BE INVITED AND THE POOL ID WHICH WE ARE INVITING USER TO
    ADDS POOL ID TO THE GIVEN USER'S "POOL INVITES" FIELD IN USER TABLE
    RETURNS THE APPENDED VALUE
    */
    public function InviteReceive($email, $pool_id, $inviter=NULL){
        //get Pool Invites values for given user (this is a string of pool ids that a user has been invited to)
        $query = "SELECT `Pool Invites` FROM  `User` WHERE  `Email Address` = '$email'"; 
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result); 
        if(!isset($result_array)) { //if the user is a new user:
            $add_new_user_result = $this->addNewUser($email); //store user in DB as an unverified user
            include 'send_mail.php'; //include email file
            //send user email:
            SendEmail($email, "You have been invited to a pool on Poolski.com!", "You have been invited to a pool by ".$inviter." on Poolski.com!  
                \n\nClick here to create an account and join the pool: ".$add_new_user_result[2]." 
                \nPlease copy and paste the entire URL into your browser if clicking on it doesn't work.
                \n\nPoolski.com is a site that allows you to create betting pools with your friends online
                \nUse Poolski to bet on anything from the Academy Awards or the outcome of your favorite TV show."
                );
            $append_value = $pool_id.","; //this is the value we will be appending to the original Pool Invites value
            $append_query = "UPDATE `User` SET `Pool Invites` = '$append_value' WHERE `Email Address` = '$email';";
            $result2 = mysqli_query($this->cxn, $append_query); //append given pool id into user's Pool Invites field in DB
            return "\n\nInvite sent to ".$email."!";
        }
        else{ //if user is NOT a new user:
            $user_id = $this->GetUserIDFromEmail($email); //get given email's USER ID
            //check to see if given user is already a member of given pool:
            $check_pool_membership_query = "SELECT * FROM  `Pool Membership` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id'";
            $membership_check_result = mysqli_query($this->cxn, $check_pool_membership_query);
            $membership_check_array = mysqli_fetch_assoc($membership_check_result);
            if(!isset($membership_check_array)){ //if given user is NOT already a member of given pool:
                $existing_pool_invites = $result_array['Pool Invites']; //store original Pool Invites value
                //Check to make sure invitee does not already have an invite for this pool waiting:
                $existing_pool_invites_array = explode(',', $existing_pool_invites);
                if(in_array($pool_id, $existing_pool_invites_array)){ //if user already has an invite pending for this pool:
                    return "\n\nInvite NOT sent to ".$email." because they have already been invited to the pool.";
                    exit();
                }
                //If invitee does NOT already have an invite pending for this pool:
                $append_value = $pool_id.","; //this is the value we will be appending to the original Pool Invites value
                $append_query = "UPDATE `User` SET `Pool Invites` = concat('$append_value', '$existing_pool_invites') WHERE `Email Address` = '$email';";
                $result2 = mysqli_query($this->cxn, $append_query); //append given pool id into user's Pool Invites field in DB
                include 'send_mail.php'; //include email file
                SendEmail($email, "You have been invited to a pool!", "You have been invited to a pool by ".$inviter."!  Click here to see the invite: ".DOMAIN."home.php");
                return "\n\nInvite sent to ".$email."!";
            }
            else{ //if the given user IS already a member of the given pool:
                return "\n\nInvite NOT sent to ".$email." because they have already been invited to the pool.";
            }
        }  
    }
          
        //return "\n\nInvite NOT sent to ".$email." because they are not registered."; //NEED TO WRITE A FUNCTION TI SEND GIVEN EMAIL ADDRESS A WELCOME EMAIL ASKING THEM TO JOIN SITE        
    

    /*REMOVE INVITE METHOD
    ACCEPTS THE USER ID AND THE POOL ID THAT WE ARE REMOVING THE INVITE FOR
    REMOVES POOL ID FROM USER'S POOL INVITES FIELD IN USER TABLE
    CURRENTLY DOES NOT RETURN ANYTHING
    */
    public function RemoveInvite($user_id, $pool_id){
        $query = "SELECT `Pool Invites` FROM  `User` WHERE  `User ID` = '$user_id'"; 
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result); 
        $old_invite_list = $result_array['Pool Invites'];
        $new_invite_list = str_replace($pool_id.",", "", $old_invite_list);
        $remove_invite_query = "UPDATE  `User` SET  `Pool Invites` =  '$new_invite_list' WHERE  `User ID` = '$user_id';";
        $result2 = mysqli_query($this->cxn, $remove_invite_query);
    }



    /*
    POOL FRIEND METHODS
    */


    /*GetFriends method
    */

    public function GetFriends($user_id){
        include_once 'inc/class.pool.inc.php';
        $query = new DB_Queries(); 
        $pool = new Pool();
        //$users_pools_array = $query->SelectFromDB('Pool ID', 'Pool Membership', 'User ID', $user_id);
        $users_pools_query = "SELECT `Pool ID` FROM  `Pool Membership` WHERE `User ID` = '$user_id';";
        $result = mysqli_query($this->cxn, $users_pools_query);
        $return_array = array();
        while($row = mysqli_fetch_assoc($result)){ //for each pool that a user is in:
            $pool_id = $row['Pool ID'];
            $pool_member_query = "SELECT `User ID` FROM  `Pool Membership` WHERE `Pool ID` = '$pool_id';";
            $result2 = mysqli_query($this->cxn, $pool_member_query);
            while($row_user_id = mysqli_fetch_assoc($result2)){ //for each user that is in the given pool
                $user_id = $row_user_id['User ID'];
                $email_array = $query->SelectFromDB('Email Address', 'User', 'User ID', $user_id);
                $return_array[$user_id] = $email_array['Email Address'];
            }
        }
        return $return_array;
    }


    /*CHECKADMIN METHOD
    **Accepts user ID 
    **Checks to see if user ID is in the admin array
    **Returns 1 if user is an admin
    **Returns 0 otherwise
    */
    public function CheckAdmin($user_id) {
        $admin_id_list = array("1","");
        if(in_array($user_id, $admin_id_list, true)){
            $check_result = 1;
        }
        else {
            $check_result = 0;
        }
        return $check_result;
    }


    /**********************************************************************
    MAIL METHODS
    **********************************************************************/


    /*SENDMAIL METHOD
    **ACCEPTS EMAIL ADDRESS THAT WE ARE SENDING MAIL TO, SUBJECT, AND MAIL TEXT VARIABLES
    **MAIL TEXT IS THE BODY OF THE EMAIL
    
    public function SendEmail($to_email=NULL, $subject, $mail_text) {
        INCLUDE AUTOLOADER FROM MAILGUN LIBRARY - THIS IS DEFINED AT THE TOP OF THIS FILE - JUST PASTED HERE FOR REFERENCE
        # Include the Autoloader (see "Libraries" for install instructions)
        require '../vendor/autoload.php';
        use Mailgun\Mailgun;
        

        # Instantiate the client.
        $mgClient = new Mailgun('key-9ugjcrpnblx1m98gcpyqejyi75a96ta5');
        $domain = "sandbox40726.mailgun.org";

        # Make the call to the client.
        $result = $mgClient->sendMessage("$domain",
                  array('from'    => 'Mailgun Sandbox <postmaster@sandbox40726.mailgun.org>',
                        'to'      => 'Evan Paul <evanwpaul@gmail.com>',
                        'subject' => $subject,
                        'text'    => $mail_text
                        ));
    }
    */
}

?>  