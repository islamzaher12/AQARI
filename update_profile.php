<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    exit("Not logged in");
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['save_bio'])) {
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $conn->query("UPDATE users SET bio='$bio' WHERE id=$user_id");
    header("Location: profile.php");
    exit;
}


if (isset($_POST['save_image'])) {
    if (!empty($_FILES['avatar']['name'])) {

        $imgName = time() . "_" . $_FILES['avatar']['name'];
        $path = "uploads/" . $imgName;

        move_uploaded_file($_FILES['avatar']['tmp_name'], $path);

        $conn->query("UPDATE users SET avatar='$path' WHERE id=$user_id");

        header("Location: profile.php");
        exit;
    }
}

?>
