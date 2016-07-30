<?php
//session_start();
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

<?php

selectAndDisplayTemplates();

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="script.js"></script>
</body>
</html>