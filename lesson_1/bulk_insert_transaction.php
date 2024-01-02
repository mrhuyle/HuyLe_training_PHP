<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();
$startTime = microtime(true);

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ","); // Skip header
    $insertData = [];
    $batchSize = 5000;
    $conn->begin_transaction(); // Start the transaction before the loop

    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        $id = $conn->real_escape_string($data[0]);
        $firstName = $conn->real_escape_string($data[1]);
        $lastName = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        $insertData[] = "('$id', '$firstName', ...)";

        if (count($insertData) == $batchSize) {
            $query = "INSERT INTO user ... VALUES " . implode(',', $insertData);
            if (!$conn->query($query)) {
                echo "Error: " . $conn->error . "\n";
            }
            $insertData = [];
            $conn->commit(); // Commit the current transaction
            $conn->begin_transaction(); // Start a new transaction
        }
    }

    if (!empty($insertData)) {
        $query = "INSERT INTO user ... VALUES " . implode(',', $insertData);
        if (!$conn->query($query)) {
            echo "Error: " . $conn->error . "\n";
        }
    }

    $conn->commit(); // Commit the final batch
    fclose($handle);
}

$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Bulk data insertion with transaction completed in $duration seconds.";

$conn->close();
