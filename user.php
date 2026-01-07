<?php
require "db.php";

if (!isset($_GET['id'])) {
    die("User not found");
}

$user_id = intval($_GET['id']);

// جلب بيانات المستخدم
$user = $conn->query("SELECT name, email, avatar, bio FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    die("User not found");
}

// جلب العقارات الخاصة بالمستخدم
$props = $conn->query("SELECT id, description, type, price, images FROM properties WHERE user_id = $user_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بروفايل <?= $user['name'] ?></title>
<style>
    /* ==== PROFILE PAGE WRAPPER ==== */
.profile-box{
    width:min(1100px, 90%);
    margin:30px auto;
    padding:25px;
    background:#fff;
    border-radius:18px;
    box-shadow:0 6px 20px rgba(0,0,0,.07);
}

/* ==== USER HEADER ==== */
.user-header{
    display:flex;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
}

.user-header img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #3b82f6;
    box-shadow:0 4px 15px rgba(59,130,246,0.25);
}

.user-header .info h2{
    margin:0;
    font-size:24px;
    font-weight:800;
}

.user-header .info p{
    margin:6px 0 0;
    color:#64748b;
    line-height:1.6;
}

/* ==== TITLE ==== */
.section-title{
    margin:30px 0 15px;
    font-size:22px;
    font-weight:800;
    color:#1e293b;
}

/* ==== GRID CARDS ==== */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(260px,1fr));
    gap:22px;
}

/* ==== PROPERTY CARD ==== */
.card{
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
    transition:.3s;
    display:flex;
    flex-direction:column;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 6px 22px rgba(0,0,0,.12);
}

.card img{
    width:100%;
    height:190px;
    object-fit:cover;
}

.card .body{
    padding:15px;
    flex:1;
}

.card h3{
    font-size:17px;
    margin:0 0 6px;
    font-weight:700;
    color:#1e293b;
}

.card .meta{
    display:flex;
    justify-content:space-between;
    margin-top:8px;
    font-size:14px;
    color:#475569;
}

.price{
    color:#10b981;
    font-size:18px;
    font-weight:900;
}

.btn{
    margin-top:12px;
    display:inline-block;
    padding:10px 14px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    text-align:center;
    transition:.3s;
}

.btn:hover{
    opacity:.85;
}

@media (max-width:768px){
    .user-header{
        justify-content:center;
        text-align:center;
    }

    .user-header img{
        width:110px;
        height:110px;
    }
}

    </style>

</head>

<body>

<div class="profile-box">

    <div style="display:flex; align-items:center; gap:20px;">
        <img src="<?= $user['avatar'] ?: 'default-avatar.png' ?>" 
             style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #ddd;">
        
        <div>
            <h2>👤 <?= htmlspecialchars($user['name']) ?></h2>
            <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        </div>
    </div>

    <hr>

    <h3>عقارات المعروضـة من قبل <?= $user['name'] ?>:</h3>

    <?php if ($props->num_rows == 0): ?>
        <p>لا يوجد عقارات لهذا المستخدم.</p>
    <?php else: ?>

        <div class="grid">
            <?php while($p = $props->fetch_assoc()): 
                $imgs = json_decode($p['images'], true);
                $img = $imgs[0] ?? "uploads/default.jpg";
            ?>
                <div class="card">
                    <img src="<?= $img ?>" class="media-image">
                    <div class="body">
                        <h3><?= $p['description'] ?></h3>
                        <div class="meta">
                            <span class="price">₪ <?= number_format($p['price']) ?></span>
                            <span><?= $p['type'] ?></span>
                        </div>
                        <a class="btn secondary" href="property.php?id=<?= $p['id'] ?>">عرض التفاصيل</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
