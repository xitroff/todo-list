<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 1:10
 */
spl_autoload_register(function($class){
    require_once('classes/' . $class . '.php');
});