<?php
// Update the path below to your autoload.php,
// see https://getcomposer.org/doc/01-basic-usage.md
require_once '/path/to/vendor/autoload.php';

use Twilio\Rest\Client;

$sid    = "AC616ac5f55c81f97fb5575fd9c21f1fe3";
$token  = "3786a5faa5c090080d0d4e9cd00be121";
$twilio = new Client($sid, $token);

$message = $twilio->messages
    ->create(
        "whatsapp:+5218714307468", // to
        array(
            "from" => "whatsapp:+14155238886",
            "contentSid" => "HXb5b62575e6e4ff6129ad7c8efe1f983e",
            "contentVariables" => '{"1":"12/1","2":"3pm"}',
            "body" => "Your Message"
        )
    );

print($message->sid);
