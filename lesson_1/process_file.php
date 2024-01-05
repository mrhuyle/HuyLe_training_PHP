<?php
require dirname(__DIR__) . '/vendor/autoload.php';

function process_file($filename)
{
    $conn = get_database_connection();
    $start_time = microtime(true);

    $batch_size = 5000;
    $insert_data = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
        $conn->begin_transaction();

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
                $conn->commit();
                $conn->begin_transaction();
            }
        }

        if (!empty($insert_data)) {
            $query = 'INSERT INTO user ... VALUES ' . implode(',', $insert_data);
            if (!$conn->query($query)) {
                echo 'Error: ' . $conn->error . PHP_EOL;
            }
        }

        $conn->commit();
        fclose($handle);
    }

    $end_time = microtime(true);
    $duration = $end_time - $start_time;
    echo 'Data insertion for ' . $filename . ' completed in ' . $duration . ' seconds.' . PHP_EOL;

    $conn->close();
}

// Assuming the filename is passed as a command-line argument
if ($argc > 1) {
    process_file($argv[1]);
}
