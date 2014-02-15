<?php


// Include the Autoloader from mailgun library (see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

function SendEmail($to_email, $subject, $mail_text) {
        
        # Instantiate the client.
        $mgClient = new Mailgun('key-9ugjcrpnblx1m98gcpyqejyi75a96ta5');
        //$domain = "sandbox40726.mailgun.org";
        $domain = "Poolski.com";

        # Make the call to the client.
        $result = $mgClient->sendMessage("$domain",
                  array('from'    => 'Poolski <postmaster@sandbox40726.mailgun.org>',
                        'to'      => $to_email,
                        'subject' => $subject,
                        'text'    => $mail_text
                        ));
}

/*
SendEmail("test@test22.com", "TEST SUBJECT2", "This is the body of the email!!!");

echo "TEST!!";
echo "<br><a href='home.php'>Click here to return to home page</a>";
*/
?>