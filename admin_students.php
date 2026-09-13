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


/* Get students */

$query = mysqli_query(
    $conn,
    "SELECT id, name, email
     FROM students
     WHERE is_admin = 0
     ORDER BY id DESC"
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

    <title>Students - Admin</title>

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
            Registered Students
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
                            <th>Name</th>
                            <th>Email</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if (mysqli_num_rows($query) > 0) {

                        while ($student = mysqli_fetch_assoc($query)) {

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
                                No students registered.
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
