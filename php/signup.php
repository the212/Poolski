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
            echo $addNewUser_result[1];
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

        <h2>Sign up here</h2>
        <form method="post" action="signup.php" id="registerform">
            <div>
                <label for="username">Email:</label>
                <input type="text" name="username" id="username" maxlength="50"><br />
                <input type="submit" name="register" id="register" value="Sign up" />
            </div>
        </form>

<?php
   
    //include_once 'common/close.php';
?>
