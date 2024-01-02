<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from mySQLi
$conn = get_database_connection();

// Start timing
$startTime = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        // Assuming CSV columns are in the order: id, first_name, last_name, address, birthday
        $id = $conn->real_escape_string($data[0]);
        $firstName = $conn->real_escape_string($data[1]);
        $lastName = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        // Construct and execute the INSERT statement
        $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ('$id', '$firstName', '$lastName', '$address', '$birthday')";
        if (!$conn->query($query)) {
            echo "Error: " . $conn->error;
        }
    }

    fclose($handle);
}

// End timing
$endTime = microtime(true);
$duration = $endTime - $startTime;

// Result: Error: Incorrect integer value: 'id' for column `one_million_records`.`user`.`id` at row 1Data insertion completed in 5950.9400439262 seconds.
echo "Data insertion completed in $duration seconds.";

// Close the database connection
$conn->close();
