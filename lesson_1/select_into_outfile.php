<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get database connection
$conn = get_database_connection();

// Start timing
$startTime = microtime(true);

// Define the path for the output CSV file
$outputFile = '../../../htdocs/exercise/data/exported_user_data.csv'; // Update with the absolute path

// Construct the query for SELECT INTO OUTFILE, including the table name
$query = "SELECT id, first_name, last_name, address, birthday 
          INTO OUTFILE '" . $conn->real_escape_string($outputFile) . "' 
          FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' 
          LINES TERMINATED BY '\\n'
          FROM user"; // Include the table name here

$result = $conn->query($query);

// End timing
$endTime = microtime(true);
$duration = $endTime - $startTime;

if (!$result) {
    echo "Error: " . $conn->error;
} else {
    echo "Data exported successfully.\n";
    echo "Export data completed in $duration seconds.";
}

// Close the database connection
$conn->close();
