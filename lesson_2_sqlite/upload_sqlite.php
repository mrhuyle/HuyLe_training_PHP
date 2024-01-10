<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Get SQLite database connection
$conn = get_database_connection_sqlite();

// Check if the form was submitted
if (isset($_POST['submit'])) {
    $target_dir = dirname(__DIR__) . '/data/'; // Directory where uploaded files will be stored
    $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);

    // Check if the file already exists
    if (file_exists($target_file)) {
        echo 'File already exists.';
    } else {
        // Check file size (optional)
        if ($_FILES['fileToUpload']['size'] > 0) {
            // Rename the file if needed
            $new_file_name = $target_dir . 'test_sqlite.csv'; // Rename the file as needed
            move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $new_file_name);

            // Truncate (empty) the 'user_test_upload' table
            $truncate_query = 'DELETE FROM user ; VACUUM;';
            if ($conn->exec($truncate_query)) {
                echo 'Table truncated successfully.';
            } else {
                echo 'Error truncating table: ' . $conn->lastErrorMsg();
            }

            // Open the file
            $file = fopen($new_file_name, 'r');

            // Skip the header line
            fgetcsv($file);

            // Begin transaction
            $conn->exec('BEGIN TRANSACTION');

            // Prepare insert statement
            $stmt = $conn->prepare('INSERT INTO user (id, first_name, last_name, address, birthday) VALUES (?, ?, ?, ?, ?)');

            // Read and insert each line from the CSV file
            while (($row = fgetcsv($file)) !== FALSE) {
                $stmt->bindParam(1, $row[0]);
                $stmt->bindParam(2, $row[1]);
                $stmt->bindParam(3, $row[2]);
                $stmt->bindParam(4, $row[3]);
                $stmt->bindParam(5, $row[4]);
                $stmt->execute();
            }

            // Commit the transaction
            $conn->exec('COMMIT');

            // Close the file
            fclose($file);

            echo 'Data uploaded successfully.';
            header('Location: index.php');
            exit;
        } else {
            echo 'File is empty or too large.';
        }
    }
}
