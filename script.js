/**
 * Created by xitroff on 29.07.16.
 */
var changeNoteStateHandler = function(){
        var noteId = $(this).val(),
            state = $(this).prop('checked') ? 1 : 0,
            li = $(this).parent(),
            showDoneState = li.parent().parent().find('.show-done').prop('checked') ? 1 : 0;

        if (!noteId) {
            $.notify(anErrorOccurred, {type:"danger"});
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
            success: function(result){
                if (!result) {
                    errorNotification("Error when updating note state!");
                    return;
                }
                successNotification("Note state updated.");
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

    notifyEditClickHandler = function(){
        $(this).parent().find('.date-time-div').toggle();
    },

    notifyByTelegramHandler = function(){

        var checkbox = $(this);

        if (checkbox.prop('checked')) {

            $.ajax({
                url: '/',
                method: "POST",
                data: {
                    action: 'userHasTelegram'
                },
                success: function(result){
                    if (!result) {
                        warningNotification('Please first authorize in Telegram Bot\'s chat');
                        checkbox.prop( "checked", false );
                    }
                }
            });
        }
    },

    notifyMeHandler = function(){
        var checkbox = $(this),
            noteId = checkbox.parent().attr('data-note_id'),
            dateTimeDiv = checkbox.parent().find('.date-time-div'),
            initialValue = checkbox.attr('data-initial_value');

        if (initialValue == '1' && !checkbox.prop('checked')) {
            unsetNotification(noteId);
            dateTimeDiv.parent().find('.notify-edit').remove();
            if ($(dateTimeDiv).is(":visible")) {
                dateTimeDiv.toggle();
            }
            return;
        }

        if (checkbox.prop('checked')) {

            $.ajax({
                url: '/',
                method: "POST",
                data: {
                    action: 'userHasEmailOrTelegram'
                },
                success: function(result){
                    if (!result) {
                        warningNotification('Please first login with your email and (optional) authorize in Telegram Bot\'s chat');
                        checkbox.prop( "checked", false );
                        if (dateTimeDiv.css('display') !== 'none') {
                            dateTimeDiv.toggle();
                        }
                        return;
                    }
                    dateTimeDiv.find('.time-input').clockpicker({
                        autoclose: true,
                        'default': 'now'
                    });
                    dateTimeDiv.find('.date-input').datepicker();
                }
            });
        }

        dateTimeDiv.toggle();
    },

    showDoneHandler = function(){
        $(this).parent().parent().find('.hidden-note').toggle();
    },

    updateNote = function(noteId, noteText){

        if (!noteId) {
            $.notify(anErrorOccurred, {type:"danger"});
            return;
        }

        if (!noteText) {
            warningNotification('Note text should not be empty');
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
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                    return;
                }
                successNotification('Note updated.');
            }
        });
    },

    updateNoteHandler = function(){
        var noteId = $(this).parent().attr('data-note_id'),
            noteText = $(this).prev().val();

        updateNote(noteId, noteText);
    },

    deleteNoteHandler = function(){
        var noteId = $(this).parent().attr('data-note_id'),
            li = $(this).parent();

        if (!noteId) {
            $.notify(anErrorOccurred, {type:"danger"});
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'deleteNote',
                noteId: noteId
            },
            success: function(result){
                if (result) {
                    successNotification('Note deleted.');
                    $(li).remove();
                } else {
                    errorNotification(anErrorOccurred);
                }
            }
        });
    },

    deleteListHandler = function(){
        var listId = $(this).parent().attr('data-list_id'),
            parent = $(this).parent(),
            h3 = parent.prev();

        if (!listId) {
            $.notify(anErrorOccurred, {type:"danger"});
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'deleteList',
                listId: listId
            },
            success: function(result){
                if (result) {
                    successNotification('List deleted.');
                    parent.remove();
                    h3.remove();
                } else {
                    errorNotification(anErrorOccurred);
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
                        \
                        <input type="checkbox" data-initial_value="0" class="notify-me" /> <span class="notify-text">Notify</span>\
                        \
                        \
                        <div class="date-time-div">\
                            <input type="text" placeholder="Set date"  class="date-input" />\
                            <input type="text" placeholder="Set time" class="time-input" />\
                            \
                            <div class="notify-by-div">\
                                <input type="checkbox" checked class="notify-by-email" /> Email\
                                <input type="checkbox" class="notify-by-telegram" /> Telegram\
                            </div>\
                            \
                            <button type="button" class="notify-ok btn btn-default btn-sm">\
                                <span class="glyphicon glyphicon-ok"></span> Ok\
                            </button>\
                        </div>\
                   </li>';
        $(ul).append(li);
        var latestLi = $(ul).find('.notes-list-item').last();
        latestLi.find('.note-done').change(changeNoteStateHandler);
        latestLi.find('.note-text').keyup(noteTextKeyUpHandler).focus(noteTextFocusHandler).focusout(noteTextFocusOutHandler);
        latestLi.find('.update-note').click(updateNoteHandler);
        latestLi.find('.delete-note').click(deleteNoteHandler);

        latestLi.find('.notify-me').change(notifyMeHandler);
        latestLi.find('.notify-edit').click(notifyEditClickHandler);
        latestLi.find('.notify-ok').click(notifyOkHandler);

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
                        <div class="show-done-div">\
                            <input type="checkbox" class="show-done" /> Show done\
                        </div>\
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
            warningNotification('Note text should not be empty');
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
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                }
                successNotification('Note has been created.');
                noteId = result;
            }
        });

        appendNewNote(noteId, noteText, ul);
    },

    addList = function(listName){

        if (!listName) {
            warningNotification('List name should not be empty');
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
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                }
                successNotification('List has been created.');
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

    notifyOkHandler = function(){

        var dateTimeDiv = $(this).parent(),
            li = dateTimeDiv.parent(),
            notifyDate = dateTimeDiv.find('.date-input').val(),
            notifyTime = dateTimeDiv.find('.time-input').val(),
            parsedDate = Date.parse(notifyDate);

        if (!parsedDate && !notifyTime) {
            warningNotification('Please set the date and time.');
            return;
        }

        if (!parsedDate) {
            warningNotification('Please set the date.');
            return;
        }

        if (!notifyTime) {
            warningNotification('Please set the time.');
            return;
        }

        var unixTimestamp = Date.parse(notifyDate + ' ' + notifyTime),
            noteId = dateTimeDiv.parent().attr('data-note_id'),
            byEmail = dateTimeDiv.find('.notify-by-email').prop('checked') ? 1 : 0,
            byTelegram = dateTimeDiv.find('.notify-by-telegram').prop('checked') ? 1 : 0;


        if (!unixTimestamp || !noteId) {
            $.notify(anErrorOccurred, {type:"danger"});
            return;
        }

        if (unixTimestamp < Date.parse(new Date)) {
            warningNotification('Please set the date and time in the future.')
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'setNotification',
                noteId: noteId,
                unixTimestamp: unixTimestamp / 1000,
                byEmail: byEmail,
                byTelegram: byTelegram
            },
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                    return;
                }
                successNotification('Notification has been set.');
                dateTimeDiv.parent().find('.notify-text').after(' <span class="notify-edit">(edit)</span>');
                dateTimeDiv.toggle();

                li.find('.notify-me').attr('data-initial_value', 1);
                li.find('.notify-edit').click(notifyEditClickHandler);
            }
        });

    },

    unsetNotification = function(noteId){

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'unsetNotification',
                noteId: noteId
            },
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                    return;
                }
                successNotification('Notification removed.');
            }
        });

    },

    noteTextKeyUpHandler = function(eventData){
        var noteId = $(this).parent().attr('data-note_id'),
            noteText = $(this).val();

        if (eventData.keyCode === enterKey) {
            if (!noteText) {
                warningNotification('Note text should not be empty');
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

    loginFieldKeyUpHandler = function(eventData){

        if (eventData.keyCode === escapeKey) {
            $(this).val('');
            $('.password-field').val('');
            $('.login-form').hide();
            $('.show-login-form-link').show();
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
            warningNotification('Email should not be empty.');
            return;
        }

        $.ajax({
            url: '/',
            method: "POST",
            data: {
                action: 'sendPasswordByEmail',
                email: email
            },
            success: function(result){
                if (!result) {
                    errorNotification(anErrorOccurred);
                    return;
                }
                successNotification('Password sent. Please login at the right side. Thank you.');
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
            warningNotification('Email and password should not be empty.');
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
            success: function(result){
                if (!result) {
                    errorNotification('Incorrect login or password');
                    return;
                }
                Cookies.set('token', result, { expires: 7 });
                Cookies.set('logged', 1, { expires: 7 });
                successNotification('Authorized, please wait...');
                setTimeout(function(){
                    window.location.replace('/');
                }, 1500);
            }
        });
    },

    logoutClickHandler = function(){
        Cookies.set('token', '');
        Cookies.set('logged', '');
        successNotification('Logging out, please wait...');
        setTimeout(function(){
            window.location.replace('/');
        }, 1500);
    },

    errorNotification = function(msg){return $.notify(msg, {type:"danger"});},
    warningNotification = function(msg){return $.notify(msg, {type:"warning"});},
    successNotification = function(msg){return $.notify(msg, {type:"success"});};

const enterKey = 13,
    escapeKey = 27,
    anErrorOccurred = 'An error occurred';

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
    $('.login-field').keyup(loginFieldKeyUpHandler);
    $('.password-field').keyup(passwordFieldKeyUpHandler);

    $('.get-password').click(getPasswordClickHandler);
    $('.send-password').click(sendPasswordClickHandler);
    
    $('.show-login-form-link').click(showLoginFormLinkClickHandler);
    $('.login-button').click(loginButtonClickHandler);

    $('.logout').click(logoutClickHandler);

    $('.notify-me').change(notifyMeHandler);
    $('.notify-edit').click(notifyEditClickHandler);
    $('.notify-ok').click(notifyOkHandler);

    $('.notify-by-telegram').change(notifyByTelegramHandler);

    $( "#accordion" ).accordion({
        heightStyle: "content",
        autoHeight: false,
        clearStyle: true
    });
});