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

$message = "";
$messageType = "";


/* ---------------------------------------
   WHEN FORM IS SUBMITTED
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

    /* ---------------------------------------
       CHECK IMAGE
    --------------------------------------- */

    elseif (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {

        $message = "Please select a book image.";
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
               IMAGE INFORMATION
            --------------------------------------- */

            $image = $_FILES["image"];

            $originalName = $image["name"];

            $tmpName = $image["tmp_name"];

            $fileSize = $image["size"];


            /* ---------------------------------------
               ALLOWED IMAGE TYPES
            --------------------------------------- */

            $allowedTypes = [

                "image/jpeg",
                "image/png",
                "image/webp",
                "image/jpg"

            ];


            $imageType = mime_content_type($tmpName);


            /* ---------------------------------------
               CHECK IMAGE TYPE
            --------------------------------------- */

            if (!in_array($imageType, $allowedTypes, true)) {

                $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
                $messageType = "danger";

            }

            /* ---------------------------------------
               CHECK IMAGE SIZE
            --------------------------------------- */

            elseif ($fileSize > 5 * 1024 * 1024) {

                $message = "Image size must be less than 5 MB.";
                $messageType = "danger";

            }

            else {


                /* ---------------------------------------
                   CREATE UPLOADS FOLDER
                --------------------------------------- */

                $uploadDirectory = "uploads/";


                if (!is_dir($uploadDirectory)) {

                    mkdir($uploadDirectory, 0777, true);

                }


                /* ---------------------------------------
                   CREATE UNIQUE IMAGE NAME
                --------------------------------------- */

                $extension = strtolower(
                    pathinfo($originalName, PATHINFO_EXTENSION)
                );


                $newFileName =
                    time() . "_" .
                    bin2hex(random_bytes(5)) .
                    "." .
                    $extension;


                $imagePath =
                    $uploadDirectory . $newFileName;


                /* ---------------------------------------
                   MOVE IMAGE
                --------------------------------------- */

                if (move_uploaded_file($tmpName, $imagePath)) {


                    /* ---------------------------------------
                       BOOK STATUS
                    --------------------------------------- */

                    $status = "available";


                    /* ---------------------------------------
                       INSERT BOOK INTO DATABASE
                    --------------------------------------- */

                    $sql = "
                        INSERT INTO books
                        (
                            title,
                            author,
                            category,
                            price,
                            contact_number,
                            book_condition,
                            image,
                            student_id,
                            status,
                            created_at
                        )
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ";


                    $stmt = mysqli_prepare($conn, $sql);


                    if (!$stmt) {

                        unlink($imagePath);

                        $message =
                            "Database error: " .
                            mysqli_error($conn);

                        $messageType = "danger";

                    }

                    else {


                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssdsssis",
                            $title,
                            $author,
                            $category,
                            $price,
                            $contactNumber,
                            $bookCondition,
                            $imagePath,
                            $studentId,
                            $status
                        );


                        if (mysqli_stmt_execute($stmt)) {

                            $message =
                                "Book uploaded successfully!";

                            $messageType = "success";


                            /* Clear form values */

                            $title = "";
                            $author = "";
                            $category = "";
                            $price = "";
                            $contactNumber = "";
                            $bookCondition = "";

                        }

                        else {

                            unlink($imagePath);

                            $message =
                                "Failed to upload book: " .
                                mysqli_stmt_error($stmt);

                            $messageType = "danger";

                        }


                        mysqli_stmt_close($stmt);

                    }

                }

                else {

                    $message =
                        "Failed to upload image.";

                    $messageType = "danger";

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Upload Book</title>


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

                        📚 Upload Book

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


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >


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
                                echo htmlspecialchars($title ?? "");
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
                                echo htmlspecialchars($author ?? "");
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


                                <optgroup label="Engineering Books">

                                    <option
                                        value="Programming"
                                        <?php
                                        if (($category ?? "") === "Programming")
                                            echo "selected";
                                        ?>
                                    >
                                        Programming
                                    </option>


                                    <option
                                        value="IOT"
                                        <?php
                                        if (($category ?? "") === "IOT")
                                            echo "selected";
                                        ?>
                                    >
                                        IOT
                                    </option>


                                    <option
                                        value="Engineering"
                                        <?php
                                        if (($category ?? "") === "Engineering")
                                            echo "selected";
                                        ?>
                                    >
                                        Engineering
                                    </option>


                                    <option
                                        value="Mathematics"
                                        <?php
                                        if (($category ?? "") === "Mathematics")
                                            echo "selected";
                                        ?>
                                    >
                                        Mathematics
                                    </option>

                                </optgroup>


                                <optgroup label="Medical Books">

                                    <option
                                        value="Physics"
                                        <?php
                                        if (($category ?? "") === "Physics")
                                            echo "selected";
                                        ?>
                                    >
                                        Physics
                                    </option>


                                    <option
                                        value="Medical"
                                        <?php
                                        if (($category ?? "") === "Medical")
                                            echo "selected";
                                        ?>
                                    >
                                        Medical
                                    </option>


                                    <option
                                        value="Chemistry"
                                        <?php
                                        if (($category ?? "") === "Chemistry")
                                            echo "selected";
                                        ?>
                                    >
                                        Chemistry
                                    </option>


                                    <option
                                        value="Biology"
                                        <?php
                                        if (($category ?? "") === "Biology")
                                            echo "selected";
                                        ?>
                                    >
                                        Biology
                                    </option>

                                </optgroup>


                                <optgroup label="Arts & Science">

                                    <option
                                        value="Art"
                                        <?php
                                        if (($category ?? "") === "Art")
                                            echo "selected";
                                        ?>
                                    >
                                        Art
                                    </option>


                                    <option
                                        value="Science"
                                        <?php
                                        if (($category ?? "") === "Science")
                                            echo "selected";
                                        ?>
                                    >
                                        Science
                                    </option>

                                </optgroup>


                                <optgroup label="Competitive Exams">

                                    <option
                                        value="Competitive exams"
                                        <?php
                                        if (($category ?? "") === "Competitive exams")
                                            echo "selected";
                                        ?>
                                    >
                                        Competitive Exams
                                    </option>

                                </optgroup>


                                <optgroup label="Novels & General Reading">

                                    <option
                                        value="Novels"
                                        <?php
                                        if (($category ?? "") === "Novels")
                                            echo "selected";
                                        ?>
                                    >
                                        Novels
                                    </option>


                                    <option
                                        value="General Reading"
                                        <?php
                                        if (($category ?? "") === "General Reading")
                                            echo "selected";
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
                                echo htmlspecialchars($price ?? "");
                                ?>"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>


                        <!-- CONTACT NUMBER -->

                        <div class="mb-3">

                            <label class="form-label">

                                Contact Number

                            </label>

                            <input
                                type="tel"
                                name="contact_number"
                                class="form-control"
                                value="<?php
                                echo htmlspecialchars($contactNumber ?? "");
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
                                    if (($bookCondition ?? "") === "new")
                                        echo "selected";
                                    ?>
                                >
                                    New
                                </option>


                                <option
                                    value="good"
                                    <?php
                                    if (($bookCondition ?? "") === "good")
                                        echo "selected";
                                    ?>
                                >
                                    Good
                                </option>


                                <option
                                    value="used"
                                    <?php
                                    if (($bookCondition ?? "") === "used")
                                        echo "selected";
                                    ?>
                                >
                                    Used
                                </option>

                            </select>

                        </div>


                        <!-- IMAGE -->

                        <div class="mb-4">

                            <label class="form-label">

                                Book Image

                            </label>


                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                                required
                            >


                            <small class="text-muted">

                                JPG, JPEG, PNG or WEBP. Maximum 5 MB.

                            </small>

                        </div>


                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            Upload Book

                        </button>


                    </form>


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