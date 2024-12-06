<?php

$logDir = '/var/www/html/recent_logs/';

// Get the list of all files in the recent_logs directory
$files = array_diff(scandir($logDir), array('..', '.')); // Exclude "." and ".." directories

// Sort files by the full timestamp (date + time), ensuring they are ordered from newest to oldest
usort($files, function($a, $b) {
    // Extract date and time parts from the filenames
    $dateA = substr($a, 0, 10);  // Get 'YYYY-MM-DD' from 'YYYY-MM-DD_HH-MM-SS'
    $timeA = substr($a, 11, 8);  // Get 'HH-MM-SS' from 'YYYY-MM-DD_HH-MM-SS'
    $dateB = substr($b, 0, 10);
    $timeB = substr($b, 11, 8);

    // Compare dates first (descending order), then compare times
    if ($dateA === $dateB) {
        // If the dates are the same, sort by time (descending order)
        return strtotime($b) - strtotime($a);  // Latest time first
    }

    // Otherwise, sort by date (descending order)
    return strcmp($b, $a);  // Latest date first
});

// Output the sorted files
echo '<pre>';
print_r($files);
echo '</pre>';

// If you need to keep the latest 10 files and delete the rest, you can use:
$filesToDelete = array_slice($files, 10);

foreach ($filesToDelete as $file) {
    $filePath = $logDir . $file;

    // Delete the file
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

echo json_encode(['success' => true, 'message' => 'Old logs deleted successfully.']);