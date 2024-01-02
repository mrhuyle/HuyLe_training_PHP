<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();

$startTime = microtime(true);

// Prepare the insert statement
$stmt = $conn->prepare("INSERT INTO user (id, first_name, last_name, address, birthday) VALUES (?, ?, ?, ?, ?)");

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ","); // Skip header

    $batchSize = 5000;

    $recordCount = 0;

    $conn->begin_transaction(); // Start the transaction

    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        // Bind parameters for each row
        $stmt->bind_param("issss", $data[0], $data[1], $data[2], $data[3], $data[4]);

        // Execute the prepared statement
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error . "\n";
        }

        // Commit and start a new transaction after each batch
        if (++$recordCount % $batchSize == 0) {
            $conn->commit();
            $conn->begin_transaction();
        }
    }

    // Commit any remaining records
    $conn->commit();
    fclose($handle);
}

$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Bulk data insertion with prepared statements completed in $duration seconds.";

$stmt->close();
$conn->close();
