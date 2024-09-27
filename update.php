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

    // Format the data as specified
    $data = "INSTALLATION_NAME=\"$installation_name\"\n";
    $data .= "AWS_REGION=\"$aws_region\"\n";
    $data .= "AWS_ACCESS_KEY=\"$aws_access_key\"\n";
    $data .= "AWS_SECRET_KEY=\"$aws_secret_key\"\n";
    $data .= "AWS_ACCOUNT_ID=\"$aws_account_id\"\n\n"; // Adding a newline for separation

    // Specify full path if necessary
    $file_path = "/var/www/html/Ngine/credentials.txt"; // Full path to the credentials file

    // Open the file in write mode
    if (file_put_contents($file_path, $data) !== false) {
        // Redirect to aws_services.html after successful write
        header("Location: aws_services.html");
        exit(); // Ensure no further code is executed
    } else {
        echo "Error writing to credentials.txt file.";
    }
}
?>