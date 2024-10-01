<?php
// create_bucket.php

header('Content-Type: application/json'); // Set the response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate that the bucket name is provided
    if (empty($data['bucket_name'])) {
        echo json_encode(['message' => 'Bucket name is required.']);
        exit;
    }

    $bucketName = escapeshellarg($data['bucket_name']); // Escape the bucket name for shell command

    // Change to the specified directory
    chdir('/home/ubuntu/OpeNgine-2');

    $buildOutput = [];
    $runOutput = [];

    // Execute 'make build-docker'
    exec('sudo make build-docker 2>&1', $buildOutput, $buildReturnVar);

    // Execute 'make docker-run' with the bucket name as an argument
    exec("sudo make docker-run BUCKET_NAME=$bucketName 2>&1", $runOutput, $runReturnVar);

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