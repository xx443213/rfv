<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow requests from your Chrome extension
// Replace '*' with your extension's origin if you want to be more restrictive
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'vendor/autoload.php'; // If you're using Composer (recommended)

use Twilio\Rest\Client;

// It's best to use environment variables or a secure configuration file for these
$account_sid = 'AC336f10688c1a7cd9f1c6924eadc69e68';
$auth_token = '63f060d3619501d967ada596e8b03c64';


// Instantiate a new Twilio Rest Client
$client = new Client($account_sid, $auth_token);

// Check if the required POST parameters are set
if (!isset($_POST['to']) || !isset($_POST['body'])) {
    echo json_encode(["status" => "error", "error" => "Missing 'to' or 'body' parameters."]);
    exit;
}

$to = $_POST['to'];
$body = $_POST['body'];

// Send the SMS
try {
    $message = $client->messages->create(
        $to, // Text this number
        [
            'from' => '+15736335463', // From a valid Twilio number
            'body' => $body
        ]
    );

    // If successful, return a JSON response
    echo json_encode(["status" => "success", "message_sid" => $message->sid]);

} catch (Exception $e) {
    // If an error occurs, return a JSON error message
    echo json_encode(["status" => "error", "error" => $e->getMessage()]);
}
?>
