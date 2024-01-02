<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require dirname(__DIR__) . '/vendor/autoload.php';

function split_csv_file($sourceFile, $linesPerFile)
{
    $sourceHandle = fopen($sourceFile, 'r');
    if (!$sourceHandle) {
        echo "Error: Unable to open source file.\n";
        return false;
    }

    // Skip the header row
    fgetcsv($sourceHandle, 2000, ",");

    $fileCount = 1;
    $rowCount = 0;
    $targetHandle = null;

    while (!feof($sourceHandle)) {
        if ($rowCount % $linesPerFile == 0) {
            if ($targetHandle) fclose($targetHandle);
            $targetFile = dirname(__DIR__) . "/data/user_part$fileCount.csv";
            $targetHandle = fopen($targetFile, 'w');
            if (!$targetHandle) {
                echo "Error: Unable to open target file: $targetFile.\n";
                return false;
            }
            $fileCount++;
        }

        $rowData = fgetcsv($sourceHandle, 0, ",");
        if (!is_array($rowData)) continue;
        fputcsv($targetHandle, $rowData);
        $rowCount++;
    }

    if ($targetHandle) fclose($targetHandle);
    fclose($sourceHandle);

    return true;
}

$result = split_csv_file(dirname(__DIR__) . '/data/user.csv', 850000);
if ($result) {
    echo "File splitting completed successfully.\n";
} else {
    echo "File splitting failed.\n";
}
