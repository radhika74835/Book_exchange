<?php
session_start();
require_once "db_connect.php";

$message = "";
$activeTab = "login";

/* Open Register tab when ?tab=register is used */
if (isset($_GET["tab"]) && $_GET["tab"] === "register") {
    $activeTab = "register";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $student_name = trim($_POST["student_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    /* =========================
       LOGIN
       ========================= */
    if ($action === "login") {

        $activeTab = "login";

        if ($student_name === "" || $password === "") {

            $message = "Please enter student name and password.";

        } else {

            /*
             * IMPORTANT:
             * is_admin must be selected here.
             */
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id, name, email, password, is_admin
                 FROM students
                 WHERE name = ?
                 LIMIT 1"
            );

            if (!$stmt) {

                $message = "Database error: " . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "s",
                    $student_name
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);

                if ($row && password_verify($password, $row["password"])) {

                    session_regenerate_id(true);

                    $_SESSION["student_id"] = (int)$row["id"];
                    $_SESSION["student_name"] = $row["name"];
                    $_SESSION["student_email"] = $row["email"];

                    /*
                     * Store admin status in the session.
                     */
                    $_SESSION["is_admin"] = (int)$row["is_admin"];

                    mysqli_stmt_close($stmt);

                    header("Location: index.php");
                    exit();

                } else {

                    $message = "Invalid student name or password.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }


    /* =========================
       REGISTER
       ========================= */
    if ($action === "register") {

        $activeTab = "register";

        if ($student_name === "" || $email === "" || $password === "") {

            $message = "Please fill all registration fields.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";

        } elseif (strlen($password) < 4) {

            $message = "Password must contain at least 4 characters.";

        } else {

            /* Check whether name or email already exists */
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM students
                 WHERE name = ? OR email = ?
                 LIMIT 1"
            );

            if (!$stmt) {

                $message = "Database error: " . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ss",
                    $student_name,
                    $email
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {

                    $message = "Student name or email already exists.";

                    mysqli_stmt_close($stmt);

                } else {

                    mysqli_stmt_close($stmt);

                    /* Encrypt password */
                    $hashed = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    /*
                     * New users are normal students.
                     * Therefore, is_admin is set to 0.
                     */
                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO students
                        (name, email, password, is_admin)
                        VALUES (?, ?, ?, 0)"
                    );

                    if (!$stmt) {

                        $message = "Database error: " . mysqli_error($conn);

                    } else {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "sss",
                            $student_name,
                            $email,
                            $hashed
                        );

                        if (mysqli_stmt_execute($stmt)) {

                            $newStudentId = mysqli_insert_id($conn);

                            session_regenerate_id(true);

                            $_SESSION["student_id"] = (int)$newStudentId;
                            $_SESSION["student_name"] = $student_name;
                            $_SESSION["student_email"] = $email;

                            /*
                             * Newly registered students are not admins.
                             */
                            $_SESSION["is_admin"] = 0;

                            mysqli_stmt_close($stmt);

                            header("Location: index.php");
                            exit();

                        } else {

                            $message = "Registration failed: " .
                                       mysqli_stmt_error($stmt);

                            mysqli_stmt_close($stmt);
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login / Register - Smart Online Book Exchange</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    >

</head>

<body class="bg-light">

<div class="container py-5" style="max-width:650px;">

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <h2 class="text-center mb-4">
                📚 Smart Online Book Exchange
            </h2>

            <?php if ($message !== ""): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <!-- LOGIN / REGISTER TABS -->

            <ul class="nav nav-tabs mb-4">

                <li class="nav-item">

                    <button
                        class="nav-link <?php echo $activeTab === "login" ? "active" : ""; ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#login"
                        type="button"
                    >
                        Login
                    </button>

                </li>

                <li class="nav-item">

                    <button
                        class="nav-link <?php echo $activeTab === "register" ? "active" : ""; ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#register"
                        type="button"
                    >
                        Register
                    </button>

                </li>

            </ul>

            <div class="tab-content">

                <!-- ================= LOGIN ================= -->

                <div
                    class="tab-pane fade <?php echo $activeTab === "login" ? "show active" : ""; ?>"
                    id="login"
                >

                    <form method="post">

                        <input
                            type="hidden"
                            name="action"
                            value="login"
                        >

                        <label class="form-label">
                            Student Name
                        </label>

                        <input
                            type="text"
                            name="student_name"
                            class="form-control mb-3"
                            placeholder="Enter your student name"
                            required
                        >

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control mb-3"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>

                    </form>

                </div>

                <!-- ================= REGISTER ================= -->

                <div
                    class="tab-pane fade <?php echo $activeTab === "register" ? "show active" : ""; ?>"
                    id="register"
                >

                    <form method="post">

                        <input
                            type="hidden"
                            name="action"
                            value="register"
                        >

                        <label class="form-label">
                            Student Name
                        </label>

                        <input
                            type="text"
                            name="student_name"
                            class="form-control mb-3"
                            placeholder="Enter student name"
                            required
                        >

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control mb-3"
                            placeholder="Enter email"
                            required
                        >

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control mb-3"
                            placeholder="Create password"
                            required
                        >

                        <button
                            type="submit"
                            class="btn btn-success w-100"
                        >
                            Register
                        </button>

                    </form>

                </div>

            </div>

            <a
                href="index.php"
                class="btn btn-link mt-3"
            >
                ← Back to Home
            </a>

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>