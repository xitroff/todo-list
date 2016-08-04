/**
 * Created by xitroff on 29.07.16.
 */
var changeNoteStateHandler = function(){
        var noteId = $(this).val(),
            state = $(this).prop('checked') ? 1 : 0,
            li = $(this).parent(),
            showDoneState = li.parent().parent().find('.show-done').prop('checked') ? 1 : 0;

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
                    if (!$(li).hasClass('hidden-note')) {
                        $(li).addClass('hidden-note');
                    }
                    if (showDoneState) {
                        $(li).show();
                    }
                } else {
                    if ($(li).hasClass('hidden-note')) {
                        $(li).removeClass('hidden-note');
                    }
                }
            }
        });
    },

    showDoneHandler = function(){
        $(this).parent().find('.hidden-note').toggle();
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

    deleteListHandler = function(event){
        var listId = $(this).parent().attr('data-list_id'),
            parent = $(this).parent(),
            h3 = parent.prev();;

        if (!listId) {
            alert('An error occurred');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'deleteList',
                listId: listId
            },
            success: function(result,status,xhr){
                if (result) {
                    parent.remove();
                    h3.remove();
                } else {
                    alert('An error occurred');
                }
            }
        });
    },

    appendNewNote = function(noteId, noteText, ul){
        if (!noteId) {
            return;
        }
        var li = '<li data-note_id="'+noteId+'" class="notes-list-item">\
                            <input type="checkbox" class="note-done" value="'+noteId+'" name="noteId" /> \
                            <input type="text" value="'+noteText+'" class="note-text" />\
                            <input type="button" value="Save" class="update-note" />\
                            <input type="button" value="Delete" class="delete-note" />\
                           </li>';
        $(ul).append(li);
        var latestLi = $(ul).last();
        latestLi.find('.note-done').change(changeNoteStateHandler);
        latestLi.find('.note-text').keyup(noteTextKeyUpHandler).focus(noteTextFocusHandler).focusout(noteTextFocusOutHandler);
        latestLi.find('.update-note').click(updateNoteHandler);
        latestLi.find('.delete-note').click(deleteNoteHandler);
        $('.new-note-text').val('');
    },

    appendNewList = function(listId, listName){
    if (!listId || !listName) {
        return;
    }

        var h3 = '<h3>'+listName+'</h3>',
            listIdDiv = '<div data-list_id="'+listId+'">\
            \
            <button type="button" class="delete-list btn btn-default btn-sm">\
                <span class="glyphicon glyphicon-remove"></span> Remove list\
            </button>\
            \
                        <ul class="notes-list"></ul>\
                        <label for="text">New note: </label>\
                        <input type="text" class="new-note-text" />\
                        <input type="button" class="add-note" value="Add" />\
                        \
                        <input type="checkbox" class="show-done" /> Show done\
                        \
                    </div>';

        $('#accordion').append(h3, listIdDiv);
        $("#accordion").accordion("refresh");

        $('.new-list-name').val('');

        var latestDiv = $('[data-list_id='+listId+']');

        $(latestDiv).find('.new-note-text').keyup(newNoteTextKeyUpHandler).focusout(newNoteTextFocusOutHandler);
        $(latestDiv).find('.add-note').click(addNoteHandler);

        $(latestDiv).find('.delete-list').click(deleteListHandler);
        $(latestDiv).find('.show-done').change(showDoneHandler);
},

    addNote = function(noteText, listId, ul){
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
                text: noteText,
                listId: listId
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
                noteId = result;
            }
        });

        appendNewNote(noteId, noteText, ul);
    },

    addList = function(listName){
        if (!listName) {
            alert('Note text should not be empty');
            return null;
        }

        var listId = 0;

        $.ajax({
            url: '/',
            method: "POST",
            async: false,
            data: {
                action: 'addList',
                listName: listName
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
                listId = result;
            }
        });

        appendNewList(listId, listName);
    },

    addNoteHandler = function(){
        var noteText = $(this).prev().val(),
            listId = $(this).parent().attr('data-list_id'),
            ul = $(this).parent().find('ul');
        addNote(noteText, listId, ul);
    },

    addListHandler = function(){
        var listName = $(this).prev().val();
        addList(listName);
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

    emailFieldKeyUpHandler = function(eventData){

        if (eventData.keyCode === enterKey) {
            $('.send-password').trigger('click');
        } else if (eventData.keyCode === escapeKey) {
            $(this).val('');
            $('.send-password-form').hide();
            $('.get-password').show();
        }
    },

    passwordFieldKeyUpHandler = function(eventData){

        if (eventData.keyCode === enterKey) {
            $('.login-button').trigger('click');
        } else if (eventData.keyCode === escapeKey) {
            $('.login-field').val('');
            $(this).val('');
            $('.login-form').hide();
            $('.show-login-form-link').show();
        }
    },

    listNameKeyUpHandler = function(eventData){
    var listName = $(this).val();

    if (eventData.keyCode === enterKey) {
        addList(listName);
        $(this).val('').blur();
    } else if (eventData.keyCode === escapeKey) {
        $(this).val('').blur();
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
            var noteText = $(this).val(),
                listId = $(this).parent().attr('data-list_id'),
                ul = $(this).parent().find('ul');
            addNote(noteText, listId, ul);
            $(this).blur();
        } else if (eventData.keyCode === escapeKey) {
            $(this).val('').blur();
        }
    },

    newNoteTextFocusOutHandler = function(eventData){
        if ($(eventData.relatedTarget).hasClass('add-note')) {
            var noteText = $(this).val(),
                listId = $(this).parent().attr('data-list_id'),
                ul = $(this).parent().find('ul');
            addNote(noteText, listId, ul);
        }
        $(this).val('');
    },

    noteTextInitialValues = [],

    getPasswordClickHandler = function(){
        $(this).hide();
        $('.send-password-form').show();
    },

    sendPasswordClickHandler = function(){
        //TODO email validation

        var email = $('.email-field').val();

        if (!email) {
            alert('Email should not be empty.');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'sendPasswordByEmail',
                email: email
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error occurred');
                }
                alert('Password sent. Please login at the right side. Thank you.');
                $('.send-password-form').hide();
            }
        });
    },

    showLoginFormLinkClickHandler = function(){
        $(this).hide();
        $('.login-form').show();
    },

    loginButtonClickHandler = function(){
        var login = $('.login-field').val(),
            password = $('.password-field').val();

        //TODO email validation
        if (!login && !password) {
            alert('Email and should not be empty.');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'checkLoginAndPassword',
                login: login,
                password: password
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('Incorrect login or password');
                    return;
                }
                window.location.replace('/');
            }
        });
    },

    logoutClickHandler = function(){
        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'logout'
            },
            success: function(result,status,xhr){
                if (!result) {
                    alert('An error iccurred.');
                    return;
                }
                window.location.replace('/');
            }
        });
    };

const enterKey = 13,
    escapeKey = 27;

$(function(){
    $('.note-done').change(changeNoteStateHandler);
    $('.note-text').keyup(noteTextKeyUpHandler).focus(noteTextFocusHandler).focusout(noteTextFocusOutHandler);
    $('.new-note-text').keyup(newNoteTextKeyUpHandler).focusout(newNoteTextFocusOutHandler);
    $('.add-note').click(addNoteHandler);
    $('.update-note').click(updateNoteHandler);
    $('.delete-note').click(deleteNoteHandler);

    $('.add-list').click(addListHandler);
    $('.new-list-name').keyup(listNameKeyUpHandler);
    $('.delete-list').click(deleteListHandler);

    $('.show-done').change(showDoneHandler);

    $('.email-field').keyup(emailFieldKeyUpHandler);
    $('.password-field').keyup(passwordFieldKeyUpHandler);

    $('.get-password').click(getPasswordClickHandler);
    $('.send-password').click(sendPasswordClickHandler);
    
    $('.show-login-form-link').click(showLoginFormLinkClickHandler);
    $('.login-button').click(loginButtonClickHandler);

    $('.logout').click(logoutClickHandler);

    $( "#accordion" ).accordion({
        heightStyle: "content",
        autoHeight: false,
        clearStyle: true,
    });
});