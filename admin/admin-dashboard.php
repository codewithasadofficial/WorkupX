<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/admin-auth.php';

/*
|--------------------------------------------------------------------------
| OVERVIEW STATS
|--------------------------------------------------------------------------
*/

$totalUsers = dbOne("
    SELECT COUNT(*) as total
    FROM users
")['total'] ?? 0;

$totalDeposits = dbOne("
    SELECT SUM(amount) as total
    FROM deposits
")['total'] ?? 0;

$totalWithdrawals = dbOne("
    SELECT SUM(amount) as total
    FROM withdrawals
")['total'] ?? 0;

$approvedDeposits = dbOne("
    SELECT SUM(amount) as total
    FROM deposits
    WHERE status='approved'
")['total'] ?? 0;

$pendingDeposits = dbOne("
    SELECT SUM(amount) as total
    FROM deposits
    WHERE status='pending'
")['total'] ?? 0;

$approvedWithdrawals = dbOne("
    SELECT SUM(amount) as total
    FROM withdrawals
    WHERE status='approved'
")['total'] ?? 0;

$pendingWithdrawals = dbOne("
    SELECT SUM(amount) as total
    FROM withdrawals
    WHERE status='pending'
")['total'] ?? 0;

$platformRevenue = $approvedDeposits - $approvedWithdrawals;

/*
|--------------------------------------------------------------------------
| RECENT USERS
|--------------------------------------------------------------------------
*/

$recentUsers = dbAll("
    SELECT *
    FROM users
    ORDER BY id DESC
    LIMIT 5
");

/*
|--------------------------------------------------------------------------
| RECENT DEPOSITS
|--------------------------------------------------------------------------
*/

$recentDeposits = dbAll("
    SELECT *
    FROM deposits
    ORDER BY id DESC
    LIMIT 5
");

/*
|--------------------------------------------------------------------------
| RECENT WITHDRAWALS
|--------------------------------------------------------------------------
*/

$recentWithdrawals = dbAll("
    SELECT *
    FROM withdrawals
    ORDER BY id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Admin Dashboard - WorkupX</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
background:#050811;
font-family:'Inter',sans-serif;
color:white;
}

.topbar{
height:72px;
background:#0c111c;
border-bottom:1px solid rgba(255,255,255,.05);
display:flex;
align-items:center;
justify-content:space-between;
padding:0 24px;
}

.logo{
display:flex;
align-items:center;
gap:12px;
font-weight:800;
font-size:1.3rem;
}

.logo-box{
width:34px;
height:34px;
background:#f0b90b;
border-radius:10px;
display:flex;
align-items:center;
justify-content:center;
color:black;
font-weight:800;
}

.logo span{
color:#f0b90b;
}

.admin-user{
background:#171d2b;
padding:10px 16px;
border-radius:30px;
font-size:.9rem;
font-weight:600;
}

.wrapper{
padding:30px;
}

.heading{
font-size:2rem;
font-weight:800;
margin-bottom:5px;
}

.sub{
color:#7f8aa3;
margin-bottom:25px;
}

.nav{
display:flex;
gap:10px;
margin-bottom:30px;
flex-wrap:wrap;
}

.nav a{
padding:12px 18px;
background:#161d2d;
border-radius:10px;
text-decoration:none;
color:#9ca3af;
font-size:.84rem;
font-weight:700;
transition:.2s;
}

.nav a.active,
.nav a:hover{
background:#f0b90b;
color:black;
}

.grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:25px;
}

.card{
background:#111827;
border-radius:18px;
padding:24px;
border:1px solid rgba(255,255,255,.05);
}

.card-title{
color:#7f8aa3;
font-size:.8rem;
margin-bottom:12px;
text-transform:uppercase;
}

.card-value{
font-size:2rem;
font-weight:800;
}

.green{
color:#00c087;
}

.red{
color:#f6465d;
}

.blue{
color:#3b82f6;
}

.yellow{
color:#f0b90b;
}

.section{
background:#111827;
border-radius:18px;
padding:22px;
margin-top:25px;
border:1px solid rgba(255,255,255,.05);
}

.section h3{
margin-bottom:20px;
font-size:1rem;
}

table{
width:100%;
border-collapse:collapse;
}

table th{
text-align:left;
padding:14px;
font-size:.8rem;
color:#7f8aa3;
border-bottom:1px solid rgba(255,255,255,.05);
}

table td{
padding:14px;
border-bottom:1px solid rgba(255,255,255,.04);
font-size:.92rem;
}

.status{
padding:6px 12px;
border-radius:30px;
font-size:.75rem;
font-weight:700;
display:inline-block;
}

.status-approved{
background:rgba(0,192,135,.1);
color:#00c087;
}

.status-pending{
background:rgba(240,185,11,.1);
color:#f0b90b;
}

.chart{
height:320px;
background:#111827;
border-radius:20px;
margin-top:25px;
position:relative;
overflow:hidden;
border:1px solid rgba(255,255,255,.05);
}

.chart::before{
content:'';
position:absolute;
inset:0;
background:
linear-gradient(to top, rgba(240,185,11,.15), transparent),
url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1200' height='320'%3E%3Cpath d='M0 250 C100 120 200 300 300 180 S500 60 600 150 S800 280 900 160 S1100 100 1200 220' stroke='%23f0b90b' fill='none' stroke-width='4'/%3E%3C/svg%3E");
background-size:cover;
}

@media(max-width:1000px){

.grid{
grid-template-columns:repeat(2,1fr);
}

}

@media(max-width:700px){

.grid{
grid-template-columns:1fr;
}

.wrapper{
padding:20px;
}

table{
display:block;
overflow:auto;
}

}

</style>
</head>
<body>

<div class="topbar">

<div class="logo">
<div class="logo-box">WX</div>
WORK<span>UPX</span>
</div>

<div class="admin-user">
Admin
</div>

</div>

<div class="wrapper">

<div class="heading">
Admin Panel
</div>

<div class="sub">
WORKUPX.COM — Backend Control
</div>

<div class="nav">

<a href="/admin/admin-dashboard.php" class="active">ADMIN DASHBOARD</a>
<a href="/admin/users.php">USERS</a>
<a href="/admin/deposits.php">DEPOSITS</a>
<a href="/admin/withdrawals.php">WITHDRAWALS</a>
<a href="/admin/broadcast.php">BROADCAST</a>
<a href="/admin/logout.php">LOGOUT</a>

</div>

<div class="grid">

<div class="card">
<div class="card-title">TOTAL USERS</div>
<div class="card-value"><?= $totalUsers ?></div>
</div>

<div class="card">
<div class="card-title">TOTAL DEPOSITS</div>
<div class="card-value green">
$<?= number_format($totalDeposits,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">TOTAL WITHDRAWALS</div>
<div class="card-value red">
$<?= number_format($totalWithdrawals,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">PLATFORM REVENUE</div>
<div class="card-value blue">
$<?= number_format($platformRevenue,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">APPROVED DEPOSITS</div>
<div class="card-value green">
$<?= number_format($approvedDeposits,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">PENDING DEPOSITS</div>
<div class="card-value yellow">
$<?= number_format($pendingDeposits,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">APPROVED WITHDRAWALS</div>
<div class="card-value red">
$<?= number_format($approvedWithdrawals,2) ?>
</div>
</div>

<div class="card">
<div class="card-title">PENDING WITHDRAWALS</div>
<div class="card-value yellow">
$<?= number_format($pendingWithdrawals,2) ?>
</div>
</div>

</div>

<div class="chart"></div>

<div class="section">

<h3>Recent Deposits</h3>

<table>

<tr>
<th>User</th>
<th>Amount</th>
<th>Status</th>
</tr>

<?php foreach($recentDeposits as $d): ?>

<tr>

<td>
<?= htmlspecialchars($d['user_name'] ?? 'User') ?>
</td>

<td class="green">
$<?= number_format($d['amount'],2) ?>
</td>

<td>

<span class="status status-<?= $d['status'] ?>">
<?= ucfirst($d['status']) ?>
</span>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="section">

<h3>Recent Withdrawals</h3>

<table>

<tr>
<th>User</th>
<th>Amount</th>
<th>Status</th>
</tr>

<?php foreach($recentWithdrawals as $w): ?>

<tr>

<td>
<?= htmlspecialchars($w['user_name'] ?? 'User') ?>
</td>

<td class="red">
$<?= number_format($w['amount'],2) ?>
</td>

<td>

<span class="status status-<?= $w['status'] ?>">
<?= ucfirst($w['status']) ?>
</span>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<div class="section">

<h3>Latest Users</h3>

<table>

<tr>
<th>Name</th>
<th>Email</th>
<th>Plan</th>
<th>Balance</th>
</tr>

<?php foreach($recentUsers as $u): ?>

<tr>

<td><?= htmlspecialchars($u['name']) ?></td>

<td><?= htmlspecialchars($u['email']) ?></td>

<td><?= strtoupper($u['plan'] ?? 'FREE') ?></td>

<td class="green">
$<?= number_format($u['balance'] ?? 0,2) ?>
</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</body>
</html>