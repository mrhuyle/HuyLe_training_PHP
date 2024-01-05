<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Start timing
$time_start = microtime(true);

// Get database connection
$conn = get_database_connection();

// User input values
$search_address = isset($_POST['searchAddress']) ? $_POST['searchAddress'] : '';
$sort_order = isset($_POST['sortOrder']) ? $_POST['sortOrder'] : 'ASC';

// Define the filename for the exported CSV
$filename = 'user_data_download.csv';

// Set response headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open a file pointer for writing to the response
$output = fopen('php://output', 'w');

// Write CSV header row
fputcsv($output, ['id', 'first_name', 'last_name', 'address', 'birthday']);

$offset = 0;
$limit = 510000; // Number of rows per chunk

while (true) {
    // Query to select data from the database with LIMIT and OFFSET
    $query = "SELECT id, first_name, last_name, address, birthday FROM user_test_upload ";

    if (!empty($search_address)) {
        $query .= "WHERE address LIKE '%" . $conn->real_escape_string($search_address) . "%' ";
    }
    $query .= "ORDER BY STR_TO_DATE(birthday, '%b-%d-%Y') $sort_order LIMIT $limit OFFSET $offset";

    // Execute the query
    $result = $conn->query($query);

    // Break the loop if no more rows
    if ($result->num_rows == 0) {
        break;
    }

    // Fetch each row from the query result and write it to the CSV file
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Increment offset for the next chunk
    $offset += $limit;
}

// Close the file pointer
fclose($output);

// End timing
$time_end = microtime(true);

// Calculate and output duration
$time_duration = $time_end - $time_start;
echo 'Export Time: ' . $time_duration . ' seconds';

// Close the database connection
$conn->close();
