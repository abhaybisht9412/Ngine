<?php
header('Content-Type: application/json');
header('Connection: keep-alive');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $value = $data['value'];
    $ec2instance = $data['ec2instance']; // This is your ec2instance string

    // Set path based on ec2instance value
    if ($ec2instance === 'asgec2') {
        $path = 'examples/v2/platform-aws/ec2-asg/manifest.yml'; // Path for ec2asg
    } elseif ($ec2instance === 's3') {
        $path = 'examples/v2/platform-aws/s3/manifest.yml'; // Path for s3
    } else {
        $path = '/home/default'; // Default path if no match
    }

    // Print final path for checking purposes
    echo "Final path: " . $path . "\n"; // Output the final path

    chdir('/app/OpeNgine-2');

    $action = ($value > 10) ? "apply" : "destroy";

    $process = popen("sudo make build-docker 2>&1; sudo EXE_COMMAND=$action PATH_VARIABLE=$path make docker-run 2>&1", 'r');

    $success = true;
    $outputBuffer = [];

    // Open the log file in write mode (overwrites the file)
    $logFile = '/var/www/html/logs/full_output.txt';
    $fileHandle = fopen($logFile, 'w'); // 'w' flag clears the file before writing

    if (!$fileHandle) {
        echo json_encode(['output' => 'Failed to open log file for writing.']);
        exit;
    }

    if (is_resource($process)) {
        while (!feof($process)) {
            $line = fgets($process);
            if ($line !== false) {
                $output = trim($line);

                // Store output in buffer
                $outputBuffer[] = $output;

                // Send output based on stages
                if (strpos($output, 'docker run') !== false) {
                    echo json_encode(['stage' => 'docker-build', 'output' => implode("\n", $outputBuffer)]);
                    flush();
                    ob_flush();
                    $outputBuffer = [];
                }

                if (strpos($output, 'Apply complete! Resources:') !== false) {
                    echo json_encode(['stage' => 'fin-bucket-1', 'output' => implode("\n", $outputBuffer)]);
                    flush();
                    ob_flush();
                    $outputBuffer = [];
                }

                if (strpos($output, 'aws_s3_bucket.this: Creation complete') !== false) {
                    echo json_encode(['stage' => 'fin-bucket-2', 'output' => implode("\n", $outputBuffer)]);
                    flush();
                    ob_flush();
                    $outputBuffer = [];
                }

                // Send live output
                echo json_encode(['output' => $output]);
                flush();
                ob_flush();

                usleep(100000);

                // Write the output to the log file, overwriting previous content
                fwrite($fileHandle, $output . "\n");

                if (strpos($line, 'error') !== false) {
                    $success = false;
                }
            }
        }
        pclose($process);
    } else {
        echo json_encode(['output' => 'Failed to start the process.']);
        $success = false;
    }

    // Send any remaining output in buffer
    if (!empty($outputBuffer)) {
        echo json_encode(['stage' => 'fin-bucket-2', 'output' => implode("\n", $outputBuffer)]);
        flush();
        ob_flush();
    }

    // Close the log file
    fclose($fileHandle);

    // Copy the full_output.txt to recent_logs with current date and time as filename
    $recentLogsDir = '/var/www/html/recent_bucket_logs/';

    // Read INSTALLATION_NAME from .env file
    $envFilePath = '/app/OpeNgine-2/.env';
    $installationName = '';
    if (file_exists($envFilePath)) {
        $envContents = file_get_contents($envFilePath);
        preg_match('/INSTALLATION_NAME=(.*)/', $envContents, $matches);
        if (isset($matches[1])) {
            // Remove any leading/trailing spaces and quotes (if present)
            $installationName = trim($matches[1], '"');  // Remove double quotes, if any
        }
    }

    // If we couldn't find INSTALLATION_NAME, set a default
    if (empty($installationName)) {
        $installationName = 'default_installation';
    }

    // Create the folder with the INSTALLATION_NAME
    $installationFolderPath = $recentLogsDir . $installationName;
    if (!is_dir($installationFolderPath)) {
        mkdir($installationFolderPath, 0777, true); // Create the directory if it doesn't exist
    }

    // Generate the filename with the current date and time
    $dateTimeNow = date('Y-m-d_H-i-s');  // Format: 2024-11-07_15-32-21
    $recentLogFilename = $installationFolderPath . '/' . $dateTimeNow . '.txt';

    // Read the content of the full_output.txt file
    $fullOutputContent = file_get_contents($logFile);
    if ($fullOutputContent === false) {
        echo json_encode(['error' => 'Failed to read the full_output.txt file.']);
        exit;
    }

    // Write the content to the new file in recent_logs
    file_put_contents($recentLogFilename, $fullOutputContent);

    // Call trim_logs.php to process the full_output.txt and create the other logs
    $trimLogsCommand = 'php /var/www/html/trim_logs.php';
    $output = shell_exec($trimLogsCommand);

    echo json_encode(['success' => $success, 'action' => $action, 'trimLogsResult' => $output, 'recentLogFile' => $recentLogFilename]);

} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
?>
