<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 07.08.16
 * Time: 3:50
 */
require_once(__DIR__ . '/autoload.php');
$db = DB::getInstance();

$query = "SELECT `email`, `note`.`id`, `text`, `list`.`name`
            FROM `note`
            JOIN `list`
            ON `note`.`list_id` = `list`.`id`
            JOIN `user`
            ON `list`.`user_id` = `user`.`id`
            WHERE `email_notification` = 1
            AND `notification_datetime` < NOW()
            AND `email_notification_status` IS NULL";

$stmt = $db->prepare($query);
$result = $stmt->execute();

if (!$result) {
    return;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $noteId = $row['id'];
    $noteText = $row['text'];
    $listName = $row['name'];
    $email = $row['email'];

    $text = 'List: '.$listName.', Text: '.$noteText;

    $result = Mailer::sendEmail($email, 'TODO.hitrov.com Notification', '<h3>Reminder</h3>', $text);

    $list = ToDoList::getListByNoteId($noteId);
    $note = new ToDoNote($list, null, $noteId);
    $note->setNotifiedBy('email_notification_status', $result ? 1 : 0);
}