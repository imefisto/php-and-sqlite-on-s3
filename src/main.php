<?php
namespace Imefisto\PhpWithSqliteOnS3;

use Aws\S3\S3Client;

require 'vendor/autoload.php';

// Edit variables
$bucket = 'sqlite-on-s3';
$database = 'mydb.sqlite';
// End edit variables

$client = new S3Client([
    'region'  => getenv('REGION'),
    'version' => 'latest',
]);

$conn = new ConnectionManager(
    $client,
    $bucket,
    $database
);

try {
    $repository = new ExecutionsRepository($conn);

    echo 'Fetch previous rows: ' . PHP_EOL;
    foreach ($repository->all() as $execution) {
        echo $execution['id'] . ' - ' . $execution['created_at'] . PHP_EOL;
    }
    echo 'Done.' . PHP_EOL;

    echo 'Adding data' . PHP_EOL;
    $repository->registerExecution();
    echo 'Done.' . PHP_EOL;
} catch (\Exception $e) {
    die($e->getMessage());
}
