<?php
session_start();
require "db.php";

$user_id = $_SESSION['user_id'];
$bio = mysqli_real_escape_string($conn, $_POST['bio']);

$conn->query("UPDATE users SET bio='$bio' WHERE id=$user_id");

header("Location: profile.php");
exit;
