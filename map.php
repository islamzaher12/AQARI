<?php
require "db.php";
 session_start();

// جلب كل العقارات مع الإحداثيات
$result = $conn->query("SELECT id, type, price, address, lat, lng, images FROM properties");
$properties = [];

while ($row = $result->fetch_assoc()) {
    $imgs = json_decode($row['images'], true);
    $firstImg = $imgs[0] ?? "uploads/default.jpg";

    $properties[] = [
        "id"    => $row['id'],
        "type"  => $row['type'],
        "price" => $row['price'],
        "address" => $row['address'],
        "lat"   => $row['lat'],
        "lng"   => $row['lng'],
        "img"   => $firstImg
    ];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>خريطة العقارات</title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<style>
    :root{
  --bg: #f8fafd;
  --panel: #ffffff;
  --panel-2: #f0f4f8;

  --text: #0f172a;
  --muted: #64748b;

  /* Vibrant Blue Gradient */
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

/* NAVBAR */
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

/* اللوغو يسار */
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

/* HERO SECTION */
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

/* QUICK FILTERS */
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

/* FILTER BAR */
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

/* BUTTONS */
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
body { margin:0; font-family: "Cairo", sans-serif; background:#f4f6fa; }
#map { height: calc(100vh - 70px); width: 100%; }

.navbar{
    background:#fff;
    padding:14px;
    display:flex;
    justify-content:center;
    gap:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
    font-size:18px;
}
.navbar a{
    text-decoration:none;
    color:#333;
    padding:8px 14px;
}
.navbar a.active{
    background:#3b82f6;
    color:white;
    border-radius:8px;
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
        <a href="add-property.php">اضف</a>
        <a class="active" href="map.php">خريطة</a> 
      <?php if(isset($_SESSION['role'])): ?>
    <a href="profile.php">صفحتي</a>
<?php endif; ?>
               <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="dashboard.php">لوحة التحكم</a>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <a  href="favorites.php">المفضلة ❤</a>
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


<div id="map"></div>

<script>
var map = L.map('map').setView([32.00, 35.30], 10);


L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19
}).addTo(map);

var properties = <?= json_encode($properties, JSON_UNESCAPED_UNICODE); ?>;

var markers = L.markerClusterGroup();

properties.forEach(p => {
    if (!p.lat || !p.lng) return;

    var marker = L.marker([p.lat, p.lng]);

    marker.bindPopup(`
        <b>${p.type}</b><br>
        السعر: ${p.price} شيكل<br>
        <small>${p.address}</small><br><br>
        <img src="${p.img}" width="180" style="border-radius:8px;"><br><br>
        <a href="property.php?id=${p.id}" 
           style="padding:8px 12px; background:#3b82f6; color:white; border-radius:6px; text-decoration:none;">
           عرض التفاصيل
        </a>
    `);

    markers.addLayer(marker);
});

map.addLayer(markers);
</script>

</body>
</html>
