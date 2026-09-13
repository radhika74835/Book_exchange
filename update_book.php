<?php

session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION["student_id"];

$bookId = (int)($_POST["id"] ?? 0);

$title = trim($_POST["title"] ?? "");

$author = trim($_POST["author"] ?? "");

$category = trim($_POST["category"] ?? "");

$price = (float)($_POST["price"] ?? 0);

$contactNumber = trim($_POST["contact_number"] ?? "");

$bookCondition = trim($_POST["book_condition"] ?? "");


/* Validate */

if (
    $bookId <= 0 ||
    $title === "" ||
    $author === "" ||
    $category === "" ||
    $price < 0 ||
    $contactNumber === "" ||
    $bookCondition === ""
) {
    exit("Please enter valid book details.");
}


/* Update only the logged-in user's book */

$stmt = mysqli_prepare(
    $conn,
    "
    UPDATE books
    SET
        title = ?,
        author = ?,
        category = ?,
        price = ?,
        contact_number = ?,
        book_condition = ?
    WHERE id = ?
    AND student_id = ?
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "sssdssii",
    $title,
    $author,
    $category,
    $price,
    $contactNumber,
    $bookCondition,
    $bookId,
    $studentId
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: my_books.php?updated=1"
    );

    exit;

} else {

    echo "Error updating book: "
        . mysqli_error($conn);

}

?>