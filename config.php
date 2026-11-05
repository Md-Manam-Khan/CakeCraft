<?php
session_start();
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "CakeCraft";
function tk($n)
    {
    $s = number_format((float) $n, 2, '.', '');
    if (substr($s, -3) === '.00') 
        {
        $s = substr($s, 0, -3);
    }
    return $s . " TK";
}
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) 
    {
    die("Connection failed: " . $conn->connect_error);
}
?>