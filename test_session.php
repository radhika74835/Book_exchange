<?php
session_start();

if (!isset($_SESSION["test"])) {
    $_SESSION["test"] = "working";
}

echo "Session ID: " . session_id();
echo "<br><br>";
echo "Session value: " . $_SESSION["test"];
?>