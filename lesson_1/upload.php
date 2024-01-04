<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Get database connection
$conn = get_database_connection();

// Check if the form was submitted
if (isset($_POST["submit"])) {
    $targetDir =  dirname(__DIR__) . '/data/'; // Directory where uploaded files will be stored
    $targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);

    echo $_FILES["fileToUpload"]["tmp_name"];

    // Check if the file already exists
    if (file_exists($targetFile)) {
        echo "File already exists.";
    } else {
        // Check file size (optional)
        if ($_FILES["fileToUpload"]["size"] > 0) {
            // Rename the file if needed
            $newFileName = $targetDir . "test.csv"; // Rename the file as needed
            move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $newFileName);

            // Truncate (empty) the 'user_test_upload' table
            $truncateQuery = "TRUNCATE TABLE user_test_upload";
            if ($conn->query($truncateQuery) === TRUE) {
                echo "Table truncated successfully.";
            } else {
                echo "Error truncating table: " . $conn->error;
            }

            // Load data from CSV file into the 'user' table
            $query = "LOAD DATA INFILE '" . $newFileName . "' INTO TABLE user_test_upload "
                . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' "  // Escaping the double quote
                . "LINES TERMINATED BY '\\n' "  // Escaping the backslash
                . "IGNORE 1 LINES "  // To ignore the header line
                . "(id, first_name, last_name, address, birthday);";

            if ($conn->query($query) === TRUE) {
                echo "Data uploaded successfully.";
                header('Location: index.php');
                exit;
            } else {
                echo "Error uploading data: " . $conn->error;
            }

            // Close the MySQL connection
            $conn->close();
        } else {
            echo "File is empty or too large.";
        }
    }
}
