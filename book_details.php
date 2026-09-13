<?php
session_start();
require_once "db_connect.php";

$id = (int)($_GET["id"] ?? 0);
$stmt = mysqli_prepare($conn, "
    SELECT b.*, s.name AS owner_name, s.id AS owner_id
    FROM books b
    JOIN students s ON s.id = b.student_id
    WHERE b.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$book) {
    http_response_code(404);
    exit("Book not found.");
}

$message = "";
if (
    isset($_GET["exchange"]) &&
    $_GET["exchange"] === "success"
) {
    $message = "Exchange request sent successfully!";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["student_id"])) {
    if (isset($_POST["send_message"])) {
        $content = trim($_POST["message"] ?? "");
        if ($content !== "") {
            $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iis", $_SESSION["student_id"], $book["owner_id"], $content);
            mysqli_stmt_execute($stmt);
            $message = "Message sent successfully.";
        }
    }
}

$myBooks = [];
if (isset($_SESSION["student_id"]) && (int)$_SESSION["student_id"] !== (int)$book["owner_id"]) {
    $stmt = mysqli_prepare($conn, "SELECT id, title FROM books WHERE student_id = ? AND status = 'available' ORDER BY title");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["student_id"]);
    mysqli_stmt_execute($stmt);
    $myBooks = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Details</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
<a href="browse_books.php" class="btn btn-secondary mb-3">← Back</a>
<div class="card shadow-sm">
<div class="row g-0">
<div class="col-md-5">
<?php if (!empty($book["image"])): ?>
<img src="<?php echo htmlspecialchars($book["image"]); ?>" class="img-fluid rounded-start" style="width:100%;height:350px;object-fit:cover;" alt="Book">
<?php endif; ?>
</div>
<div class="col-md-7"><div class="card-body">
<h2><?php echo htmlspecialchars($book["title"]); ?></h2>
<p><b>Author:</b> <?php echo htmlspecialchars($book["author"]); ?></p>
<p><b>Category:</b> <?php echo htmlspecialchars($book["category"]); ?></p>
<p><b>Price:</b> ₹<?php echo htmlspecialchars($book["price"]); ?></p>
<p><b>Condition:</b> <?php echo htmlspecialchars(ucfirst($book["book_condition"])); ?></p>
<p><b>Owner:</b> <?php echo htmlspecialchars($book["owner_name"]); ?></p>
<p><b>Contact:</b> <?php echo htmlspecialchars($book["contact_number"]); ?></p>

<?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

<?php if (!isset($_SESSION["student_id"])): ?>
    <a href="login.php" class="btn btn-primary">Login to Exchange / Message</a>
<?php elseif ((int)$_SESSION["student_id"] !== (int)$book["owner_id"]): ?>
    <h5 class="mt-4">🔄 Request Exchange</h5>
    <?php if (mysqli_num_rows($myBooks) > 0): ?>
    <form method="post" action="exchange_request.php" class="mb-4">
        <input type="hidden" name="desired_book_id" value="<?php echo (int)$book["id"]; ?>">
        <label class="form-label">Choose your book to offer</label>
        <select class="form-select mb-2" name="current_book_id" required>
            <?php while ($my = mysqli_fetch_assoc($myBooks)): ?>
                <option value="<?php echo (int)$my["id"]; ?>"><?php echo htmlspecialchars($my["title"]); ?></option>
            <?php endwhile; ?>
        </select>
        <textarea class="form-control mb-2" name="exchange_message" placeholder="Message (optional)"></textarea>
        <button class="btn btn-success">Send Exchange Request</button>
    </form>
    <?php else: ?>
        <p class="text-muted">Upload one of your books first to request an exchange.</p>
    <?php endif; ?>

    <div class="alert alert-secondary mt-4">

    <strong>💬 Messaging</strong><br>

    Messaging will be available after
    the exchange request is accepted.

</div>
<?php else: ?>
    <div class="alert alert-info">This is your own book.</div>
<?php endif; ?>
</div></div>
</div></div>
</div>
</body>
</html>