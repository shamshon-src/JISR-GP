<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "jisrgp";

// إنشاء اتصال
$mysqli = new mysqli($host, $username, $password, $dbname);

// تحقق من الاتصال
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}
?>
