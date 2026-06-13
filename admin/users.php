<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';

startSess();

if(!isset($_SESSION['admin'])){
    header("Location:/admin/admin-dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| ADD USER
|--------------------------------------------------------------------------
*/

if(isset($_POST['add_user'])){

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $plan     = trim($_POST['plan']);
    $status   = trim($_POST['status']);

    $stmt = db()->prepare("
        INSERT INTO users
        (
            name,
            email,
            password_hash,
            plan,
            status,
            balance,
            deposit,
            today_earn,
            total_earn,
            withdrawn,
            ref_earn,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,?,NOW()
        )
    ");

    $stmt->execute([
        $name,
        $email,
        $password,
        $plan,
        $status,
        0,
        0,
        0,
        0,
        0,
        0
    ]);

    header("Location: users.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE USER
|--------------------------------------------------------------------------
*/

if(isset($_POST['update_user'])){

    $id           = (int)$_POST['user_id'];

    $name         = trim($_POST['name']);
    $email        = trim($_POST['email']);

    $balance      = $_POST['balance'];
    $deposit      = $_POST['deposit'];
    $today_earn   = $_POST['today_earn'];
    $total_earn   = $_POST['total_earn'];
    $withdrawn    = $_POST['withdrawn'];
    $ref_earn     = $_POST['ref_earn'];
    $direct_referrals = $_POST['direct_referrals'] ?? 0;
    $indirect_referrals = $_POST['indirect_referrals'] ?? 0;
    $referral_bonus_percent = $_POST['referral_bonus_percent'] ?? 0;

    $plan         = $_POST['plan'];
    $status       = $_POST['status'];

    $new_password = trim($_POST['new_password']);

    if($new_password){

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = db()->prepare("
            UPDATE users SET
            name=?,
            email=?,
            balance=?,
            deposit=?,
            today_earn=?,
            total_earn=?,
            withdrawn=?,
            ref_earn=?,
            plan=?,
            status=?,
            password_hash=?
            WHERE id=?
        ");

        $stmt->execute([
            $name,
            $email,
            $balance,
            $deposit,
            $today_earn,
            $total_earn,
            $withdrawn,
            $ref_earn,
            $plan,
            $status,
            $password_hash,
            $id
        ]);

    } else {

        $stmt = db()->prepare("
            UPDATE users SET
            name=?,
            email=?,
            balance=?,
            deposit=?,
            today_earn=?,
            total_earn=?,
            withdrawn=?,
            ref_earn=?,
            plan=?,
            status=?
            WHERE id=?
        ");

        $stmt->execute([
            $name,
            $email,
            $balance,
            $deposit,
            $today_earn,
            $total_earn,
            $withdrawn,
            $ref_earn,
            $plan,
            $status,
            $id
        ]);
    }

    header("Location: users.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

if(isset($_GET['delete'])){

    $id = (int)$_GET['delete'];

    $stmt = db()->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);

    header("Location: users.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT *
    FROM users
    ORDER BY id DESC
");

$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Users - WorkupX Admin</title>

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
    font-weight:800;
    font-size:1.4rem;
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
    border-radius:10px;
    color:#8b95a7;
    text-decoration:none;
    font-size:.9rem;
    font-weight:700;
}

.nav a.active{
    background:#f0b90b;
    color:black;
}

.card{
    background:#0f1523;
    border:1px solid rgba(255,255,255,.05);
    border-radius:20px;
    overflow:hidden;
}

.card-header{
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.btn{
    background:#f0b90b;
    color:black;
    border:none;
    padding:12px 18px;
    border-radius:10px;
    font-weight:700;
    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#151b29;
    color:#7d8794;
    font-size:.78rem;
    text-transform:uppercase;
    padding:16px;
    text-align:left;
}

td{
    padding:18px 16px;
    border-top:1px solid rgba(255,255,255,.03);
}

.status{
    background:rgba(14,203,129,.1);
    color:#0ecb81;
    padding:6px 12px;
    border-radius:30px;
    font-size:.75rem;
    font-weight:700;
}

.edit-btn{
    background:#1d2638;
    color:white;
    border:none;
    padding:8px 12px;
    border-radius:8px;
    cursor:pointer;
}

.delete-btn{
    background:#34131b;
    color:#ff5b75;
    border:none;
    padding:8px 12px;
    border-radius:8px;
    text-decoration:none;
}

.modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.7);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:999;
}

.modal-box{
    width:100%;
    max-width:760px;
    background:#171d2d;
    border-radius:22px;
    padding:28px;
}

.modal h2{
    margin-bottom:20px;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

input,select{
    width:100%;
    padding:14px;
    border:none;
    background:#101624;
    color:white;
    border-radius:10px;
    margin-top:8px;
    outline:none;
}

.save-btn{
    width:100%;
    margin-top:25px;
    background:#f0b90b;
    border:none;
    padding:16px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
}

.close{
    float:right;
    cursor:pointer;
    font-size:1.2rem;
}

.money{
    color:#f0b90b;
    font-weight:700;
}

.deposit{
    color:#4aa3ff;
    font-weight:700;
}

.earn{
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
        <a href="users.php" class="active">Users</a>
        <a href="deposits.php">Deposits</a>
        <a href="withdrawals.php">Withdrawals</a>
        <a href="broadcast.php">Broadcast</a>
    </div>

    <div class="card">

        <div class="card-header">

            <h3>User Management</h3>

            <button class="btn" onclick="openAddModal()">
                + Add User
            </button>

        </div>

        <table>

            <thead>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Direct</th><th>Indirect</th><th>Bonus %</th><th>Balance</th>
                    <th>Deposit</th>
                    <th>Earn</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($users as $user): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($user['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['email']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['plan']) ?>
                    </td>

                    <td class="money">
                        $<?= number_format($user['balance'],2) ?>
                    </td>

                    <td class="deposit">
                        $<?= number_format($user['deposit'],2) ?>
                    </td>

                    <td class="earn">
                        $<?= number_format($user['total_earn'],2) ?>
                    </td>

                    <td>

                        <span class="status">

                            <?= htmlspecialchars($user['status']) ?>

                        </span>

                    </td>

                    <td>

                        <button
                        class="edit-btn"

                        onclick="editUser(
                        '<?= $user['id'] ?>',
                        '<?= htmlspecialchars($user['name'],ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($user['email'],ENT_QUOTES) ?>',
                        '<?= $user['balance'] ?>',
                        '<?= $user['deposit'] ?>',
                        '<?= $user['today_earn'] ?>',
                        '<?= $user['total_earn'] ?>',
                        '<?= $user['withdrawn'] ?>',
                        '<?= $user['ref_earn'] ?>',
                        '<?= $user['plan'] ?>',
                        '<?= $user['status'] ?>'
                        )">

                        Edit

                        </button>

                        <a
                        class="delete-btn"
                        href="?delete=<?= $user['id'] ?>"
                        onclick="return confirm('Delete user?')">

                        Del

                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ADD USER -->

<div class="modal" id="addModal">

    <div class="modal-box">

        <span class="close" onclick="closeAddModal()">✕</span>

        <h2>Add User</h2>

        <form method="POST">

            <div class="grid">

                <div>
                    Name
                    <input type="text" name="name" required>
                </div>

                <div>
                    Email
                    <input type="email" name="email" required>
                </div>

                <div>
                    Password
                    <input type="text" name="password" required>
                </div>

                <div>
                    Plan

                    <select name="plan">

                        <option>Silver</option>
                        <option>Gold</option>
                        <option>VIP</option>

                    </select>

                </div>

                <div>
                    Status

                    <select name="status">

                        <option>active</option>
                        <option>pending</option>
                        <option>banned</option>

                    </select>

                </div>

            </div>

            <button class="save-btn" name="add_user">
                Add User
            </button>

        </form>

    </div>

</div>

<!-- EDIT USER -->

<div class="modal" id="editModal">

    <div class="modal-box">

        <span class="close" onclick="closeModal()">✕</span>

        <h2 style="margin-bottom:25px">
            ✏️ Edit User
        </h2>

        <form method="POST">

            <input type="hidden" name="user_id" id="user_id">

            <div style="
            background:#101624;
            padding:14px;
            border-radius:12px;
            margin-bottom:20px;
            color:#7d8794;
            font-size:.92rem">

                Editing:
                <strong id="edit_name" style="color:white"></strong>

                -
                <span id="edit_email"></span>

            </div>

            <div class="grid">

                <div>
                    Name
                    <input type="text" name="name" id="name">
                </div>

                <div>
                    Email
                    <input type="email" name="email" id="email">
                </div>

                <div>
                    Balance ($)
                    <input type="text" name="balance" id="balance">
                </div>

                <div>
                    Deposit ($)
                    <input type="text" name="deposit" id="deposit">
                </div>

                <div>
                    Today Earn ($)
                    <input type="text" name="today_earn" id="today_earn">
                </div>

                <div>
                    Total Earn ($)
                    <input type="text" name="total_earn" id="total_earn">
                </div>

                <div>
                    Withdrawn ($)
                    <input type="text" name="withdrawn" id="withdrawn">
                </div>

                <div>
                    Ref Earn ($)
                    <input type="text" name="ref_earn" id="ref_earn">
                </div>

                <div>
                    Plan

                    <select name="plan" id="plan">

                        <option value="Silver">🥈 Silver</option>
                        <option value="Gold">🥇 Gold</option>
                        <option value="VIP">💎 VIP</option>

                    </select>

                </div>

                <div>
                    Status

                    <select name="status" id="status">

                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="banned">Banned</option>

                    </select>

                </div>

                <div style="grid-column:1/3">

                    New Password

                    <input
                    type="text"
                    name="new_password"
                    placeholder="Leave blank to keep current">

                </div>

            </div>

            <button class="save-btn" name="update_user">

                💾 Save Changes

            </button>

        </form>

    </div>

</div>

<script>

function openAddModal(){

    document.getElementById('addModal').style.display='flex';
}

function closeAddModal(){

    document.getElementById('addModal').style.display='none';
}

function closeModal(){

    document.getElementById('editModal').style.display='none';
}

function editUser(
id,
name,
email,
balance,
deposit,
today_earn,
total_earn,
withdrawn,
ref_earn,
plan,
status
){

    document.getElementById('editModal').style.display='flex';

    document.getElementById('user_id').value=id;

    document.getElementById('name').value=name;
    document.getElementById('email').value=email;

    document.getElementById('edit_name').innerText=name;
    document.getElementById('edit_email').innerText=email;

    document.getElementById('balance').value=balance;
    document.getElementById('deposit').value=deposit;
    document.getElementById('today_earn').value=today_earn;
    document.getElementById('total_earn').value=total_earn;
    document.getElementById('withdrawn').value=withdrawn;
    document.getElementById('ref_earn').value=ref_earn;

    document.getElementById('plan').value=plan;
    document.getElementById('status').value=status;
}

</script>

</body>
</html>