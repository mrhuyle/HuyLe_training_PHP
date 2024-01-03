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

// Start measuring memory usage before the database query and processing
$start_memory = memory_get_peak_usage();

// Start timing for performance measurement
$startTime = microtime(true);

// Query to select data from the database
$query = "SELECT id, first_name, last_name, address, birthday FROM user";

// Execute the query
$result = $conn->query($query);
if (!$result) {
    die("Error: Unable to execute query. " . $conn->error . "\n");
}

// Fetch each row from the query result and write it to the CSV file
while ($row = $result->fetch_assoc()) {
    fputcsv($outputHandle, $row);
}

// Close the file handle
fclose($outputHandle);

// End measuring memory usage after the database query and processing
$end_memory = memory_get_peak_usage();

// Calculate the peak memory usage during this part of the script
$memory_usage = ($end_memory - $start_memory) / (1024 * 1024); // Convert bytes to megabytes
echo "Peak memory usage during data export: {$memory_usage} MB\n";

// End timing and calculate the duration
$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Data export completed in $duration seconds.\n";

// Close the database connection
$conn->close();
