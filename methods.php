<?php

function updateNote($noteId, $text)
{
    require('config.php');
    $userId = getUserId();
    $listId = getListIdByNoteId($noteId);
    if (empty($userId) || empty($listId) || !listBelongsToUser($listId) || empty($noteId) || empty($text)) {
        return null;
    }
    $query = "UPDATE `note` 
              SET `text` = :text 
              WHERE `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
        ':text' => $text,
    ]);
    return $result;
}

function deleteList($listId, $token = null)
{
    require('config.php');
    $userId = $token ? getUserId($token) : getUserId();

    if (empty($userId) || empty($listId) || !listBelongsToUser($listId, $userId)) {
        return null;
    }

    $query = "DELETE FROM `list` 
              WHERE `id` = :list_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':list_id' => $listId,
    ]);
    return $result;
}

function deleteNote($noteId, $token = null)
{
    require('config.php');
    $userId = $token ? getUserId($token) : getUserId();
    $listId = getListIdByNoteId($noteId);
    if (empty($userId) || empty($listId) || !listBelongsToUser($listId, $userId) || empty($noteId)) {
        return null;
    }
    $query = "DELETE FROM `note` 
              WHERE `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
    ]);
    return $result;
}

function getLists($token = null)
{
    require('config.php');
    $userId = $token ? getUserId($token) : getUserId();
    if (empty($userId)) {
        return null;
    }
    $query = "SELECT `id`, `name`
                  FROM `list`
                  WHERE `user_id` = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNotes()
{
    $lists = getLists();
    if (empty($lists)) {
        return null;
    }
    $notes = array();
    foreach ($lists as $listItem) {
        $listId = $listItem['id'];
        $listName = $listItem['name'];
        $currentListNotes = getNotesByListId($listId);
        $notes[$listId] = array(
            'listName' => $listName,
            'notes' => $currentListNotes ? $currentListNotes : array(),
        );
    }
    return $notes;
}

function getNotesByListName($listName, $token = null)
{
    require('config.php');
    $userId = $token ? getUserId($token) : getUserId();
    if (empty($userId) || empty($listName)) {
        return null;
    }
    $query = "SELECT `note`.`id`, `text`, `done`
                  FROM `note`
                  JOIN `list`
                  ON `note`.`list_id` = `list`.`id`
                  WHERE `list`.`name` = :list_name
                  AND `list`.`user_id` = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':list_name' => $listName,
        ':user_id' => $userId,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getListIdByName($listName, $token)
{
    require('config.php');
    if (empty($listName) || empty($token)) {
        return null;
    }
    $userId = getUserId($token);
    if (empty($userId)) {
        return null;
    }
    $query = "SELECT `id`
                  FROM `list`
                  WHERE `name` = :list_name
                  AND `user_id` = :user_id 
                  LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':list_name' => $listName,
        ':user_id' => $userId,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
}

function getNoteIdByListIdAndNoteText($listId, $noteText, $token)
{
    require('config.php');
    if (empty($listId) || empty($noteText) || empty($token)) {
        return null;
    }
    $userId = getUserId($token);
    if (empty($userId)) {
        return null;
    }
    $query = "SELECT `note`.`id`
                  FROM `note`
                  JOIN `list`
                  ON `note`.`list_id` = `list`.`id`
                  WHERE `note`.`text` = :note_text
                  AND `list_id` = :list_id
                  AND `user_id` = :user_id 
                  LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':note_text' => $noteText,
        ':list_id' => $listId,
        ':user_id' => $userId,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
}

function getNotesByListId($listId, $undone = false)
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId)) {
        return null;
    }
    if ($undone) {
        $query = "SELECT `id`, `text`, `done`
                  FROM `note`
                  WHERE `list_id` = :list_id
                  AND `done` = 0";
    } else {
        $query = "SELECT `id`, `text`, `done`
                  FROM `note`
                  WHERE `list_id` = :list_id";
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':list_id' => $listId,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getListIdByNoteId($noteId)
{
    require('config.php');
    $query = "SELECT `list_id`
                  FROM `note`
                  WHERE `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':note_id' => $noteId,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['list_id'];
}

function listBelongsToUser($listId, $userId = null)
{
    require('config.php');

    if (!$userId) {
        $userId = getUserId();
    }

    if (empty($userId) || empty($listId)) {
        return null;
    }
    $query = "SELECT `user_id` 
              FROM `list`
              WHERE `id` = :list_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':list_id' => $listId,
    ]);

    if (!$result) {
        return null;
    }

    return $stmt->fetch(PDO::FETCH_ASSOC)['user_id'] == $userId;
}

function addNote($text, $listId, $userId = null)
{
    require('config.php');
    !$userId ? $userId = getUserId() : '';
    if (empty($userId) || !listBelongsToUser($listId, $userId) || empty($text) || empty($listId)) {
        return null;
    }
    $query = "INSERT INTO `note`
                  (`list_id`, `text`)
                  VALUES (:list_id, :text)";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':list_id' => $listId,
        ':text' => $text,
    ]);
    if ($result) {
        return $pdo->lastInsertId();
    } else {
        return null;
    }
}

function updateNoteState($noteId, $state = null)
{
    require('config.php');
    $userId = getUserId();
    $listId = getListIdByNoteId($noteId);
    if (empty($userId) || empty($listId) || !listBelongsToUser($listId)) {
        return null;
    }
    $query = "UPDATE `note` 
              SET `done` = :state 
              WHERE `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
        ':state' => $state ? 1 : 0,
    ]);
    return $result;
}

function getUserId($token = null)
{
    require('config.php');

    if (!$token) {
        if (!isset($_COOKIE['token'])) {
            return false;
        }
        $token = $_COOKIE['token'];
    }
    $query = "SELECT `id` 
              FROM `user`
              WHERE `token` = :token";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':token' => $token,
    ]);

    if (!$result) {
        return null;
    }

    $rowCount = $stmt->rowCount();
    if ($rowCount == 1) {
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    } elseif ($rowCount > 1) {
        throw new Exception('More than one user with equal token.');
    }
    return false;
}

function userEmailAndPasswordExists()
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId)) {
        throw new Exception('Can not get user id.');
    }
    $query = "SELECT `email`, `password` 
              FROM `user`
              WHERE `id` = :id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':id' => $userId,
    ]);

    if (!$result) {
        throw new Exception('Error while retriving user email and password.');
    }

    $rowCount = $stmt->rowCount();
    if ($rowCount) {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $userData['email'] && $userData['password'];
    }
}

function checkLoggedIn()
{
    return (bool) getUserId();
}

function processPostAction()
{
    if (isset($_POST['action'])) {

        switch ($_POST['action']) {
            case 'updateNote':
                $noteId = $_POST['noteId'];
                $text = $_POST['text'];
                die(updateNote($noteId, $text));
            case 'deleteNote':
                $noteId = $_POST['noteId'];
                die(deleteNote($noteId));
            case 'updateNoteState':
                $noteId = $_POST['noteId'];
                $state = $_POST['state'];
                die(updateNoteState($noteId, $state));
            case 'addNote':
                $text = $_POST['text'];
                $listId = $_POST['listId'];
                die(addNote($text, $listId));
            case 'deleteList':
                $listId = $_POST['listId'];
                die(deleteList($listId));
            case 'addList':
                $listName = $_POST['listName'];
                die(addList($listName));
            case 'sendPasswordByEmail':
                $email = $_POST['email'];
                die(sendPasswordByEmail($email));
            case 'checkLoginAndPassword':
                $login = $_POST['login'];
                $password = $_POST['password'];
                $result = checkLoginAndPassword($login, $password);
                die((bool) $result);
            case 'logout':
                die(logout());
            default:
                die;
        }
    }
}

function selectAndDisplayTemplates()
{
    if (!checkLoggedIn()) {
        registerNewUser();
        header('Location: /');
    }

    if (checkLoggedIn()) {
        if (userEmailAndPasswordExists()) {
            include('templates/logout.php');
        } else {
            include('templates/getPasswordByEmailAndLogin.php');
        }
        include('templates/notes.php');
        include('templates/addNewList.php');
    }
}

function logout()
{
    if (!checkLoggedIn()) {
        return null;
    }
    return setcookie('token', '');
}

function checkLoginAndPassword($login, $password) {
    require('config.php');
    $hashedPassword = sha1($password);
    $query = "SELECT `token` 
              FROM `user`
              WHERE `email` = :login 
              AND `password` = :password";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':login' => $login,
        ':password' => $hashedPassword,
    ]);

    if (!$result) {
        return null;
    }

    $rowCount = $stmt->rowCount();
    if ($rowCount == 1) {
        $token = $stmt->fetch(PDO::FETCH_ASSOC)['token'];
        $oneWeek = 60 * 60 * 24 * 7;
        setcookie('token', $token, time() + $oneWeek);
        return $token ? $token : null;
    }
    return null;
}

function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

function generateTokenValue()
{
    return sha1(rand(0, 1000)) . sha1(time()) . sha1(rand(0, 1000)) . sha1(rand(0, 1000));
}

function addList($listName, $userId = null)
{
    require('config.php');
    !$userId ? $userId = getUserId() : '';
    if (empty($userId) || empty($listName)) {
        return null;
    }
    $query = "INSERT INTO `list` 
                (`name`, `user_id`)
                VALUES (:name, :user_id)";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':name' => $listName,
        ':user_id' => $userId,
    ]);
    if ($result) {
        return $pdo->lastInsertId();
    } else {
        return null;
    }
}

function createDefaultNotesListForNewUser($userId, $notesListName = 'Notes')
{
    require('config.php');
    $listId = addList($notesListName, $userId);
    if (!$listId) {
        throw new Exception('Can not create default notes list for new user.');
    }
    createDefaultNoteForNewUser($listId, $userId);
}

function createDefaultNoteForNewUser($listId, $userId, $text = 'Your first note')
{
    require('config.php');
    if (empty($listId)) {
        return null;
    }
    $noteId = addNote($text, $listId, $userId);
    if (!$noteId) {
        throw new Exception('Can not create default note for new user.');
    }
}

function registerNewUser()
{
    require('config.php');
    $token = generateTokenValue();
    if (empty($token)) {
        throw new Exception('Can not generate token for new user.');
    }
    $query = "INSERT INTO `user` 
                (`token`)
                VALUES (:token)";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':token' => $token,
    ]);

    $userId = $pdo->lastInsertId();
    if ($result && $userId)  {
        $oneWeek = 60 * 60 * 24 * 7;
        setcookie('token', $token, time() + $oneWeek);
        createDefaultNotesListForNewUser($userId);
    } else {
        throw new Exception('Can not create new user.');
    }
}

function updateUserInfo($email, $hashedPassword)
{
    require('config.php');
    $userId = getUserId();
    $token = generateTokenValue();
    if (empty($userId) || empty($email) || empty($hashedPassword) || empty($token)) {
        return null;
    }
    $query = "UPDATE `user`
              SET `email` = :email,
                  `password` = :password,
                  `token` = :token
              WHERE `id` = :user_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':email' => $email,
        ':password' => $hashedPassword,
        ':user_id' => $userId,
        ':token' => $token,
    ]);

    return $result;
}

function sendEmail($to, $subject, $mailHeader, $mailBody)
{
    require './sendmail/PHPMailerAutoload.php';

    $mail = new PHPMailer;

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.yandex.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'noreply@todo.hitrov.com';                 // SMTP username
    $mail->Password = 'hFg5DseT';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to

    $mail->setFrom('noreply@todo.hitrov.com', 'To-Do List Mailer');
    $mail->addAddress($to);     // Add a recipient
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;

    $mail->Body = $mailHeader;
    $mail->Body .= $mailBody;

    return $mail->send();
}

function generatePasswordAndUpdateUserData($email)
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId) || empty($email)) {
        return null;
    }
    $password = generatePassword();
    $hashedPassword = sha1($password);

    return array(
        'updated' => updateUserInfo($email, $hashedPassword),
        'password' => $password,
    );
}

function sendPasswordByEmail($email)
{
    //TODO email validation
    $result = generatePasswordAndUpdateUserData($email);
    $password = $result['password'];
    if (!$result['updated'] || empty($password)) {
        die;
    }

    $subject = 'Your To-Do List password';

    $mailHeader = '<h2>Your To-Do List password</h2>';
    $mailBody = '<p>Hello. Your password below:</p>';
    $mailBody .= '<strong>'.$password.'</strong>';
    $mailBody .= '<p>Please use it to login <a href="http://todo.hitrov.com">here</a>.</p>';

    return sendEmail($email, $subject, $mailHeader, $mailBody);
}

// API methods

function requestApi($method, array $parameters)
{
    $url = 'http://www.todo-l.hitrov.com/api/?action='.$method;
    $ch = curl_init( $url );

    $curlOptions = array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $parameters,
        CURLOPT_RETURNTRANSFER => true,
    );

    curl_setopt_array($ch, $curlOptions);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function processApiRequestAction()
{
    if (isset($_REQUEST['action'])) {

        switch ($_REQUEST['action']) {
            case 'usernameExists':
                $username = $_REQUEST['username'];
                die(usernameExists($username));
            case 'testAction':
                die(json_encode($_REQUEST));
            case 'checkLoginAndPassword':
                $login = $_REQUEST['login'];
                $password = $_REQUEST['password'];
                die(checkLoginAndPassword($login, $password));
            case 'getLists':
                $token = $_REQUEST['token'];
                $lists = getLists($token);
                die(json_encode($lists));
            case 'getNotesByListName':
                $listName = $_REQUEST['listName'];
                $token = $_REQUEST['token'];
                $notes = getNotesByListName($listName, $token);
                die(json_encode($notes));
            case 'addList':
                $listName = $_REQUEST['listName'];
                $token = $_REQUEST['token'];
                $userId = getUserId($token);
                die(addList($listName, $userId));
            case 'addNote':
                $listName = $_REQUEST['listName'];
                $token = $_REQUEST['token'];
                $text = $_REQUEST['text'];
                $listId = getListIdByName($listName, $token);
                $userId = getUserId($token);
                die(addNote($text, $listId, $userId));
            case 'deleteList':
                $listName = $_REQUEST['listName'];
                $token = $_REQUEST['token'];
                $listId = getListIdByName($listName, $token);
                die(deleteList($listId, $token));
            case 'deleteNote':
                $token = $_REQUEST['token'];
                $listName = $_REQUEST['listName'];
                $listId = getListIdByName($listName, $token);
                $noteText = $_REQUEST['noteText'];
                $noteId = getNoteIdByListIdAndNoteText($listId, $noteText, $token);
                die(deleteNote($noteId, $token));
            default:
                die;
        }
    }
}

function usernameExists($username)
{
    require('config.php');
    if (empty($username)) {
        return false;
    }
    $query = "SELECT `id` 
              FROM `user`
              WHERE `email` = :username";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':username' => $username,
    ]);

    return $result && $stmt->rowCount() == 1 && $stmt->fetch(PDO::FETCH_ASSOC)['id'];
}