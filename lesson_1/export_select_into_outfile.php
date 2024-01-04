<?php
require dirname(__DIR__) . '/vendor/autoload.php';

// Start timing
$time_start = microtime(true);

// Get database connection
$conn = get_database_connection();

// User input values
$searchAddress = isset($_POST["searchAddress"]) ? $_POST["searchAddress"] : '';
$sortOrder = isset($_POST["sortOrder"]) ? $_POST["sortOrder"] : 'ASC';

// Define the filename for the exported CSV
$filename = "user_data_download.csv";

// Path to save the file on the server
$serverPath = dirname(__DIR__) . '/data/' . $filename; // Replace with your server path

// Build the query using SELECT INTO OUTFILE
$query = "SELECT id, first_name, last_name, address, birthday INTO OUTFILE '" . $serverPath . "' "
    . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\n' "
    . "FROM user_test_upload ";

if (!empty($searchAddress)) {
    $query .= "WHERE address LIKE '%" . $conn->real_escape_string($searchAddress) . "%' ";
}
$query .= "ORDER BY STR_TO_DATE(birthday, '%b-%d-%Y') $sortOrder";

// Execute the query
if ($conn->query($query) === TRUE) {

    // Set response headers for file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . basename($serverPath) . '"');

    // Open the file
    $file = fopen($serverPath, 'rb');

    // Check if the file is opened successfully
    if ($file) {
        // Set a reasonable chunk size (e.g., 1 MB)
        $chunkSize = 1024 * 1024 * 8;

        // Loop through the file and echo chunks
        while (!feof($file)) {
            echo fread($file, $chunkSize);
            ob_flush(); // Flush the output buffer
            flush();    // Send the output to the client
        }

        // Close the file
        fclose($file);
    } else {
        echo "Error opening file.";
    }

    // Optionally delete the file after download
    unlink($serverPath);
} else {
    echo "Error exporting file: " . $conn->error;
}

// End timing
$time_end = microtime(true);

// Calculate and output duration
$time_duration = $time_end - $time_start;
echo "Export Time: " . $time_duration . " seconds";

// Close the database connection
$conn->close();
