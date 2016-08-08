<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 1:33
 */
class User
{
    private $id = 0;
    private $token = '';
    private $email = '';
    private $telegramUserId = 0;
    private $telegramChatId = 0;
    
    private $db;
    
    public function __construct($token, $id = null, $email = null, $telegramUserId = null, $telegramChatId = null)
    {
        $this->db = DB::getInstance();
        $this->token = $token;
        $id ? $this->id = $id : '';

        $email ? $this->email = $email : '';
        $telegramUserId ? $this->telegramUserId = $telegramUserId : '';
        $telegramChatId ? $this->telegramChatId = $telegramChatId : '';
    }

    public function getId()
    {
        return $this->id;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getTelegramUserId()
    {
        return $this->telegramUserId;
    }

    public function getTelegramChatId()
    {
        return $this->telegramChatId;
    }

    public static function registerNewUser()
    {
        $token = Helper::generateTokenValue();
        if (empty($token)) {
            throw new Exception('Can not generate token for new user.');
        }
        $db = DB::getInstance();
        $query = "INSERT INTO `user` 
                (`token`)
                VALUES (:token)";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':token' => $token,
        ]);

        $id = $db->lastInsertId();
        if ($result && $id) {
            $user = new self($token, $id);
            setcookie('token', $token, time() + Helper::ONE_WEEK);
            $user->createDefaultNotesList();
            return $user;
        } else {
            throw new Exception('registerNewUser: can not create new user.');
        }
    }

    public static function get($token = null)
    {
        if (!$token && empty($_COOKIE['token'])) {
            return self::registerNewUser();
        }
        $token = $token
            ? $token
            : $_COOKIE['token'];
        $query = "SELECT `id`, `email`, `telegram_user_id`, `telegram_chat_id` 
              FROM `user`
              WHERE `token` = :token";
        $db = DB::getInstance();
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':token' => $token,
        ]);

        if (!$result) {
            return null;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rowCount = $stmt->rowCount();
        if ($rowCount == 1) {
            $id = $result['id'];
            $email = $result['email'];
            $telegramUserId = $result['telegram_user_id'];
            $telegramChatId = $result['telegram_chat_id'];
            return new self($token, $id, $email, $telegramUserId, $telegramChatId);
        } elseif ($rowCount > 1) {
            throw new Exception('More than one user with equal token.');
        } else {
            setcookie('token', '');
            header('Location: /');
        }
        return null;
    }

    private function createDefaultNotesList($listName = 'Notes')
    {
        if (empty($this->id)) {
            throw new Exception('createDefaultNotesList: no user id.');
        }
        $list = new ToDoList($this->id, $listName);
        if (empty($list->getId())) {
            throw new Exception('createDefaultNotesList: can not create notes list.');
        }
        $this->createDefaultNote($list);
    }

    private function createDefaultNote(ToDoList $list, $text = 'Your first note')
    {
        $note = new ToDoNote($list, $text);
        if (!$note->getId()) {
            throw new Exception('createDefaultNote: can not create note.');
        }
    }

    private function updateUserData($field, $value)
    {
        if (empty($this->id) || empty($field)) {
            return null;
        }
        $query = "UPDATE `user`
                      SET `".$field."` = :value
                      WHERE `id` = :user_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':value' => $value,
            ':user_id' => $this->id,
        ]);

        return $result;
    }

    public function getLoggedStatus()
    {
        //TODO separate field with hash in DB for that
        return !empty($_COOKIE['logged']);
    }

    public static function checkLoginAndPassword($login, $password)
    {
        if (empty($login) || empty($password)) {
            return null;
        }
        $hashedPassword = sha1($password);
        $query = "SELECT `token` 
              FROM `user`
              WHERE `email` = :login 
              AND `password` = :password";
        $db = DB::getInstance();
        $stmt = $db->prepare($query);
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
            return $token ? $token : null;
        }
        return null;
    }

    public function sendPasswordByEmail($email, $password = null)
    {
        //TODO email validation
        if (empty($email)) {
            return null;
        }

        if (!$password) {
            $password = Helper::generatePassword();
        }
        $hashedPassword = sha1($password);

        $emailUpdateResult = $this->updateUserData('email', $email);
        $passwordCreationResult = $this->updateUserData('password', $hashedPassword);
        
        if (!$emailUpdateResult || !$passwordCreationResult) {
            return null;
        }
        $this->email = $email;

        $subject = 'Your To-Do List password';

        $mailHeader = '<h2>Your To-Do List password</h2>';
        $mailBody = '<p>Hello. Your password below:</p>';
        $mailBody .= '<strong>'.$password.'</strong>';
        $mailBody .= '<p>Please use it to login <a href="https://www.todo.hitrov.com">here</a>.</p>';

        return Mailer::sendEmail($email, $subject, $mailHeader, $mailBody);
    }
    
    public static function usernameExists($username)
    {
        if (empty($username)) {
            return false;
        }
        $db = DB::getInstance();
        $query = "SELECT `id` 
              FROM `user`
              WHERE `email` = :username";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':username' => $username,
        ]);

        return $result && $stmt->rowCount() == 1 && $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function passwordWasSentByEmail()
    {
        return !empty($this->email);
    }
    
    public function deleteUser()
    {
        if (empty($this->id)) {
            throw new Exception('deleteUser: no user id.');
        }
        $query = "DELETE FROM `user` 
                  WHERE `id` = :user_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':user_id' => $this->id,
        ]);
        return $result;
    }

    public function setTelegramUserIdAndChatId($telegramUserId, $telegramChatId)
    {
        if (empty($this->id) || empty($telegramUserId) || empty($telegramChatId)) {
            return null;
        }
        $updateTelegramUserId = $this->updateUserData('telegram_user_id', $telegramUserId);
        $pdateTelegramChatId = $this->updateUserData('telegram_chat_id', $telegramChatId);
        if (!$updateTelegramUserId || !$pdateTelegramChatId) {
            return null;
        }
        $this->telegramUserId = $telegramUserId;
        $this->telegramChatId = $telegramChatId;
        return true;
    }
    
}