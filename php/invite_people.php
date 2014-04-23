<?php

/*
TO DO AS OF 7:30 PM ON 11/22/13:
-WE DO NOT CURRENTLY HAVE A WAY TO CHECK TO SEE WHETHER THE INVITE IS A DUPLICATE FOR THE GIVEN USER - PROB SHOULD IMPLEMENT THIS IN USER CLASS FILE FOR InviteReceive FUNCTION
    -OR WE CAN ADD A FIELD TO THE POOL MEMBERSHIP TABLE FOR "INVITE ACCEPTED" - FIELD STARTS AS 0 WHEN INVITE IS SENT, THEN WE MAKE THIS FIELD 1 ONLY WHEN USER ACCEPTS THE INVITE
-NEED TO CREATE A WAY TO SEND INVITEE EMAILS!
*/

if($_POST['invite'] == 1){ //if this file is being run thru the invite ajax function:
    $invitee_array = $_POST['invitees_array']; //get array of invitee emails
    $pool_id = $_POST['pool_id']; //get pool ID that we are inviting people for
    $inviter = $_POST['inviter']; //get email/username of inviter
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser();
    foreach($invitee_array as $invitee_index => $invitee_email){ //foreach invitee email...
        $invite_receive_result = $user->InviteReceive($invitee_email, $pool_id, $inviter); //add pool id to user's "Pool Invites" field in DB
        echo $invite_receive_result;
    }
    exit();
}

else{ //if this file is being accessed by user navigation and not thru ajax:
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Invite";
    include_once "inc/header.php";
    include_once 'inc/class.users.inc.php';
    $user = new SiteUser();

    $pool_id = $_GET['pool_id'];
    $inviter = $_SESSION['Username']; //get the inviter's email address/username (this is so that the invitee knows who is inviting them)
    $user_id = $user->GetUserIDFromEmail($inviter);
    $users_friends_array = $user->GetFriends($user_id);

?>
    <br>
    <div class="row" >
        <div class="col-md-7" id="invite_email_input_container">
            <h3>Enter email addresses to invite people to the pool:</h3>
            <br>
            <div id="invitee_email_form">   
                    <input type="text" name="new_invitee_email" id="new_invitee_email" size="75" required>
                    <input id="submit_invitee_email" type="submit" value="Add to invite list">
                    <span id="invite_error_message" style='color:red'></span>
            </div>
            <br>
            <div id="invitee_email_list">

            </div>
            <br>
            <form action="javascript:invite_people(<?php echo $pool_id; ?>, '<?php echo $inviter; ?>')" method="post">
                <br>
                <h4>When you're finished adding emails, click below to send invites:</h4>
                <h3><input id="invite_button" type="submit" value="Send Invites"><h3>
                <input type="hidden" name="form_sent" value="form_sent">
            </form>
            <br>
        </div>
        <div class="col-md-5">
            <h3>Previous Pool <?php echo FRIEND; ?>s</h3>
            <p style="font-style:italic;">You've played with them before.  Know anyone?</p>
            <h4>Click an email to add to invite list</h4>
            <br>
<?php
            sort($users_friends_array, SORT_STRING);
            foreach($users_friends_array as $user_id => $user_email){
?>
            <div class="friend_invite_div" id="friend_invite_id_<?php echo $user_id; ?>">
                <a href='JAVASCRIPT:add_friend_invitee_email("<?php echo $user_email; ?>", <?php echo $user_id; ?>);'><span class='friend_invite_span label label-primary'><?php echo $user_email; ?></span></a>
            </div>
<?php
            }
?>
        </div>
    </div>
    <br>
    <!--<h3>Or, choose people from the list of past pool participants</h3>-->
<?php
} //end of first IF statement
    //generate a list of all users that have been in the given user's past pools here 
        //we can probably use the Pool Membership table for this
?>