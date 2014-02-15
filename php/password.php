<?php
    include_once "inc/constants.inc.php";
    $pageTitle = "Forgot Password?";
    include_once "inc/header.php";
    
    if(!empty($_POST['username'])) {
        $entryValue = $_POST['username'];
        //check to make sure email address is valid
        
        if(preg_match("/^.+@.+\..+$/",$entryValue)) {   
            //if email is valid:
            include_once "inc/class.users.inc.php";
            $user = new SiteUser();
            $user_id = $user->GetUserIDFromEmail($entryValue);
            if(isset($user_id)){ //if an account for the given email exists:
                $reset_password_result = $user->ResetPassword($user_id, $entryValue); 
                echo "<h5 style='color:#5cb85c; margin-left:20px;'>We have sent a link to reset your password to your email address.</h5>";
            }
            else { //if no account exists for this email:
                echo "<p style='color:red; margin-left:20px;'>No account exists for that email address.  Please try again.</p>";
            }
        }
        else {
    	   //if email is not valid:
    	   echo "<p style='color:red; margin-left:20px;'>Please enter a valid email address</p>";
        }
    }
    //if page loads and form is blank:
    
?> 

        <br>
        <div style="margin-left:20px;">
             <h2>Reset Your Password</h2>
            <p>Enter the email address you signed up with and we'll send
            you a link to reset your password.</p>
            <form method="post" action="password.php" id="resetpassword">
                <div>
                    <label for="username">Email:</label>
                    <input type="text" name="username" id="username" maxlength="50" style="width:25%;"><br/>
                    <input type="hidden" name="action" value="resetpassword">
                    <br>
                    <input type="submit" name="reset" id="reset" value="Reset Password" class="button">
                </div>
            </form>
        </div>

<?php
    include_once 'inc/close.php';
?>
