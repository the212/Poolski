<?php
    include_once "inc/constants.inc.php";
    $pageTitle = "Reset Password";
    include_once "inc/header.php";
    
    if(isset($_GET['v']) && isset($_GET['user_id']))
    {
        include_once "inc/class.users.inc.php";
        $user = new SiteUser();
        $ret = $user->verifyAccount($_GET['v'], $_GET['user_id']);
    }
    else { //redirect to home page if "v" and "user id" variables are not properly set in URL
        header("Location: home.php");
        exit;
    }
    //if page loads and form is blank:
?>
        <br>
        <div style="margin-left:20px;">
            <h2>Reset Your Password</h2>

            <form method="post" action="accountverify.php">
                <div>
                    <label for="p">Choose a New Password:</label>
                    <input type="password" name="p" id="p" /><br />
                    <label for="r">Re-Type Password:</label>
                    <input type="password" name="r" id="r" /><br />
                    <input type="hidden" name="v" value="<?php echo $_GET['v']; ?>" />
                    <input type="hidden" name="form_sent" value="<?php echo $_GET['user_id']; ?>" />
                    <input type="submit" name="verify" id="verify" value="Reset Your Password" />
                </div>
            </form>
        </div>
<?php
    include_once 'inc/close.php';
?>