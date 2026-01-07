<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور</title>

    <style>
        body{
            background:#f8fafc;
            font-family:"Cairo", sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .auth-card{
            width:400px;
            padding:30px;
            background:#fff;
            border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
        }
        h1{
            text-align:center;
            margin-bottom:10px;
            color:#1e293b;
        }
        .field{
            margin-bottom:15px;
        }
        input{
            width:100%;
            padding:12px;
            border:1px solid #d1d5db;
            border-radius:12px;
            font-size:15px;
        }
        .btn{
            width:100%;
            padding:12px;
            border:none;
            border-radius:12px;
            background:#3b82f6;
            color:white;
            font-weight:bold;
            cursor:pointer;
        }
        #msg{
            margin-top:15px;
            text-align:center;
        }
    </style>

</head>
<body>

<div class="auth-card">

    <h1>إعادة تعيين كلمة المرور</h1>

    <form method="POST">

        <div class="field">
            <label>كلمة المرور الجديدة</label>
            <input type="password" name="password" required placeholder="أدخل كلمة المرور الجديدة">
        </div>

        <button type="submit" class="btn" name="reset_pass">تغيير كلمة المرور</button>

        <p id="msg"><?= $msg ?></p>

    </form>
</div>

</body>
</html>
