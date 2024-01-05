<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();
$start_time = microtime(true);

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ','); // Skip header
    $insert_data = [];
    $batch_size = 10000;
    $conn->begin_transaction(); // Start the transaction before the loop

    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        $id = $conn->real_escape_string($data[0]);
        $first_name = $conn->real_escape_string($data[1]);
        $last_name = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        $insert_data[] = "('$id', '$first_name', '$last_name', '$address', '$birthday')";

        if (count($insert_data) === $batch_size) {
            $query = 'INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
            if (!$conn->query($query)) {
                echo 'Error: ' . $conn->error . PHP_EOL;
            }
            $insert_data = [];
            // $conn->commit(); // Commit the current transaction
            // $conn->begin_transaction(); // Start a new transaction
        }
    }

    if (!empty($insert_data)) {
        $query = 'INSERT INTO user ... VALUES ' . implode(',', $insert_data);
        if (!$conn->query($query)) {
            echo 'Error: ' . $conn->error . PHP_EOL;
        }
    }

    $conn->commit(); // Commit the final batch
    fclose($handle);
}

$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Bulk data insertion with transaction completed in ' . $duration . ' seconds.';

$conn->close();
