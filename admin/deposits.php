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
| ADD MANUAL DEPOSIT
|--------------------------------------------------------------------------
*/

if(isset($_POST['add_deposit'])){

    $user_id = (int)$_POST['user_id'];
    $amount  = $_POST['amount'];
    $network = trim($_POST['network']);
    $tx_hash = trim($_POST['tx_hash']);
    $status  = trim($_POST['status']);

    $stmt = db()->prepare("
        INSERT INTO deposits
        (
            user_id,
            amount,
            network,
            tx_hash,
            status,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,NOW()
        )
    ");

    $stmt->execute([
        $user_id,
        $amount,
        $network,
        $tx_hash,
        $status
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER DEPOSIT BALANCE
    |--------------------------------------------------------------------------
    */

    if($status == 'Completed'){

        $stmt = db()->prepare("
            UPDATE users
            SET
            deposit = deposit + ?,
            balance = balance + ?
            WHERE id=?
        ");

        $stmt->execute([
            $amount,
            $amount,
            $user_id
        ]);
    }

    header("Location: deposits.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONFIRM DEPOSIT
|--------------------------------------------------------------------------
*/

if(isset($_GET['confirm'])){

    $id = (int)$_GET['confirm'];

    $stmt = db()->prepare("
        SELECT *
        FROM deposits
        WHERE id=?
    ");

    $stmt->execute([$id]);

    $deposit = $stmt->fetch(PDO::FETCH_ASSOC);

    if($deposit){

        $stmt = db()->prepare("
            UPDATE deposits
            SET status='Completed'
            WHERE id=?
        ");

        $stmt->execute([$id]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE USER BALANCE
        |--------------------------------------------------------------------------
        */

        $stmt = db()->prepare("
            UPDATE users
            SET
            deposit = deposit + ?,
            balance = balance + ?
            WHERE id=?
        ");

        $stmt->execute([
            $deposit['amount'],
            $deposit['amount'],
            $deposit['user_id']
        ]);
    }

    header("Location: deposits.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CSV EXPORT
|--------------------------------------------------------------------------
*/

if(isset($_GET['export'])){

    header('Content-Type:text/csv');
    header('Content-Disposition: attachment; filename=deposits.csv');

    $output = fopen("php://output", "w");

    fputcsv($output, [
        'User',
        'Amount',
        'Network',
        'TX HASH',
        'Status',
        'Date'
    ]);

    $stmt = db()->prepare("
        SELECT deposits.*, users.name
        FROM deposits
        LEFT JOIN users ON users.id = deposits.user_id
        ORDER BY deposits.id DESC
    ");

    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        fputcsv($output, [
            $row['name'],
            $row['amount'],
            $row['network'],
            $row['tx_hash'],
            $row['status'],
            $row['created_at']
        ]);
    }

    fclose($output);
    exit;
}

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];

if(!empty($_GET['user'])){

    $where[] = "users.name LIKE ?";
    $params[] = "%".$_GET['user']."%";
}

if(!empty($_GET['status'])){

    $where[] = "deposits.status = ?";
    $params[] = $_GET['status'];
}

if(!empty($_GET['network'])){

    $where[] = "deposits.network = ?";
    $params[] = $_GET['network'];
}

$sqlWhere = '';

if($where){

    $sqlWhere = "WHERE ".implode(" AND ", $where);
}

/*
|--------------------------------------------------------------------------
| GET DEPOSITS
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT deposits.*, users.name
    FROM deposits
    LEFT JOIN users ON users.id = deposits.user_id
    $sqlWhere
    ORDER BY deposits.id DESC
");

$stmt->execute($params);

$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare("
    SELECT *
    FROM users
    ORDER BY name ASC
");

$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Deposits - WorkupX</title>

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
    justify-content:space-between;
    align-items:center;
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

.card{
    background:#0f1523;
    border-radius:20px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.05);
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
    text-decoration:none;
}

.filters{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

input,select{
    background:#151b29;
    border:none;
    color:white;
    padding:12px;
    border-radius:10px;
    outline:none;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#151b29;
    color:#7d8794;
    text-transform:uppercase;
    font-size:.75rem;
    padding:16px;
    text-align:left;
}

td{
    padding:18px 16px;
    border-top:1px solid rgba(255,255,255,.03);
}

.amount{
    color:#f0b90b;
    font-weight:700;
}

.network{
    background:rgba(0,136,255,.12);
    color:#4aa3ff;
    padding:6px 12px;
    border-radius:30px;
    font-size:.75rem;
    font-weight:700;
}

.status{
    padding:6px 12px;
    border-radius:30px;
    font-size:.75rem;
    font-weight:700;
}

.completed{
    background:rgba(14,203,129,.1);
    color:#0ecb81;
}

.pending{
    background:rgba(240,185,11,.12);
    color:#f0b90b;
}

.copy-btn{
    background:#1d2638;
    border:none;
    color:white;
    padding:8px 10px;
    border-radius:8px;
    cursor:pointer;
}

.confirm-btn{
    background:#0ecb81;
    color:black;
    padding:10px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
}

.modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.7);
    display:none;
    justify-content:center;
    align-items:center;
}

.modal-box{
    width:100%;
    max-width:620px;
    background:#171d2d;
    border-radius:22px;
    padding:30px;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.save-btn{
    width:100%;
    margin-top:20px;
    background:#f0b90b;
    border:none;
    padding:16px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
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
        <a href="deposits.php" class="active">Deposits</a>
        <a href="withdrawals.php">Withdrawals</a>
        <a href="broadcast.php">Broadcast</a>

    </div>

    <form class="filters">

        <input
        type="text"
        name="user"
        placeholder="Search user">

        <select name="status">

            <option value="">All Status</option>
            <option>Pending</option>
            <option>Completed</option>

        </select>

        <select name="network">

            <option value="">All Network</option>
            <option>BEP20</option>
            <option>TRC20</option>

        </select>

        <button class="btn">
            Filter
        </button>

    </form>

    <div class="card">

        <div class="card-header">

            <h3>Deposit Requests</h3>

            <div style="display:flex;gap:10px">

                <button class="btn" onclick="copyTable()">
                    Copy Data
                </button>

                <a href="?export=1" class="btn">
                    Download CSV
                </a>

                <button class="btn" onclick="openModal()">
                    + Add Deposit
                </button>

            </div>

        </div>

        <table id="depositTable">

            <thead>

                <tr>

                    <th>User</th>
                    <th>Amount</th>
                    <th>Network</th>
                    <th>TX HASH</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php foreach($deposits as $d): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($d['name']) ?>
                    </td>

                    <td class="amount">
                        $<?= number_format($d['amount'],2) ?>
                    </td>

                    <td>
                        <span class="network">
                            <?= htmlspecialchars($d['network']) ?>
                        </span>
                    </td>

                    <td>

                        <span id="tx<?= $d['id'] ?>">

                            <?= $d['tx_hash'] ?: '—' ?>

                        </span>

                        <?php if($d['tx_hash']): ?>

                            <button
                            class="copy-btn"
                            onclick="copyTx('tx<?= $d['id'] ?>')">

                            Copy

                            </button>

                        <?php endif; ?>

                    </td>

                    <td>

                        <span class="status <?= strtolower($d['status']) ?>">

                            <?= htmlspecialchars($d['status']) ?>

                        </span>

                    </td>

                    <td>
                        <?= date('Y-m-d', strtotime($d['created_at'])) ?>
                    </td>

                    <td>

                        <?php if($d['status'] != 'Completed'): ?>

                            <a
                            class="confirm-btn"
                            href="?confirm=<?= $d['id'] ?>">

                            Confirm

                            </a>

                        <?php else: ?>

                            <span style="color:#0ecb81;font-weight:700">
                                Done
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ADD DEPOSIT MODAL -->

<div class="modal" id="depositModal">

    <div class="modal-box">

        <h2 style="margin-bottom:20px">
            Add Deposit
        </h2>

        <form method="POST">

            <div class="grid">

                <div>

                    User

                    <select name="user_id" required>

                        <?php foreach($users as $u): ?>

                            <option value="<?= $u['id'] ?>">

                                <?= htmlspecialchars($u['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    Amount

                    <input
                    type="number"
                    step="0.01"
                    name="amount"
                    required>

                </div>

                <div>

                    Network

                    <select name="network">

                        <option>BEP20</option>
                        <option>TRC20</option>

                    </select>

                </div>

                <div>

                    Status

                    <select name="status">

                        <option>Pending</option>
                        <option>Completed</option>

                    </select>

                </div>

                <div style="grid-column:1/3">

                    TX HASH

                    <input
                    type="text"
                    name="tx_hash"
                    placeholder="Leave blank if payment pending">

                </div>

            </div>

            <button class="save-btn" name="add_deposit">

                Save Deposit

            </button>

        </form>

    </div>

</div>

<script>

function openModal(){

    document.getElementById('depositModal').style.display='flex';
}

function copyTx(id){

    let text = document.getElementById(id).innerText;

    navigator.clipboard.writeText(text);

    alert("TX HASH copied");
}

function copyTable(){

    let table = document.getElementById('depositTable').innerText;

    navigator.clipboard.writeText(table);

    alert("Deposit table copied");
}

window.onclick=function(e){

    let modal=document.getElementById('depositModal');

    if(e.target==modal){

        modal.style.display='none';
    }
}

</script>

</body>
</html>