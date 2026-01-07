<?php
require "db.php";
session_start();

if (!isset($_GET['id'])) {
    die("رقم العقار غير موجود!");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    die("العقار غير موجود!");
}

$prop = $result->fetch_assoc();
$owner_id = $prop['user_id'];

$conn->query("UPDATE properties SET views = views + 1 WHERE id = $id");

// تجهيز رقم الهاتف للواتساب
$phone = $prop['phone'] ?? "";
$clean_phone = "00970" . substr($phone, 1); // يحول 059 → 0097059

// الرسالة الافتراضية
$msg = urlencode("مرحباً، أنا مهتم بالعقار: " . $prop['type'] . " في " . $prop['address']);

// رابط الواتساب النهائي
$whatsapp = "https://wa.me/" . $clean_phone . "?text=" . $msg;

// فك JSON الصور
$imgs = json_decode($prop['images'] ?? '[]', true);
if (!is_array($imgs)) $imgs = [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تفاصيل العقار | عقاري</title>

<style>
body { font-family: Cairo; background:#f6f6f8; margin:0; }

/* NAVIGATION */
.navbar {
    background:#fff;
    padding:14px 26px;
    display:flex;
    justify-content: center;
    align-items: center;
    border-bottom:1px solid #ddd;
    position:sticky;
    top:0;
    z-index:10;
}
.wa-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: white;
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 700;
    margin: 10px 0;
    transition: 0.3s;
}

.wa-btn:hover {
    background: #1ebe5d;
    transform: translateY(-2px);
}

.wa-icon {
    width: 20px;
    height: 20px;
}

.brand {
    position:absolute;
    right:26px;
    display:flex;
    align-items:center;
    gap:10px;
}
.brand img {
    width:40px;
    height:40px;
    border-radius:10px;
    border:1px solid #ddd;
    padding:3px;
    background:#fff;
}
.links {
    display:flex;
    gap:14px;
}
.links a {
    text-decoration:none;
    color:#64748b;
    padding:8px 12px;
    border-radius:8px;
    transition:.2s;
    font-weight:600;
}
.fav-btn {
    display: inline-block;
    background: #ff4d6d;
    color: #fff;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: bold;
    text-decoration: none;
    font-size: 16px;
    transition: 0.3s;
}

.fav-btn:hover {
    background: #d93654;
    transform: scale(1.05);
}

.fav-btn:active {
    transform: scale(0.97);
}

.links a:hover {
    background:#eaeef6;
    color:#1d4ed8;
}
.links a.active {
    background:#1d4ed8;
    color:white;
}

/* PAGE */
.container { 
    max-width:900px; 
    margin:30px auto; 
    background:#fff; 
    padding:20px; 
    border-radius:12px; 
    box-shadow:0 3px 12px rgba(0,0,0,.1); 
}
.gallery img { width:100%; border-radius:12px; margin-bottom:10px; }
.title { font-size:26px; font-weight:900; margin:10px 0; }
.price { font-size:22px; color:#10b981; font-weight:900; }
.info { margin:10px 0; color:#444; font-size:16px; }
.desc { background:#f7f7f7; padding:15px; border-radius:10px; line-height:1.8; }

.swiper {
    width: 100%;
    height: 350px;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
}

.swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.wa-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: #fff !important;
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 16px;
    transition: 0.25s ease;
    margin: 14px 0;
}

.wa-btn:hover {
    background: #1ebe5d;
    transform: translateY(-3px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

.wa-icon {
    width: 24px;
    height: 24px;
}


</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

</head>
<body>

<header class="navbar">
    <a class="brand" href="mainpage.php">
        <img src="logo.png">
    </a>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <nav class="links">
        <a href="mainpage.php">الرئيسية</a>
        <a href="buy.php">للبيع</a>
        <a href="rent.php">للإيجار</a>
        <a href="land.php">أراضي</a>
        <a href="add-property.php">أضف عقارك</a>
        <a href="map.php">الخريطة</a>
 <?php if(isset($_SESSION['role'])): ?>
    <a href="profile.php">صفحتي</a>
<?php endif; ?>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="dashboard.php">لوحة التحكم</a>
        <?php endif; ?>

 <?php if(isset($_SESSION['user_id'])): ?>
      <a  href="favorites.php">المفضلة ❤</a>
    <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">تسجيل خروج</a>
        <?php else: ?>
            <a href="login.php">تسجيل دخول</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">

   <div class="swiper mySwiper">
    <div class="swiper-wrapper">
        <?php foreach($imgs as $img): ?>
            <div class="swiper-slide">
                <img src="<?= htmlspecialchars($img) ?>" alt="صورة العقار" />
            </div>
        <?php endforeach; ?>
    </div>

    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>

    <div class="swiper-pagination"></div>
</div>


    <div class="title">
        <?= htmlspecialchars($prop['type']) ?> في <?= htmlspecialchars($prop['address']) ?>
    </div>
    <p><strong>عدد المشاهدات:</strong> <?= $prop['views'] ?> 👁️</p>


    <div class="price">₪ <?= number_format($prop['price']) ?></div>
<div class="info">
    <strong>المحافظة:</strong> <?= htmlspecialchars($prop['province']) ?> <br>
    <strong>المدينة / القرية:</strong> <?= htmlspecialchars($prop['city']) ?> <br>
    <strong>المساحة:</strong> <?= htmlspecialchars($prop['size']) ?> م² <br>
    <strong>العنوان:</strong> <?= htmlspecialchars($prop['address']) ?> <br>
</div>

<?php
$phone = $prop['phone']; // مثال: 0599123456
$clean_phone = "00970" . substr($phone, 1); // يحذف 0 ويضيف 00970

$msg = urlencode("مرحباً، أنا مهتم بالعقار: " . $prop['type'] . " في " . $prop['address']);
?>
<a href="<?= $whatsapp ?>" target="_blank" class="wa-btn">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="wa-icon">
    تواصل عبر واتساب
</a>
<div>
<a href="add_fav.php?id=<?= $prop['id'] ?>" class="fav-btn">❤️ أضف إلى المفضلة</a>
</div>
<?php if(isset($prop['user_id'])): ?>
    <a href="user.php?id=<?= $prop['user_id'] ?>" 
       style="display:inline-block; margin:12px 0; background:#3b82f6; padding:10px 16px; 
              color:white; border-radius:10px; text-decoration:none; font-weight:bold;">
        👤 عرض ملف صاحب العقار
    </a>
<?php endif; ?>

    <h3>وصف العقار</h3>
    <div class="desc">
        <?= nl2br(htmlspecialchars($prop['description'])) ?>
    </div>

</div>
<h3>موقع العقار على الخريطة</h3>
<div id="map" style="width:100%; height:350px;"></div>
<script>
    var lat = <?= $prop['lat'] ?>;
    var lng = <?= $prop['lng'] ?>;

    var map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("موقع العقار")
        .openPopup();
</script>



<script>
var swiper = new Swiper(".mySwiper", {
    loop: true,
    grabCursor: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});

</script>



</body>
</html>
