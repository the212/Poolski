<?php
    include_once "inc/constants.inc.php";
    $pageTitle = "Home";
    include_once "inc/header.php";

    //check to see if the LoggedIn and Username $_SESSION variables are set (if so, then the user is already logged in and they dont need to see this page)
    if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username'])):
        //if so, redirect user to their home page:
        header("Location: home.php");
    //otherwise, if session variables are not set, check to see if the login form was submitted:
    elseif(!empty($_POST['username']) && !empty($_POST['password'])):
        //if so, create a new instance of the user class and run the accountLogin method
        include_once 'inc/class.users.inc.php';
        $user = new SiteUser();
        $email_entry = $_POST['username'];
        $password_entry = md5($_POST['password']); //encrypt password input
        $timezone = $_POST['time']; //get timezone from ajax call
        $user->accountLogin($email_entry , $password_entry, $timezone); //run accountLogin method in user class.  This will log the user in and set the session variables if the user is authenticated successfully
        exit;
    else:
        if(isset($_GET['login'])):
            //if the user was not logged in and the "login" variable was set (indicating that we came from the AJAX login function):
?>
<h2>Login Failed&mdash;Try Again?</h2>
        <form method="post" action="javascript:login_function()" name="loginform" id="loginform">
            <div>
                <input type="text" name="username" id="username" />
                <label for="username">Email</label>
                <br /><br />
                <input type="password" name="password" id="password" />
                <label for="password">Password</label>
                <br /><br />
                <input type="submit" name="login" id="login" value="Login" class="button" />
            </div>
        </form><br><br>
        <p><a href="/password.php">Did you forget your password?</a></p>
<?php
        else:
            //if the login form was not submitted, then display the below HTML prompting user to login:
?>
    <div style="margin-left:20px;">
        <br>
        <h2>Please log in...</h2>
        <form method="post" action="javascript:login_function()" name="loginform" id="loginform">
            <div>
                <input type="text" name="username" id="username" />
                <label for="username">Email</label>
                <br /><br />
                <input type="password" name="password" id="password" />
                <label for="password">Password</label>
                <br /><br />
                <input type="submit" name="login" id="login" value="Login" class="button" />
            </div>
        </form>
        <br>
        <h4><a href="password.php">Did you forget your password?</a></h4>
        <br>
        <h4><a href="signup.php">Don't have an account?  Click here to sign up!</a></h4>
    </div>
<?php
        endif;
    endif;
?>

        <div style="clear: both;"></div> 

<?php
        
    
    include_once 'inc/close.php';
?>
