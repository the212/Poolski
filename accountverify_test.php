<?php
    include_once "constants.inc.php";
    $pageTitle = "TEST VERIFY";
    include_once "header.php";

    //if page loads and form is not blank:
    if(!empty($_POST['email']) AND !empty($_POST['verification'])):
    	//JUST FOR TESTING PURPOSES: set entered verification code as $verification_value variable
        $verification_value = $_POST['verification'];
        $email_value = $_POST['email'];
    	include_once "class.users.inc.php";
        $user = new SiteUser();
        $user->verifyAccount($verification_value, $email_value); 
    	
    //if page loads and form is blank:
    else:
?> 

        <h2>TEST PAGE: Please Verify your account</h2>

        <form method="post" action="accountverify_test.php">
            <div>
                <!--Email input below is just for test purposes-->
                <label for="p">Enter your email address here</label>
                <input type="text" name="email" id="email" /><br />
                <!--Verification code input below is just for test purposes-->
                <label for="p">Enter your verification code here</label>
                <input type="text" name="verification" id="verification" /><br />
                
                <input type="hidden" name="form_sent" value="<?php echo $_GET['form_sent'] ?>" />
                <input type="submit" name="verify" id="verify" value="Verify Your Account" />
            </div>
        </form>

<?php
    //If the user didn't fill in all the fields (THIS IS JUST FOR TESTING):
    if(isset($_POST['form_sent'])){
        echo "<p style='color:red'>Please fill in all fields.</p>";
    }
    endif;
    //include_once 'common/close.php';
    //test comment here2
?>
