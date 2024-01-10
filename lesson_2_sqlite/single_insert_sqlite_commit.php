<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from SQLite
$conn = get_database_connection_sqlite();

// Start timing
$start_time = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/test.csv', 'r')) !== FALSE) {
    // Skip the header row if your CSV file has one
    fgetcsv($handle, 2000, ',');

    $batchSize = 5000;
    $currentCount = 0;

    // Begin the first transaction
    $conn->exec('BEGIN TRANSACTION');

    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        $id = intval($data[0]);
        $first_name = $conn->escapeString($data[1]);
        $last_name = $conn->escapeString($data[2]);
        $address = $conn->escapeString($data[3]);
        $birthday = $conn->escapeString($data[4]);

        // Construct and execute the INSERT statement
        $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ('$id', '$first_name', '$last_name', '$address', '$birthday')";
        if (!$conn->exec($query)) {
            echo 'Error: ' . $conn->lastErrorMsg();
        }

        $currentCount++;
        // Check if current batch size limit is reached
        if ($currentCount % $batchSize == 0) {
            // Commit the transaction
            $conn->exec('COMMIT');

            // Start a new transaction
            $conn->exec('BEGIN TRANSACTION');
        }
    }

    // Commit any remaining records
    $conn->exec('COMMIT');

    fclose($handle);
}

// End timing
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'SQLite: Batched data insertion completed in ' . $duration . ' seconds.';

// Close the database connection
$conn->close();
