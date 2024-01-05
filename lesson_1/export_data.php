<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get database connection
$conn = get_database_connection();

// Define the name of the output CSV file
$output_file = dirname(__DIR__) . '/data/exported_user_data.csv';

// Open a file handle for the output CSV file
$output_handle = fopen($output_file, 'w');
if (!$output_handle) {
    die('Error: Unable to open file for writing.' . PHP_EOL);
}

// Start timing for performance measurement
$start_time = microtime(true);

$offset = 0;
$limit = 600000; // Number of rows per chunk

while (true) {
    // Query to select data from the database with LIMIT and OFFSET
    $query = "SELECT id, first_name, last_name, address, birthday FROM user LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);

    if (!$result) {
        die('Error: Unable to execute query. ' . $conn->error . PHP_EOL);
    }

    // Break the loop if no more rows
    if ($result->num_rows == 0) {
        break;
    }

    // Fetch each row from the query result and write it to the CSV file
    while ($row = $result->fetch_assoc()) {
        fputcsv($output_handle, $row);
    }

    // Increment offset for the next chunk
    $offset += $limit;
}

// Close the file handle
fclose($output_handle);

// End timing and calculate the duration
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Chunked data export completed in ' . $duration . ' seconds.' . PHP_EOL;

// Close the database connection
$conn->close();
