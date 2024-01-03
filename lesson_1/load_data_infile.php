<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection
$conn = get_database_connection();

// Start timing
$startTime = microtime(true);

$query = "LOAD DATA INFILE '../../../htdocs/exercise/data/user.csv' INTO TABLE user "
    . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' "  // Escaping the double quote
    . "LINES TERMINATED BY '\\n' "  // Escaping the backslash
    . "IGNORE 1 LINES "  // To ignore the header line
    . "(id, first_name, last_name, address, birthday);";

$result = $conn->query($query);

// End timing
$endTime = microtime(true);
$duration = $endTime - $startTime;

if (!$result) {
    echo "Error: " . $conn->error;
} else {
    echo "Data loaded successfully.\n";
    echo "Load data completed in $duration seconds.";
}

// Close the database connection
$conn->close();
