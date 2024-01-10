<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Start timing
$time_start = microtime(true);

// Get SQLite database connection
$conn = get_database_connection_sqlite(); // Adjust this to get a SQLite3 connection

// Define a custom SQLite function to convert date format
$conn->createFunction('CUSTOM_DATE_FORMAT', function ($date) {
    if ($date) {
        $dateParts = date_parse_from_format('M-d-Y', $date);
        return sprintf('%04d-%02d-%02d', $dateParts['year'], $dateParts['month'], $dateParts['day']);
    }
    return null;
});

// User input values
$search_address = isset($_POST['searchAddress']) ? $_POST['searchAddress'] : '';
$sort_order = isset($_POST['sortOrder']) ? $_POST['sortOrder'] : 'ASC';

// Define the filename for the exported CSV
$filename = 'user_data_sqlite.csv';

// Set response headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open a file pointer for writing to the response
$output = fopen('php://output', 'w');

// Write CSV header row
fputcsv($output, ['id', 'first_name', 'last_name', 'address', 'birthday']);

// Start the transaction
$conn->exec('BEGIN');

$offset = 0;
$limit = 595000; // Number of rows per chunk

while (true) {

    // Modify your query to use the custom function
    $query = "SELECT id, first_name, last_name, address, birthday FROM user ";
    if (!empty($search_address)) {
        $query .= "WHERE address LIKE '%" . $conn->escapeString($search_address) . "%' ";
    }
    $query .= "ORDER BY CUSTOM_DATE_FORMAT(birthday) $sort_order LIMIT $limit OFFSET $offset";

    // Execute the query
    $result = $conn->query($query);

    // Fetch the first row
    $firstRow = $result->fetchArray(SQLITE3_ASSOC);

    // Break the loop if no more rows
    if ($firstRow === false) {
        break;
    }

    // Process the first row
    fputcsv($output, $firstRow);

    // Fetch each row from the query result and write it to the CSV file
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        fputcsv($output, $row);
    }

    // Increment offset for the next chunk
    $offset += $limit;
}

// Commit the transaction
$conn->exec('COMMIT');

// Close the file pointer
fclose($output);

// End timing
$time_end = microtime(true);

// Calculate and output duration
$time_duration = $time_end - $time_start;
echo 'Export Time: ' . $time_duration . ' seconds';

// Close the database connection
$conn->close();
