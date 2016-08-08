<?php ob_start() ?>

<div id="accordion">
        <?php
            if (!empty($this->lists)) {
                foreach ($this->lists as $listItem) {
                    $listId = $listItem->getId();
                    $listName = $listItem->getListName();
                    $currentListNotes = $listItem->getNotes();

                    echo '<h3>'.$listName.'</h3>';
                    echo '<div data-list_id="'.$listId.'">';

                        echo '<button type="button" class="delete-list btn btn-default btn-sm">';
                            echo '<span class="glyphicon glyphicon-remove"></span> Remove list';
                        echo '</button>';

                        echo '<ul class="notes-list">';
                        if (!empty($currentListNotes)) {
                            /**
                             * @var $noteItem ToDoNote
                             */
                            foreach ($currentListNotes as $noteItem) {
                                $noteId = $noteItem->getId();
                                $noteText = $noteItem->getText();
                                $noteDone = $noteItem->getDone();
                                $emailNotification = $noteItem->getEmailNotification();
                                $telegramlNotification = $noteItem->getTelegramlNotification();
                                $notificationDatetime = $noteItem->getNotificationDatetime();
                                $notificationDate = $noteItem->getNotificationDate();
                                $notificationTime = $noteItem->getNotificationTime();
                                echo '<li data-note_id="'.$noteId.'"';
                                $listItemClass = 'notes-list-item';
                                $listItemClass .= $noteDone ? ' hidden-note': '';
                                echo ' class="'.$listItemClass.'">';
                                    echo '<input type="checkbox" class="note-done" value="'.$noteId.'"';
                                    echo $noteDone ? ' checked' : '';
                                    echo '/> ';
                                    echo '<input type="text" value="'.$noteText.'" class="note-text" />';
                                    echo ' <input type="button" value="Save" class="update-note" />';
                                    echo ' <input type="button" value="Delete" class="delete-note" />';

                                    echo ' <input type="checkbox"'. ($notificationDatetime ? ' data-initial_value="1" checked ' : ' data-initial_value="0" ') .'class="notify-me" /> <span class="notify-text">Notify</span>';
                                    echo $notificationDatetime ? ' <span class="notify-edit">(edit)</span>' : '';

                                    echo '<div class="date-time-div">';
                                        echo '<input type="text" placeholder="Set date" value="'.$notificationDate.'" class="date-input" />';
                                        echo '<input type="text" placeholder="Set time" value="'.$notificationTime.'" class="time-input" />';

                                        echo '<div class="notify-by-div">';
                                            echo ' <input type="checkbox" '.($emailNotification ? 'checked' : '').' class="notify-by-email" /> Email';
                                            echo ' <input type="checkbox" '.($telegramlNotification ? 'checked' : '').' class="notify-by-telegram" /> Telegram';
                                        echo '</div>';

                                        echo '<button type="button" class="notify-ok btn btn-default btn-sm">';
                                            echo '<span class="glyphicon glyphicon-ok"></span> Ok';
                                        echo '</button>';
                                    echo '</div>';

                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                        echo '<label for="text">New note: </label>';
                        echo '<input type="text" class="new-note-text" />';
                        echo '<input type="button" class="add-note" value="Add" />';

                        echo '<div class="show-done-div">';
                            echo '<input type="checkbox" class="show-done" /> Show done';
                        echo '</div>';
                    echo '</div>';
                }
            }
        ?>
</div>

<?php $content = ob_get_clean() ?>

<?php include 'templates/layout.php' ?>
