<?php
require "db.php";

$id = intval($_POST['id'] ?? 0);

if ($id == 0) exit("error");

if ($id == 1) exit("forbidden");

$q = $conn->prepare("DELETE FROM users WHERE id=?");
$q->bind_param("i", $id);

echo $q->execute() ? "success" : "error";
