<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db_connect.php";


/* =====================================================
   LATEST 7 AVAILABLE BOOKS
   ===================================================== */

$latestBooks = mysqli_query(
    $conn,
    "
    SELECT
        b.*,
        s.name AS owner_name
    FROM books b
    JOIN students s ON s.id = b.student_id
    WHERE LOWER(TRIM(b.status)) = 'available'
    ORDER BY b.created_at DESC
    LIMIT 7
    "
);


/* =====================================================
   LATEST 3 STUDENT REVIEWS
   ===================================================== */

$reviews = mysqli_query(
    $conn,
    "
    SELECT
        r.review,
        r.rating,
        s.name AS student_name
    FROM reviews r
    JOIN students s ON s.id = r.student_id
    ORDER BY r.id DESC
    LIMIT 3
    "
);


/* =====================================================
   HOME PAGE CATEGORIES
   ===================================================== */

$categories = [
    "Engineering Books",
    "Medical Books",
    "Arts & Science",
    "Competitive Exam Books",
    "Novels & General Reading"
];


/* =====================================================
   CATEGORY MAPPING
   ===================================================== */

$categoryMapping = [

    "Engineering Books" => [
        "Programming",
        "IOT",
        "IoT",
        "Engineering",
        "Mathematics"
    ],

    "Medical Books" => [
        "Medical",
        "Biology",
        "Bio",
        "Physics",
        "Chemistry"
    ],

    "Arts & Science" => [
        "Art",
        "Art and Science",
        "Science"
    ],

    "Competitive Exam Books" => [
        "Competitive exams",
        "Competitive Exams"
    ],

    "Novels & General Reading" => [
        "Novels",
        "General Reading"
    ]

];

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Smart Online Book Exchange</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    >

    <style>

        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .hero-section {
            padding: 50px 20px;
            background: white;
            border-radius: 15px;
        }

        .category-btn {
            min-height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .book-card {
            transition: 0.2s;
        }

        .book-card:hover {
            transform: translateY(-4px);
        }

        .book-image {
            height: 220px;
            object-fit: cover;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 20px;
        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVIGATION BAR
     ===================================================== -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand"
            href="index.php"
        >
            📚 Smart Online Book Exchange
        </a>


        <div class="d-flex align-items-center flex-wrap gap-2">

            <?php if (isset($_SESSION["student_id"])): ?>

                <span class="text-white me-2">

                    Hi,

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["student_name"] ?? "Student"
                    );
                    ?>

                </span>


                <a
                    href="upload_book.php"
                    class="btn btn-light btn-sm"
                >
                    Upload
                </a>


                <a
                    href="my_books.php"
                    class="btn btn-light btn-sm"
                >
                    My Books
                </a>


                <a
                    href="my_requests.php"
                    class="btn btn-light btn-sm"
                >
                    My Requests
                </a>


                <a
                    href="notifications.php"
                    class="btn btn-light btn-sm"
                >
                    Notifications
                </a>


                <a
                    href="messages.php"
                    class="btn btn-light btn-sm"
                >
                    Messages
                </a>


                <!-- ADMIN DASHBOARD BUTTON -->

                <?php if (
                    isset($_SESSION["is_admin"]) &&
                    (int)$_SESSION["is_admin"] === 1
                ): ?>

                    <a
                        href="admin_dashboard.php"
                        class="btn btn-warning btn-sm"
                    >
                        Admin Dashboard
                    </a>

                <?php endif; ?>


                <a
                    href="logout.php"
                    class="btn btn-warning btn-sm"
                >
                    Logout
                </a>


            <?php else: ?>

                <a
                    href="login.php"
                    class="btn btn-light btn-sm"
                >
                    Login / Register
                </a>

            <?php endif; ?>

        </div>

    </div>

</nav>



<!-- =====================================================
     MAIN CONTAINER
     ===================================================== -->

<div class="container py-4">


    <!-- =====================================================
         HERO
         ===================================================== -->

    <div
        class="hero-section mb-4 shadow-sm text-center"
    >

        <h1>
            📚 Smart Online Book Exchange
        </h1>

        <p class="lead">
            Buy, Sell, Exchange Books Easily
        </p>


        <a
            href="browse_books.php"
            class="btn btn-primary"
        >
            Browse Books
        </a>

    </div>



    <!-- =====================================================
         SEARCH
         ===================================================== -->

    <h3 class="section-title">
        🔎 Search
    </h3>


    <form
        class="input-group mb-4"
        action="search_books.php"
        method="get"
    >

        <input
            type="text"
            class="form-control"
            name="query"
            placeholder="Search by title, author or category"
            required
        >


        <button
            type="submit"
            class="btn btn-success"
        >
            Search
        </button>

    </form>



    <!-- =====================================================
         CATEGORIES
         ===================================================== -->

    <h3 class="section-title">
        📂 Categories
    </h3>


    <div class="row mb-5">

        <?php foreach ($categories as $category): ?>

            <div class="col-md-4 col-lg-2 mb-2">

                <?php

                $mappedCategories =
                    $categoryMapping[$category] ?? [$category];

                $categoryParameter =
                    implode("|", $mappedCategories);

                ?>


                <a
                    class="btn btn-outline-primary w-100 category-btn"
                    href="category_books.php?category=<?php echo urlencode($categoryParameter); ?>"
                >

                    <?php
                    echo htmlspecialchars($category);
                    ?>

                </a>

            </div>

        <?php endforeach; ?>

    </div>



    <!-- =====================================================
         LATEST BOOKS
         ===================================================== -->

    <h3 class="section-title">
        📖 Latest Books
    </h3>


    <div class="row">

        <?php if (
            $latestBooks &&
            mysqli_num_rows($latestBooks) > 0
        ): ?>


            <?php while (
                $book = mysqli_fetch_assoc($latestBooks)
            ): ?>

                <div class="col-md-4 col-lg-3 mb-4">

                    <div class="card h-100 shadow-sm book-card">


                        <?php if (!empty($book["image"])): ?>

                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $book["image"]
                                );
                                ?>"
                                class="card-img-top book-image"
                                alt="Book cover"
                            >

                        <?php else: ?>

                            <div
                                class="d-flex align-items-center justify-content-center bg-secondary text-white book-image"
                            >
                                📚 No Image
                            </div>

                        <?php endif; ?>


                        <div class="card-body">


                            <h5 class="card-title">

                                <?php
                                echo htmlspecialchars(
                                    $book["title"] ?? ""
                                );
                                ?>

                            </h5>


                            <p class="mb-1">

                                <strong>Author:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $book["author"] ?? ""
                                );
                                ?>

                            </p>


                            <p class="mb-1">

                                <strong>Category:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $book["category"] ?? ""
                                );
                                ?>

                            </p>


                            <p class="mb-1">

                                <strong>Price:</strong>

                                ₹<?php
                                echo htmlspecialchars(
                                    $book["price"] ?? "0"
                                );
                                ?>

                            </p>


                            <p class="text-muted">

                                <strong>Owner:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $book["owner_name"] ?? ""
                                );
                                ?>

                            </p>


                            <a
                                href="book_details.php?id=<?php echo (int)$book["id"]; ?>"
                                class="btn btn-primary"
                            >
                                View Details
                            </a>


                        </div>

                    </div>

                </div>

            <?php endwhile; ?>


        <?php else: ?>


            <div class="col-12">

                <div class="alert alert-info">

                    No books available yet.

                </div>

            </div>


        <?php endif; ?>

    </div>



    <!-- =====================================================
         STUDENT REVIEWS
         ===================================================== -->

    <?php if (
        $reviews &&
        mysqli_num_rows($reviews) > 0
    ): ?>


        <h3 class="section-title mt-4">

            ⭐ Student Reviews

        </h3>


        <?php while (
            $review = mysqli_fetch_assoc($reviews)
        ): ?>


            <div class="alert alert-secondary">


                <strong>

                    <?php
                    echo htmlspecialchars(
                        $review["student_name"] ?? ""
                    );
                    ?>

                </strong>


                <?php if (
                    $review["rating"] !== null
                ): ?>

                    <span class="ms-2">

                        ⭐

                        <?php
                        echo (int)$review["rating"];
                        ?>/5

                    </span>

                <?php endif; ?>


                <br>


                <span>

                    "<?php
                    echo htmlspecialchars(
                        $review["review"] ?? ""
                    );
                    ?>"

                </span>


            </div>


        <?php endwhile; ?>


    <?php endif; ?>


</div>


</body>

</html>