<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION["student_id"];
$stmt = mysqli_prepare($conn, "SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
<h2>🔔 Notifications</h2>
<a href="index.php" class="btn btn-secondary mb-3">Home</a>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<div class="alert alert-info">
<strong><?php echo htmlspecialchars(ucfirst($row["status"])); ?></strong><br>
<?php echo htmlspecialchars($row["message"]); ?><br>
<small><?php echo htmlspecialchars($row["created_at"]); ?></small>
</div>
<?php endwhile; ?>
</div>
</body>
</html>