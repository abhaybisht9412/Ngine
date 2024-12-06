<?php

// Define the full output file and the location of the logs
$fullOutputFile = '/var/www/html/logs/full_output.txt';
$bucket1File = '/var/www/html/logs/bucket_1.txt';
$bucket2File = '/var/www/html/logs/bucket_2.txt';
$stateBucketFile = '/var/www/html/logs/state_bucket.txt';

// Read the full_output.txt file
$fullOutput = file_get_contents($fullOutputFile);
if ($fullOutput === false) {
    echo json_encode(['error' => 'Failed to read the full_output.txt file.']);
    exit;
}

// Split into four parts based on stages
$bucket1Stage = "";
$bucket2Stage = "";
$stateBucketStage = "";

// Initialize variables to track stage boundaries
$inBucket1Stage = false;
$inBucket2Stage = false;
$inStateBucketStage = false;

// Extract each stage from the full output based on patterns and start/stop markers
$lines = explode("\n", $fullOutput);
foreach ($lines as $line) {

    // Start or end capturing the state bucket stage based on a marker
    if (strpos($line, 'terraform.tfstate') !== false) {
        $stateBucketStage .= "-- layer 1 ----------\n"; // Dynamic layer marker for state bucket
        $inStateBucketStage = true;
    }
    if ($inStateBucketStage) {
        $stateBucketStage .= $line . "\n";
    }

    // Check if we've encountered bucket 1 creation stage
    if (strpos($line, 'aws_s3_bucket.this: Creating') !== false) {
        $bucket1Stage .= "-- layer 2 ----------\n"; // Dynamic layer marker for bucket 1
        $inBucket1Stage = true;
    }

    if ($inBucket1Stage) {
        $bucket1Stage .= $line . "\n";
    }

    // Check if we've encountered bucket 2 creation stage
    if (strpos($line, 'aws_s3_bucket.this: Creation complete') !== false) {
        $inBucket2Stage = true;
    }

    // Only add lines to bucket2Stage without any additional layer markers (removed layer 3 marker)
    if ($inBucket2Stage) {
        $bucket2Stage .= $line . "\n";
    }
}

// Write the extracted stages into their respective files
file_put_contents($bucket1File, $bucket1Stage);
file_put_contents($bucket2File, $bucket2Stage);
file_put_contents($stateBucketFile, $stateBucketStage);

// Return success message
echo json_encode(['success' => true, 'message' => 'Logs have been trimmed and saved.']);
?>