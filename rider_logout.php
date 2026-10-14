<?php
session_start();
unset($_SESSION['rider_id']);
unset($_SESSION['rider_name']);
session_destroy();
header("Location: rider_login.php");
exit;
?>