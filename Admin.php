<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['admin'])) {
    header('Location: Login.php');
    exit;
}

$admin = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Admin Dashboard</title>
  <link rel="stylesheet" href="css/Style.css"/>
</head>
<body class="auth">

<?php include __DIR__ . '/nav_admin.php'; ?>

  <div style="max-width:800px;margin:60px auto;padding:0 24px;text-align:center;">
    <h1 style="font-family:'DM Serif Display',serif;font-size:2rem;color:#0a4d8c;margin-bottom:12px;">
      Welcome, <?= htmlspecialchars($admin['first_name']) ?>!
    </h1>
    <p style="color:#4a6278;font-size:1rem;">Admin dashboard is coming soon.</p>
    <a href="Logout.php" style="display:inline-block;margin-top:24px;padding:10px 24px;background:#0a4d8c;color:white;border-radius:8px;text-decoration:none;font-weight:600;">
      Log Out
    </a>
  </div>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>