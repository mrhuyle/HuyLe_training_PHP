<?php

namespace Util;

class Config
{
    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'one_million_records';

    /** 
     * Database user
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = 'cms-8341';

    /**
     * Show or hide error message on screen
     * @var boolean
     */
    const SHOW_ERRORS = TRUE;

    /**
     * Path to SQLite database file
     * @var string
     */
    const SQLITE_FILE = '/htdocs/exercise/data/ten_million_records.db';
}
