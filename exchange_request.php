<?php

session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$currentBookId = (int)($_POST["current_book_id"] ?? 0);
$desiredBookId = (int)($_POST["desired_book_id"] ?? 0);
$exchangeMessage = trim($_POST["exchange_message"] ?? "");

$studentId = (int)$_SESSION["student_id"];


/* Check valid book IDs */

if (
    $currentBookId <= 0 ||
    $desiredBookId <= 0 ||
    $currentBookId === $desiredBookId
) {
    exit("Invalid exchange request.");
}


/* STEP 1: Check current book belongs to logged-in student */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT id, title
    FROM books
    WHERE id = ?
    AND student_id = ?
    AND status = 'available'
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $currentBookId,
    $studentId
);

mysqli_stmt_execute($stmt);

$current = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);


/* STEP 2: Check requested book */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT id, title, student_id
    FROM books
    WHERE id = ?
    AND status = 'available'
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $desiredBookId
);

mysqli_stmt_execute($stmt);

$desired = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);


if (
    !$current ||
    !$desired ||
    (int)$desired["student_id"] === $studentId
) {
    exit("Invalid books selected for exchange.");
}


/* =====================================================
   STEP 3: CHECK DUPLICATE REQUEST
   ===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT id, status
    FROM exchange_request
    WHERE student_id = ?
    AND current_book_id = ?
    AND desired_book_id = ?
    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $studentId,
    $currentBookId,
    $desiredBookId
);

mysqli_stmt_execute($stmt);

$existing = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);


/* If request already exists */

if ($existing) {

    header(
        "Location: exchange_status.php?id="
        . $existing["id"]
        . "&duplicate=1"
    );

    exit;
}


/* =====================================================
   STEP 4: INSERT NEW EXCHANGE REQUEST
   ===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "
    INSERT INTO exchange_request
    (
        student_id,
        current_book_id,
        desired_book_id,
        message,
        status
    )
    VALUES (?, ?, ?, ?, 'pending')
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "iiis",
    $studentId,
    $currentBookId,
    $desiredBookId,
    $exchangeMessage
);

mysqli_stmt_execute($stmt);

$requestId = mysqli_insert_id($conn);


/* =====================================================
   STEP 5: CREATE NOTIFICATION FOR BOOK OWNER
   ===================================================== */

$notificationText =
    $_SESSION["student_name"]
    . " sent an exchange request for your book: "
    . $desired["title"];

$type = "exchange_request";
$status = "pending";


$stmt = mysqli_prepare(
    $conn,
    "
    INSERT INTO notifications
    (
        student_id,
        request_id,
        message,
        type,
        status
    )
    VALUES (?, ?, ?, ?, ?)
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "iisss",
    $desired["student_id"],
    $requestId,
    $notificationText,
    $type,
    $status
);

mysqli_stmt_execute($stmt);


/* =====================================================
   STEP 6: GO BACK TO BOOK DETAILS
   ===================================================== */

header(
    "Location: book_details.php?id="
    . $desiredBookId
    . "&exchange=success"
);

exit;

?>