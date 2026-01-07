<?php
require "db.php";

if (!isset($_POST['id'])) {
    echo "لم يتم استلام ID";
    exit;
}

$id = intval($_POST['id']);

$query = "DELETE FROM properties WHERE id = $id";

if ($conn->query($query)) {
    echo "تم حذف العقار بنجاح";
} else {
    echo "خطأ في تنفيذ الحذف: " . $conn->error;
}
?>
