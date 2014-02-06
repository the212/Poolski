<?php

# Include the Autoloader (see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun('key-9ugjcrpnblx1m98gcpyqejyi75a96ta5');
$domain = "sandbox40726.mailgun.org";

# Make the call to the client.
$result = $mgClient->sendMessage("$domain",
                  array('from'    => 'Mailgun Sandbox <postmaster@sandbox40726.mailgun.org>',
                        'to'      => 'Evan Paul <evanwpaul@gmail.com>',
                        'subject' => 'Hello Evan Paul',
                        'text'    => 'Congratulations Evan Paul, you just sent an email with Mailgun!  You are truly awesome!  You can see a record of this email in your logs: https://mailgun.com/cp/log .  You can send up to 300 emails/day from this sandbox server.  Next, you should add your own domain so you can send 10,000 emails/month for free.'
                        ));
    
echo "TEST!!";
echo "<br><a href='home.php'>Click here to return to home page</a>";

?>