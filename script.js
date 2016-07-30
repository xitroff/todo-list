/**
 * Created by xitroff on 29.07.16.
 */
var changeNoteStateHandler = function(){
        var noteId = $(this).val(),
            state = $(this).prop('checked') ? 1 : 0,
            li = $(this).parent();

        if (!noteId) {
            alert('An error occurred');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'updateNoteState',
                noteId: noteId,
                state: state
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
                if (state) {
                    $(li).hide(1500);
                }
            }
        });
    },

    updateNote = function(noteId, noteText){
        if (!noteId) {
            alert('An error occurred');
            return;
        }

        if (!noteText) {
            alert('Note text should not be empty');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'updateNote',
                noteId: noteId,
                text: noteText
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
            }
        });
    },

    updateNoteHandler = function(event){
        var noteId = $(this).parent().attr('data-note_id'),
            noteText = $(this).prev().val();

        updateNote(noteId, noteText);
    },

    deleteNoteHandler = function(event){
        var noteId = $(this).parent().attr('data-note_id'),
            li = $(this).parent();

        if (!noteId) {
            alert('An error occurred');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'deleteNote',
                noteId: noteId
            },
            success: function(result,status,xhr){
                if (result) {
                    $(li).remove();
                } else {
                    alert('An error occurred');
                }
            }
        });
    },
    appendNewNote = function(noteId, noteText){
        if (!noteId) {
            return;
        }
        var ul = $('.notes-list'),
            li = '<li data-note_id="'+noteId+'" class="notes-list-item">\
                            <input type="checkbox" class="note-done" value="'+noteId+'" name="noteId" />\
                            <input type="text" value="'+noteText+'" class="note-text" />\
                            <input type="submit" value="Save" class="update-note" />\
                            <input type="submit" value="Delete" class="delete-note" />\
                           </li>';
        $(ul).append(li);
        var latestLi = $('.notes-list-item').last();
        latestLi.find('.note-done').change(changeNoteStateHandler);
        latestLi.find('.note-text').keyup(noteTextKeyUpHandler).focus(noteTextFocusHandler).focusout(noteTextFocusOutHandler);
        latestLi.find('.update-note').click(updateNoteHandler);
        latestLi.find('.delete-note').click(deleteNoteHandler);
        $('.new-note-text').val('');
    },

    addNote = function(noteText){
        if (!noteText) {
            alert('Note text should not be empty');
            return null;
        }

        var noteId = 0;

        $.ajax({
            url: '/',
            method: "POST",
            async: false,
            data: {
                action: 'addNote',
                text: noteText
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
                noteId = result;
            }
        });

        appendNewNote(noteId, noteText);
    },

    addNoteHandler = function(event){
        var noteText = $(this).prev().val();
        addNote(noteText);
    },

    noteTextKeyUpHandler = function(eventData){
        var noteId = $(this).parent().attr('data-note_id'),
            noteText = $(this).val();

        if (eventData.keyCode === enterKey) {
            if (!noteText) {
                alert('Note text should not be empty');
                return;
            }
            if (noteTextInitialValues[noteId] !== noteText) {
                updateNote(noteId, noteText);
            }
            noteTextInitialValues = [];
            $(this).blur();
            $(this).parent().find('.update-note').css({
                'visibility': 'hidden'
            });
        } else if (eventData.keyCode === escapeKey && noteTextInitialValues[noteId] !== undefined) {
            var initialNoteText = noteTextInitialValues[noteId];
            noteTextInitialValues = [];
            $(this).val(initialNoteText).blur();
            $(this).parent().find('.update-note').css({
                'visibility': 'hidden'
            });
        }
    },

    noteTextFocusHandler = function(){
        $(this).parent().find('.update-note').css({
            'visibility': 'visible'
        });
        var noteId = $(this).parent().attr('data-note_id');
        if (noteTextInitialValues[noteId]) {
            return;
        }
        noteTextInitialValues[noteId] = $(this).val();
    },

    noteTextFocusOutHandler = function(eventData){
        var noteId = $(this).parent().attr('data-note_id');
        if ($(eventData.relatedTarget).hasClass('update-note')) {
            var noteText = $(this).val();
            if (noteTextInitialValues[noteId] !== noteText) {
                updateNote(noteId, noteText);
            }
            $(this).parent().find('.update-note').css({
                'visibility': 'hidden'
            });
            return;
        }

        if (!noteTextInitialValues[noteId]) {
            return;
        }
        var initialNoteText = noteTextInitialValues[noteId];
        $(this).val(initialNoteText);
        noteTextInitialValues = [];
        $(this).parent().find('.update-note').css({
            'visibility': 'hidden'
        });
    },

    newNoteTextKeyUpHandler = function(eventData){

        if (eventData.keyCode === enterKey) {
            var noteText = $(this).val();
            addNote(noteText);
            $(this).blur();
        } else if (eventData.keyCode === escapeKey) {
            $(this).val('').blur();
        }
    },

    newNoteTextFocusOutHandler = function(eventData){
        if ($(eventData.relatedTarget).hasClass('add-note')) {
            var noteText = $(this).val();
            addNote(noteText);
        }
        $(this).val('');
    },

    noteTextInitialValues = [];

const enterKey = 13,
    escapeKey = 27;

$(function(){
    $('.note-done').change(changeNoteStateHandler);
    $('.note-text').keyup(noteTextKeyUpHandler).focus(noteTextFocusHandler).focusout(noteTextFocusOutHandler);
    $('.new-note-text').keyup(newNoteTextKeyUpHandler).focusout(newNoteTextFocusOutHandler);
    $('.add-note').click(addNoteHandler);
    $('.update-note').click(updateNoteHandler);
    $('.delete-note').click(deleteNoteHandler);
});