<?php

session_start();
require_once "db_connect.php";


/* =========================================================
   1. CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit;
}


$ownerId = (int)$_SESSION["student_id"];


/* =========================================================
   2. GET REQUEST DATA
   ========================================================= */

$requestId = (int)($_POST["request_id"] ?? 0);

$action = $_POST["action"] ?? "";


if ($requestId <= 0) {

    exit("Invalid exchange request.");

}


if ($action !== "accept" && $action !== "reject") {

    exit("Invalid action.");

}


/* =========================================================
   3. DECIDE NEW STATUS
   ========================================================= */

$newStatus =
    ($action === "accept")
    ? "accepted"
    : "rejected";


/* =========================================================
   4. GET EXCHANGE REQUEST
   ========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT

        er.id AS request_id,

        er.student_id AS requester_id,

        er.status AS request_status,

        er.desired_book_id,

        desired_book.title AS book_title,

        desired_book.student_id AS book_owner_id,

        owner.name AS owner_name,

        requester.name AS requester_name

    FROM exchange_request er

    JOIN books desired_book
        ON desired_book.id = er.desired_book_id

    JOIN students owner
        ON owner.id = desired_book.student_id

    JOIN students requester
        ON requester.id = er.student_id

    WHERE er.id = ?

    LIMIT 1
    "
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $requestId
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$request = mysqli_fetch_assoc($result);


/* =========================================================
   5. CHECK REQUEST EXISTS
   ========================================================= */

if (!$request) {

    exit("Exchange request not found.");

}


/* =========================================================
   6. CHECK WHETHER CURRENT USER IS BOOK OWNER
   ========================================================= */

if (
    (int)$request["book_owner_id"] !== $ownerId
) {

    exit(
        "You are not allowed to process this exchange request."
    );

}


/* =========================================================
   7. CHECK REQUEST IS STILL PENDING
   ========================================================= */

if (
    strtolower($request["request_status"]) !== "pending"
) {

    exit(
        "This exchange request has already been processed."
    );

}


/* =========================================================
   8. START DATABASE TRANSACTION
   ========================================================= */

mysqli_begin_transaction($conn);


try {


    /* =====================================================
       9. UPDATE exchange_request
       ===================================================== */

    $stmt = mysqli_prepare(
        $conn,
        "
        UPDATE exchange_request

        SET status = ?

        WHERE id = ?
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $newStatus,
        $requestId
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not update exchange request."
        );

    }


    /* =====================================================
       10. INSERT exchange_status
       ===================================================== */

    $stmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO exchange_status
        (
            request_id,
            status
        )

        VALUES
        (
            ?,
            ?
        )
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $requestId,
        $newStatus
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not save exchange status."
        );

    }


    /* =====================================================
       11. CREATE NOTIFICATION
       ===================================================== */

    if ($newStatus === "accepted") {

        $notificationMessage =
            "Your exchange request for "
            . $request["book_title"]
            . " has been accepted by "
            . $request["owner_name"]
            . ".";

    } else {

        $notificationMessage =
            "Your exchange request for "
            . $request["book_title"]
            . " has been rejected.";

    }


    $notificationType = "exchange_status";

    $notificationStatus = "unread";


    /* =====================================================
       12. SEND NOTIFICATION TO REQUESTER
       ===================================================== */

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

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "iisss",
        $request["requester_id"],
        $requestId,
        $notificationMessage,
        $notificationType,
        $notificationStatus
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not create notification."
        );

    }


    /* =====================================================
       13. EVERYTHING SUCCESSFUL
       ===================================================== */

    mysqli_commit($conn);


    /* =====================================================
       14. RETURN TO EXCHANGE REQUEST PAGE
       ===================================================== */

    header(
        "Location: exchange_status.php?id="
        . $requestId
        . "&success=1"
    );

    exit;


} catch (Exception $e) {


    /* =====================================================
       15. ROLLBACK IF SOMETHING FAILS
       ===================================================== */

    mysqli_rollback($conn);


    exit(
        "Error processing exchange request: "
        . htmlspecialchars($e->getMessage())
    );

}

?>