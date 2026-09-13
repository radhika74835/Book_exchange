<?php

session_start();
include("db_connect.php");


/* Admin check */

if (!isset($_SESSION["student_id"]) ||
    !isset($_SESSION["is_admin"]) ||
    $_SESSION["is_admin"] != 1) {

    header("Location: admin_login.php");
    exit();
}


/* Get books */

$query = mysqli_query(
    $conn,
    "SELECT
        b.id,
        b.title,
        b.author,
        b.category,
        b.price,
        b.book_condition,
        b.status,
        s.name AS owner_name
     FROM books b
     LEFT JOIN students s
        ON b.student_id = s.id
     ORDER BY b.id DESC"
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

    <title>Books - Admin</title>

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
            All Books
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
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Condition</th>
                            <th>Owner</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($query) > 0) {

                        while ($book = mysqli_fetch_assoc($query)) {

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
                                ₹<?php
                                echo htmlspecialchars(
                                    $book["price"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $book["book_condition"]
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
                                colspan="8"
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

</div>


</body>
</html>