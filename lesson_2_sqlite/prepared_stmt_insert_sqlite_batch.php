<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from SQLite
$conn = get_database_connection_sqlite();

// Prepare the insert statement
$stmt = $conn->prepare('INSERT INTO user (id, first_name, last_name, address, birthday) VALUES (?, ?, ?, ?, ?)');

// Start timing
$start_time = microtime(true);

if (($handle = fopen(dirname(__DIR__) . '/data/test.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ','); // Skip the first row (header)

    $batch_size = 5000;
    $counter = 0;

    $conn->exec('BEGIN TRANSACTION'); // Begin the transaction

    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        // Bind the data to the prepared statement
        $stmt->bindValue(1, $data[0], SQLITE3_INTEGER);
        $stmt->bindValue(2, $data[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $data[2], SQLITE3_TEXT);
        $stmt->bindValue(4, $data[3], SQLITE3_TEXT);
        $stmt->bindValue(5, $data[4], SQLITE3_TEXT);

        // Execute the prepared statement
        $stmt->execute();
        $counter++;

        if ($counter % $batch_size == 0) {
            $conn->exec('COMMIT');
            $conn->exec('BEGIN TRANSACTION');
        }
    }

    if ($counter % $batch_size != 0) {
        $conn->exec('COMMIT');
    }

    fclose($handle);
}

// End timing
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'SQLite: Prepared statements insertion with batch-size commit completed in ' . $duration . ' seconds.';

$conn->close();
