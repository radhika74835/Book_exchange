<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId =
    (int)$_SESSION["student_id"];


$stmt = mysqli_prepare(
    $conn,
    "
    SELECT

        er.id,
        er.status,
        er.created_at,

        desired_book.title AS book_title,

        owner.name AS owner_name

    FROM exchange_request er

    JOIN books desired_book
        ON desired_book.id = er.desired_book_id

    JOIN students owner
        ON owner.id = desired_book.student_id

    WHERE er.student_id = ?

    ORDER BY er.created_at DESC
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $studentId
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>My Exchange Requests</title>

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

</head>

<body class="bg-light">

<div class="container py-5">

<h2>🔄 My Exchange Requests</h2>

<a
href="index.php"
class="btn btn-secondary mb-3">

Home

</a>


<div class="table-responsive">

<table class="table table-bordered bg-white">

<thead>

<tr>

<th>Book</th>
<th>Owner</th>
<th>Status</th>
<th>Requested On</th>
<th>Details</th>

</tr>

</thead>

<tbody>


<?php while (
    $row = mysqli_fetch_assoc($result)
): ?>

<tr>

<td>

<?php
echo htmlspecialchars(
    $row["book_title"]
);
?>

</td>

<td>

<?php
echo htmlspecialchars(
    $row["owner_name"]
);
?>

</td>

<td>

<span class="badge bg-<?php

echo $row["status"] === "accepted"
    ? "success"
    : (
        $row["status"] === "rejected"
        ? "danger"
        : "warning"
    );

?>">

<?php
echo htmlspecialchars(
    ucfirst($row["status"])
);
?>

</span>

</td>

<td>

<?php
echo date(
    "d-m-Y",
    strtotime($row["created_at"])
);
?>

</td>

<td>

<a
href="exchange_status.php?id=<?php
echo (int)$row["id"];
?>"
class="btn btn-sm btn-primary">

View

</a>

</td>

</tr>

<?php endwhile; ?>


</tbody>

</table>

</div>

</div>

</body>
</html>