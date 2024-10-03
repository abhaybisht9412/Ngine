<?php
// get_output.php

header('Content-Type: application/json');

$buildOutputFile = '/tmp/build_output.txt';
$runOutputFile = '/tmp/run_output.txt';

$buildOutput = file_exists($buildOutputFile) ? file_get_contents($buildOutputFile) : '';
$runOutput = file_exists($runOutputFile) ? file_get_contents($runOutputFile) : '';

echo json_encode([
    'build_output' => nl2br($buildOutput),
    'run_output' => nl2br($runOutput)
]);
?>