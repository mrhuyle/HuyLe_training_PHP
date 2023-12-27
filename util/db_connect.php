<?php

use Util\Config;

/**
 * Get database connection
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
