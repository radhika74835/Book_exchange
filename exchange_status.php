<?php

session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$requestId = (int)($_GET["id"] ?? 0);
$studentId = (int)$_SESSION["student_id"];

if ($requestId <= 0) {
    exit("Invalid exchange request.");
}


/*
    Get exchange request.

    The logged-in student must be either:
    1. Requester
    OR
    2. Owner of the requested book
*/

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        er.id AS request_id,
        er.student_id AS requester_id,
        er.current_book_id,
        er.desired_book_id,
        er.message,
        er.status,
        er.created_at,

        requester.name AS requester_name,

        current_book.title AS current_book_title,

        desired_book.title AS desired_book_title,
        desired_book.student_id AS owner_id,

        owner.name AS owner_name

    FROM exchange_request er

    LEFT JOIN students requester
        ON requester.id = er.student_id

    LEFT JOIN books current_book
        ON current_book.id = er.current_book_id

    LEFT JOIN books desired_book
        ON desired_book.id = er.desired_book_id

    LEFT JOIN students owner
        ON owner.id = desired_book.student_id

    WHERE er.id = ?

    AND (
        er.student_id = ?
        OR desired_book.student_id = ?
    )

    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $requestId,
    $studentId,
    $studentId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$request = mysqli_fetch_assoc($result);


if (!$request) {

    exit(
        "Exchange request not found or you are not authorized to view it."
    );

}


/*
    Check whether current user is owner
*/

$isOwner =
    ((int)$request["owner_id"] === $studentId);


/*
    Display status safely
*/

$status = strtolower(
    $request["status"] ?? "pending"
);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Exchange Request</title>

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
>

</head>


<body class="bg-light">


<div
class="container py-5"
style="max-width:700px;"
>


<a
href="notifications.php"
class="btn btn-secondary mb-3"
>

← Back to Notifications

</a>


<div class="card shadow">


<div class="card-body">


<h2>🔄 Exchange Request</h2>

<hr>


<!-- REQUESTER -->

<p>

<b>Requester:</b>

<?php

echo htmlspecialchars(
    $request["requester_name"] ?? "Unknown"
);

?>

</p>


<!-- REQUESTER'S BOOK -->

<p>

<b>Requester Book:</b>

<?php

echo htmlspecialchars(
    $request["current_book_title"] ?? "Book unavailable"
);

?>

</p>


<!-- REQUESTED BOOK -->

<p>

<b>Book Requested:</b>

<?php

echo htmlspecialchars(
    $request["desired_book_title"] ?? "Book unavailable"
);

?>

</p>


<!-- DATE -->

<p>

<b>Requested on:</b>

<?php

echo date(
    "d-m-Y h:i A",
    strtotime($request["created_at"])
);

?>

</p>


<!-- STATUS -->

<p>

<b>Status:</b>


<?php

if ($status === "accepted") {

    $badgeClass = "success";

} elseif ($status === "rejected") {

    $badgeClass = "danger";

} else {

    $badgeClass = "warning";

}

?>


<span
class="badge bg-<?php echo $badgeClass; ?>"
>

<?php

echo htmlspecialchars(
    ucfirst($status)
);

?>

</span>

</p>


<!-- REQUEST MESSAGE -->

<?php if (!empty($request["message"])): ?>

<p>

<b>Message:</b>

<br>

<?php

echo nl2br(
    htmlspecialchars(
        $request["message"]
    )
);

?>

</p>

<?php endif; ?>


<!-- ACCEPT / REJECT -->

<?php

if (
    $isOwner &&
    $status === "pending"
):

?>

<hr>


<form
method="post"
action="process_exchange.php"
>


<input
type="hidden"
name="request_id"
value="<?php echo (int)$request["request_id"]; ?>"
>


<button
type="submit"
name="action"
value="accept"
class="btn btn-success"
>

Accept

</button>


<button
type="submit"
name="action"
value="reject"
class="btn btn-danger"
>

Reject

</button>


</form>


<?php endif; ?>


<!-- ACCEPTED -->

<?php

if ($status === "accepted"):

?>

<hr>


<div class="alert alert-success">

<h5>
✅ Exchange Accepted
</h5>


<?php

if ($studentId == $request["owner_id"]):

?>

<p>
You can now message the requester.
</p>


<a
href="messages.php?student_id=<?php
echo (int)$request["requester_id"];
?>"
class="btn btn-primary"
>

💬 Message Requester

</a>


<?php else: ?>


<p>
You can now message the book owner.
</p>


<a
href="messages.php?student_id=<?php
echo (int)$request["owner_id"];
?>"
class="btn btn-primary"
>

💬 Message Owner

</a>


<?php endif; ?>


</div>


<?php endif; ?>


<!-- REJECTED -->

<?php

if ($status === "rejected"):

?>

<hr>


<div class="alert alert-danger">

❌ This exchange request was rejected.

</div>


<?php endif; ?>


</div>

</div>

</div>


</body>

</html>