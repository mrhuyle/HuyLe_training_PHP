<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from mySQLi
$conn = get_database_connection();

// Start timing
$startTime = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    // Skip the first row (header)
    fgetcsv($handle, 2000, ",");

    $insertData = [];
    $batchSize = 60000; // Number of records to accumulate before bulk insert

    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        // Escape data for SQL insertion
        $id = $conn->real_escape_string($data[0]);
        $firstName = $conn->real_escape_string($data[1]);
        $lastName = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        // Accumulate insert data
        $insertData[] = "('$id', '$firstName', '$lastName', '$address', '$birthday')";

        // Perform bulk insert when batch size is reached
        if (count($insertData) == $batchSize) {
            $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES " . implode(',', $insertData);
            if (!$conn->query($query)) {
                echo "Error: " . $conn->error . "\n";
            }
            $insertData = []; // Reset the insert data array
        }
    }

    // Insert any remaining records
    if (!empty($insertData)) {
        $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES " . implode(',', $insertData);
        if (!$conn->query($query)) {
            echo "Error: " . $conn->error . "\n";
        }
    }

    fclose($handle);
}

// End timing
$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Bulk data insertion completed in $duration seconds.";

// Close the database connection
$conn->close();
