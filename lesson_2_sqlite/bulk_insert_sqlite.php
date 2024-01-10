<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from SQLite
$conn = get_database_connection_sqlite();

// Start timing
$start_time = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/test.csv', 'r')) !== FALSE) {
    // Skip the first row (header)
    fgetcsv($handle, 2000, ',');

    $insert_data = [];
    $batch_size = 5000; // Number of records to accumulate before bulk insert

    // Begin the transaction
    $conn->exec('BEGIN TRANSACTION');

    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        // Escape data for SQL insertion
        $id = $conn->escapeString($data[0]);
        $first_name = $conn->escapeString($data[1]);
        $last_name = $conn->escapeString($data[2]);
        $address = $conn->escapeString($data[3]);
        $birthday = $conn->escapeString($data[4]);

        // Accumulate insert data
        $insert_data[] = "('$id', '$first_name', '$last_name', '$address', '$birthday')";

        // Perform bulk insert when batch size is reached
        if (count($insert_data) === $batch_size) {
            $query = 'INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
            if (!$conn->exec($query)) {
                echo 'Error: ' . $conn->lastErrorMsg() . PHP_EOL;
            }
            $insert_data = []; // Reset the insert data array

            // Commit the transaction and begin a new one
            // $conn->exec('COMMIT');
            // $conn->exec('BEGIN TRANSACTION');
        }
    }

    // Insert any remaining records
    if (!empty($insert_data)) {
        $query = 'INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
        if (!$conn->exec($query)) {
            echo 'Error: ' . $conn->lastErrorMsg() . PHP_EOL;
        }
    }

    // Commit the final transaction
    $conn->exec('COMMIT');

    fclose($handle);
}

// End timing
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'SQLite: Bulk data insertion completed in ' . $duration . ' seconds.';

// Close the database connection
$conn->close();
