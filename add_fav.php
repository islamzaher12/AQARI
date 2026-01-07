<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    die("يجب تسجيل الدخول أولاً");
}

$user = $_SESSION['user_id'];
$property = $_GET['id'];

$stmt = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user, $property);
$stmt->execute();

header("Location: property.php?id=" . $property);
exit;
?>
