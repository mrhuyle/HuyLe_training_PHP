<?php
require dirname(__DIR__) . '/vendor/autoload.php';

function process_file($filename)
{
    $conn = get_database_connection();
    $startTime = microtime(true);

    $batchSize = 5000;
    $insertData = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
        $conn->begin_transaction();

        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            $id = $conn->real_escape_string($data[0]);
            $firstName = $conn->real_escape_string($data[1]);
            $lastName = $conn->real_escape_string($data[2]);
            $address = $conn->real_escape_string($data[3]);
            $birthday = $conn->real_escape_string($data[4]);

            $insertData[] = "('$id', '$firstName', '$lastName', '$address', '$birthday')";

            if (count($insertData) == $batchSize) {
                $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES " . implode(',', $insertData);
                if (!$conn->query($query)) {
                    echo "Error: " . $conn->error . "\n";
                }
                $insertData = [];
                $conn->commit();
                $conn->begin_transaction();
            }
        }

        if (!empty($insertData)) {
            $query = "INSERT INTO user ... VALUES " . implode(',', $insertData);
            if (!$conn->query($query)) {
                echo "Error: " . $conn->error . "\n";
            }
        }

        $conn->commit();
        fclose($handle);
    }

    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    echo "Data insertion for $filename completed in $duration seconds.\n";

    $conn->close();
}

// Assuming the filename is passed as a command-line argument
if ($argc > 1) {
    process_file($argv[1]);
}
