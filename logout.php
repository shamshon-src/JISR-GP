<?php
session_start();
session_unset();
session_destroy();
setcookie('user_id', '', time() - 3600, '/');
setcookie('first_name', '', time() - 3600, '/');
setcookie('role_id', '', time() - 3600, '/');
header("Location: login.php");
exit();
?>
