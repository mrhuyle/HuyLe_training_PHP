<?php

use Util\Config;

/**
 * Get database MySQLi connection
 * 
 * @return mixed
 */
function get_database_connection()
{
    $conn = new \mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASSWORD, Config::DB_NAME);

    $conn->set_charset('utf8mb4');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
        echo 'Error';
    }

    return $conn;
}

/**
 * Get database SQLite connection
 * 
 * @return \SQLite
 */
function get_database_connection_sqlite()
{
    try {
        $conn = new \SQLite3(Config::SQLITE_FILE);

        // Set error mode to exceptions
        $conn->enableExceptions(true);

        return $conn;
    } catch (\Exception $e) {
        if (Config::SHOW_ERRORS) {
            die('Connection failed: ' . $e->getMessage());
        }
        return null;
    }
}
