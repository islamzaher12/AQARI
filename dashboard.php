<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: mainpage.php");
    exit;
}


function getCount($sql, $conn){
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_row();
        return (int)$row[0];
    }
    return 0;
}

$count_props = getCount("SELECT COUNT(*) FROM properties", $conn);
$count_sale  = getCount("SELECT COUNT(*) FROM properties WHERE type='شقة للبيع'", $conn);
$count_rent  = getCount("SELECT COUNT(*) FROM properties WHERE type='شقة للإيجار'", $conn);
$count_land  = getCount("SELECT COUNT(*) FROM properties WHERE type='أرض للبيع'", $conn);
$count_users = getCount("SELECT COUNT(*) FROM users", $conn);

$users = $conn->query("SELECT id, name, email, role, last_login FROM users ORDER BY id DESC");

$latest_props = $conn->query("SELECT id, description, type, price FROM properties ORDER BY id DESC LIMIT 50");

$latest_users = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 3");


$chart_types_labels = ["شقق للبيع", "شقق للإيجار", "أراضي"];
$chart_types_data   = [$count_sale, $count_rent, $count_land];

$city_labels = [];
$city_counts = [];
$city_q = $conn->query("SELECT address AS city, COUNT(*) AS total
                        FROM properties
                        GROUP BY address
                        ORDER BY total DESC");
if ($city_q) {
    while ($row = $city_q->fetch_assoc()) {
        $city_labels[] = $row['city'] ?: "غير محدد";
        $city_counts[] = (int)$row['total'];
    }
}

$month_labels = [];
$month_counts = [];
$month_q = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
    FROM properties
    GROUP BY ym
    ORDER BY ym
");
if ($month_q) {
    while ($row = $month_q->fetch_assoc()) {
        $month_labels[] = $row['ym'];
        $month_counts[] = (int)$row['total'];
    }
}

$alert_msg  = $_GET['msg']  ?? '';
$alert_type = $_GET['type'] ?? 'success'; 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم | عقاري</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root{
            --bg: #f1f5f9;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --brand: #3b82f6;
            --brand-2: #1d4ed8;
            --danger: #dc2626;
            --ok: #16a34a;
            --shadow: 0 6px 18px rgba(15,23,42,0.10);
            --radius: 16px;
            --radius-sm: 10px;
            --transition: 0.25s ease;
        }

        *{
            box-sizing:border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body{
            font-family: "Cairo", system-ui, Tahoma, Arial, sans-serif;
            background: radial-gradient(circle at 0 0,#e5edff 0,transparent 50%),
                        radial-gradient(circle at 100% 100%,#fee2e2 0,transparent 50%),
                        #f8fafc;
            margin:0;
            color:var(--text);
        }

        .navbar {
            position: sticky; top:0; inset-inline:0;
            display:flex; align-items:center; justify-content:center;
            padding:14px 26px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.35);
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
            background: linear-gradient(90deg, transparent, rgba(59,130,246,0.7) 50%, transparent);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .navbar:hover::after{ opacity: 1; }

        .brand{
            position:absolute;
            left:26px;
            top:50%;
            transform:translateY(-50%);
            display:flex;
            align-items:center;
            gap:10px;
        }

        .logo{
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: contain;
            background-color: #fff;
            border: 1px solid #e2e8f0;
            padding: 3px;
        }

        .links{
            display:flex;
            align-items:center;
            gap:14px;
            flex-wrap:wrap;
        }

        .links a{
            color:var(--muted);
            text-decoration:none;
            padding:.5rem .8rem;
            border-radius:var(--radius-sm);
            transition: var(--transition);
            position: relative;
            font-weight: 500;
            font-size:14px;
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

        .links a.active {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white !important;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(59,130,246,0.45);
            transform: translateY(-2px);
        }

        .dashboard{
            width:min(1200px, 95%);
            margin:24px auto 40px;
        }

        h1{
            text-align:center;
            margin-bottom:24px;
            font-size:28px;
            letter-spacing:-0.5px;
        }

        h1 span{
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }

        .stats{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap:16px;
            margin-bottom:25px;
        }

        .card{
            background:var(--panel);
            padding:16px 18px;
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            position:relative;
            overflow:hidden;
        }

        .card::after{
            content:"";
            position:absolute;
            inset-inline-end:-40px;
            bottom:-40px;
            width:110px;
            height:110px;
            border-radius:50%;
            background: radial-gradient(circle, rgba(59,130,246,0.18), transparent 55%);
        }

        .card h3{
            margin:0 0 6px 0;
            font-size:15px;
            color:#64748b;
        }

        .card span{
            font-size:26px;
            font-weight:800;
            color:#0f172a;
        }

        .card small{
            display:block;
            margin-top:4px;
            font-size:12px;
            color:#94a3b8;
        }

        .section-box{
            margin-top:20px;
            background:var(--panel);
            padding:18px 18px 22px;
            border-radius:var(--radius);
            box-shadow:var(--shadow);
        }

        .section-box h2{
            margin-top:0;
            font-size:18px;
            margin-bottom:12px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            font-size:14px;
        }

        table th, table td{
            padding:10px 8px;
            text-align:right;
            border-bottom:1px solid #e2e8f0;
        }

        table thead th{
            background:#f8fafc;
            font-weight:600;
            color:#475569;
        }

        tr:hover td{
            background:#f9fafb;
        }

        .btn-action{
            padding:6px 10px;
            border-radius:8px;
            border:none;
            font-size:12px;
            cursor:pointer;
            font-weight:600;
        }

        .btn-edit{
            background:#0ea5e9;
            color:#fff;
        }
        .btn-edit:hover{ background:#0284c7; }

        .btn-delete{
            background:#dc2626;
            color:#fff;
        }
        .btn-delete:hover{ background:#b91c1c; }

        .btn-role{
            background:#6366f1;
            color:#fff;
            margin-inline-start:4px;
        }
        .btn-role.demote{
            background:#6b7280;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            padding:3px 8px;
            border-radius:999px;
            font-size:11px;
            font-weight:600;
            gap:4px;
        }

        .badge-admin{
            background:rgba(59,130,246,0.1);
            color:#1d4ed8;
        }

        .badge-user{
            background:#f1f5f9;
            color:#475569;
        }

        .badge-online{
            background:rgba(34,197,94,0.12);
            color:#15803d;
        }

        .badge-offline{
            background:#fef2f2;
            color:#b91c1c;
        }

        .charts{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap:18px;
        }

        .chart-card{
            background:#fff;
            padding:18px;
            border-radius:var(--radius);
            box-shadow:var(--shadow);
        }

        .chart-card h3{
            margin-top:0;
            font-size:15px;
            margin-bottom:8px;
        }

        .alert{
            position:fixed;
            top:20px;
            left:50%;
            transform:translateX(-50%);
            padding:10px 16px;
            border-radius:999px;
            color:#fff;
            font-size:13px;
            box-shadow:0 4px 16px rgba(0,0,0,0.25);
            z-index:9999;
            display:none;
        }
        .alert.success{ background:#16a34a; }
        .alert.error{ background:#dc2626; }

        @media (max-width: 768px){
            body{ padding:0; }
            .dashboard{ margin-top:16px; }
            .brand{ left:16px; }
            .navbar{ padding-inline:14px; }
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
    <a href="profile.php">صفحتي</a>
<?php endif; ?>

      <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a class="active" href="dashboard.php">لوحة التحكم</a>
      <?php endif; ?>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="favorites.php">المفضلة ❤</a>
      <?php endif; ?>

      <a href="whous.php">من نحن</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">تسجيل خروج</a>
      <?php else: ?>
        <a href="login.php">تسجيل دخول</a>
      <?php endif; ?>
  </nav>
</header>

<div id="alertBox" class="alert"></div>

<div class="dashboard">

    <h1>لوحة تحكم <span>عقاري</span></h1>

    <div class="stats">
        <div class="card">
            <h3>إجمالي العقارات</h3>
            <span><?= $count_props ?></span>
            <small>كل الأنواع في النظام</small>
        </div>

        <div class="card">
            <h3>شقق للبيع</h3>
            <span><?= $count_sale ?></span>
            <small>عقارات متاحة للبيع</small>
        </div>

        <div class="card">
            <h3>شقق للإيجار</h3>
            <span><?= $count_rent ?></span>
            <small>معروضة للإيجار</small>
        </div>

        <div class="card">
            <h3>أراضي</h3>
            <span><?= $count_land ?></span>
            <small>قطع الأراضي في النظام</small>
        </div>

        <div class="card">
            <h3>عدد المستخدمين</h3>
            <span><?= $count_users ?></span>
            <small>مستخدمين مسجّلين</small>
        </div>
    </div>

    <div class="charts section-box" style="margin-top:10px;">
        <div class="chart-card">
            <h3>نسبة العقارات حسب النوع</h3>
            <canvas id="typesChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>عدد العقارات في كل مدينة</h3>
            <canvas id="citiesChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>عدد العقارات المضافة لكل شهر</h3>
            <canvas id="monthsChart"></canvas>
        </div>
    </div>

    <div class="section-box">
        <h2>آخر العقارات المضافة</h2>
        <table id="propsTable" class="display">
            <thead>
            <tr>
                <th>ID</th>
                <th>العنوان / الوصف</th>
                <th>النوع</th>
                <th>السعر (₪)</th>
                <th>إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!$latest_props) {
                echo "<tr><td colspan='5' style='text-align:center;color:red;'>خطأ في جملة SQL: " . $conn->error . "</td></tr>";
            } elseif ($latest_props->num_rows == 0) {
                echo "<tr><td colspan='5' style='text-align:center;'>لا يوجد عقارات</td></tr>";
            } else {
                while($row = $latest_props->fetch_assoc()):
            ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['type']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td>
                        <button class="btn-action btn-edit"
                                onclick="window.location='edit-property.php?id=<?= $row['id'] ?>'">
                            تعديل
                        </button>
                        <button class="btn-action btn-delete btn-delete-prop" data-id="<?= $row['id'] ?>">
                            حذف
                        </button>
                    </td>
                </tr>
            <?php
                endwhile;
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="section-box">
        <h2>المستخدمين</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>الاسم</th>
                <th>الإيميل</th>
                <th>الدور</th>
                <th>الحالة</th>
                <th>آخر تسجيل دخول</th>
                <th>إجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($users && $users->num_rows > 0): ?>
                <?php while($u = $users->fetch_assoc()):
                    $role   = $u['role'] ?? 'user';
                    $isAdmin = ($role === 'admin');

                    $last_login = $u['last_login'] ?? null;
                    $active_badge = '';
                    if ($last_login) {
                        $ts = strtotime($last_login);
                        if ($ts && (time() - $ts) <= 600) { 
                            $active_badge = '<span class="badge badge-online">نشِط الآن</span>';
                        } else {
                            $active_badge = '<span class="badge badge-offline">غير نشِط</span>';
                        }
                    } else {
                        $active_badge = '<span class="badge badge-offline">غير معروف</span>';
                    }
                ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php if($isAdmin): ?>
                            <span class="badge badge-admin">أدمن</span>
                        <?php else: ?>
                            <span class="badge badge-user">مستخدم</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $active_badge ?></td>
                    <td><?= htmlspecialchars($last_login ?? '-') ?></td>
                    <td>
                        <?php if ($_SESSION['user_id'] != $u['id']): ?>
                            <button onclick="deleteUser(<?= $u['id'] ?>)" class="btn-action btn-delete">حذف</button>
                        <?php endif; ?>


                        <?php if ($_SESSION['user_id'] != $u['id']): ?>

    
                            <?php if ($isAdmin): ?>
      
                                <button class="btn-action btn-role demote"
                onclick="changeRole(<?= $u['id'] ?>, 'user')">
            إرجاع لمستخدم
        </button>
    <?php else: ?>
        <button class="btn-action btn-role"
                onclick="changeRole(<?= $u['id'] ?>, 'admin')">
            ترقية لأدمن
        </button>
    <?php endif; ?>

<?php endif; ?>

                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">لا يوجد مستخدمين</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section-box" style="margin-top:20px;">
        <h2>أحدث المستخدمين المسجلين</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($latest_users && $latest_users->num_rows > 0):
                while ($u = $latest_users->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                </tr>
            <?php
                endwhile;
            else:
                echo "<tr><td colspan='3' style='text-align:center;'>لا يوجد مستخدمين بعد</td></tr>";
            endif;
            ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function showAlert(message, type){
        const box = document.getElementById('alertBox');
        box.textContent = message;
        box.className = 'alert ' + (type === 'error' ? 'error' : 'success');
        box.style.display = 'block';
        setTimeout(() => {
            box.style.display = 'none';
        }, 3000);
    }

    <?php if(!empty($alert_msg)): ?>
    showAlert("<?= htmlspecialchars($alert_msg) ?>", "<?= $alert_type === 'error' ? 'error' : 'success' ?>");
    <?php endif; ?>

    $(document).ready(function () {
        $('#propsTable').DataTable({
            pageLength: 7,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
            }
        });
    });

    const typesLabels = <?= json_encode($chart_types_labels, JSON_UNESCAPED_UNICODE) ?>;
    const typesData   = <?= json_encode($chart_types_data,   JSON_UNESCAPED_UNICODE) ?>;

    const cityLabels  = <?= json_encode($city_labels,  JSON_UNESCAPED_UNICODE) ?>;
    const cityData    = <?= json_encode($city_counts, JSON_UNESCAPED_UNICODE) ?>;

    const monthLabels = <?= json_encode($month_labels, JSON_UNESCAPED_UNICODE) ?>;
    const monthData   = <?= json_encode($month_counts, JSON_UNESCAPED_UNICODE) ?>;

    new Chart(document.getElementById('typesChart'), {
        type: 'pie',
        data: {
            labels: typesLabels,
            datasets: [{
                data: typesData
            }]
        }
    });

    new Chart(document.getElementById('citiesChart'), {
        type: 'bar',
        data: {
            labels: cityLabels,
            datasets: [{
                label: 'عدد العقارات',
                data: cityData
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('monthsChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'عدد العقارات',
                data: monthData,
                tension: 0.3
            }]
        }
    });

    $(document).on('click', '.btn-delete-prop', function () {
        const id = $(this).data('id');
        if (!confirm('هل أنت متأكد من حذف هذا العقار؟')) return;

        $.post('delete.php', {id: id}, function (res) {
            res = res.trim();
            if (res === 'OK' || res === 'success') {
                $('#row-' + id).fadeOut(300, function(){ $(this).remove(); });
                showAlert('تم حذف العقار بنجاح', 'success');
            } else {
                showAlert(res || 'حدث خطأ أثناء الحذف', 'error');
            }
        }).fail(function () {
            showAlert('خطأ في الاتصال بالسيرفر', 'error');
        });
    });

function deleteUser(id) {
    if (!confirm("هل تريد حذف هذا المستخدم نهائياً؟")) return;

    fetch("remove_admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            alert("تم حذف المستخدم");
            location.reload();
        } else if (data.trim() === "forbidden") {
            alert("لا يمكن حذف حساب الأدمن!");
        } else {
            alert("حدث خطأ أثناء الحذف");
        }
    });
}

 function changeRole(id, role) {
    fetch('change_role.php', {
        method: 'POST',
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "id=" + id + "&role=" + role
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "OK") {
            alert("تم تحديث الدور بنجاح");
            location.reload();
        } else {
            alert("خطأ: " + data);
        }
    });
}

</script>

</body>
</html>
