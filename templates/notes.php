<div id="accordion">
        <?php
            $notes = getNotes();
            if (!empty($notes)) {
                foreach ($notes as $listId => $listItem) {
                    $listName = $listItem['listName'];
                    $currentListNotes = $listItem['notes'];

                    echo '<h3>'.$listName.'</h3>';
                    echo '<div data-list_id="'.$listId.'">';

                        echo '<button type="button" class="delete-list btn btn-default btn-sm">';
                            echo '<span class="glyphicon glyphicon-remove"></span> Remove list';
                        echo '</button>';

                        echo '<ul class="notes-list">';
                        if (!empty($currentListNotes)) {
                            foreach ($currentListNotes as $noteItem) {
                                $noteId = $noteItem['id'];
                                $noteText = $noteItem['text'];
                                $noteDone = $noteItem['done'];
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
                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                        echo '<label for="text">New note: </label>';
                        echo '<input type="text" class="new-note-text" />';
                        echo '<input type="button" class="add-note" value="Add" />';

                        echo '<input type="checkbox" class="show-done" /> Show done';
                    echo '</div>';
                }
            }
        ?>
</div>