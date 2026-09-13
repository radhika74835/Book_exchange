<?php
session_start();
include("db_connect.php");

/* --------------------------------
   ADMIN ACCESS CHECK
--------------------------------- */

if (!isset($_SESSION["student_id"]) ||
    !isset($_SESSION["is_admin"]) ||
    $_SESSION["is_admin"] != 1) {

    header("Location: admin_login.php");
    exit();
}


/* --------------------------------
   GET ADMIN NAME
--------------------------------- */

$adminName = $_SESSION["student_name"];


/* --------------------------------
   TOTAL STUDENTS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE is_admin = 0"
);

$row = mysqli_fetch_assoc($result);
$totalStudents = $row["total"];


/* --------------------------------
   TOTAL BOOKS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM books"
);

$row = mysqli_fetch_assoc($result);
$totalBooks = $row["total"];


/* --------------------------------
   AVAILABLE BOOKS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM books
     WHERE status = 'available'"
);

$row = mysqli_fetch_assoc($result);
$availableBooks = $row["total"];


/* --------------------------------
   TOTAL EXCHANGE REQUESTS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM exchange_request"
);

$row = mysqli_fetch_assoc($result);
$totalRequests = $row["total"];


/* --------------------------------
   PENDING REQUESTS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM exchange_request
     WHERE status = 'pending'"
);

$row = mysqli_fetch_assoc($result);
$pendingRequests = $row["total"];


/* --------------------------------
   ACCEPTED REQUESTS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM exchange_request
     WHERE status = 'accepted'"
);

$row = mysqli_fetch_assoc($result);
$acceptedRequests = $row["total"];


/* --------------------------------
   REJECTED REQUESTS
--------------------------------- */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM exchange_request
     WHERE status = 'rejected'"
);

$row = mysqli_fetch_assoc($result);
$rejectedRequests = $row["total"];


/* --------------------------------
   RECENT STUDENTS
--------------------------------- */

$studentsQuery = mysqli_query(
    $conn,
    "SELECT id, name, email
     FROM students
     WHERE is_admin = 0
     ORDER BY id DESC
     LIMIT 5"
);


/* --------------------------------
   RECENT BOOKS
--------------------------------- */

$booksQuery = mysqli_query(
    $conn,
    "SELECT
        b.id,
        b.title,
        b.author,
        b.category,
        b.status,
        s.name AS owner_name
     FROM books b
     LEFT JOIN students s
        ON b.student_id = s.id
     ORDER BY b.id DESC
     LIMIT 5"
);


/* --------------------------------
   RECENT EXCHANGE REQUESTS
--------------------------------- */

$requestsQuery = mysqli_query(
    $conn,
    "SELECT
        er.id,
        er.status,
        er.created_at,
        requester.name AS requester_name,
        b.title AS book_title
     FROM exchange_request er

     LEFT JOIN students requester
        ON er.student_id = requester.id

     LEFT JOIN books b
        ON er.current_book_id = b.id

     ORDER BY er.id DESC

     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - Smart Online Book Exchange</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<!-- ============================
     NAVBAR
============================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="admin_dashboard.php"
        >
            Admin Dashboard
        </a>

        <div class="d-flex align-items-center">

            <span class="text-white me-3">
                Welcome,
                <?php echo htmlspecialchars($adminName); ?>
            </span>

            <a
                href="admin_logout.php"
                class="btn btn-danger btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- ============================
     MAIN CONTAINER
============================= -->

<div class="container py-4">


    <div class="mb-4">

        <h2>
            Admin Dashboard
        </h2>

        <p class="text-muted">
            Manage and monitor the Smart Online Book Exchange system.
        </p>

    </div>


    <!-- ============================
         STATISTICS
    ============================= -->

    <div class="row g-4 mb-4">


        <!-- STUDENTS -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Total Students
                    </h6>

                    <h2>
                        <?php echo $totalStudents; ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- BOOKS -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Total Books
                    </h6>

                    <h2>
                        <?php echo $totalBooks; ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- AVAILABLE -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Available Books
                    </h6>

                    <h2>
                        <?php echo $availableBooks; ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- REQUESTS -->

        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Exchange Requests
                    </h6>

                    <h2>
                        <?php echo $totalRequests; ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================
         REQUEST STATISTICS
    ============================= -->

    <div class="row g-4 mb-5">


        <div class="col-md-4">

            <div class="card border-warning shadow-sm">

                <div class="card-body">

                    <h6>
                        Pending Requests
                    </h6>

                    <h3>
                        <?php echo $pendingRequests; ?>
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-success shadow-sm">

                <div class="card-body">

                    <h6>
                        Accepted Requests
                    </h6>

                    <h3>
                        <?php echo $acceptedRequests; ?>
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card border-danger shadow-sm">

                <div class="card-body">

                    <h6>
                        Rejected Requests
                    </h6>

                    <h3>
                        <?php echo $rejectedRequests; ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================
         QUICK ACTIONS
    ============================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h4 class="mb-3">
                Admin Management
            </h4>

            <a
                href="admin_students.php"
                class="btn btn-primary me-2"
            >
                View Students
            </a>

            <a
                href="admin_books.php"
                class="btn btn-success me-2"
            >
                View Books
            </a>

            <a
                href="admin_requests.php"
                class="btn btn-warning"
            >
                View Exchange Requests
            </a>

        </div>

    </div>


    <!-- ============================
         RECENT STUDENTS
    ============================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Recent Students
            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($studentsQuery) > 0) {

                        while ($student = mysqli_fetch_assoc($studentsQuery)) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $student["id"]; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student["name"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student["email"]
                                );
                                ?>
                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="3"
                                class="text-center"
                            >
                                No students found.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- ============================
         RECENT BOOKS
    ============================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Recent Books
            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Book</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Owner</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($booksQuery) > 0) {

                        while ($book = mysqli_fetch_assoc($booksQuery)) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $book["id"]; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $book["title"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $book["author"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $book["category"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $book["owner_name"] ?? "Unknown"
                                );
                                ?>
                            </td>

                            <td>

                                <?php if ($book["status"] == "available") { ?>

                                    <span class="badge bg-success">
                                        Available
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-secondary">
                                        <?php
                                        echo htmlspecialchars(
                                            $book["status"]
                                        );
                                        ?>
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center"
                            >
                                No books found.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- ============================
         RECENT REQUESTS
    ============================= -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                Recent Exchange Requests
            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Requester</th>
                            <th>Book</th>
                            <th>Status</th>
                            <th>Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($requestsQuery) > 0) {

                        while ($request = mysqli_fetch_assoc($requestsQuery)) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $request["id"]; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["requester_name"] ?? "Unknown"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["book_title"] ?? "Unknown"
                                );
                                ?>
                            </td>

                            <td>

                                <?php
                                $status = strtolower(
                                    $request["status"]
                                );
                                ?>

                                <?php if ($status == "pending") { ?>

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                <?php } elseif ($status == "accepted") { ?>

                                    <span class="badge bg-success">
                                        Accepted
                                    </span>

                                <?php } elseif ($status == "rejected") { ?>

                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-secondary">
                                        <?php
                                        echo htmlspecialchars(
                                            $request["status"]
                                        );
                                        ?>
                                    </span>

                                <?php } ?>

                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["created_at"]
                                );
                                ?>
                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center"
                            >
                                No exchange requests found.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


</body>
</html>