<?php
require "db.php";
 session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $type        = $_POST['type'];
    $price       = $_POST['price'];
    $address     = $_POST['address'];
    $lat         = $_POST['lat'];
    $lng         = $_POST['lng'];
    $description = $_POST['description'];
    $owner       = $_POST['owner'];
    $phone       = $_POST['phone'];
    $payment     = $_POST['payment'];
    $video       = $_POST['video'];
    $size = $_POST['size'];


    if (!preg_match("/^059[0-9]{7}$/", $phone)) {
        die("رقم الهاتف يجب أن يبدأ بـ 059 ويتبعه 7 أرقام");
    }

    $uploaded_images = [];

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {

            $tmp = $_FILES['images']['tmp_name'][$key];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $uniqueName = uniqid() . "." . $ext;

            $uploadPath = "uploads/" . $uniqueName;

            move_uploaded_file($tmp, $uploadPath);

            $uploaded_images[] = $uploadPath;
        }
    }

    $images_json = json_encode($uploaded_images, JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare("
INSERT INTO properties
(user_id, type, price, province, city, address, lat, lng, size, description, owner, phone, payment, video, images)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$uid   = (int) $_SESSION['user_id'];
$price = (int) $price;
$size  = (int) $size;
$lat   = (float) $lat;
$lng   = (float) $lng;

$stmt->bind_param(
    "isisssddissssss",
    $uid,               
    $type,              
    $price,             
    $_POST['province'], 
    $_POST['city'],     
    $address,           
    $lat,              
    $lng,               
    $size,              
    $description,       
    $owner,             
    $phone,             
    $payment,           
    $video,             
    $images_json        
);



    if ($stmt->execute()) {
        echo "<script>alert('تم إضافة العقار بنجاح'); window.location.href='mainpage.php';</script>";
        exit;
    } else {
        echo "خطأ: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة عقار</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <style>
    :root{
  --bg: #f8fafd;
  --panel: #ffffff;
  --panel-2: #f0f4f8;

  --text: #0f172a;
  --muted: #64748b;

  --brand: #3b82f6;
  --brand-2: #1d4ed8;
  --brand-rgb: 59, 130, 246;
  --accent: #8b5cf6;
  --accent-rgb: 139, 92, 246;

  --ok: #10b981;

  --shadow: 0 4px 20px rgba(0,0,0,.06);
  --shadow-lg: 0 12px 48px rgba(0,0,0,.1);
  --brand-shadow: 0 4px 24px rgba(59,130,246,0.25);
  --brand-shadow-lg: 0 8px 32px rgba(59,130,246,0.35);
  --accent-shadow: 0 4px 24px rgba(139,92,246,0.3);

  --radius: 16px;
  --radius-sm: 12px;
  --radius-lg: 20px;
  --gap: 16px;
  
  --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

*{
  box-sizing:border-box;
  -webkit-tap-highlight-color: transparent;
}

html{
  scroll-behavior: smooth;
}

html,body{height:100%}

body{
  margin:0;
  background: 
    radial-gradient(circle at 20% 20%, rgba(139,92,246,0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(59,130,246,0.05) 0%, transparent 50%),
    linear-gradient(180deg, #fafcfe 0%, #f0f4f9 100%) fixed;
  color: var(--text);
  font-family: "Cairo", system-ui, Tahoma, Arial, sans-serif;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.navbar {
  position: sticky; top:0; inset-inline:0;
  display:flex; align-items:center; justify-content:center;
  padding:14px 26px;
  background: rgba(255,255,255,0.75);
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 1px solid rgba(221, 221, 221, 0.6);
  z-index: 50;
  transition: var(--transition);
}

.navbar::after{
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(59,130,246,0.4) 50%, transparent);
  opacity: 0;
  transition: opacity var(--transition);
}

.navbar:hover::after{
  opacity: 1;
}

.brand{
  position:absolute;
  left:26px;
  top:50%;
  transform:translateY(-50%);
}

.logo{
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: contain;
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 3px;
}

.links{display:flex; align-items:center; gap:14px; flex-wrap:wrap}

.links a{
  color:var(--muted); 
  text-decoration:none; 
  padding:.5rem .8rem; 
  border-radius:var(--radius-sm);
  transition: var(--transition);
  position: relative;
  font-weight: 500;
}

.links a::before{
  content: "";
  position: absolute;
  inset: 0;
  border-radius: var(--radius-sm);
  background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(139,92,246,0.08));
  opacity: 0;
  transition: opacity var(--transition);
}

.links a:hover{
  color:var(--brand);
}

.links a:hover::before{
  opacity: 1;
}

.links a.active{
  color:#fff;
  background: linear-gradient(135deg, var(--brand), var(--brand-2), var(--accent));
  box-shadow: var(--brand-shadow);
  transform: translateY(-1px);
}

.hero{
  position: relative; 
  isolation:isolate;
  min-height: 58vh;
  display:grid; 
  place-items:center;
  background:
    linear-gradient(160deg, rgba(255,255,255,0.7) 0%, rgba(250,252,255,0.95) 35%, #fff 100%),
    url("https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1920&auto=format&fit=crop") center/cover no-repeat;
  overflow: hidden;
}

.hero::before{
  content: "";
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse 900px 500px at 30% 0%, rgba(139,92,246,0.15), transparent 70%),
    radial-gradient(ellipse 900px 500px at 70% 0%, rgba(59,130,246,0.15), transparent 70%);
  animation: heroGlow 8s ease-in-out infinite;
  z-index: -1;
}

@keyframes heroGlow{
  0%, 100%{ opacity: 0.6; }
  50%{ opacity: 1; }
}

.hero::after{
  content:""; 
  position:absolute; 
  inset:0; 
  z-index:-1;
  background: radial-gradient(
    600px 300px at 50% 0%, 
    transparent 0%, 
    rgba(255,255,255,0.6) 65%, 
    #fff 100%
  );
}

.hero-overlay{
  text-align:center; 
  padding:32px; 
  border-radius:24px;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border:1px solid rgba(221, 221, 221, 0.8);
  box-shadow: var(--shadow-lg);
  max-width: 900px; 
  width: min(92%, 900px);
  animation: heroFadeIn 1s ease-out;
}

@keyframes heroFadeIn{
  from{ 
    opacity: 0; 
    transform: translateY(30px) scale(0.95); 
  }
  to{ 
    opacity: 1; 
    transform: translateY(0) scale(1); 
  }
}

.hero-overlay h1{ 
  margin:0 0 10px; 
  font-size: clamp(26px, 3.5vw, 42px); 
  background: linear-gradient(135deg, var(--brand), var(--accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.hero-overlay p{ 
  margin:0 0 16px; 
  color:#475569; 
  font-size: clamp(15px, 2vw, 18px);
  line-height: 1.7;
}

.hero-btn{
  display:inline-block; 
  text-decoration:none; 
  font-weight:700;
  color:#fff;
  background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 50%, var(--accent) 100%);
  background-size: 200% 200%;
  padding:14px 28px; 
  border-radius:14px;
  box-shadow: var(--brand-shadow-lg);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift{
  0%, 100%{ background-position: 0% 50%; }
  50%{ background-position: 100% 50%; }
}

.hero-btn::before{
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(255,255,255,0.2));
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.hero-btn:hover{
  transform: translateY(-2px) scale(1.05);
  box-shadow: var(--brand-shadow-lg), 0 12px 40px rgba(var(--brand-rgb), 0.3);
}

.hero-btn:hover::before{
  transform: translateX(100%);
}

.hero-btn:active{
  transform: translateY(0) scale(1.02);
}

.quick-filters{
  width:min(1200px,90%);
  margin:25px auto;
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  justify-content:center;
}

.quick-filters button{
  background:#fff;
  border:1px solid #dce1e9;
  padding:10px 18px;
  border-radius:var(--radius-sm);
  font-size:15px;
  font-weight: 500;
  cursor:pointer;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.quick-filters button::before{
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(139,92,246,0.1));
  opacity: 0;
  transition: opacity var(--transition);
}

.quick-filters button:hover{
  border-color:var(--brand);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.quick-filters button:hover::before{
  opacity: 1;
}

.quick-filters button:active{
  transform: translateY(0);
}

.filters{
  margin: 28px auto 10px; 
  width:min(1200px, 92%);
  background: #fff;
  border:1px solid #ddd; 
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  padding: 18px;
  transition: var(--transition);
}

.filters:hover{
  box-shadow: var(--shadow-lg);
}

#filterForm{
  display:flex; 
  flex-wrap:wrap; 
  gap: var(--gap); 
  justify-content:center; 
  align-items:center;
}

#filterForm select, #filterForm input{
  background: #f9fafc; 
  color: var(--text);
  border:1px solid #d8dde6; 
  border-radius:var(--radius-sm);
  padding:12px 14px; 
  min-width: 180px;
  outline:none; 
  transition: var(--transition);
  font-family: inherit;
  font-size: 15px;
}

#filterForm select:focus, #filterForm input:focus{
  border-color: var(--brand);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(var(--brand-rgb), 0.1), 
              0 0 6px rgba(var(--brand-rgb), 0.25);
  transform: translateY(-1px);
}

#filterForm select:hover, #filterForm input:hover{
  border-color: #a0aac2;
}

.btn{
  border:none; 
  cursor:pointer; 
  border-radius:var(--radius-sm); 
  padding:12px 18px; 
  font-weight:700;
  font-family: inherit;
  font-size: 15px;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.btn::after{
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.2);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.btn:hover::after{
  opacity: 1;
}

.btn.primary{
  color:#fff; 
  background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 50%, var(--accent) 100%);
  background-size: 200% 200%;
  box-shadow: var(--brand-shadow);
  animation: gradientShift 3s ease infinite;
}

.btn.primary:hover{
  transform: translateY(-2px);
  box-shadow: var(--brand-shadow-lg);
}

.btn.ghost{ 
  color:var(--text); 
  background: #f4f4f9; 
  border:1px solid #ccc; 
}

.btn.ghost:hover{
  background: #e8eaf0;
  border-color: #999;
}

.btn:active{
  transform: translateY(0) scale(0.98);
}

.grid{
  width:min(1200px, 92%); 
  margin: 22px auto 60px;
  display:grid; 
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.card{
  background: var(--panel);
  border: 1px solid #ddd;
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow);
  display:flex; 
  flex-direction:column;
  transition: var(--transition-slow);
  opacity:0; 
  transform:translateY(20px);
  animation: fadeInUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  will-change: transform, opacity;
}

@keyframes fadeInUp{
  to {
    opacity:1; 
    transform:translateY(0);
  }
}

.card:nth-child(1) { animation-delay: 0.05s; }
.card:nth-child(2) { animation-delay: 0.1s; }
.card:nth-child(3) { animation-delay: 0.15s; }
.card:nth-child(4) { animation-delay: 0.2s; }
.card:nth-child(5) { animation-delay: 0.25s; }
.card:nth-child(6) { animation-delay: 0.3s; }

.card:hover{
  transform: translateY(-10px) scale(1.02);
  border-color: #94a3b8;
  box-shadow: 
    0 0 0 1px rgba(var(--brand-rgb), 0.15),
    var(--brand-shadow-lg), 
    0 20px 50px rgba(59,130,246,.12);
  z-index: 10;
}

.media{ 
  position:relative;
  overflow: hidden;
}

.media img{ 
  width:100%; 
  aspect-ratio: 16/11; 
  object-fit:cover; 
  display:block;
  transition: transform var(--transition-slow);
}

.card:hover .media img{
  transform: scale(1.08);
}

.badge{
  position:absolute; 
  top:12px; 
  inset-inline-start:12px;
  background: linear-gradient(135deg, var(--brand) 0%, var(--accent) 100%);
  color:#fff; 
  font-weight:800; 
  padding:7px 12px;
  border-radius:10px;
  box-shadow: var(--shadow), var(--accent-shadow);
  font-size: 13px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  backdrop-filter: blur(4px);
  animation: badgePulse 2s ease-in-out infinite;
}

@keyframes badgePulse{
  0%, 100%{ transform: scale(1); }
  50%{ transform: scale(1.05); }
}

.body{ 
  padding: 16px; 
  display:flex; 
  flex-direction:column; 
  gap:11px;
  flex: 1;
}

.body h3{ 
  margin:0; 
  font-size: 18px; 
  color:var(--text);
  font-weight: 700;
  letter-spacing: -0.3px;
  line-height: 1.3;
  transition: color var(--transition);
}

.card:hover .body h3{
  color: var(--brand);
}

.body p{ 
  margin:0; 
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
}

.meta{
  display:flex; 
  align-items:center; 
  justify-content:space-between;
  color: #64748b;
  font-size: 14px;
  padding-top: 6px;
  border-top: 1px solid #e2e8f0;
  margin-top: auto;
}

.price{ 
  color:#10b981; 
  font-weight:900; 
  letter-spacing:.5px;
  font-size: 16px;
}

.city{ 
  color:#334155;
  font-weight: 500;
}

.btn.secondary{
  align-self:flex-start; 
  text-decoration:none; 
  color:var(--text); 
  font-weight:700;
  background:#f8f9fb; 
  border:1px solid #ccc; 
  padding:10px 16px; 
  border-radius:var(--radius-sm);
  font-size: 14px;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn.secondary:hover{ 
  border-color:#888; 
  background:#e8eaf2;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.btn.secondary:active{
  transform: translateY(0);
}

.footer{
  border-top:1px solid #e2e8f0;
  background: linear-gradient(180deg, #fafcfe 0%, #f0f4f9 100%);
  color: #64748b;
  text-align:center; 
  padding: 28px 10px;
  font-size: 14px;
}

@media (max-width: 900px){
  .navbar{ padding:12px 16px; }
  .brand{ left: 16px; }
  .links{ gap:8px; }
  .hero-overlay{ padding:24px; }
  #filterForm{ gap:12px; }
  .grid{ gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
}

@media (max-width: 600px){
  .hero{ min-height: 50vh; }
  .hero-overlay h1{ font-size: 24px; }
  .hero-btn{ padding: 12px 20px; }
  .filters{ padding: 14px; }
  #filterForm select, #filterForm input{ min-width: 140px; }
}
    body{
      background:#f4f6fa;
      font-family: "Cairo", sans-serif;
      margin:0; padding:20px;
    }

    .container{
      width: min(900px, 95%);
      margin:auto;
      background:#fff;
      padding:25px;
      border-radius:16px;
      box-shadow:0 4px 20px rgba(0,0,0,.08);
    }

    h2{ margin-top:0; }

    label{
      display:block;
      font-size:15px;
      margin-bottom:6px;
      font-weight:600;
    }

    input, select, textarea{
      width:100%;
      padding:12px;
      border:1px solid #ccc;
      border-radius:10px;
      margin-bottom:18px;
      font-size:15px;
      outline:none;
    }

    textarea{ resize:vertical; height:130px; }

    #map{
      height:300px;
      width:100%;
      border-radius:12px;
      margin-bottom:20px;
    }

    .btn{
      background:#2E8BC0;
      color:white;
      padding:14px;
      border:none;
      border-radius:12px;
      font-size:18px;
      cursor:pointer;
      width:100%;
      font-weight:700;
    }
  </style>
</head>

<body>
    <header class="navbar">
    <a class="brand" href="mainpage.php">
        <img class="logo" src="logo.png" alt="عقاري">
    </a>

    <nav class="links">
        <a href="mainpage.php">الرئيسية</a>
        <a href="buy.php">للبيع</a>
        <a href="rent.php">للإيجار</a>
        <a href="land.php">أراضي</a>
        <a class="active" href="add-property.php"> اضف عقارك هنا</a>
        <a  href="map.php">خريطة</a>
        <?php if(isset($_SESSION['role'])): ?>
    <a href="profile.php">صفحتي</a>
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


<div class="container">

  <h2>إضافة عقار جديد</h2>

  <form id="propertyForm" method="POST" action="" enctype="multipart/form-data">

    <label>نوع العقار</label>
    <select name="type">
      <option value="شقة للبيع">شقة للبيع</option>
      <option value="شقة للإيجار">شقة للإيجار</option>
      <option value="أرض للبيع">أرض للبيع</option>
    </select>

    <label>السعر</label>
    <input type="number" name="price" placeholder="مثال: 120000" required>
    <label>المحافظة</label>
    <select id="province" name="province" required>
        <option value="">اختر المحافظة</option>
    </select>

    <label>المدينة / القرية</label>
    <select id="city_select" name="city" required>
        <option value="">اختر المدينة / القرية</option>
    </select>


<label>العنوان (اختياري توضيحي)</label>
<input type="text" id="address" name="address" placeholder="مثال: قرب الدوار، شارع رئيسي">

    <label>المساحة بالمتر المربع</label>
<input type="number" name="size" placeholder="مثال: 150" required>


    <label>موقع العقار على الخريطة</label>
    <div id="map"></div>

    <input type="text" id="lat" name="lat" placeholder="Latitude" readonly required>
    <input type="text" id="lng" name="lng" placeholder="Longitude" readonly required>

    <label>صور العقار</label>
    <input type="file" name="images[]" multiple required>

    <label>الوصف</label>
    <textarea name="description" required></textarea>

    <label>اسم المالك</label>
    <input type="text" name="owner" required>

    <label>رقم الهاتف</label>
    <input type="text" name="phone" id="phone" placeholder="059XXXXXXX" required>

    <label>طريقة الدفع</label>
    <select name="payment" required>
      <option>كاش</option>
      <option>تقسيط</option>
      <option>بنك / قرض</option>
    </select>

    <label>رابط فيديو (اختياري)</label>
    <input type="text" name="video">

    <button class="btn" type="submit">حفظ العقار</button>

  </form>

</div>
<script>
const data = {

    "القدس": [
        "بيت حنينا", "شعفاط", "صور باهر", "الطور", "العيساوية", "أبوديس", "العيزرية",
        "السواحرة", "عناتا", "الرام"
    ],

    "رام الله والبيرة": [
        "البيرة", "رام الله", "بيتونيا", "عين عريك", "كوبر", "بيرزيت",
        "أبو شخيدم", "عطارة", "دير غسانة", "بير نبالا"
    ],

    "الخليل": [
        "الخليل", "حلحول", "دورا", "يطا", "ترقوميا", "الظاهرية",
        "السموع", "بيت أمر", "بيت كاحل", "صوريف"
    ],

    "نابلس": [
        "نابلس", "رفيديا", "عصيرة الشمالية", "عصيرة الجنوبية", "بيتا", "قبلان",
        "تل", "عينابوس", "حوارة", "جماعين"
    ],

    "بيت لحم": [
        "بيت جالا", "بيت ساحور", "الخضر", "الدهيشة", "تقوع", "العبيدية",
        "زعاترة", "نحالين", "وادي فوكين"
    ],

    "قلقيلية": [
        "قلقيلية", "عزون", "جيوس", "كفر ثلث", "سنيريا", "حبلة"
    ],

    "طولكرم": [
        "طولكرم", "عنبتا", "بلعا", "إكتابا", "ذنابة", "شوفة"
    ],

    "جنين": [
        "جنين", "اليامون", "قباطية", "عرابة", "كفر راعي", "فقوعة",
        "اليامون", "مثلث الشهداء"
    ],

    "سلفيت": [
        "سلفيت", "بديا", "كفر الديك", "قراوة بني حسان", "كفل حارس",
        "اسكاكا", "حارس"
    ],

    "طوباس": [
        "طوباس", "طمون", "عقابا", "تياسير", "بردلة"
    ],

    "أريحا والأغوار": [
        "أريحا", "العوجا", "الجفتلك", "مرج نعجة", "فصايل"
    ],

    "غزة": [
        "غزة", "تل الهوا", "الشجاعية", "الزيتون", "الرمال", "الصبرة"
    ],

    "الشمال": [
        "جباليا", "بيت لاهيا", "بيت حانون"
    ],

    "الوسطى": [
        "دير البلح", "البريج", "النصيرات", "المغازي"
    ],

    "خانيونس": [
        "خانيونس", "بني سهيلا", "عبسان", "القرارة"
    ],

    "رفح": [
        "رفح", "صور", "الشوكة", "تل السلطان"
    ]

};


const provinceSelect = document.getElementById("province");
const citySelect     = document.getElementById("city_select");

Object.keys(data).forEach(p => {
    const opt = document.createElement("option");
    opt.value = p;
    opt.textContent = p;
    provinceSelect.appendChild(opt);
});

provinceSelect.addEventListener("change", function(){
    const villages = data[this.value] || [];
    citySelect.innerHTML = '<option value="">اختر المدينة / القرية</option>';

    villages.forEach(v => {
        const opt = document.createElement("option");
        opt.value = v;
        opt.textContent = v;
        citySelect.appendChild(opt);
    });
});

</script>
<script>
  var map = L.map('map').setView([31.9028, 35.2034], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

  var marker;

  map.on('click', function(e){
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;

    document.getElementById("lat").value = lat;
    document.getElementById("lng").value = lng;

    if (marker) map.removeLayer(marker);

    marker = L.marker([lat, lng]).addTo(map);
  });

  document.getElementById("phone").addEventListener("input", function () {
    if (!this.value.startsWith("059")) {
      this.setCustomValidity("رقم الهاتف يجب أن يبدأ بـ 059");
    } else {
      this.setCustomValidity("");
    }
  });
</script>

</body>
</html>
