<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 2:22
 */
class ToDoList
{
    private $id;
    private $userId = 0;
    private $listName = '';
    private $notes = array();

    private $db;
    
    public function __construct($userId, $listName = null, $id = null)
    {
        $this->db = DB::getInstance();
        if (empty($userId)) {
            return null;
        }
        $this->userId = $userId;
        $listName ? $this->listName = $listName : '';
        if (!$id && $listName) {
            $this->addList();
        } else {
            $this->id = $id;
        }
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }

    public function setListName($listName)
    {
        return $this->listName = $listName;
    }

    public function getListName()
    {
        return $this->listName;
    }
    
    public function setNotes(array $notes)
    {
        $this->notes = $notes;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    private function addList()
    {
        if (empty($this->userId) || empty($this->listName)) {
            return null;
        }
        $query = "INSERT INTO `list` 
                (`name`, `user_id`)
                VALUES (:name, :user_id)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':name' => $this->listName,
            ':user_id' => $this->userId,
        ]);
        if ($result) {
            $this->id = $this->db->lastInsertId();
            return $this;
        } else {
            return null;
        }
    }

    public function listBelongsToUser()
    {
        if (empty($this->userId)) {
            return null;
        }
        $query = "SELECT `user_id` 
              FROM `list`
              WHERE `id` = :list_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':list_id' => $this->id,
        ]);

        if (!$result) {
            return null;
        }
        $userId = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];

        return $userId == $this->userId;
    }

    public function deleteList()
    {
        if (empty($this->userId) || empty($this->id) || !$this->listBelongsToUser()) {
            return null;
        }
        $query = "DELETE FROM `list` 
                  WHERE `id` = :list_id
                  AND `user_id` = :user_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':list_id' => $this->id,
            ':user_id' => $this->userId,
        ]);
        return $result;
    }

    public static function getListByNoteId($noteId)
    {
        if (empty($noteId)) {
            return null;
        }
        $db = DB::getInstance();
        $query = "SELECT `note`.`list_id`, `list`.`name`, `list`.`user_id`
                  FROM `list`
                  JOIN `note`
                  ON `list`.`id` = `note`.`list_id`
                  WHERE `note`.`id` = :note_id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':note_id' => $noteId,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        $listId = $result['list_id'];
        $listName = $result['name'];
        $userId = $result['user_id'];
        if (empty($listId) || empty($listName) || empty($userId)) {
            return null;
        }
        $list = new self($userId, $listName, $listId);
        return $list;
    }

    public function getLists()
    {
        if (empty($this->userId)) {
            return null;
        }
        $query = "SELECT `user_id`, `name`, `id` 
                  FROM `list`
                  WHERE `user_id` = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id' => $this->userId,
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        $lists = array();
        foreach ($result as $listItem) {
            $userId = $listItem['user_id'];
            $listName = $listItem['name'];
            $id = $listItem['id'];
            if (empty($userId) || empty($listName) || empty($id)) {
                continue;
            }
            $lists[] = new self($userId, $listName, $id);
        }
        return $lists;
    }

    public function getListByName()
    {
        if (empty($this->userId) || empty($this->listName)) {
            return null;
        }
        $query = "SELECT `id`
                  FROM `list`
                  WHERE `name` = :list_name
                  AND `user_id` = :user_id 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':list_name' => $this->listName,
            ':user_id' => $this->userId,
        ]);
        if (!$result) {
            return null;
        }
        $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        if (empty($id)) {
            return null;
        }
        $this->id = $id;
        return $this;
    }

}