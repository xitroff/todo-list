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
    $userId = $_SESSION['userId'];
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
    $userId = $_SESSION['userId'];
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
    $userId = $_SESSION['userId'];
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

function showNotesList()
{
    require('config.php');
    $notes = getNotes();
    if (empty($notes)) {
        return null;
    }
    foreach ($notes as $note) {
        echo '<li data-note_id="'.$note['id'].'" class="notes-list-item">';
            echo '<input type="checkbox" class="note-done" value="'.$note['id'].'" name="noteId"';
            echo $note['done'] ? 'checked="checked"' : '';
            echo '/>';
            echo '<input type="text" value="'.$note['text'].'" class="note-text" />';
            echo ' <input type="submit" value="Save" class="update-note" />';
            echo ' <input type="submit" value="Delete" class="delete-note" />';
        echo '</li>';
    }
}

function addNote($text)
{
    require('config.php');
    $userId = $_SESSION['userId'];
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
    $userId = $_SESSION['userId'];
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

function processPostAction()
{
    if (isset($_POST['action'])) {

        switch ($_POST['action']) {
            case 'updateNote':
                $noteId = $_POST['noteId'];
                $text = $_POST['text'];
                die(updateNote($noteId, $text));
                break;
            case 'deleteNote':
                $noteId = $_POST['noteId'];
                die(deleteNote($noteId));
                break;
            case 'updateNoteState':
                $noteId = $_POST['noteId'];
                $state = $_POST['state'];
                die(updateNoteState($noteId, $state));
                break;
            case 'addNote':
                $text = $_POST['text'];
                die(addNote($text));
                break;
            default:
                die;
        }
    }
}
