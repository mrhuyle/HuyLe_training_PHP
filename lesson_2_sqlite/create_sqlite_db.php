<?php
try {
    $db = new SQLite3(dirname(__DIR__) . '/data/ten_million_records.db');
    $db->exec('CREATE TABLE user (
        id INTEGER PRIMARY KEY,
        first_name TEXT,
        last_name TEXT,
        address TEXT,
        birthday TEXT
      )');
    echo 'Table created successfully';
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
