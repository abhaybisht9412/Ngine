<?php
// create_bucket.php
header('Content-Type: application/json');
header('Connection: keep-alive');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data
    $data = json_decode(file_get_contents("php://input"), true);
    $value = $data['value']; // Get the value from the JSON payload

    // Change to the specified directory
    chdir('/home/ubuntu/OpeNgine-2');

    $action = ($value > 10) ? "apply" : "destroy"; // Determine action based on value

    // Start a process to execute the commands and pipe output
    $process = popen("sudo make build-docker 2>&1; sudo EXE_COMMAND=$action make docker-run 2>&1", 'r');

    $success = true; // Variable to track if the operation was successful

    if (is_resource($process)) {
        // Stream the output
        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                $output = trim($line); // Collect output
                echo json_encode(['output' => $output]); // Send output as JSON
                flush(); // Flush the output buffer
                ob_flush(); // Flush the output buffer
                usleep(100000); // Add a small delay to avoid overwhelming the client
                // Check if the output indicates a failure
                if (strpos($line, 'error') !== false) {
                    $success = false;
                }
            }
        }
        pclose($process);
    } else {
        echo json_encode(['output' => 'Failed to start the process.']);
        $success = false; // Mark as failed if the process could not be started
    }

    // Final success status is sent in the last JSON response
    echo json_encode(['success' => $success, 'action' => $action]);
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
?>
