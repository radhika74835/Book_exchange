<?php

session_start();

require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit;

}

$category = trim($_GET["category"] ?? "");


if ($category === "") {

    exit("Invalid category.");

}

$selectedCategories = array_filter(
    array_map(
        "trim",
        explode("|", $category)
    )
);


if (empty($selectedCategories)) {

    exit("Invalid category.");

}

$displayCategory = "Books";


if (
    in_array("Programming", $selectedCategories, true) ||
    in_array("IOT", $selectedCategories, true) ||
    in_array("Engineering", $selectedCategories, true) ||
    in_array("Mathematics", $selectedCategories, true)
) {

    $displayCategory = "Engineering Books";

}
elseif (
    in_array("Physics", $selectedCategories, true) ||
    in_array("Medical", $selectedCategories, true) ||
    in_array("Chemistry", $selectedCategories, true) ||
    in_array("Biology", $selectedCategories, true)
) {

    $displayCategory = "Medical Books";

}
elseif (
    in_array("Art", $selectedCategories, true) ||
    in_array("Science", $selectedCategories, true)
) {

    $displayCategory = "Arts & Science";

}
elseif (
    in_array("Competitive exams", $selectedCategories, true)
) {

    $displayCategory = "Competitive Exam Books";

}
elseif (
    in_array("Novels", $selectedCategories, true) ||
    in_array("General Reading", $selectedCategories, true)
) {

    $displayCategory = "Novels & General Reading";

}

$placeholders = implode(
    ",",
    array_fill(
        0,
        count($selectedCategories),
        "?"
    )
);

$sql = "
    SELECT *
    FROM books
    WHERE category IN ($placeholders)
    AND status = 'available'
    ORDER BY created_at DESC
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}

$types = str_repeat(
    "s",
    count($selectedCategories)
);


mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$selectedCategories
);

mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);

?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>

        <?php
        echo htmlspecialchars(
            $displayCategory
        );
        ?>

    </title>


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    >

</head>


<body class="bg-light">


<div class="container py-5">

    <a
        href="index.php"
        class="btn btn-secondary mb-4"
    >

        ← Back to Home

    </a>

    <h2 class="mb-4">

        📚

        <?php
        echo htmlspecialchars(
            $displayCategory
        );
        ?>

    </h2>


    <!-- BOOKS -->

    <?php if (
        mysqli_num_rows($result) === 0
    ): ?>


        <div class="alert alert-info">

            No books found in this category.

        </div>


    <?php else: ?>


        <div class="row">


            <?php while (
                $book =
                mysqli_fetch_assoc($result)
            ): ?>


                <div class="col-md-4 col-lg-4 mb-4">


                    <div
                        class="card h-100 shadow-sm"
                    >

                        <?php if (
                            !empty($book["image"])
                        ): ?>


                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $book["image"]
                                );
                                ?>"
                                class="card-img-top"
                                style="
                                    height:230px;
                                    object-fit:cover;
                                "
                                alt="Book Image"
                            >

                        <?php else: ?>


                            <div
                                class="
                                    d-flex
                                    align-items-center
                                    justify-content-center
                                    bg-secondary
                                    text-white
                                "
                                style="
                                    height:230px;
                                "
                            >

                                📚 No Image

                            </div>


                        <?php endif; ?>


                        <div class="card-body">

                            <h5 class="card-title">

                                <?php
                                echo htmlspecialchars(
                                    $book["title"]
                                );
                                ?>

                            </h5>
                            <p class="mb-1">

                                <strong>
                                    Author:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $book["author"]
                                );
                                ?>

                            </p>
                            <p class="mb-1">

                                <strong>
                                    Category:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $book["category"]
                                );
                                ?>

                            </p>
                            <p class="mb-1">

                                <strong>
                                    Price:
                                </strong>

                                ₹<?php
                                echo htmlspecialchars(
                                    $book["price"]
                                );
                                ?>

                            </p>

                            <p class="mb-3">

                                <strong>
                                    Condition:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    ucfirst(
                                        $book["book_condition"]
                                    )
                                );
                                ?>

                            </p>
                            <a
                                href="book_details.php?id=<?php
                                echo (int)$book["id"];
                                ?>"
                                class="btn btn-primary w-100"
                            >

                                👁️ View Details

                            </a>


                        </div>


                    </div>


                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

</div>

<?php

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>

</body>

</html>
