<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$receiverId = (int)($_POST["receiver_id"] ?? 0);
$content = trim($_POST["content"] ?? "");

if ($receiverId <= 0 || $content === "") {
    exit("Invalid message.");
}

$stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iis", $_SESSION["student_id"], $receiverId, $content);
mysqli_stmt_execute($stmt);

header("Location:messages.php?student_id=" . $receiverID);
exit;
