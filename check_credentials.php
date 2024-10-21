<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Only proceed if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to prevent command injection
    $awsAccessKey = escapeshellarg($_POST['aws_access_key']);
    $awsSecretKey = escapeshellarg($_POST['aws_secret_key']);
    $awsAccountId = escapeshellarg($_POST['aws_account_id']);
    $awsRegion = escapeshellarg($_POST['aws_region']);

    // Execute the shell script with the provided arguments
    $output = shell_exec("./check_credentials.sh $awsAccessKey $awsSecretKey $awsAccountId $awsRegion 2>&1");

    // Return the output to the client
    echo $output;
}
?>
