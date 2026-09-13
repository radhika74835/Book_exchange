<?php
require_once "db_connect.php";

$query = trim($_GET["query"] ?? "");
$price = trim($_GET["price"] ?? "");
$condition = trim($_GET["condition"] ?? "");
$department = trim($_GET["department"] ?? "");

$sql = "SELECT b.*, s.name AS owner_name
        FROM books b
        JOIN students s ON s.id = b.student_id
        WHERE b.status = 'available'";
$types = "";
$params = [];

if ($query !== "") {
    $like = "%$query%";
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.category LIKE ?)";
    $types .= "sss";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($price !== "" && is_numeric($price)) {
    $sql .= " AND b.price <= ?";
    $types .= "d";
    $params[] = (float)$price;
}
if (in_array($condition, ["new", "good", "used"], true)) {
    $sql .= " AND b.book_condition = ?";
    $types .= "s";
    $params[] = $condition;
}
if ($department !== "") {
    $sql .= " AND b.category LIKE ?";
    $types .= "s";
    $params[] = "%$department%";
}

$sql .= " ORDER BY b.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$books = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Books</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
<h2>🔎 Search Books</h2>
<form class="row g-2 mb-4">
<input type="text" class="form-control" name="query" placeholder="Search" value="<?php echo htmlspecialchars($query); ?>">
<input type="number" class="form-control" name="price" placeholder="Maximum price" value="<?php echo htmlspecialchars($price); ?>">
<select class="form-select" name="condition">
<option value="">Any condition</option>
<option value="new" <?php echo $condition==="new"?"selected":""; ?>>New</option>
<option value="good" <?php echo $condition==="good"?"selected":""; ?>>Good</option>
<option value="used" <?php echo $condition==="used"?"selected":""; ?>>Used</option>
</select>
<input type="text" class="form-control" name="department" placeholder="Category" value="<?php echo htmlspecialchars($department); ?>">
<button class="btn btn-primary">Search</button>
</form>
<a href="index.php" class="btn btn-secondary mb-3">Home</a>

<div class="table-responsive">
<table class="table table-bordered bg-white">
<tr><th>Title</th><th>Author</th><th>Category</th><th>Price</th><th>Condition</th><th>Owner</th><th>Details</th></tr>
<?php while ($book = mysqli_fetch_assoc($books)): ?>
<tr>
<td><?php echo htmlspecialchars($book["title"]); ?></td>
<td><?php echo htmlspecialchars($book["author"]); ?></td>
<td><?php echo htmlspecialchars($book["category"]); ?></td>
<td>₹<?php echo htmlspecialchars($book["price"]); ?></td>
<td><?php echo htmlspecialchars(ucfirst($book["book_condition"])); ?></td>
<td><?php echo htmlspecialchars($book["owner_name"]); ?></td>
<td><a href="book_details.php?id=<?php echo (int)$book["id"]; ?>">View</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>
</body>
</html>