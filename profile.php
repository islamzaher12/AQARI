<?php
session_start();
require "db.php";

// لو المستخدم مش مسجل دخول → رجّعه للّوجين
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// جلب بيانات المستخدم
$user = $conn->query("SELECT name, email, last_login, avatar, bio FROM users WHERE id = $user_id")->fetch_assoc();

// جلب عدد عقاراته
$q = $conn->query("SELECT COUNT(*) FROM properties WHERE user_id = $user_id");

if (!$q) {
    die("SQL ERROR: " . $conn->error);
}

$count_my_props = $q->fetch_row()[0];

// جلب العقارات الخاصة فيه
$my_props = $conn->query("SELECT id, description, type, price FROM properties WHERE user_id = $user_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8" />
<title>الملف الشخصي | عقاري</title>
<link rel="stylesheet" href="static/css/mainpage.css" />

<style>
/* ============================
   PROFILE PAGE CLEAN + RESPONSIVE
   ============================ */

.profile-box{
    width: min(1100px, 95%);
    margin: 30px auto;
    padding: 20px;
    background:#fff;
    border-radius:16px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

/* ترتيب صورة البروفايل + البايو */
.profile-header-wrapper{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    flex-wrap:wrap;
}

.avatar-img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #3b82f6;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.bio-input{
    width:100%;
    height:100px;
    border-radius:12px;
    padding:12px;
    border:1px solid #ccc;
    resize:none;
    font-size:15px;
}

/* آخر تسجيل دخول + عداد العقارات */
.profile-info{
    margin-top:20px;
    line-height:1.9;
}

/* الزر */
.btn-save-bio{
    margin-top:10px;
    padding:10px 18px;
    border:none;
    background:#10b981;
    color:white;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

/* ============================
   PROPERTY TABLE
   ============================ */
.table-box table{
    width:100%;
    border-collapse:collapse;
    border-radius:12px;
    overflow:hidden;
}

.table-box th{
    background:#f1f5f9;
    padding:12px;
    font-weight:700;
    text-align:center;
}

.table-box td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    text-align:center;
    font-size:14px;
}

.btn-edit, .btn-delete{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:13px;
}
.btn-edit{ background:#0ea5e9; color:white; }
.btn-delete{ background:#ef4444; color:white; }

/* ============================
   RESPONSIVE
   ============================ */
@media (max-width: 768px){

    .profile-header-wrapper{
        flex-direction:column-reverse;
        text-align:center;
        align-items:center;
    }

    .avatar-img{
        width:110px;
        height:110px;
    }

    table{
        font-size:13px;
    }

    .bio-input{
        height:120px;
    }
}

@media (max-width: 480px){
    .btn-edit, .btn-delete{
        display:block;
        width:100%;
        margin-bottom:6px;
    }
}


</style>
</head>

<body>

<header class="navbar">
  <a class="brand" href="mainpage.php">
      <img class="logo" src="logo.png" alt="عقاري" />
      
  </a>

  <nav class="links">
      <a href="mainpage.php">الرئيسية</a>
      <a href="buy.php">للبيع</a>
      <a href="rent.php">للإيجار</a>
      <a href="land.php">أراضي</a>
      <a href="add-property.php">اضف</a>
      <a href="map.php">خريطة</a>
      <?php if(isset($_SESSION['role'])): ?>
    <a class="active" href="profile.php">صفحتي</a>
<?php endif; ?>

          <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="dashboard.php">لوحة التحكم</a>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <a href="favorites.php">المفضلة ❤</a>
<?php endif; ?>

      <a href="whous.php">من نحن</a>
  
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php">تسجيل خروج</a>
<?php endif; ?>



    <?php if (!isset($_SESSION['user_id'])): ?>
    <a href="login.php">تسجيل دخول</a>
<?php endif; ?>

  </nav>
</header>


<div class="profile-box">
    
    <div class="profile-header-wrapper">
  <?php
$profile_img = $user['avatar'] ?: "default.jpg";
$bio = $user['bio'] ?: "";
?>
<div style="
    display:flex; 
    flex-direction: row-reverse; 
    justify-content: space-between; 
    align-items:flex-start;
    gap: 20px;
">


    <form action="update_profile.php" method="POST" enctype="multipart/form-data" style="width:70%;">
        <textarea name="bio" class="bio-input"><?= htmlspecialchars($bio) ?></textarea>

        <button type="submit" name="save_bio" class="btn-save-bio">
            حفظ
        </button>
    </form>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data" style="text-align:center;">
        <img src="<?= $profile_img ?>"
             class="avatar-img">

        <br><br>

        <input type="file" name="avatar" accept="image/*">
        <button type="submit" name="save_image" class="avatar-btn">
            تغيير الصورة
        </button>
    </form>

</div>


        <h2>👤 مرحباً، <?= htmlspecialchars($user['name']) ?></h2>
        <p><b>الإيميل:</b> <?= htmlspecialchars($user['email']) ?></p>
        <p><b>آخر تسجيل دخول:</b> <?= $user['last_login'] ?></p>
        <p><b>عدد عقاراتك:</b> <?= $count_my_props ?></p>
        <a href="add-property.php" class="btn primary">➕ إضافة عقار جديد</a>
    </div>

    <div class="table-box">
        <h3>عقاراتي</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>الوصف</th>
                <th>النوع</th>
                <th>السعر</th>
                <th>إجراءات</th>
            </tr>

            <?php
            if ($my_props->num_rows == 0) {
                echo "<tr><td colspan='5' style='text-align:center;'>لا يوجد عقارات بعد</td></tr>";
            } else {
                while ($p = $my_props->fetch_assoc()):
            ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['description']) ?></td>
                    <td><?= htmlspecialchars($p['type']) ?></td>
                    <td><?= htmlspecialchars($p['price']) ?></td>
                    <td>
                        <button class="btn-edit" onclick="location.href='edit-property.php?id=<?= $p['id'] ?>'">تعديل</button>
                        <button class="btn-delete" onclick="deleteMyProperty(<?= $p['id'] ?>)">حذف</button>
                    </td>
                </tr>
            <?php endwhile; } ?>

        </table>
    </div>

</div>

<script>
function deleteMyProperty(id){
    if(!confirm("هل أنت متأكد من حذف هذا العقار؟")) return;

    fetch("delete_property.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "OK") {
            document.getElementById("row-" + id).remove();
            alert("تم حذف العقار.");
        } else {
            alert("حدث خطأ أثناء الحذف");
        }
    });
}
</script>
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>تعديل معلوماتي</h3>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data">

            <label>تغيير الصورة الشخصية:</label>
            <input type="file" name="avatar" accept="image/*">

            <label>الوصف الشخصي:</label>
            <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

            <button class="btn primary">حفظ التعديلات</button>
            <button type="button" class="btn ghost" onclick="closeEditModal()">إلغاء</button>

        </form>
    </div>
</div>

<style>
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    backdrop-filter:blur(3px);
    justify-content:center;
    align-items:center;
}
.modal-content{
    background:#fff;
    width:350px;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 20px rgba(0,0,0,.2);
}
.modal-content input, .modal-content textarea{
    width:100%;
    margin-top:8px;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}
</style>

<script>
function openEditModal(){
    document.getElementById("editModal").style.display = "flex";
}
function closeEditModal(){
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>
