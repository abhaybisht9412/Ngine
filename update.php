<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $installation_name = $_POST["installation_name"];
    $aws_access_key = $_POST["aws_access_key"];
    $aws_secret_key = $_POST["aws_secret_key"];
    $aws_account_id = $_POST["aws_account_id"];
    $aws_service = $_POST["aws_service"];
    $aws_region = $_POST["aws_region"];

    // Format the data as key-value pairs for .env file
    $env_data = "INSTALLATION_NAME=\"$installation_name\"\n";
    $env_data .= "AWS_REGION=\"$aws_region\"\n";
    $env_data .= "AWS_ACCESS_KEY=\"$aws_access_key\"\n";
    $env_data .= "AWS_SECRET_KEY=\"$aws_secret_key\"\n";
    $env_data .= "AWS_ACCOUNT_ID=\"$aws_account_id\"\n";

    // Specify full path to the .env file
    $file_path = "/home/ubuntu/OpeNgine-2/.env"; // Update this path as necessary

    // Open the file in write mode
    if (file_put_contents($file_path, $env_data) !== false) {
        // Redirect to aws_services.html after successful write
        header("Location: aws_services.html");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "Error writing to .env file.";
    }
}
?>