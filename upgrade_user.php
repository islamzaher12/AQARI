<?php
require "db.php";
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("❌ غير مسموح");
}

$id = intval($_GET['id']);

$conn->query("UPDATE users SET role = 'admin' WHERE id = $id");

header("Location: admin_users.php");
exit;
