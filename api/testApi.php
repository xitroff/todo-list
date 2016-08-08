<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 03.08.16
 * Time: 22:26
 */
require_once('../autoload.php');
const LIST_NAME = 'list created by api';
const NOTE_TEXT = 'note created by api';

$user = User::registerNewUser();
$token = $user->getToken();
$username = 'testapi@todo.hitrov.com';
$password = '1';
$user->sendPasswordByEmail($username, $password);
var_dump($user);

function requestApi($params)
{
    $action = $params['action'];
    echo '<h2>'.$action.'</h2>';
    $data = $params['data'];
    echo Helper::requestApi($action, $data);
}

$apiMethods = array(
    array(
        'action' => 'usernameExists',
        'data' => array(
            'username' => $username,
        ),
    ),

    array(
        'action' => 'checkLoginAndPassword',
        'data' => array(
            'login' => $username,
            'password' => $password,
        ),
    ),

    array(
        'action' => 'addList',
        'data' => array(
            'token' => $token,
            'listName' => LIST_NAME,
        ),
    ),

    array(
        'action' => 'getLists',
        'data' => array(
            'token' => $token,
        ),
    ),

    array(
        'action' => 'addNote',
        'data' => array(
            'token' => $token,
            'listName' => LIST_NAME,
            'text' => NOTE_TEXT,
        ),
    ),

    array(
        'action' => 'getNotesByListName',
        'data' => array(
            'token' => $token,
            'listName' => LIST_NAME,
        ),
    ),

    array(
        'action' => 'deleteNote',
        'data' => array(
            'token' => $token,
            'listName' => LIST_NAME,
            'noteText' => NOTE_TEXT,
        ),
    ),

    array(
        'action' => 'deleteList',
        'data' => array(
            'token' => $token,
            'listName' => LIST_NAME,
        ),
    ),
);

array_walk($apiMethods, 'requestApi');
var_dump($user->deleteUser());