<?php
session_start();
require "db.php";

$msg = "";

if (isset($_POST['reset'])) {
    $email = trim($_POST['email']);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 1) {

        $token = bin2hex(random_bytes(32));

        $stmt = $conn->prepare("
            UPDATE users
            SET reset_token = ?, reset_expire = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
            WHERE email = ?
        ");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        $reset_link = "http://localhost/aqari/reset.php?token=".$token;

        $msg = "<span class='success'>تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني.</span>";
    } else {
        $msg = "<span class='error'>هذا البريد غير موجود!</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>عقاري | استعادة كلمة المرور</title>

<style>
:root{
    --bg: #f7f9fc;
    --panel: #ffffff;
    --brand: #3b82f6;
    --brand-2: #1d4ed8;
    --text: #0f172a;
    --muted: #64748b;
    --radius: 16px;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
}

*{
    box-sizing: border-box;
}

html, body{
    height: 100%;
}

body{
    margin:0;
    font-family: "Cairo", sans-serif;
    background: linear-gradient(180deg, #fafcff, #f1f5f9);
    color: var(--text);
    display: flex;
    flex-direction: column;
}

.page{
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.auth-card{
    width: 420px;
    max-width: 100%;
    background: var(--panel);
    padding: 32px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-align: center;
}

.auth-card h1{
    margin: 0 0 8px;
    font-size: 26px;
}

.auth-card p{
    margin: 0 0 22px;
    font-size: 15px;
    color: var(--muted);
}

.field{
    text-align: right;
    margin-bottom: 16px;
}

.field label{
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
}

.field input{
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #dce1e9;
    font-size: 15px;
}

.field input:focus{
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(59,130,246,.2);
}

.btn{
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.btn:hover{
    opacity: .9;
}

.auth-footer-text{
    margin-top: 16px;
    font-size: 14px;
}

.auth-footer-text a{
    color: var(--brand);
    text-decoration: none;
    font-weight: 600;
}

#msg{
    margin-top: 14px;
    font-size: 14px;
}

.success{ color: #16a34a; }
.error{ color: #dc2626; }

.footer{
    text-align: center;
    padding: 18px 10px;
    font-size: 14px;
    color: var(--muted);
    border-top: 1px solid #e5e7eb;
    background: #fff;
}
</style>
</head>

<body>

<div class="page">
    <section class="auth-card">

        <h1>استعادة كلمة المرور</h1>
        <p>أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين</p>

        <form method="POST">
            <div class="field">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" name="email" id="email" placeholder="example@mail.com" required>
            </div>

            <button type="submit" name="reset" class="btn">
                إرسال رابط إعادة التعيين
            </button>

            <p class="auth-footer-text">
                تذكّرت كلمة المرور؟
                <a href="login.php">العودة لتسجيل الدخول</a>
            </p>

            <div id="msg"><?= $msg ?></div>
        </form>

    </section>
</div>

<footer class="footer">
    © 2025 جميع الحقوق محفوظة — عقاري
</footer>

</body>
</html>
