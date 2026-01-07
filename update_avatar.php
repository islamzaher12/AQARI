<?php
session_start();
require "db.php";

$user_id = $_SESSION['user_id'];

if (!empty($_FILES['avatar']['name'])) {

    $file = $_FILES['avatar'];
    $path = "uploads/avatars/" . time() . "_" . $file['name'];

    move_uploaded_file($file['tmp_name'], $path);

    $conn->query("UPDATE users SET avatar='$path' WHERE id=$user_id");
}

header("Location: profile.php");
exit;
