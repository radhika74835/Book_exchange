<?php
require_once "db_connect.php";

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$sql = "SELECT b.*, s.name AS owner_name
        FROM books b
        JOIN students s ON s.id = b.student_id
        WHERE b.status = 'available'";
$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.category LIKE ?)";
    $like = "%$search%";
    $types .= "sss";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

if ($category !== "") {
    $sql .= " AND b.category LIKE ?";
    $types .= "s";
    $params[] = "%$category%";
}

$sql .= " ORDER BY b.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);

if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Browse Books</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
<h2>📚 Browse Books</h2>
<form class="row g-2 mb-4">
<div class="col-md-5"><input class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, author or category"></div>
<div class="col-md-5"><input class="form-control" name="category" value="<?php echo htmlspecialchars($category); ?>" placeholder="Category"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Search</button></div>
</form>
<a href="index.php" class="btn btn-secondary mb-3">Home</a>
<div class="row">
<?php while ($book = mysqli_fetch_assoc($result)): ?>
<div class="col-md-4 mb-4">
<div class="card h-100 shadow-sm">
<?php if (!empty($book["image"])): ?><img src="<?php echo htmlspecialchars($book["image"]); ?>" class="card-img-top" style="height:220px;object-fit:cover;" alt="Book"><?php endif; ?>
<div class="card-body">
<h5><?php echo htmlspecialchars($book["title"]); ?></h5>
<p>Author: <?php echo htmlspecialchars($book["author"]); ?></p>
<p>Category: <?php echo htmlspecialchars($book["category"]); ?></p>
<p>Price: ₹<?php echo htmlspecialchars($book["price"]); ?></p>
<p>Owner: <?php echo htmlspecialchars($book["owner_name"]); ?></p>
<a href="book_details.php?id=<?php echo (int)$book["id"]; ?>" class="btn btn-primary">View Details</a>
</div></div></div>
<?php endwhile; ?>
</div>
</div>
</body>
</html>