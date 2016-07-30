<ul class="notes-list">
    
    <?php
        $notes = getNotes();
        if (!empty($notes)) {
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
    ?>

</ul>

<label for="text">New note: </label>
<input type="text" name="text" class="new-note-text" />
<input type="button" class="add-note" value="Add" />