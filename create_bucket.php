<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Connection: keep-alive');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data
    $data = json_decode(file_get_contents("php://input"), true);
    $value = $data['value']; // Get the value from the JSON payload

    // Change to the specified directory
    chdir('/app/OpeNgine-2');
    
    $action = ($value > 10) ? "apply" : "destroy"; // Determine action based on value

    // Define the full_output.txt log file path
    $fullLogFile = '/var/www/html/logs/full_output.txt';

    // Clear the full output file at the beginning of each request
    file_put_contents($fullLogFile, ""); // Reset the log file

    // Start a process to execute the commands and pipe output
    $process = popen("sudo make build-docker 2>&1; sudo EXE_COMMAND=$action make docker-run 2>&1", 'r');

    if (is_resource($process)) {
        // Stream the output
        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                $output = trim($line); // Collect output

                // Write output to the full_output.txt file
                if (file_put_contents($fullLogFile, $output . "\n", FILE_APPEND) === false) {
                    error_log("Failed to write output to $fullLogFile");
                }
            }
        }
        pclose($process);
    } else {
        file_put_contents($fullLogFile, "Failed to start the process.\n", FILE_APPEND);
        error_log("Failed to open process for command execution.");
    }

    // Respond with a success message
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
