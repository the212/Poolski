<?php
    include_once "inc/constants.inc.php";
    $pageTitle = "Choose password";
    include_once "inc/header.php";

    //if page loads and BOTH "v" and "e" variables are passed.  E.g.: http://localhost/custom_pool_site/accountverify.php?v=ec731d77113c284d&e=test@test444.com
    //NOTE - MAKE SURE TO HASH EMAIL
    //NOTE IF A USER ENTERS URL WITH THEIR V AND E VALUES SPECIFIED, THEY WILL BE ABLE TO CHANGE THE ACCT'S PASSWORD
    if(isset($_GET['v']) && isset($_GET['e'])) {
    	//store variables from URL:
        $verification_value = $_GET['v'];
        $user_id = $_GET['e'];
    	include_once "inc/class.users.inc.php";
        $user = new SiteUser();
        $verify_account_result = $user->verifyAccount($verification_value, $user_id); 
        if($verify_account_result[0]>3){
            //if verifyAccount result is greater than 3 and we don't want the user to enter a new password:
            echo $verify_account_result[1];
        }
    }

    //if form is submitted and the input passwords are correct length and match each other:
    if(isset($_POST['form_sent']) /*&& !empty($_POST['username'])*/ && strlen($_POST['p'])>7 && $_POST['p']===$_POST['r']) {   
        include_once "inc/class.users.inc.php";
        $user = new SiteUser();
        //$username_entry = $_POST['username'];
        $password_entry1 = $_POST['p'];
        $password_entry2 = $_POST['r'];
        $user_id = $_POST['form_sent']; //store user ID from hidden field in form as $user_id variable (hidden field value comes from URL)
        //store entered password in database:
        $updatePassword_result = $user->updatePassword($password_entry1, $password_entry2, $user_id);
        //$user->updateUsername($email, $username_entry);
        echo $updatePassword_result;
        echo "<h4><a href='login.php'>Click here to go to the home page</a></h4>";
        exit();
    }

    //if the plain URL for this page is entered in with out the e variable:
    //we should probably direct the user back to the home page in this case
    if((!isset($_GET['e']) OR !isset($_GET['v'])) && !isset($_POST['form_sent'])) {
        //if the URL for this page did not include both the email and ver code values, we return the user to the home page:
        header("Location: home.php");
        exit();
    }

    else {
?> 

        <h2>Please choose a password:</h2>

        <form method="post" action="accountverify.php?e=<?php echo $_GET['e'] ?>">
            <div>
            <!--
                <label for="p">Choose a Username:</label>
                <input type="text" name="username" id="username" /><br />
                <br>
            -->
                <label for="p">Choose a Password:</label>
                <input type="password" name="p" id="p" /><br />
                <label for="r">Re-Type Password:</label>
                <input type="password" name="r" id="r" /><br />

                <input type="hidden" name="form_sent" value="<?php echo $_GET['e'] ?>" /> <!--We store the email from the URL here which is used when the form is submitted-->
                <input type="submit" name="verify" id="verify" value="Verify Your Account" />
            </div>
        </form>

<?php
        //if form was submitted but something was wrong with the inputs
        if(isset($_POST['form_sent'])){
            //If the user didn't fill in all the fields:
            if(empty($_POST['username']) OR empty($_POST['p']) OR empty($_POST['r'])) {
                echo "<p style='color:red'>Please fill in all fields.</p>";
                exit();
            }
            //if the input passwords do not match:
            if($_POST['p']!=$_POST['r']){
                echo "<p style='color:red'>Input passwords do not match.</p>";
            }
            //if the input password(s) are 7 characters or less:
            if(strlen($_POST['p'])<8){
                echo "<p style='color:red'>Passwords must be at 8 characters.</p>";
            }
        }
    }
    
    //include_once 'common/close.php';
?>
