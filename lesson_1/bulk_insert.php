<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from MySQLi
$conn = get_database_connection();

// Start timing
$start_time = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    // Skip the first row (header)
    fgetcsv($handle, 2000, ',');

    $insert_data = [];
    $batch_size = 60000; // Number of records to accumulate before bulk insert

    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        // Escape data for SQL insertion
        $id = $conn->real_escape_string($data[0]);
        $first_name = $conn->real_escape_string($data[1]);
        $last_name = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        // Accumulate insert data
        $insert_data[] = "('$id', '$first_name', '$last_name', '$address', '$birthday')";

        // Perform bulk insert when batch size is reached
        if (count($insert_data) === $batch_size) {
            $query = 'INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
            if (!$conn->query($query)) {
                echo 'Error: ' . $conn->error . PHP_EOL;
            }
            $insert_data = []; // Reset the insert data array
        }
    }

    // Insert any remaining records
    if (!empty($insert_data)) {
        $query = 'INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
        if (!$conn->query($query)) {
            echo 'Error: ' . $conn->error . PHP_EOL;
        }
    }

    fclose($handle);
}

// End timing
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Bulk data insertion completed in ' . $duration . ' seconds.';

// Close the database connection
$conn->close();
