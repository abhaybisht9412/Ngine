<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Mapping stage IDs to log files
    $logFiles = [
        'docker-build' => '/var/www/html/logs/full_output.txt',
        'back-bucket' => '/var/www/html/logs/full_output.txt',
        'fin-bucket-1' => '/var/www/html/logs/full_output.txt',
        'fin-bucket-2' => '/var/www/html/logs/bucket_2.txt'
    ];

    $stageId = $_GET['stage']; // Get the stage from the query parameter

    if (array_key_exists($stageId, $logFiles)) {
        $logFilePath = $logFiles[$stageId];

        if (file_exists($logFilePath)) {
            $logContent = file_get_contents($logFilePath);
            echo json_encode(['success' => true, 'logs' => $logContent]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Log file not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid stage ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
