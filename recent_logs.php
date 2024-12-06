<?php
header('Content-Type: application/json');

// Path to the 'recent_bucket_logs' directory
$recentLogsDir = '/var/www/html/recent_bucket_logs/';

// Check if the directory exists
if (is_dir($recentLogsDir)) {
    // Scan the directory for subdirectories (installation names)
    $directories = array_filter(glob($recentLogsDir . '*'), 'is_dir');

    // Extract the directory names (installation names) only
    $directoryNames = array_map(function($dir) {
        return basename($dir);  // Get folder name without the path
    }, $directories);

    // Return the list of directory names as a JSON response
    echo json_encode(['directories' => $directoryNames]);
} else {
    echo json_encode(['error' => 'Directory does not exist.']);
}
?>
