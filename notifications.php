<?php

session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION["student_id"];


/* Get notifications of logged-in student */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT *
    FROM notifications
    WHERE student_id = ?
    ORDER BY created_at DESC
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $studentId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Notifications</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>


<body class="bg-light">


<div class="container py-5">


<h2>🔔 Notifications</h2>


<a
href="index.php"
class="btn btn-secondary mb-3">

Home

</a>


<?php if (mysqli_num_rows($result) === 0): ?>

    <div class="alert alert-info">

        No notifications found.

    </div>

<?php endif; ?>


<?php while ($row = mysqli_fetch_assoc($result)): ?>


<div class="card mb-3 shadow-sm">

<div class="card-body">


<!-- Notification type -->

<strong>

<?php
echo htmlspecialchars(
    ucfirst($row["type"])
);
?>

</strong>


<!-- Notification message -->

<p class="mt-2 mb-1">

<?php
echo htmlspecialchars(
    $row["message"]
);
?>

</p>


<!-- Notification date -->

<small class="text-muted">

<?php
echo date(
    "d-m-Y h:i A",
    strtotime($row["created_at"])
);
?>

</small>


<!-- =================================================
     VIEW EXCHANGE REQUEST BUTTON
     ================================================= -->

<?php if (!empty($row["request_id"])): ?>


<div class="mt-3">

<a
href="exchange_status.php?id=<?php
echo (int)$row["request_id"];
?>"
class="btn btn-primary">

View Exchange Request

</a>

</div>


<?php endif; ?>


</div>
</div>


<?php endwhile; ?>


</div>


</body>
</html>