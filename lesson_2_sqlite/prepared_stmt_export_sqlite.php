<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get SQLite database connection
$conn = get_database_connection_sqlite();

// Define the name of the output CSV file
$output_file = dirname(__DIR__) . '/data/exported_user_sqlite.csv';

// Open a file handle for the output CSV file
$output_handle = fopen($output_file, 'w');
if (!$output_handle) {
    die('Error: Unable to open file for writing.' . PHP_EOL);
}

// Start timing for performance measurement
$start_time = microtime(true);

// Begin the transaction
$conn->exec('BEGIN TRANSACTION');

$offset = 0;
$limit = 600000; // Number of rows per chunk

// Prepare the select statement
$stmt = $conn->prepare("SELECT id, first_name, last_name, address, birthday FROM user LIMIT ? OFFSET ?");

while (true) {
    // Bind the limit and offset parameters
    $stmt->bindValue(1, $limit, SQLITE3_INTEGER);
    $stmt->bindValue(2, $offset, SQLITE3_INTEGER);

    // Execute the query
    $result = $stmt->execute();

    if (!$result) {
        die('Error: Unable to execute query. ' . $conn->lastErrorMsg() . PHP_EOL);
    }

    // Check if the first row is false, indicating no more rows
    $firstRow = $result->fetchArray(SQLITE3_ASSOC);
    if ($firstRow === false) {
        break;
    }

    // Process the first row
    fputcsv($output_handle, $firstRow);

    // Fetch each subsequent row from the query result and write it to the CSV file
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        fputcsv($output_handle, $row);
    }

    // Increment offset for the next chunk
    $offset += $limit;
}

// Commit the transaction
$conn->exec('COMMIT');

// Close the file handle
fclose($output_handle);

// End timing and calculate the duration
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'SQLite: Chunked data prepared stmt export completed in ' . $duration . ' seconds.' . PHP_EOL;

// Close the database connection
$conn->close();
