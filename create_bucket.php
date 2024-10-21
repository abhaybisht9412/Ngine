<?php
// create_bucket.php
header('Content-Type: application/json');
header('Connection: keep-alive');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data
    $data = json_decode(file_get_contents("php://input"), true);
    $value = $data['value']; // Get the value from the JSON payload

    echo "<script>console.log('$value');</script>";


    // Change to the specified directory
    chdir('/home/ubuntu/OpeNgine-2');

    $action = ($value > 10) ? "apply" : "destroy" ;
    echo "<script>console.log('$action');</script>";
    // Start a process to execute the commands and pipe output
    $process = popen("sudo make build-docker 2>&1; sudo EXE_COMMAND=$action make docker-run 2>&1", 'r');

    if (is_resource($process)) {
        // Stream the output
        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                echo json_encode(['output' => trim($line)]);
                flush(); // Flush the output buffer
                ob_flush();
                usleep(100000); // Add a small delay to avoid overwhelming the client
            }
        }
        pclose($process);
    } else {
        echo json_encode(['output' => 'Failed to start the process.']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
?>
