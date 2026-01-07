<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "forbidden";
    exit;
}

$id   = $_POST['id']   ?? null;
$role = $_POST['role'] ?? null;

if (!$id || !$role) {
    echo "missing";
    exit;
}

if ($id == $_SESSION['user_id']) {
    echo "forbidden"; 
    exit;
}


$stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
$stmt->bind_param("si", $role, $id);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "error";
}
