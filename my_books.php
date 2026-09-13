<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION["student_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM books WHERE student_id = ? ORDER BY created_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>My Books</title>

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
>

</head>


<body class="bg-light">


<div class="container py-5">


<h2>📚 My Uploaded Books</h2>


<a
href="upload_book.php"
class="btn btn-success mb-3">

+ Upload New Book

</a>


<a
href="index.php"
class="btn btn-secondary mb-3">

Home

</a>


<!-- SUCCESS MESSAGE AFTER UPDATE -->

<?php if (isset($_GET["updated"])): ?>

<div class="alert alert-success">

    Book updated successfully.

</div>

<?php endif; ?>


<!-- SUCCESS MESSAGE AFTER DELETE -->

<?php if (isset($_GET["deleted"])): ?>

<div class="alert alert-success">

    Book deleted successfully.

</div>

<?php endif; ?>


<!-- SUCCESS MESSAGE AFTER UPLOAD -->

<?php if (isset($_GET["success"])): ?>

<div class="alert alert-success">

    Book uploaded successfully.

</div>

<?php endif; ?>


<div class="row">


<?php while ($book = mysqli_fetch_assoc($result)): ?>


<div class="col-md-4 mb-4">


<div class="card h-100 shadow-sm">


<!-- BOOK IMAGE -->

<?php if (!empty($book["image"])): ?>

<img
src="<?php echo htmlspecialchars($book["image"]); ?>"
class="card-img-top"
style="height:220px;object-fit:cover;"
alt="Book"
>

<?php endif; ?>


<div class="card-body">


<!-- BOOK TITLE -->

<h5>

<?php
echo htmlspecialchars($book["title"]);
?>

</h5>


<!-- AUTHOR -->

<p>

<strong>Author:</strong>

<?php
echo htmlspecialchars($book["author"]);
?>

</p>


<!-- CATEGORY -->

<p>

<strong>Category:</strong>

<?php
echo htmlspecialchars($book["category"]);
?>

</p>


<!-- PRICE -->

<p>

<strong>Price:</strong>

₹<?php
echo htmlspecialchars($book["price"]);
?>

</p>


<!-- CONDITION -->

<p>

<strong>Condition:</strong>

<?php
echo htmlspecialchars(
    ucfirst($book["book_condition"])
);
?>

</p>


<!-- =========================================
     BUTTONS
     ========================================= -->


<div class="d-flex gap-2 flex-wrap">


<!-- VIEW -->

<a
href="book_details.php?id=<?php echo (int)$book["id"]; ?>"
class="btn btn-primary">

View

</a>


<!-- EDIT -->

<a
href="edit_book.php?id=<?php echo (int)$book["id"]; ?>"
class="btn btn-warning">

Edit

</a>


<!-- DELETE -->

<a
href="delete_book.php?id=<?php echo (int)$book["id"]; ?>"
class="btn btn-danger"
onclick="return confirm('Are you sure you want to delete this book?');">

Delete

</a>


</div>


</div>

</div>

</div>


<?php endwhile; ?>


</div>


</div>


</body>

</html>