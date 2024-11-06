<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer's autoloader
require '/app/vendor/autoload.php';

use Aws\S3\S3Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable('/app/OpeNgine-2');
$dotenv->load(); // Load the environment variables

// Get AWS credentials from environment variables
$awsAccessKey = $_ENV['AWS_ACCESS_KEY'] ?? false;
$awsSecretKey = $_ENV['AWS_SECRET_KEY'] ?? false;
$region = $_ENV['AWS_REGION'] ?? false;

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Check if environment variables are set
if (!$awsAccessKey || !$awsSecretKey || !$region) {
    echo json_encode(['error' => 'AWS credentials or region not set in environment variables.']);
    exit;
}

try {
    // Create S3 Client
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => $region,
        'credentials' => [
            'key'    => $awsAccessKey,
            'secret' => $awsSecretKey,
        ],
    ]);

    // List S3 buckets
    $result = $s3Client->listBuckets();
    $buckets = $result['Buckets'];

    // Return the bucket list as JSON
    echo json_encode($buckets);
} catch (Aws\Exception\AwsException $e) {
    // Output error message if fails
    echo json_encode(['error' => $e->getMessage()]);
}
?>
