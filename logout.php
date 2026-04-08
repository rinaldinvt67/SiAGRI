<?php
session_start();
session_destroy();

header("Location: Login page.html");
exit;
?>