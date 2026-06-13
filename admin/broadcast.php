<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';

startSess();

if(!isset($_SESSION['admin'])){
    header("Location:/admin/admin-dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AUTO CREATE TABLES
|--------------------------------------------------------------------------
*/

db()->exec("
CREATE TABLE IF NOT EXISTS signals (

    id INT AUTO_INCREMENT PRIMARY KEY,

    trade_code VARCHAR(255) UNIQUE,

    coin_pair VARCHAR(100),

    direction VARCHAR(50),

    message TEXT,

    expected_return VARCHAR(100),

    valid_until VARCHAR(100),

    target_plan VARCHAR(100),

    profit_percent DECIMAL(10,2) DEFAULT 0,

    expires_at DATETIME NULL,

    status VARCHAR(50) DEFAULT 'active',

    created_at DATETIME

)
");

db()->exec("
CREATE TABLE IF NOT EXISTS signal_claims (

    id INT AUTO_INCREMENT PRIMARY KEY,

    signal_code VARCHAR(255),

    user_id INT,

    profit DECIMAL(10,2) DEFAULT 0,

    created_at DATETIME

)
");

/*
|--------------------------------------------------------------------------
| SEND SIGNAL
|--------------------------------------------------------------------------
*/

if(isset($_POST['send_signal'])){

    $trade_code      = trim($_POST['trade_code']);
    $coin_pair       = trim($_POST['coin_pair']);
    $direction       = trim($_POST['direction']);
    $message         = trim($_POST['message']);
    $expected_return = trim($_POST['expected_return']);
    $valid_until     = trim($_POST['valid_until']);
    $target_plan     = trim($_POST['target_plan']);
    $profit_percent  = (float)$_POST['profit_percent'];

    /*
    |--------------------------------------------------------------------------
    | EXPIRE AFTER 1 HOUR
    |--------------------------------------------------------------------------
    */

    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    /*
    |--------------------------------------------------------------------------
    | INSERT SIGNAL
    |--------------------------------------------------------------------------
    */

    $stmt = db()->prepare("
        INSERT INTO signals
        (
            trade_code,
            coin_pair,
            direction,
            message,
            expected_return,
            valid_until,
            target_plan,
            profit_percent,
            expires_at,
            status,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,NOW()
        )
    ");

    $stmt->execute([
        $trade_code,
        $coin_pair,
        $direction,
        $message,
        $expected_return,
        $valid_until,
        $target_plan,
        $profit_percent,
        $expires_at,
        'active'
    ]);

    /*
    |--------------------------------------------------------------------------
    | GET USERS
    |--------------------------------------------------------------------------
    */

    if($target_plan == 'All Users'){

        $stmt = db()->prepare("
            SELECT *
            FROM users
            WHERE status='active'
        ");

        $stmt->execute();

    }else{

        $stmt = db()->prepare("
            SELECT *
            FROM users
            WHERE status='active'
            AND plan=?
        ");

        $stmt->execute([$target_plan]);
    }

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | GIVE PROFIT
    |--------------------------------------------------------------------------
    */

    foreach($users as $user){

        /*
        |--------------------------------------------------------------------------
        | ONE TIME ONLY
        |--------------------------------------------------------------------------
        */

        $check = db()->prepare("
            SELECT id
            FROM signal_claims
            WHERE signal_code=?
            AND user_id=?
            LIMIT 1
        ");

        $check->execute([
            $trade_code,
            $user['id']
        ]);

        if(!$check->fetch()){

            $deposit = (float)$user['deposit'];

            $profit = ($deposit * $profit_percent) / 100;

            /*
            |--------------------------------------------------------------------------
            | UPDATE USER
            |--------------------------------------------------------------------------
            */

            $update = db()->prepare("
                UPDATE users
                SET
                balance = balance + ?,
                today_earn = today_earn + ?,
                total_earn = total_earn + ?
                WHERE id=?
            ");

            $update->execute([
                $profit,
                $profit,
                $profit,
                $user['id']
            ]);

            /*
            |--------------------------------------------------------------------------
            | SAVE CLAIM
            |--------------------------------------------------------------------------
            */

            $claim = db()->prepare("
                INSERT INTO signal_claims
                (
                    signal_code,
                    user_id,
                    profit,
                    created_at
                )
                VALUES
                (
                    ?,?,?,NOW()
                )
            ");

            $claim->execute([
                $trade_code,
                $user['id'],
                $profit
            ]);
        }
    }

    header("Location:broadcast.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AUTO EXPIRE
|--------------------------------------------------------------------------
*/

$checkColumns = db()->query("SHOW COLUMNS FROM signals");
$columns = $checkColumns->fetchAll(PDO::FETCH_COLUMN);

if(in_array('expires_at',$columns) && in_array('status',$columns)){

    $stmt = db()->prepare("
        UPDATE signals
        SET status='expired'
        WHERE expires_at < NOW()
    ");

    $stmt->execute();
}

/*
|--------------------------------------------------------------------------
| GET SIGNALS
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT *
    FROM signals
    ORDER BY id DESC
");

$stmt->execute();

$signals = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Broadcast - WorkupX</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#050811;
    color:white;
    font-family:'Inter',sans-serif;
}

.topbar{
    height:72px;
    background:#0b101b;
    border-bottom:1px solid rgba(255,255,255,.05);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:1.4rem;
    font-weight:800;
}

.logo-box{
    width:34px;
    height:34px;
    background:#f0b90b;
    color:black;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
}

.container{
    padding:30px;
}

h1{
    font-size:2rem;
    margin-bottom:8px;
}

.sub{
    color:#7d8794;
    margin-bottom:25px;
}

.nav{
    display:flex;
    gap:10px;
    margin-bottom:30px;
}

.nav a{
    padding:12px 18px;
    background:#151b29;
    color:#8b95a7;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    font-size:.9rem;
}

.nav a.active{
    background:#f0b90b;
    color:black;
}

.broadcast-box{
    background:linear-gradient(180deg,#11140d,#0f1523);
    border:1px solid rgba(240,185,11,.25);
    border-radius:22px;
    padding:22px;
}

.head{
    display:flex;
    gap:15px;
    align-items:center;
    margin-bottom:25px;
}

.head-icon{
    width:42px;
    height:42px;
    background:#f0b90b;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:black;
    font-weight:800;
}

.head-title{
    font-size:1.1rem;
    font-weight:700;
}

.head-sub{
    color:#7d8794;
    font-size:.9rem;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

label{
    display:block;
    margin-bottom:10px;
    color:#7d8794;
    font-size:.82rem;
    font-weight:700;
    text-transform:uppercase;
}

input,select,textarea{
    width:100%;
    background:#171d2d;
    border:1px solid rgba(255,255,255,.06);
    color:white;
    padding:15px;
    border-radius:10px;
    outline:none;
}

textarea{
    height:120px;
    resize:none;
}

button{
    width:100%;
    background:#f0b90b;
    color:black;
    border:none;
    padding:16px;
    border-radius:12px;
    font-weight:800;
    margin-top:20px;
    cursor:pointer;
}

.history{
    margin-top:30px;
    background:#0f1523;
    border-radius:22px;
    border:1px solid rgba(255,255,255,.05);
    overflow:hidden;
}

.history-title{
    padding:18px 20px;
    border-bottom:1px solid rgba(255,255,255,.05);
    font-weight:700;
}

.signal{
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,.03);
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
}

.code{
    color:#f0b90b;
    font-weight:800;
    margin-bottom:8px;
}

.msg{
    color:#7d8794;
    font-size:.92rem;
    margin-bottom:10px;
}

.badges{
    display:flex;
    gap:8px;
}

.badge{
    padding:6px 12px;
    border-radius:30px;
    font-size:.75rem;
    font-weight:700;
}

.blue{
    background:rgba(0,136,255,.12);
    color:#4aa3ff;
}

.green{
    background:rgba(14,203,129,.12);
    color:#0ecb81;
}

.purple{
    background:rgba(168,85,247,.12);
    color:#c084fc;
}

.right{
    text-align:right;
}

.return{
    color:#0ecb81;
    font-weight:700;
}

.expired{
    color:#ff5b6e;
    font-weight:700;
}

.active-status{
    color:#0ecb81;
    font-weight:700;
}

</style>

</head>
<body>

<div class="topbar">

    <div class="logo">

        <div class="logo-box">WX</div>

        WORKUPX

    </div>

    <div>Admin</div>

</div>

<div class="container">

    <h1>Admin Panel</h1>

    <div class="sub">
        WORKUPX.COM — Backend Control
    </div>

    <div class="nav">

        <a href="admin-dashboard.php">ADMIN DASHBOARD</a>
        <a href="users.php">Users</a>
        <a href="deposits.php">Deposits</a>
        <a href="withdrawals.php">Withdrawals</a>
        <a href="broadcast.php" class="active">Broadcast</a>

    </div>

    <div class="broadcast-box">

        <div class="head">

            <div class="head-icon">📡</div>

            <div>

                <div class="head-title">
                    Signal Broadcast Studio
                </div>

                <div class="head-sub">
                    Send official copy trade codes to users
                </div>

            </div>

        </div>

        <form method="POST">

            <label>Trade Code *</label>

            <input
            type="text"
            name="trade_code"
            placeholder="E.G. WORKUPX-BULL-001"
            required>

            <div class="grid" style="margin-top:18px">

                <div>

                    <label>Coin Pair</label>

                    <select name="coin_pair">

                        <option>BTC/USDT</option>
                        <option>ETH/USDT</option>
                        <option>BNB/USDT</option>
                        <option>SOL/USDT</option>

                    </select>

                </div>

                <div>

                    <label>Direction</label>

                    <select name="direction">

                        <option>LONG 📈</option>
                        <option>SHORT 📉</option>

                    </select>

                </div>

            </div>

            <div style="margin-top:18px">

                <label>Message *</label>

                <textarea
                name="message"
                placeholder="Market analysis and trading instructions..."
                required></textarea>

            </div>

            <div class="grid" style="margin-top:18px">

                <div>

                    <label>Expected Return</label>

                    <input
                    type="text"
                    name="expected_return"
                    placeholder="e.g. 2%-3%">

                </div>

                <div>

                    <label>Valid Until</label>

                    <input
                    type="text"
                    name="valid_until"
                    placeholder="e.g. 9:00 AM">

                </div>

            </div>

            <div class="grid" style="margin-top:18px">

                <div>

                    <label>Target Plan</label>

                    <select name="target_plan">

                        <option>All Users</option>
                        <option>Silver</option>
                        <option>Gold</option>
                        <option>Diamond</option>

                    </select>

                </div>

                <div>

                    <label>Profit %</label>

                    <input
                    type="number"
                    step="0.01"
                    name="profit_percent"
                    value="2">

                </div>

            </div>

            <button type="submit" name="send_signal">
                🚀 Broadcast Signal to Users
            </button>

        </form>

    </div>

    <div class="history">

        <div class="history-title">
            Broadcast History
        </div>

        <?php foreach($signals as $signal): ?>

            <div class="signal">

                <div>

                    <div class="code">
                        <?= htmlspecialchars($signal['trade_code']) ?>
                    </div>

                    <div class="msg">
                        📊 <?= htmlspecialchars($signal['message']) ?>
                    </div>

                    <div class="badges">

                        <div class="badge blue">
                            <?= htmlspecialchars($signal['coin_pair']) ?>
                        </div>

                        <div class="badge green">
                            <?= htmlspecialchars($signal['direction']) ?>
                        </div>

                        <div class="badge purple">
                            <?= htmlspecialchars($signal['target_plan']) ?>
                        </div>

                    </div>

                </div>

                <div class="right">

                    <div style="color:#7d8794;font-size:.85rem">

                        <?= date('Y-m-d H:i', strtotime($signal['created_at'])) ?>

                    </div>

                    <div class="return">

                        Return:
                        <?= htmlspecialchars($signal['expected_return']) ?>

                    </div>

                    <div style="margin-top:10px">

                        <?php if($signal['status']=='expired'): ?>

                            <span class="expired">
                                EXPIRED
                            </span>

                        <?php else: ?>

                            <span class="active-status">
                                ACTIVE
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

</body>
</html>