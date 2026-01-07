<?php
require "db.php";

if (!isset($_GET['id'])) {
    die("No property ID");
}

$id = intval($_GET['id']);
$q  = $conn->query("SELECT * FROM properties WHERE id = $id");
$prop = $q->fetch_assoc();

if (!$prop) die("Property not found");

$images = json_decode($prop['images'], true);

$province = $prop['province'] ?? "";
$city     = $prop['city'] ?? "";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل العقار رقم <?= $id ?></title>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
body{
    background:#f4f6fa;
    font-family:"Cairo",sans-serif;
    padding:20px;
}
.container{
    width:min(900px,95%);
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,.08);
}
input,select,textarea{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border-radius:10px;
    border:1px solid #ccc;
}
.btn{
    background:#2E8BC0;
    color:#fff;
    padding:14px;
    width:100%;
    border:none;
    border-radius:12px;
    font-size:18px;
    cursor:pointer;
}
#map{
    height:300px;
    margin-bottom:20px;
    border-radius:12px;
}
.img-preview{
    width:120px;
    height:100px;
    border-radius:8px;
    object-fit:cover;
    margin-left:8px;
}
</style>
</head>

<body>

<div class="container">
<h2>تعديل العقار رقم <?= $id ?></h2>

<form method="POST" action="update-property.php" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $id ?>">

    <label>نوع العقار</label>
    <select name="type">
        <option <?= $prop['type']=="شقة للبيع"?"selected":"" ?>>شقة للبيع</option>
        <option <?= $prop['type']=="شقة للإيجار"?"selected":"" ?>>شقة للإيجار</option>
        <option <?= $prop['type']=="أرض للبيع"?"selected":"" ?>>أرض للبيع</option>
    </select>

    <label>السعر</label>
    <input type="number" name="price" value="<?= $prop['price'] ?>">

    <label>المحافظة</label>
    <select id="province" name="province" required>
        <option value="">اختر المحافظة</option>
    </select>

    <label>المدينة / القرية</label>
    <select id="city_select" name="city" required>
        <option value="">اختر المدينة / القرية</option>
    </select>

    <label>العنوان (اختياري توضيحي)</label>
    <input type="text" id="address" name="address" value="<?= $prop['address'] ?>">

    <label>المساحة بالمتر المربع</label>
    <input type="number" name="size" value="<?= $prop['size'] ?>" required>

    <label>موقع العقار على الخريطة</label>
    <div id="map"></div>

    <input type="text" id="lat" name="lat" value="<?= $prop['lat'] ?>" readonly>
    <input type="text" id="lng" name="lng" value="<?= $prop['lng'] ?>" readonly>

    <label>الصور الحالية</label><br>
    <?php foreach($images as $img): ?>
        <img src="<?= $img ?>" class="img-preview">
    <?php endforeach; ?>

    <br><br>
    <label>تغيير الصور (اختياري)</label>
    <input type="file" name="images[]" multiple>

    <label>الوصف</label>
    <textarea name="description"><?= $prop['description'] ?></textarea>

    <label>المالك</label>
    <input type="text" name="owner" value="<?= $prop['owner'] ?>">

    <label>هاتف</label>
    <input type="text" name="phone" value="<?= $prop['phone'] ?>">

    <label>طريقة الدفع</label>
    <select name="payment">
        <option <?= $prop['payment']=="كاش"?"selected":"" ?>>كاش</option>
        <option <?= $prop['payment']=="تقسيط"?"selected":"" ?>>تقسيط</option>
        <option <?= $prop['payment']=="بنك / قرض"?"selected":"" ?>>بنك / قرض</option>
    </select>

    <label>رابط فيديو</label>
    <input type="text" name="video" value="<?= $prop['video'] ?>">

    <button class="btn">تحديث العقار</button>
</form>

</div>

<script>
var map = L.map('map').setView([<?= $prop['lat'] ?>, <?= $prop['lng'] ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

var marker = L.marker([<?= $prop['lat'] ?>, <?= $prop['lng'] ?>]).addTo(map);

map.on("click", function(e){
    marker.setLatLng(e.latlng);
    document.getElementById("lat").value = e.latlng.lat;
    document.getElementById("lng").value = e.latlng.lng;
});
</script>

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

let savedProvince = "<?= $province ?>";
let savedCity     = "<?= $city ?>";

if (savedProvince !== "") {
    provinceSelect.value = savedProvince;
    provinceSelect.dispatchEvent(new Event("change"));

    setTimeout(()=>{
        citySelect.value = savedCity;
    }, 150);
}
</script>

</body>
</html>
