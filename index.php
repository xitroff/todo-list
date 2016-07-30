<?php
session_start();
$_SESSION['userId'] = 1;
require_once('methods.php');
processPostAction();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css" />
    <title>To-Do List</title>
</head>
<body>
<ul class="notes-list">

<?php
showNotesList();
?>

</ul>
<br />

<label for="text">New note: </label>
<input type="text" name="text" class="new-note-text" />
<input type="submit" class="add-note" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="script.js"></script>
</body>
</html>