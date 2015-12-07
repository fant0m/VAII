<?php namespace lib;

use PDO;

class Connection
{
    private static $connection;

    public function __construct() {}

    public static function getInstance() {
        if (!self::$connection) {
            self::$connection =  new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USERNAME, DB_PASSWORD);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$connection;
    }
}