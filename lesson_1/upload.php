<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Get database connection
$conn = get_database_connection();

// Check if the form was submitted
if (isset($_POST['submit'])) {
    $target_dir = dirname(__DIR__) . '/data/'; // Directory where uploaded files will be stored
    $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);

    echo $_FILES['fileToUpload']['tmp_name'];

    // Check if the file already exists
    if (file_exists($target_file)) {
        echo 'File already exists.';
    } else {
        // Check file size (optional)
        if ($_FILES['fileToUpload']['size'] > 0) {
            // Rename the file if needed
            $new_file_name = $target_dir . 'test.csv'; // Rename the file as needed
            move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $new_file_name);

            // Truncate (empty) the 'user_test_upload' table
            $truncate_query = 'TRUNCATE TABLE user_test_upload';
            if ($conn->query($truncate_query) === TRUE) {
                echo 'Table truncated successfully.';
            } else {
                echo 'Error truncating table: ' . $conn->error;
            }

            // Load data from CSV file into the 'user' table
            $query = "LOAD DATA INFILE '" . $new_file_name . "' INTO TABLE user_test_upload "
                . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' "  // Escaping the double quote
                . "LINES TERMINATED BY '\\n' "  // Escaping the backslash
                . "IGNORE 1 LINES "  // To ignore the header line
                . "(id, first_name, last_name, address, birthday);";

            if ($conn->query($query) === TRUE) {
                echo 'Data uploaded successfully.';
                header('Location: index.php');
                exit;
            } else {
                echo 'Error uploading data: ' . $conn->error;
            }

            // Close the MySQL connection
            $conn->close();
        } else {
            echo 'File is empty or too large.';
        }
    }
}
