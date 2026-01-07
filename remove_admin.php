<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("forbidden");
}

if (!isset($_POST['id'])) {
    exit("error");
}

$id = intval($_POST['id']);

if ($id == $_SESSION['user_id']) {
    exit("forbidden");
}

$conn->query("DELETE FROM users WHERE id=$id");
exit("success");
?>
