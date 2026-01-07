<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    die("يجب تسجيل الدخول");
}

$user_id = $_SESSION['user_id'];
$property_id = intval($_GET['id'] ?? 0);

if ($property_id > 0) {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
}

header("Location: favorites.php");
exit;
?>
