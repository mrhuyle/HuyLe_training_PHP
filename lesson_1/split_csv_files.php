<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require dirname(__DIR__) . '/vendor/autoload.php';

function split_csv_file($source_file, $lines_per_file)
{
    $source_handle = fopen($source_file, 'r');
    if (!$source_handle) {
        echo 'Error: Unable to open source file.' . PHP_EOL;
        return false;
    }

    // Skip the header row
    fgetcsv($source_handle, 2000, ',');

    $file_count = 1;
    $row_count = 0;
    $target_handle = null;

    while (!feof($source_handle)) {
        if ($row_count % $lines_per_file == 0) {
            if ($target_handle) fclose($target_handle);
            $target_file = dirname(__DIR__) . "/data/user_part$file_count.csv";
            $target_handle = fopen($target_file, 'w');
            if (!$target_handle) {
                echo "Error: Unable to open target file: $target_file." . PHP_EOL;
                return false;
            }
            $file_count++;
        }

        $row_data = fgetcsv($source_handle, 0, ',');
        if (!is_array($row_data)) continue;
        fputcsv($target_handle, $row_data);
        $row_count++;
    }

    if ($target_handle) fclose($target_handle);
    fclose($source_handle);

    return true;
}

$result = split_csv_file(dirname(__DIR__) . '/data/user.csv', 850000);
if ($result) {
    echo 'File splitting completed successfully.' . PHP_EOL;
} else {
    echo 'File splitting failed.' . PHP_EOL;
}
