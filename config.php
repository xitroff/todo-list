<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 29.07.16
 * Time: 19:45
 */

$pdo = new PDO(
    'mysql:host=localhost;dbname=todo',
    'root',
    '123456',
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
