<?php
    include_once "inc/constants.inc.php";
    $pageTitle = "Register";
    include_once "inc/header.php";
    
    if(!empty($_POST['username'])) {
        $entryValue = $_POST['username'];
        //check to make sure email address is valid
        //PROBABLY SHOULD ADD A DNS CHECK HERE TOO.  SEE http://www.soaptray.com/blog/2008/04/validate-email-addresses-using-php/
        if(preg_match("/^.+@.+\..+$/",$entryValue)) {   
            //if email is valid:
            include_once "inc/class.users.inc.php";
            $user = new SiteUser();
            $addNewUser_result = $user->addNewUser($entryValue); 

            if($addNewUser_result[0] == 2 OR $addNewUser_result[0] == 4){
                //Send Email to new user:
                include_once 'inc/send_mail.php'; //include email file
                $verification_instruction = "<h4>A verification link has been sent to your email address.  Please click the link to verify your account</h4>";
                SendEmail($entryValue, "Welcome to ".BRAND_NAME, 
                    "Thank you for signing up.  
                    Please click the following link to verify your account: ".$addNewUser_result[2]."\n
                    If clicking the link does not work, please copy and paste it into your browser."
                    );
            }
            
            echo "<div id='signup_page_message_div' style='padding-left:20px;'>";
            echo $addNewUser_result[1];
            echo $verification_instruction;
            echo "</div>";
            if($addNewUser_result[0]==2){
                //if email was stored successfully, don't show the signup HTML
                exit(); 
            }
        }
        else {
    	   //if email is not valid:
    	   echo "<p style='color:red'>Please enter a valid email address</p>";
        }
    }
    //if page loads and form is blank:
    
?> 

        <br>
        <div style="margin-left:20px;">
            <h2>Sign up here</h2>
            <form method="post" action="signup.php" id="registerform">
                <div>
                    <label for="username">Email:</label>
                    <input type="text" name="username" id="username" maxlength="50" style="width:25%;"><br/>
                    <input type="submit" name="register" id="register" value="Sign up" />
                </div>
            </form>
        </div>

<?php
    include_once 'inc/close.php';
?>
