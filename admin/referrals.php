<?php

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';

startSess();

if(!isset($_SESSION['admin'])){
    header("Location:/admin/admin-dashboard.php");
    exit;
}

$stmt = db()->query("
SELECT
id,
name,
email,
direct_referrals,
indirect_referrals,
referral_bonus_percent
FROM users
ORDER BY direct_referrals DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
<title>Referral Management</title>

<style>
body{
    background:#050811;
    color:white;
    font-family:Inter,sans-serif;
}

.card{
    background:#0f1523;
    padding:20px;
    border-radius:20px;
    margin:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:14px;
    border-bottom:1px solid #222;
}

th{
    background:#151b29;
}
</style>

</head>
<body>

<div class="card">

<h2>Referral Management</h2>

<table>

<tr>
<th>Name</th>
<th>Email</th>
<th>Direct</th>
<th>Indirect</th>
<th>Bonus %</th>
</tr>

<?php foreach($users as $user): ?>

<tr>

<td><?= htmlspecialchars($user['name']) ?></td>

<td><?= htmlspecialchars($user['email']) ?></td>

<td><?= $user['direct_referrals'] ?></td>

<td><?= $user['indirect_referrals'] ?></td>

<td><?= $user['referral_bonus_percent'] ?>%</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</body>
</html>