<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 1:12
 */
class Application
{
    private $user;
    private $lists = array();

    public function __construct($data, $api = null)
    {
        if ($api) {
            $this->initApiActionsListener($data);
            return;
        }
        $this->user = User::get();
        $this->initActionsListener($data);
        $this->getUserListsWithNotes();
        $this->displayTemplates();
    }
    
    private function getUserListsWithNotes()
    {
        $list = new ToDoList($this->user->getId());
        $lists = $list->getLists();
        if (empty($lists)) {
            return;
        }

        foreach ($lists as $listItem) {
            $note = new ToDoNote($listItem);
            $notes = $note->getNotes();
            /**
             * @var $listItem ToDoList
             */
            $listItem->setNotes($notes);
        }
        $this->lists = $lists;
    }

    private function initActionsListener($postData)
    {
        if (empty($postData['action'])) {
            return;
        }

        switch ($postData['action']) {

            case 'sendPasswordByEmail':
                $email = $postData['email'];
                die($this->user->sendPasswordByEmail($email));

            case 'checkLoginAndPassword':
                $login = $postData['login'];
                $password = $postData['password'];
                $token = User::checkLoginAndPassword($login, $password);
                if (empty($token)) {
                    die;
                }
                $this->user->setToken('');
                die($token);

            case 'addList':
                $listName = $postData['listName'];
                $list = new ToDoList($this->user->getId(), $listName);
                die($list->getId());

            case 'deleteList':
                $listId = $postData['listId'];
                $list = new ToDoList($this->user->getId(), null, $listId);
                die($list->deleteList());

            case 'addNote':
                $text = $postData['text'];
                $listId = $postData['listId'];
                $list = new ToDoList($this->user->getId(), null, $listId);
                $note = new ToDoNote($list, $text);
                die($note->getId());

            case 'updateNote':
                $noteId = $postData['noteId'];
                $text = $postData['text'];
                $list = ToDoList::getListByNoteId($noteId);
                $note = new ToDoNote($list, $text, $noteId);
                die($note->updateNote($text));

            case 'updateNoteState':
                $noteId = $postData['noteId'];
                $state = $postData['state'];
                $list = ToDoList::getListByNoteId($noteId);
                $note = new ToDoNote($list, null, $noteId);
                die($note->updateNoteState($state));

            case 'deleteNote':
                $noteId = $postData['noteId'];
                $list = ToDoList::getListByNoteId($noteId);
                $note = new ToDoNote($list, null, $noteId);
                die($note->deleteNote());

            case 'userHasEmailOrTelegram':
                die($this->user->getEmail() || ($this->user->getTelegramUserId() && $this->user->getTelegramChatId()));

            case 'userHasTelegram':
                die($this->user->getTelegramUserId() && $this->user->getTelegramChatId());

            case 'setNotification':
                $noteId = $postData['noteId'];
                $unixTimestamp = $postData['unixTimestamp'];
                $byEmail = $this->user->getEmail() ? $postData['byEmail'] : 0;
                $byTelegram = $this->user->getTelegramUserId() && $this->user->getTelegramChatId() ? $postData['byTelegram'] : 0;
                $list = ToDoList::getListByNoteId($noteId);
                $note = new ToDoNote($list, null, $noteId);
                die($note->setNotification($unixTimestamp, $byEmail, $byTelegram, $this->user));

            case 'unsetNotification':
                $noteId = $postData['noteId'];
                $list = ToDoList::getListByNoteId($noteId);
                $note = new ToDoNote($list, null, $noteId);
                die($note->unsetNotification());

            default:
                die;
        }
    }

    private function displayTemplates()
    {
        !$this->user->passwordWasSentByEmail() ? require('templates/getPasswordByEmail.html') : '';
        if ($this->user->getLoggedStatus()) {
            require('templates/telegramPromo.html');
            require('templates/logout.html');
        } else {
            require('templates/login.html');
        }
        require('templates/notes.php');
        require('templates/addNewList.html');
    }

    private function initApiActionsListener($requestData)
    {
        if (empty($requestData['action'])) {
            die;
        }

        if (!empty($requestData['token'])) {
            $this->user = User::get($requestData['token']);
        }

        switch ($requestData['action']) {

            case 'usernameExists':
                $username = $requestData['username'];
                die(User::usernameExists($username));

            case 'checkLoginAndPassword':
                $login = $requestData['login'];
                $password = $requestData['password'];
                die(User::checkLoginAndPassword($login, $password));

            case 'getLists':
                $list = new ToDoList($this->user->getId());
                $lists = $list->getLists();
                $listNames = array();
                if (empty($lists)) {
                    die(json_encode($listNames));
                }
                /**
                 * @var $listItem ToDoList
                 */
                foreach ($lists as $listItem) {
                    $listNames[] = $listItem->getListName();
                }
                die(json_encode($listNames));

            case 'getNotesByListName':
                $listName = $requestData['listName'];
                $list = new ToDoList($this->user->getId());
                $list->setListName($listName);
                $list->getListByName();
                $note = new ToDoNote($list);
                $notes = $note->getNotesByListName();
                $noteTexts = array();
                if (empty($notes)) {
                    die(json_encode($noteTexts));
                }
                /**
                 * @var $noteItem ToDoNote
                 */
                foreach ($notes as $noteItem) {
                    $noteTexts[] = $noteItem->getText();
                }
                die(json_encode($noteTexts));

            case 'addList':
                $listName = $requestData['listName'];
                $list = new ToDoList($this->user->getId(), $listName);
                die($list->getId());

            case 'addNote':
                $listName = $requestData['listName'];
                $text = $requestData['text'];

                $list = new ToDoList($this->user->getId());
                $list->setListName($listName);
                $list->getListByName();

                $note = new ToDoNote($list, $text);
                die($note->getId());

            case 'deleteList':
                $listName = $requestData['listName'];
                $list = new ToDoList($this->user->getId());
                $list->setListName($listName);
                $list->getListByName();
                die($list->deleteList());

            case 'deleteNote':
                $listName = $requestData['listName'];
                $noteText = $requestData['noteText'];

                $list = new ToDoList($this->user->getId());
                $list->setListName($listName);
                $list->getListByName();

                $note = new ToDoNote($list);
                $note->setText($noteText);
                $note->getNoteByText();
                die($note->deleteNote());

            case 'setTelegramUserIdAndChatId':
                $telegramUserId = $requestData['telegramUserId'];
                $telegramChatId = $requestData['telegramChatId'];
                die($this->user->setTelegramUserIdAndChatId($telegramUserId, $telegramChatId));

            default:
                die;
        }
    }
}