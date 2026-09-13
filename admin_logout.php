<?php

session_start();

unset($_SESSION["student_id"]);
unset($_SESSION["student_name"]);
unset($_SESSION["is_admin"]);

session_destroy();

header("Location: admin_login.php");
exit();

?>