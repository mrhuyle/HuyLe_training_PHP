<?php

namespace Util;

use PDO;
use PDOException;

class Database
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO('mysql:host=' . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ';charset=utf8', Config::DB_USER, Config::DB_PASSWORD);

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (Config::SHOW_ERRORS) {
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            }
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
    }

    /**
     * Create connection with PDO
     * 
     * @return mixed
     */
    public function get_connection()
    {
        return $this->connection;
    }
}
