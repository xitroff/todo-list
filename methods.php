<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 29.07.16
 * Time: 19:55
 */
function updateNote($noteId, $text)
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId) || empty($noteId) || empty($text)) {
        return null;
    }
    $query = "UPDATE `note` 
              SET `text` = :text 
              WHERE `user_id` = :user_id 
              AND `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
        ':user_id' => $userId,
        ':text' => $text,
    ]);
    return $result;
}

function deleteNote($noteId)
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId) || empty($noteId)) {
        return null;
    }
    $query = "DELETE FROM `note` 
              WHERE `user_id` = :user_id 
              AND `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
        ':user_id' => $userId,
    ]);
    return $result;
}

function getNotes()
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId)) {
        return null;
    }
    $query = "SELECT `id`, `text`, `done`
                  FROM `note`
                  WHERE `user_id` = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addNote($text)
{
    require('config.php');
    $userId = getUserId();
    if (empty($userId) || empty($text)) {
        return null;
    }
    $query = "INSERT INTO `note`
                  (`text`, `user_id`)
                  VALUES (:text, :user_id)";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':text' => $text,
        ':user_id' => $userId,
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
    if (empty($userId)) {
        return null;
    }
    $query = "UPDATE `note` 
              SET `done` = :state 
              WHERE `user_id` = :user_id 
              AND `id` = :note_id";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':note_id' => $noteId,
        ':user_id' => $userId,
        ':state' => $state ? 1 : 0,
    ]);
    return $result;
}

function getUserId()
{
    require('config.php');
    if (!isset($_COOKIE['token'])) {
        return false;
    }
    $token = $_COOKIE['token'];
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
    }
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
        throw new Exception('Error while retriving user wemail and password.');
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
                die(addNote($text));
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
        return true;
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

    if ($result) {
        $oneWeek = 60 * 60 * 24 * 7;
        setcookie('token', $token, time() + $oneWeek);
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
