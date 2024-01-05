<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();

$start_time = microtime(true);

// Prepare the insert statement
$stmt = $conn->prepare('INSERT INTO user (id, first_name, last_name, address, birthday) VALUES (?, ?, ?, ?, ?)');

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ','); // Skip header

    $batch_size = 5000;

    $record_count = 0;

    $conn->begin_transaction(); // Start the transaction

    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        // Bind parameters for each row
        $stmt->bind_param('issss', $data[0], $data[1], $data[2], $data[3], $data[4]);

        // Execute the prepared statement
        if (!$stmt->execute()) {
            echo 'Error: ' . $stmt->error . PHP_EOL;
        }

        // Commit and start a new transaction after each batch
        if (++$record_count % $batch_size == 0) {
            $conn->commit();
            $conn->begin_transaction();
        }
    }

    // Commit any remaining records
    $conn->commit();
    fclose($handle);
}

$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Bulk data insertion with prepared statements completed in ' . $duration . ' seconds.';

$stmt->close();
$conn->close();
