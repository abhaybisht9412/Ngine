<?php
// create_bucket.php

header('Content-Type: application/json'); // Set the response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Change to the specified directory
    chdir('/home/ubuntu/OpeNgine-2');

    $buildOutput = [];
    $runOutput = [];

    // Execute 'make build-docker'
    exec('sudo make build-docker 2>&1', $buildOutput, $buildReturnVar);

    // Execute 'make docker-run'
    exec('sudo make docker-run 2>&1', $runOutput, $runReturnVar);

    // Check if both commands were successful
    if ($buildReturnVar === 0 && $runReturnVar === 0) {
        echo json_encode(['message' => 'Bucket created and commands executed successfully.']);
    } else {
        echo json_encode([
            'message' => 'Error executing commands.',
            'build_output' => implode("\n", $buildOutput),
            'run_output' => implode("\n", $runOutput)
        ]);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
?>