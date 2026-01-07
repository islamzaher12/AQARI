<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    die("يجب تسجيل الدخول للوصول إلى المفضلة");
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT properties.*
    FROM favorites
    JOIN properties ON properties.id = favorites.property_id
    WHERE favorites.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>المفضلة | عقاري</title>

<style>
:root{
  --bg: #f1f5f9;
  --panel: #ffffff;

  --brand: #3b82f6;
  --brand-2: #1d4ed8;

  --text: #0f172a;
  --muted: #64748b;

  --radius: 16px;
  --shadow: 0 4px 20px rgba(0,0,0,.06);
  --transition: .3s;
}

body{
  margin:0;
  min-height:100vh;
  background: var(--bg);
  font-family: "Cairo";
  color: var(--text);
  display:flex;
  flex-direction:column;
}

.navbar{
  position: sticky;
  top:0;
  background:#fff;
  padding:14px 20px;
  display:flex;
  justify-content:center;
  align-items:center;
  gap:20px;
  border-bottom:1px solid #ddd;
  backdrop-filter: blur(10px);
  z-index:1000;
}

.brand{
  position:absolute;
  left:20px;
}

.navbar .links{
  display:flex;
  gap:18px;
  flex-wrap:wrap;
}

.navbar .links a{
  text-decoration:none;
  color:var(--muted);
  font-weight:600;
  padding:8px 14px;
  border-radius:10px;
  transition:.2s;
}

.navbar .links a:hover{
  background:#e8efff;
  color:var(--brand);
}

.navbar .links a.active{
  background:linear-gradient(135deg,var(--brand),var(--brand-2));
  color:#fff;
}

.page-title{
  text-align:center;
  font-size:26px;
  margin:25px 0;
  font-weight:800;
}

.grid{
  width:min(1200px, 90%);
  margin:auto;
  display:grid;
  gap:20px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.card{
  background:#fff;
  border-radius:var(--radius);
  overflow:hidden;
  box-shadow:var(--shadow);
  transition: var(--transition);
  display:flex;
  flex-direction:column;
}

.card:hover{
  transform:translateY(-6px);
}

.card img{
  width:100%;
  height:220px;
  object-fit:cover;
}

.card-body{
  padding:16px;
  display:flex;
  flex-direction:column;
  gap:8px;
}

.card-body h3{
  margin:0;
  font-size:18px;
  font-weight:700;
}

.card-body p{
  margin:0;
  color:var(--muted);
}

.price{
  color:#10b981;
  font-size:20px;
  font-weight:900;
}

.actions{
  margin-top:10px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
}

.btn{
  flex:1;
  text-align:center;
  padding:10px 14px;
  border-radius:10px;
  background:linear-gradient(135deg,var(--brand),var(--brand-2));
  color:#fff;
  text-decoration:none;
  font-weight:700;
  transition:.3s;
}

.btn:hover{
  opacity:.85;
}

.btn.remove{
  background:#ef4444;
}

.footer{
  margin-top:auto;
  text-align:center;
  padding:20px;
  background:#fff;
  color:var(--muted);
  border-top:1px solid #ddd;
}

@media (max-width: 500px){
  .actions{
    flex-direction:column;
  }
  .btn{
    width:100%;
  }
}

</style>

</head>
<body>

<header class="navbar">

  <a class="brand" href="mainpage.php">
      <img src="logo.png" width="40">
  </a>

  <nav class="links">
    <a href="mainpage.php">الرئيسية</a>
    <a href="buy.php">للبيع</a>
    <a href="rent.php">للإيجار</a>
    <a href="land.php">أراضي</a>
    <a href="add-property.php">اضف</a>
    <a href="map.php">الخريطة</a>
     <?php if(isset($_SESSION['role'])): ?>
    <a href="profile.php">صفحتي</a>
<?php endif; ?>

    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="dashboard.php">لوحة التحكم</a>
    <?php endif; ?>



    <?php if(isset($_SESSION['user_id'])): ?>
      <a class="active" href="favorites.php">المفضلة ❤</a>
    <?php endif; ?>

    <a href="whous.php">من نحن</a>

    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="logout.php">تسجيل خروج</a>
    <?php else: ?>
      <a href="login.php">تسجيل دخول</a>
    <?php endif; ?>
  </nav>
</header>

<h2 class="page-title">❤ العقارات المفضلة</h2>

<div class="grid">
<?php if ($result->num_rows === 0): ?>
    <p style="grid-column:1/-1; text-align:center;">لا يوجد عقارات في المفضلة بعد.</p>

<?php else: ?>
    <?php while($row = $result->fetch_assoc()):
        $imgs = json_decode($row['images'], true);
        $img = $imgs[0] ?? "no-img.png";
    ?>
    <div class="card">
    <img src="<?= $img ?>">

    <div class="card-body">
        <h3><?= $row['type'] ?></h3>
        <p><?= $row['address'] ?></p>

        <div class="price">₪ <?= number_format($row['price']) ?></div>

        <div class="actions">
            <a href="property.php?id=<?= $row['id'] ?>" class="btn primary">عرض التفاصيل</a>

            <a href="remove_fav.php?id=<?= $row['id'] ?>" class="btn remove"
               onclick="return confirm('هل تريد إزالة هذا العقار من المفضلة؟');">
               ❌ إزالة
            </a>
        </div>
    </div>
</div>

    <?php endwhile; ?>
<?php endif; ?>
</div>

<div class="footer">
  © جميع الحقوق محفوظة — عقاري 2025
</div>

</body>
</html>
