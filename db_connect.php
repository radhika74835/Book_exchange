<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = mysqli_connect("localhost", "root", "", "book_exchange_db");
mysqli_set_charset($conn, "utf8mb4");
?>