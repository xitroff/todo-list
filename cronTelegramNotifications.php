<?php
/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 07.08.16
 * Time: 1:23
 */
require_once(__DIR__ . '/autoload.php');
$db = DB::getInstance();

$query = "SELECT `note`.`id`, `text`, `list`.`name`, `telegram_user_id`, `telegram_chat_id`
            FROM `note`
            JOIN `list`
            ON `note`.`list_id` = `list`.`id`
            JOIN `user`
            ON `list`.`user_id` = `user`.`id`
            WHERE `telegram_notification` = 1
            AND `notification_datetime` < NOW()
            AND `telegram_notification_status` IS NULL";

$stmt = $db->prepare($query);
$result = $stmt->execute();

if (!$result) {
    return;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $noteId = $row['id'];
    $noteText = $row['text'];
    $listName = $row['name'];
    $telegramUserId = $row['telegram_user_id'];
    $telegramChatId = $row['telegram_chat_id'];

    $encodedResponse = Helper::sendTelegramBotMessage($telegramUserId, $telegramChatId, $listName, $noteText);
    $response = json_decode($encodedResponse, true);
    $list = ToDoList::getListByNoteId($noteId);
    $note = new ToDoNote($list, null, $noteId);
    if ($response && $response['ok']) {
        $note->setNotifiedBy('telegram_notification_status');
    } else {
        $note->setNotifiedBy('telegram_notification_status', 0);
    }
}