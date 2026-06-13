<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

startSess();

/*
|--------------------------------------------------------------------------
| ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if(isset($_SESSION['admin'])){
    header("Location: /admin/admin-dashboard.php");
    exit;
}

$error = '';

/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | FIND ADMIN
    |--------------------------------------------------------------------------
    */

    $stmt = db()->prepare("
        SELECT *
        FROM admins
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | LOGIN CHECK
    |--------------------------------------------------------------------------
    */

    if($admin && md5($password) === $admin['password']){

        $_SESSION['admin'] = [

            'id'    => $admin['id'],
            'email' => $admin['email']

        ];

        header("Location: /admin/admin-dashboard.php");
        exit;

    } else {

        $error = "Invalid admin credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Admin Login - WorkupX</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#070b12;
    color:white;
    font-family:'Inter',sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    overflow:hidden;
}

body::before{
    content:'';
    position:fixed;
    inset:0;
    background:
    radial-gradient(circle at top left, rgba(240,185,11,.08), transparent 30%),
    radial-gradient(circle at bottom right, rgba(14,203,129,.05), transparent 30%);
}

.login-box{
    width:100%;
    max-width:430px;
    background:#111929;
    border:1px solid rgba(240,185,11,.15);
    border-radius:24px;
    padding:38px;
    position:relative;
    z-index:2;
    box-shadow:0 0 40px rgba(0,0,0,.5);
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    height:65px;
}

h1{
    text-align:center;
    font-size:2rem;
    font-weight:800;
    margin-bottom:10px;
}

.subtitle{
    text-align:center;
    color:#8b97aa;
    margin-bottom:30px;
    font-size:.95rem;
}

.error{
    background:rgba(246,70,93,.08);
    border:1px solid rgba(246,70,93,.2);
    color:#ff5c73;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-size:.92rem;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:8px;
    font-size:.78rem;
    color:#9aa4b2;
    font-weight:700;
    letter-spacing:1px;
    text-transform:uppercase;
}

input{
    width:100%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:#1b2638;
    color:white;
    font-size:.95rem;
    outline:none;
    border:1px solid transparent;
    transition:.2s;
}

input:focus{
    border-color:#f0b90b;
    box-shadow:0 0 0 3px rgba(240,185,11,.1);
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:#f0b90b;
    color:black;
    font-size:1rem;
    font-weight:700;
    cursor:pointer;
    transition:.2s;
}

button:hover{
    background:#dca800;
    transform:translateY(-1px);
}

.footer{
    margin-top:22px;
    text-align:center;
    color:#7d8898;
    font-size:.85rem;
}

</style>

</head>
<body>

<div class="login-box">

    <div class="logo">
        <img src="/logo.png" alt="WorkupX">
    </div>

    <h1>Admin Access</h1>

    <div class="subtitle">
        Secure backend authentication panel
    </div>

    <?php if($error): ?>

        <div class="error">
            <?= $error ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="admin@workupx.com"
                required
            >

        </div>

        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="••••••••"
                required
            >

        </div>

        <button type="submit">
            Secure Login →
        </button>

    </form>

    <div class="footer">
        WORKUPX ADMIN SYSTEM
    </div>

</div>

</body>
</html>