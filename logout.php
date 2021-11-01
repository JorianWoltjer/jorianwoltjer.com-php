<?php
session_start();
session_destroy();
setcookie('PHPSESSID', null, -1, '/'); // unset cookie

header('Location: /'.$_GET["return"]);
?>