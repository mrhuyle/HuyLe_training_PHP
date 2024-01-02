<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$conn = get_database_connection();
$startTime = microtime(true);

// Create a temporary table
$conn->query("CREATE TEMPORARY TABLE temp_user LIKE user");

if (($handle = fopen(dirname(__DIR__) . '/data/user.csv', 'r')) !== FALSE) {
    fgetcsv($handle, 2000, ","); // Skip header
    $insertData = [];
    $batchSize = 5000;
    $conn->begin_transaction();

    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        $id = $conn->real_escape_string($data[0]);
        $firstName = $conn->real_escape_string($data[1]);
        $lastName = $conn->real_escape_string($data[2]);
        $address = $conn->real_escape_string($data[3]);
        $birthday = $conn->real_escape_string($data[4]);

        $insertData[] = "('$id', '$firstName', '$lastName', '$address', '$birthday')";

        if (count($insertData) == $batchSize) {
            $query = "INSERT INTO temp_user (id, first_name, last_name, address, birthday) VALUES " . implode(',', $insertData);
            if (!$conn->query($query)) {
                echo "Error: " . $conn->error . "\n";
            }
            $insertData = [];
            $conn->commit();
            $conn->begin_transaction();
        }
    }

    if (!empty($insertData)) {
        $query = "INSERT INTO temp_user (id, first_name, last_name, address, birthday) VALUES " . implode(',', $insertData);
        if (!$conn->query($query)) {
            echo "Error: " . $conn->error . "\n";
        }
    }

    $conn->commit();
    fclose($handle);

    // Transfer data from temporary to final table
    $conn->query("INSERT INTO user SELECT * FROM temp_user");
    // Drop the temporary table
    $conn->query("DROP TEMPORARY TABLE temp_user");
}

$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Bulk data insertion using temporary table completed in $duration seconds.";

$conn->close();
