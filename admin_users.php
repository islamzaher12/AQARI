<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("❌ غير مسموح");
}

$users = $conn->query("SELECT id, name, email, role, last_login FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة المستخدمين</title>
<style>
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: center;
}
.btn-admin {
    background: #3b82f6;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}
.btn-remove {
    background: #ef4444;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}
</style>
</head>
<body>

<h2>👥 إدارة المستخدمين</h2>

<table>
    <tr>
        <th>ID</th>
        <th>الاسم</th>
        <th>الإيميل</th>
        <th>الدور</th>
        <th>آخر تسجيل دخول</th>
        <th>إجراء</th>
    </tr>

    <?php while($u = $users->fetch_assoc()): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= $u['name'] ?></td>
        <td><?= $u['email'] ?></td>
        <td><?= $u['role'] ?></td>
        <td><?= $u['last_login'] ?: '---' ?></td>

        <td>
            <?php if ($u['role'] === 'user'): ?>
                <a href="make_admin.php?id=<?= $u['id'] ?>" class="btn-admin">ترقية لإدمن</a>
            <?php else: ?>
                <a href="remove_admin.php?id=<?= $u['id'] ?>" class="btn-remove">إرجاع لمستخدم</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
