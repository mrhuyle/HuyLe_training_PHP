<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Get connection from MySQLi
$conn = get_database_connection();

// Start timing
$start_time = microtime(true);

// Open the CSV file
if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    // Skip the header row if your CSV file has one
    fgetcsv($handle, 2000, ',');

    // Read each line of the CSV file
    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        // Assuming CSV columns are in the order: id, first_name, last_name, address, birthday
        // Ensure that $data[0] (id) is an integer
        $id = intval($data[0]);
        $first_name = $conn->real_escape_string($data[1]);
        $last_name = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        // Construct and execute the INSERT statement
        $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ('$id', '$first_name', '$last_name', '$address', '$birthday')";
        if (!$conn->query($query)) {
            echo 'Error: ' . $conn->error;
        }
    }

    fclose($handle);
}

// End timing
$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Data insertion completed in ' . $duration . ' seconds.';

// Close the database connection
$conn->close();
