<?php

session_start();

require_once "db_connect.php";


/* ---------------------------------------
   CHECK LOGIN
--------------------------------------- */

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit;

}


$studentId = (int)$_SESSION["student_id"];


/* ---------------------------------------
   GET BOOK ID
--------------------------------------- */

$bookId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;


if ($bookId <= 0) {

    exit("Invalid book ID.");

}


/* ---------------------------------------
   GET BOOK DETAILS
--------------------------------------- */

$sql = "
    SELECT *
    FROM books
    WHERE id = ?
    AND student_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $bookId,
    $studentId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$book = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ---------------------------------------
   CHECK BOOK
--------------------------------------- */

if (!$book) {

    exit("Book not found or you do not have permission to edit this book.");

}


/* ---------------------------------------
   VARIABLES
--------------------------------------- */

$message = "";
$messageType = "";


/* ---------------------------------------
   UPDATE BOOK
--------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* ---------------------------------------
       GET FORM VALUES
    --------------------------------------- */

    $title = trim($_POST["title"] ?? "");

    $author = trim($_POST["author"] ?? "");

    $category = trim($_POST["category"] ?? "");

    $price = trim($_POST["price"] ?? "");

    $contactNumber = trim($_POST["contact_number"] ?? "");

    $bookCondition = trim($_POST["book_condition"] ?? "");


    /* ---------------------------------------
       CHECK REQUIRED FIELDS
    --------------------------------------- */

    if (
        $title === "" ||
        $author === "" ||
        $category === "" ||
        $price === "" ||
        $contactNumber === "" ||
        $bookCondition === ""
    ) {

        $message = "Please fill all the required fields.";

        $messageType = "danger";

    }

    else {


        /* ---------------------------------------
           ALLOWED CATEGORIES
        --------------------------------------- */

        $allowedCategories = [

            "Programming",
            "IOT",
            "Engineering",
            "Mathematics",

            "Physics",
            "Medical",
            "Chemistry",
            "Biology",

            "Art",
            "Science",

            "Competitive exams",

            "Novels",
            "General Reading"

        ];


        /* ---------------------------------------
           CHECK CATEGORY
        --------------------------------------- */

        if (!in_array($category, $allowedCategories, true)) {

            $message = "Invalid book category.";

            $messageType = "danger";

        }

        else {


            /* ---------------------------------------
               UPDATE BOOK
            --------------------------------------- */

            $sql = "
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
            ";


            $stmt = mysqli_prepare($conn, $sql);


            if (!$stmt) {

                $message =
                    "Database error: " .
                    mysqli_error($conn);

                $messageType = "danger";

            }

            else {


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


                    $message =
                        "Book updated successfully!";

                    $messageType = "success";


                    /* ---------------------------------------
                       UPDATE VALUES SHOWN IN FORM
                    --------------------------------------- */

                    $book["title"] = $title;

                    $book["author"] = $author;

                    $book["category"] = $category;

                    $book["price"] = $price;

                    $book["contact_number"] = $contactNumber;

                    $book["book_condition"] = $bookCondition;

                }

                else {

                    $message =
                        "Failed to update book: " .
                        mysqli_stmt_error($stmt);

                    $messageType = "danger";

                }


                mysqli_stmt_close($stmt);

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Book</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<div class="container mt-5 mb-5">


    <div class="row justify-content-center">


        <div class="col-md-7">


            <div class="card shadow">


                <div class="card-body p-4">


                    <h2 class="text-center mb-4">

                        ✏️ Edit Book

                    </h2>


                    <?php if ($message !== ""): ?>

                        <div
                            class="alert alert-<?php echo $messageType; ?>"
                        >

                            <?php
                            echo htmlspecialchars($message);
                            ?>

                        </div>

                    <?php endif; ?>


                    <!-- CURRENT BOOK IMAGE -->

                    <?php if (!empty($book["image"])): ?>

                        <div class="text-center mb-4">

                            <img
                                src="<?php
                                echo htmlspecialchars($book["image"]);
                                ?>"
                                alt="Book Image"
                                style="
                                    width:180px;
                                    height:220px;
                                    object-fit:cover;
                                "
                                class="rounded shadow"
                            >

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- TITLE -->

                        <div class="mb-3">

                            <label class="form-label">

                                Book Title

                            </label>


                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars(
                                    $book["title"]
                                );
                                ?>"
                                required
                            >

                        </div>


                        <!-- AUTHOR -->

                        <div class="mb-3">

                            <label class="form-label">

                                Author

                            </label>


                            <input
                                type="text"
                                name="author"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars(
                                    $book["author"]
                                );
                                ?>"
                                required
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="mb-3">

                            <label class="form-label">

                                Book Category

                            </label>


                            <select
                                name="category"
                                class="form-select"
                                required
                            >


                                <option value="">

                                    Select Book Category

                                </option>


                                <!-- ENGINEERING -->

                                <optgroup label="Engineering Books">


                                    <option
                                        value="Programming"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Programming"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Programming
                                    </option>


                                    <option
                                        value="IOT"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "IOT"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        IOT
                                    </option>


                                    <option
                                        value="Engineering"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Engineering"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Engineering
                                    </option>


                                    <option
                                        value="Mathematics"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Mathematics"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Mathematics
                                    </option>


                                </optgroup>


                                <!-- MEDICAL -->

                                <optgroup label="Medical Books">


                                    <option
                                        value="Physics"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Physics"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Physics
                                    </option>


                                    <option
                                        value="Medical"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Medical"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Medical
                                    </option>


                                    <option
                                        value="Chemistry"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Chemistry"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Chemistry
                                    </option>


                                    <option
                                        value="Biology"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Biology"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Biology
                                    </option>


                                </optgroup>


                                <!-- ARTS & SCIENCE -->

                                <optgroup label="Arts & Science">


                                    <option
                                        value="Art"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Art"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Art
                                    </option>


                                    <option
                                        value="Science"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Science"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Science
                                    </option>


                                </optgroup>


                                <!-- COMPETITIVE -->

                                <optgroup label="Competitive Exams">


                                    <option
                                        value="Competitive exams"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Competitive exams"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Competitive Exams
                                    </option>


                                </optgroup>


                                <!-- NOVELS -->

                                <optgroup
                                    label="Novels & General Reading"
                                >


                                    <option
                                        value="Novels"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "Novels"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Novels
                                    </option>


                                    <option
                                        value="General Reading"
                                        <?php
                                        if (
                                            $book["category"]
                                            === "General Reading"
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        General Reading
                                    </option>


                                </optgroup>


                            </select>

                        </div>


                        <!-- PRICE -->

                        <div class="mb-3">

                            <label class="form-label">

                                Price

                            </label>


                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars(
                                    $book["price"]
                                );
                                ?>"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>


                        <!-- CONTACT -->

                        <div class="mb-3">

                            <label class="form-label">

                                Contact Number

                            </label>


                            <input
                                type="tel"
                                name="contact_number"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars(
                                    $book["contact_number"]
                                );
                                ?>"
                                required
                            >

                        </div>


                        <!-- CONDITION -->

                        <div class="mb-3">

                            <label class="form-label">

                                Book Condition

                            </label>


                            <select
                                name="book_condition"
                                class="form-select"
                                required
                            >


                                <option value="">

                                    Select Condition

                                </option>


                                <option
                                    value="new"
                                    <?php
                                    if (
                                        $book["book_condition"]
                                        === "new"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    New
                                </option>


                                <option
                                    value="good"
                                    <?php
                                    if (
                                        $book["book_condition"]
                                        === "good"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Good
                                </option>


                                <option
                                    value="used"
                                    <?php
                                    if (
                                        $book["book_condition"]
                                        === "used"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Used
                                </option>


                            </select>

                        </div>


                        <!-- UPDATE BUTTON -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            Update Book

                        </button>


                    </form>


                    <!-- BACK BUTTON -->

                    <div class="text-center mt-3">

                        <a href="index.php">

                            ← Back to Home

                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>


<?php

mysqli_close($conn);

?>
