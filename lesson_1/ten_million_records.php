<?php
require dirname(__DIR__) . '/vendor/autoload.php';

$db = new Util\Database();
$pdo = $db->get_connection();

$conn = get_database_connection();

// Create a Faker instance
$faker = Faker\Factory::create();

// Disable autocommit for performance improvement
$conn->autocommit(FALSE);

// Start timing
$startTime = microtime(true);

// Insert 10 million records
for ($i = 1; $i <= 10000000; $i++) {
    $id = $i;
    $firstName = $conn->real_escape_string($faker->firstName);
    $lastName = $conn->real_escape_string($faker->lastName);
    $address = $conn->real_escape_string($faker->address);
    $birthday = $faker->dateTimeThisCentury->format('M-d-Y');

    $query = "INSERT INTO user (id, first_name, last_name, address, birthday) VALUES ($id, '$firstName', '$lastName', '$address', '$birthday')";

    if (!$conn->query($query)) {
        echo "Error: " . $conn->error;
        $conn->rollback();
        exit();
    }

    // Commit every 1000 inserts to avoid too large transactions
    if ($i % 1000 == 0) {
        $conn->commit();
    }
}

// Commit any remaining transactions
$conn->commit();

// End timing for final batch and calculate duration
$endTime = microtime(true);
$duration = $endTime - $startTime;
echo "Final batch committed in $duration seconds.\n";

// Close the connection
$conn->close();

echo "Data insertion complete.";
