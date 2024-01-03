<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get database connection
$conn = get_database_connection();

// Define the name of the output CSV file
$outputFile = dirname(__DIR__) . '/data/exported_user_data.csv';

// Open a file handle for the output CSV file
$outputHandle = fopen($outputFile, 'w');
if (!$outputHandle) {
    die("Error: Unable to open file for writing.\n");
}

// Start timing for performance measurement
$startTime = microtime(true);

$offset = 0;
$limit = 600000; // Number of rows per chunk

while (true) {
    // Query to select data from the database with LIMIT and OFFSET
    $query = "SELECT id, first_name, last_name, address, birthday FROM user LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);

    if (!$result) {
        die("Error: Unable to execute query. " . $conn->error . "\n");
    }

    // Break the loop if no more rows
    if ($result->num_rows == 0) {
        break;
    }

    // Fetch each row from the query result and write it to the CSV file
    while ($row = $result->fetch_assoc()) {
        fputcsv($outputHandle, $row);
    }

    // Increment offset for the next chunk
    $offset += $limit;
}

// Close the file handle
fclose($outputHandle);

// End timing and calculate the duration
$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Chunked data export completed in $duration seconds.\n";

// Close the database connection
$conn->close();
