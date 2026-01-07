<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "db.php";

$login_error    = null;
$signup_error   = null;
$signup_success = null;

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $login_error = "يرجى إدخال البريد وكلمة المرور";
    } else {

        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            die("SQL ERROR (login select): " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashed, $role);
            $stmt->fetch();
        /** @var string $hashed */


            if (password_verify($pass, $hashed)) {
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['role']      = $role;

                $conn->query("UPDATE users SET last_login = NOW() WHERE id = $id");

                header("Location: mainpage.php");
                exit;
            } else {
                $login_error = "كلمة المرور غير صحيحة";
            }
        }
         else {
            $login_error = "البريد غير موجود";
        }

        $stmt->close();
    }
}

if (isset($_POST['signup'])) {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $pass === '') {
        $signup_error = "يرجى تعبئة جميع الحقول";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$check) {
            die("SQL ERROR (signup check): " . $conn->error);
        }

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $signup_error = "هذا البريد مستخدم مسبقاً!";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if (!$stmt) {
                die("SQL ERROR (signup insert): " . $conn->error);
            }

            $stmt->bind_param("sss", $name, $email, $hashed);
            $stmt->execute();

            $signup_success = "تم إنشاء الحساب! يمكنك تسجيل الدخول الآن.";
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تسجيل الدخول | عقاري</title>
    <!-- IMPORTANT: المسار الصحيح -->
    <style>

        /* ألوان مشروع عقاري */
:root{
  --bg: #f7f9fc;
  --panel: #ffffff;
  --brand: #2E8BC0;
  --brand-2: #0C6E8F;
  --text: #1b1d27;
  --muted: #667085;
  --shadow: 0 4px 18px rgba(0,0,0,.08);
  --radius: 18px;
}

body {
    margin: 0;
    padding: 0;
    background: var(--bg);
    font-family: "Cairo", sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* الحاوية */
.auth-container {
    width: 900px;
    height: 560px;
    background: var(--panel);
    box-shadow: var(--shadow);
    border-radius: var(--radius);
    position: relative;
    overflow: hidden;
}

/* اللوحات */
.auth-panel {
    position: absolute;
    top: 0;
    width: 50%;
    height: 100%;
    color: #fff;
    background: linear-gradient(90deg, var(--brand), var(--brand-2));
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 14px;
    text-align: center;
    transition: .6s;
}

.left-panel { right: 50%; }
.right-panel { right: -100%; }

.form {
    position: absolute;
    top: 0;
    width: 50%;
    height: 100%;
    background: var(--panel);
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    justify-content: center;
    transition: .6s;
}

.login-form { right: 0; }
.signup-form { right: -100%; }

input {
    padding: 14px;
    border-radius: 12px;
    border: 1px solid #d7dce2;
    font-size: 15px;
}

.btn {
    padding: 12px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    font-weight: 700;
    transition: .25s;
}

.btn.primary {
    background: linear-gradient(90deg, var(--brand), var(--brand-2));
    color: #fff;
}

.btn.ghost {
    background: transparent;
    border: 2px solid #fff;
    color: #fff;
}

.socials {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.socials div {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-container.active .left-panel { right: -100%; }
.auth-container.active .right-panel { right: 50%; }

.auth-container.active .login-form { right: 100%; }
.auth-container.active .signup-form { right: 0; }


.forgot {
    font-size: 14px;
    color: var(--brand);
    text-decoration: none;
    margin-right: 5px;
    text-align: right;
    display: block;
    transition: .2s;
}

.forgot:hover {
    color: var(--brand-2);
    text-decoration: underline;
}
.form-logo{
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.form-logo img{
    width: 80px;
    height: 80px;
    object-fit: contain;
    background: #fff;
    border-radius: 16px;
    padding: 8px;
    box-shadow: 0 6px 18px rgba(0,0,0,.12);
}


    </style>



</head>

<body>

<div class="auth-container">

    <div class="auth-panel left-panel">
        
        <h2>جديد هنا؟</h2>
        <h3>عقاري وجهتك الوحيدة</h3>
        <p>أنشئ حسابًا جديدًا للبدء معنا خلال ثوانٍ!</p>
        <button id="showSignup" class="btn ghost">إنشاء حساب</button>
    </div>

    <div class="auth-panel right-panel">
     
        <h2>رجعت؟</h2>
         <h3>عقاري وجهتك الوحيدة</h3>
        <p>سجّل دخولك الآن للمتابعة.</p>
        <button id="showLogin" class="btn ghost">تسجيل دخول</button>
    </div>

   <form method="POST" class="form login-form">

    <div class="form-logo">
        <img src="logo.png" alt="عقاري">
    </div>

    <h2>تسجيل الدخول</h2>

    <?php if ($login_error): ?>
        <div class="error-box"><?= $login_error ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="البريد الإلكتروني" />
    <input type="password" name="password" placeholder="كلمة المرور" />

    <a href="forgot.php" class="forgot">نسيت كلمة المرور؟</a>

    <button type="submit" name="login" class="btn primary">دخول</button>
</form>


    <form method="POST" class="form signup-form">

    <div class="form-logo">
        <img src="logo.png" alt="عقاري">
    </div>

    <h2>إنشاء حساب</h2>

    <?php if ($signup_error): ?>
        <div class="error-box"><?= $signup_error ?></div>
    <?php endif; ?>

    <?php if ($signup_success): ?>
        <div class="success-box"><?= $signup_success ?></div>
    <?php endif; ?>

    <input type="text" name="name" placeholder="اسم المستخدم" />
    <input type="email" name="email" placeholder="البريد الإلكتروني" />
    <input type="password" name="password" placeholder="كلمة المرور" />

    <button type="submit" name="signup" class="btn primary">إنشاء حساب</button>
</form>


</div>

<script >const container = document.querySelector(".auth-container");
const goSignup = document.getElementById("showSignup");
const goLogin = document.getElementById("showLogin");

goSignup.onclick = () => container.classList.add("active");
goLogin.onclick = () => container.classList.remove("active");
</script>
</body>
</html>
