<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 1:26
 */
class DB
{
    public static $cn;
    private static $db_host = 'localhost';
    private static $db_name = 'todo';
    private static $db_login = 'root';
    private static $db_password = '123456';

    private function __construct(){}

    public static function getInstance()
    {
        try {
            if (empty(self::$cn)) {
                self::$cn = new PDO(
                    'mysql:host='.self::$db_host.';dbname='.self::$db_name, self::$db_login, self::$db_password, 
                    array(
                        PDO::ATTR_PERSISTENT => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    )
                );
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        return self::$cn;
    }
}