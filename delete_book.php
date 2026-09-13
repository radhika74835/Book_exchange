<?php

session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION["student_id"];

$bookId = (int)($_GET["id"] ?? 0);

if ($bookId <= 0) {
    exit("Invalid book.");
}

$stmt = mysqli_prepare(
    $conn,
    "
    DELETE FROM books
    WHERE id = ?
    AND student_id = ?
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $bookId,
    $studentId
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: my_books.php?deleted=1"
    );

    exit;

} else {

    echo "Error deleting book: "
        . mysqli_error($conn);

}

?>
