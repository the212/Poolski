<?php  
/*Class to handle user interactions within app*/
/*By Evan Paul October 2013*/

include_once "constants.inc.php";

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
	   	//display code on screen - this is just for testing purposes
	    //add the new verification code to proper row in User database table.  the row corresponds to the email address passed to the function
	    $verification_query = "UPDATE  `User` SET  `Verification` = '$verification_code' WHERE  `user`.`Email Address` = '$email'";
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
                //call the GenerateVerification function in order to generate verification code for user to activate acct
                $ver_code = $this->GenerateVerification($email);
                    //$ver = "testing1"; this is for email testing 
                    //$this->sendVerificationEmail($email, $ver); this is for email testing
                $return_variable = array(2, "<p>Email was successfully stored!</p>");
                //create the URL for user to verify account (this will normally be sent in the verification email)
                $verify_url = "http://localhost/custom_pool_site/accountverify.php?v=".$ver_code."&e=".$email;
                //display verification URL on screen (needs to eventually be email)
                echo "<p><a href=".$verify_url.">Click here to verify your account</a></p>";
            }
            else {
                $return_variable = array(3, "<p style='color:red'>There was an error connecting to the database<p>");
            }
        }
        return $return_variable;
    } //END OF ADDNEWUSER METHOD


    //SEND VERIFICATION EMAIL METHOD - currently not working on localhost server :(
    public function sendVerificationEmail($email, $ver){
        //I'm having trouble getting this to work as of 10/16/13 - I'll come back to it later I guess
        $e = sha1($email); // For verification purposes.  $e will be appended to verification URL
        $to = 'evanwpaul@gmail.com';
        $subject = "This is an automated test email";
        $msg = "Hi!";
        $headers = 'From: webmaster@example.com' . "\n" .
    		'Reply-To: webmaster@example.com' . "\n" .
    		'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $msg, $headers);
        if (mail($to, $subject, $msg, $headers)) {
            echo "<br>message sent!!";
        }
        else {
            echo "<br>email failed to send! bummer";
        }
    } //END OF SENDVERIFICATIONEMAIL METHOD


    //CHECK VERIFICATION CODE METHOD
    //Accepts verification code and email address.  Returns result code and result message in array as $return_variable
    /*Result codes: 
    **  2=account is already activated but password is null
    **  3=account is already activated with a stored password
    **  4=given account does not exist in database
    **  5=database query error
    **  6=account successfully verified (account was previously unverified)
    */
    public function verifyAccount($ver_code, $email){
        $query = "SELECT * FROM `User` WHERE `Verification`='$ver_code' AND `Email Address`='$email'"; //AND `Account activated`=0
        if($result = mysqli_query($this->cxn, $query)){
            //if database query was successful, store the selected acct data in $verification_result variable array:
            $verification_result = mysqli_fetch_assoc($result); //this converts $result into an object we can use and display on the page
            //check if the given email and verification code combination exist in DB
            if (isset($verification_result)) {
                //if we are able to find the email and verification code combination in DB:
                if($verification_result['Account activated']==0){
                    //if the given account is not already activated:
                    $activate_query = "UPDATE  `User` SET  `Account activated` =  '1' WHERE  `user`.`Email Address` ='$email';";
                    mysqli_query($this->cxn,$activate_query)
                        or die("<br>Error: Could activate account");
                    $return_variable = array (6, "<p>Verification successful!</p>");
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


    //UPDATE PASSWORD METHOD
    public function updatePassword($password_entry1, $password_entry2, $email) {
        $encrypt_password = md5($password_entry1);
        $query = "UPDATE  `User` SET  `Password` =  '$encrypt_password' WHERE  `Email Address` ='$email';";
        if($result = mysqli_query($this->cxn, $query)) {
            //if connection to DB was successful:
            $return_variable = "<p>Your new password has been successfully stored.</p>";
            //Log the user in once their password is successfully stored:
            $_SESSION['Username'] = $email;
            $_SESSION['LoggedIn'] = 999;  //didn't want to make the LoggedIn session variable intuitive.  999 for logged in, 0 otherwise
        }
        else {
            //if connection to DB failed:
            $return_variable = "<p>There was an error connecting to the database<p>";
        }
        return $return_variable;
    } //END OF UPDATE PASSWORD METHOD


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
    public function InviteReceive($email, $pool_id){
        //get Pool Invites values for given user (this is a string of pool ids that a user has been invited to)
        $query = "SELECT `Pool Invites` FROM  `User` WHERE  `Email Address` = '$email'"; 
        $result = mysqli_query($this->cxn, $query);
        $result_array = mysqli_fetch_assoc($result); 
        if(isset($result_array)) {
            //IF GIVEN EMAIL EXISTS IN DB:
            $user_id = $this->GetUserIDFromEmail($email); //get given email's USER ID
            //check to see if given user is already a member of given pool:
            $check_pool_membership_query = "SELECT * FROM  `Pool Membership` WHERE `User ID` = '$user_id' AND `Pool ID` = '$pool_id'";
            $membership_check_result = mysqli_query($this->cxn, $check_pool_membership_query);
            $membership_check_array = mysqli_fetch_assoc($membership_check_result);
            if(!isset($membership_check_array)){ //if given user is not already a member of given pool:
                $existing_pool_invites = $result_array['Pool Invites']; //store original Pool Invites value
                $append_value = $pool_id.","; //this is the value we will be appending to the original Pool Invites value
                $append_query = "UPDATE `User` SET `Pool Invites` = concat('$append_value', '$existing_pool_invites') WHERE `Email Address` = '$email';";
                $result2 = mysqli_query($this->cxn, $append_query); //append given pool id into user's Pool Invites field in DB
                return "\n\nInvite sent to ".$email."!";
            }
            else{
                return "\n\nInvite NOT sent to ".$email." because they have already been invited to the pool.";
            }     
        }
        else{
            //IF GIVEN EMAIL DOES NOT EXIST IN DB (THIS NEEDS TO BE MODIFIED TO SEND EMAIL)
            return "\n\nInvite NOT sent to ".$email." because they are not registered."; //NEED TO WRITE A FUNCTION TI SEND GIVEN EMAIL ADDRESS A WELCOME EMAIL ASKING THEM TO JOIN SITE
        }
    }

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
    public function SendTestEmail(){
        # Include the Autoloader (see "Libraries" for install instructions)
        require 'vendor/autoload.php';
        use Mailgun\Mailgun;

        # Instantiate the client.
        $mgClient = new Mailgun('key-9ugjcrpnblx1m98gcpyqejyi75a96ta5');
        $domain = "sandbox40726.mailgun.org";

        # Make the call to the client.
        $result = $mgClient->sendMessage("$domain",
                  array('from'    => 'Mailgun Sandbox <postmaster@sandbox40726.mailgun.org>',
                        'to'      => 'Evan Paul <evanwpaul@gmail.com>',
                        'subject' => 'Hello Evan Paul',
                        'text'    => 'Congratulations Evan Paul, you just sent an email with Mailgun!  You are truly awesome!  You can see a record of this email in your logs: https://mailgun.com/cp/log .  You can send up to 300 emails/day from this sandbox server.  Next, you should add your own domain so you can send 10,000 emails/month for free.'));
    
    }
*/
}

?>  