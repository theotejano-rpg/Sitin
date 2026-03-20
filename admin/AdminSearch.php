<?php
session_start();
require_once 'db.php';
if (empty($_SESSION['admin'])) { header('Location: ../Login.php'); exit; }
$db = get_db();

$search_query   = trim($_GET['q'] ?? '');
$search_results = [];
if ($search_query !== '') {
    $stmt = $db->prepare("SELECT * FROM students WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ORDER BY last_name ASC");
    $stmt->execute(["%$search_query%","%$search_query%","%$search_query%","%$search_query%"]);
    $search_results = $stmt->fetchAll();
}

$nav_admin_active = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Search Students</title>
  <link rel="stylesheet" href="../css/Style.css"/>
  <link rel="stylesheet" href="../css/Admin.css"/>
</head>
<body class="admin-page">
<?php include __DIR__ . '/nav_admin.php'; ?>
<main class="admin-main">
  <span class="section-eyebrow">Administration</span>
  <h2 class="section-title">Search Student</h2>
  <div class="admin-card">
    <div class="admin-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Find a Student
    </div>
    <div class="admin-table-wrap">
      <form method="GET" action="AdminSearch.php" style="display:flex;gap:10px;align-items:center;margin-bottom:20px;">
        <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Enter Student ID, name or email..." style="flex:1;padding:11px 14px;border:1.5px solid #ccdeed;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;"/>
        <button type="submit" class="admin-btn blue">Search</button>
      </form>
      <?php if ($search_query && empty($search_results)): ?>
        <div class="admin-alert error">&#10005; No results found for "<?= htmlspecialchars($search_query) ?>".</div>
      <?php endif; ?>
      <?php if (!empty($search_results)): ?>
      <table class="admin-table">
        <thead><tr><th>ID Number</th><th>Name</th><th>Course</th><th>Year</th><th>Email</th><th>Sessions Left</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($search_results as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
            <td><?= htmlspecialchars($s['course_code']) ?></td>
            <td><?= htmlspecialchars($s['level']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= $s['sessions'] - $s['used'] ?></td>
            <td><a href="AdminSitin.php?q=<?= urlencode($s['student_id']) ?>" class="admin-btn blue sm">Sit-In</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>