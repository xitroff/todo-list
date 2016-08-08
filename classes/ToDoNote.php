<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 2:23
 */
class ToDoNote
{
    private $id = 0;
    private $list;
    private $text = '';
    private $done;
    private $emailNotification;
    private $telegramlNotification;
    private $notificationDatetime;
    private $notificationDate;
    private $notificationTime;

    private $db;
    
    public function __construct(
        ToDoList $list, 
        $text = null, $id = null, $done = null, 
        $emailNotification = null, $telegramlNotification = null,
        $notificationDatetime = null, $notificationDate = null, $notificationTime = null
    )
    {
        $this->db = DB::getInstance();
        if (empty($list)) {
            return null;
        }
        $this->list = $list;
        $text ? $this->text = $text : '';
        $done !== null ? $this->done = $done : '';
        $emailNotification !== null ? $this->emailNotification = $emailNotification : '';
        $telegramlNotification !== null ? $this->telegramlNotification = $telegramlNotification : '';
        $notificationDatetime !== null ? $this->notificationDatetime = $notificationDatetime : '';
        $notificationDate !== null ? $this->notificationDate = $notificationDate : '';
        $notificationTime !== null ? $this->notificationTime = $notificationTime : '';
        if (!$id && $text) {
            $this->addNote();
        } else {
            $this->id = $id;
        }
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setText($text)
    {
        return $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getDone()
    {
        return $this->done;
    }

    public function getEmailNotification()
    {
        return $this->emailNotification;
    }

    public function getTelegramlNotification()
    {
        return $this->telegramlNotification;
    }

    public function getNotificationDatetime()
    {
        return $this->notificationDatetime;
    }

    public function getNotificationDate()
    {
        return $this->notificationDate;
    }

    public function getNotificationTime()
    {
        return $this->notificationTime;
    }

    public function addNote()
    {
        $listId = $this->list->getId();
        $userId = $this->list->getUserId();
        if (empty($userId) || !$this->list->listBelongsToUser() || empty($this->text) || empty($listId)) {
            return null;
        }
        $query = "INSERT INTO `note`
                  (`list_id`, `text`)
                  VALUES (:list_id, :text)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':list_id' => $listId,
            ':text' => $this->text,
        ]);
        $id = $this->db->lastInsertId();
        if ($result && $id) {
            $this->id = $id;
            return $this;
        } else {
            return null;
        }
    }

    public function updateNote($text)
    {
        if (empty($this->id) || empty($this->list) || empty($text)) {
            return null;
        }
        if (!$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "UPDATE `note` 
              SET `text` = :text 
              WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':note_id' => $this->id,
            ':text' => $text,
        ]);
        return $result;
    }

    public function updateNoteState($state = null)
    {
        //TODO check maybe $state should be required parameter
        
        if (empty($this->id) || empty($this->list) || !$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "UPDATE `note` 
              SET `done` = :state 
              WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':note_id' => $this->id,
            ':state' => $state ? 1 : 0,
        ]);
        return $result;
    }

    public function deleteNote()
    {
        if (empty($this->id) || empty($this->list)) {
            return null;
        }
        if (!$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "DELETE FROM `note` 
              WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':note_id' => $this->id,
        ]);
        return $result;
    }

    public function getNotes($undone = false)
    {
        $notes = array();
        if (empty($this->list)) {
            return $notes;
        }
        $listId = $this->list->getId();
        if (empty($listId)) {
            return $notes;
        }
        if ($undone) {
            $query = "SELECT `id`, `text`, `done`, 
                        `email_notification`, `telegram_notification`, `notification_datetime`, 
                        TIME(`notification_datetime`) as `time`, DATE(`notification_datetime`) as `date`
                        FROM `note`
                        WHERE `list_id` = :list_id
                        AND `done` = 0";
        } else {
            $query = "SELECT `id`, `text`, `done`,
                          `email_notification`, `telegram_notification`, `notification_datetime`,
                          TIME(`notification_datetime`) as `time`, DATE(`notification_datetime`) as `date`
                          FROM `note`
                          WHERE `list_id` = :list_id";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':list_id' => $listId,
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return $notes;
        }
        foreach ($result as $noteItem) {
            $id = $noteItem['id'];
            $text = $noteItem['text'];
            $done = $noteItem['done'];
            $emailNotification = $noteItem['email_notification'];
            $telegramlNotification = $noteItem['telegram_notification'];
            $notificationDatetime = $noteItem['notification_datetime'];
            $notificationDate = Helper::getFormattedDate($noteItem['date']);
            $notificationTime = Helper::getFormattedTime($noteItem['time']);
            if (empty($id) || empty($text)) {
                continue;
            }
            $notes[] = new self(
                $this->list, $text, $id, $done, 
                $emailNotification, $telegramlNotification,
                $notificationDatetime, $notificationDate, $notificationTime
            );
        }
        return $notes;
    }

    public function getNotesByListName()
    {
        if (empty($this->list)) {
            return null;
        }
        $listName = $this->list->getListName();
        $userId = $this->list->getUserId();
        if (empty($listName) || empty($userId)) {
            return null;
        }
        $query = "SELECT `note`.`id`, `text`, `done`
                  FROM `note`
                  JOIN `list`
                  ON `note`.`list_id` = `list`.`id`
                  WHERE `list`.`name` = :list_name
                  AND `list`.`user_id` = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':list_name' => $listName,
            ':user_id' => $userId,
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        $notes = array();
        foreach ($result as $noteItem) {
            $id = $noteItem['id'];
            $text = $noteItem['text'];
            $done = $noteItem['done'];
            if (empty($id) || empty($text)) {
                continue;
            }
            $notes[] = new self($this->list, $text, $id, $done);
        }
        return $notes;
    }

    public function getNoteByText()
    {
        if (empty($this->list) || empty($this->text)) {
            return null;
        }
        $listId = $this->list->getId();
        $userId = $this->list->getUserId();
        if (empty($listId) || empty($userId)) {
            return null;
        }
        $query = "SELECT `note`.`id`, `done`
                  FROM `note`
                  JOIN `list`
                  ON `note`.`list_id` = `list`.`id`
                  WHERE `note`.`text` = :note_text
                  AND `list_id` = :list_id
                  AND `user_id` = :user_id 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':note_text' => $this->text,
            ':list_id' => $listId,
            ':user_id' => $userId,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        $id = $result['id'];
        $done = $result['done'];
        if (!$result) {
            return null;
        }
        $this->id = $id;
        $this->done = $done;
        return $this;
    }

    /**
     * @param int $unixTimestamp
     * @param bool $byEmail
     * @param bool $byTelegram
     * @param User $user
     * @return bool|null
     */
    public function setNotification($unixTimestamp, $byEmail, $byTelegram, $user)
    {
        if (!$this->id || empty($this->list) || $unixTimestamp < time() || !Helper::isValidTimeStamp($unixTimestamp) || (!$byEmail && !$byTelegram)) {
            return null;
        }
        if ($byEmail && !$user->getEmail()) {
            return null;
        }
        if ($byTelegram && (!$user->getTelegramChatId() || !$user->getTelegramUserId())) {
            return null;
        }
        if (!$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "UPDATE `note` 
                    SET `email_notification` = :email_notification,
                    `telegram_notification` = :telegram_notification,
                    `notification_datetime` = FROM_UNIXTIME(:notification_datetime)
                    WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':email_notification' => $byEmail,
            ':telegram_notification' => $byTelegram,
            ':notification_datetime' => $unixTimestamp,
            ':note_id' => $this->id,
        ]);
        return $result;
    }

    public function unsetNotification()
    {
        if (!$this->id || empty($this->list)) {
            return null;
        }
        if (!$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "UPDATE `note` 
                    SET `email_notification` = NULL,
                    `telegram_notification` = NULL,
                    `notification_datetime` = NULL
                    WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':note_id' => $this->id,
        ]);
        return $result;
    }

    public function setNotifiedBy($field, $status = 1)
    {
        if (!$this->id || empty($this->list)) {
            return null;
        }
        if (!$this->list->listBelongsToUser()) {
            return null;
        }
        $query = "UPDATE `note` 
                    SET `".$field."` = :status
                    WHERE `id` = :note_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':note_id' => $this->id,
            ':status' => $status,
        ]);
        return $result;
    }

}