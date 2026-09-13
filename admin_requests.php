<?php

session_start();
include("db_connect.php");


/* Admin check */

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["is_admin"]) ||
    $_SESSION["is_admin"] != 1
) {
    header("Location: admin_login.php");
    exit();
}


/* Get exchange requests */

$query = mysqli_query(
    $conn,
    "SELECT
        er.id,
        er.student_id,
        er.current_book_id,
        er.desired_book_id,
        er.message,
        er.status,
        er.created_at,

        requester.name AS requester_name,

        current_book.title AS current_book_title,

        desired_book.title AS desired_book_title,

        owner.name AS owner_name

     FROM exchange_request er

     LEFT JOIN students requester
        ON er.student_id = requester.id

     LEFT JOIN books current_book
        ON er.current_book_id = current_book.id

     LEFT JOIN books desired_book
        ON er.desired_book_id = desired_book.id

     LEFT JOIN students owner
        ON desired_book.student_id = owner.id

     ORDER BY er.id DESC"
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

    <title>Exchange Requests - Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            href="admin_dashboard.php"
            class="navbar-brand"
        >
            Admin Dashboard
        </a>

        <a
            href="admin_logout.php"
            class="btn btn-danger btn-sm"
        >
            Logout
        </a>

    </div>

</nav>


<div class="container py-4">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            Exchange Requests
        </h2>

        <a
            href="admin_dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>


    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Requester</th>
                            <th>Current Book</th>
                            <th>Desired Book</th>
                            <th>Requested To</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($query) > 0) {

                        while ($request = mysqli_fetch_assoc($query)) {

                            $status = strtolower(
                                $request["status"]
                            );

                    ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["id"]
                                );
                                ?>
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
                                    $request["current_book_title"] ?? "Unknown"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["desired_book_title"] ?? "None"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["owner_name"] ?? "Unknown"
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["message"] ?? ""
                                );
                                ?>
                            </td>

                            <td>

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
                                colspan="8"
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
