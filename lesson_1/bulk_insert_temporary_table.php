<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();
$start_time = microtime(true);

// Create a temporary table
$conn->query('CREATE TEMPORARY TABLE temp_user LIKE user');

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ','); // Skip header
    $insert_data = [];
    $batch_size = 5000;
    $conn->begin_transaction();

    while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
        $id = $conn->real_escape_string($data[0]);
        $first_name = $conn->real_escape_string($data[1]);
        $last_name = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        $insert_data[] = "('$id', '$first_name', '$last_name', '$address', '$birthday')";

        if (count($insert_data) === $batch_size) {
            $query = 'INSERT INTO temp_user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
            if (!$conn->query($query)) {
                echo 'Error: ' . $conn->error . "\n";
            }
            $insert_data = [];
            $conn->commit();
            $conn->begin_transaction();
        }
    }

    if (!empty($insert_data)) {
        $query = 'INSERT INTO temp_user (id, first_name, last_name, address, birthday) VALUES ' . implode(',', $insert_data);
        if (!$conn->query($query)) {
            echo 'Error: ' . $conn->error . "\n";
        }
    }

    $conn->commit();
    fclose($handle);

    // Transfer data from temporary to final table
    $conn->query('INSERT INTO user SELECT * FROM temp_user');
    // Drop the temporary table
    $conn->query('DROP TEMPORARY TABLE temp_user');
}

$end_time = microtime(true);
$duration = $end_time - $start_time;
echo 'Bulk data insertion using temporary table completed in ' . $duration . ' seconds.';

$conn->close();
