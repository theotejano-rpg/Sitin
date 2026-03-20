<?php
session_start();
require_once 'db.php';
if (empty($_SESSION['admin'])) { header('Location: ../Login.php'); exit; }
$db = get_db();

$records = $db->query("
    SELECT sl.*, s.first_name, s.last_name, s.student_id as sid, s.course_code
    FROM sitin_logs sl JOIN students s ON sl.student_id = s.id
    ORDER BY sl.date_in DESC
")->fetchAll();

$nav_admin_active = 'records';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Sit-In Records</title>
  <link rel="stylesheet" href="../css/Style.css"/>
  <link rel="stylesheet" href="../css/Admin.css"/>
</head>
<body class="admin-page">
<?php include __DIR__ . '/nav_admin.php'; ?>
<main class="admin-main">
  <span class="section-eyebrow">Administration</span>
  <h2 class="section-title">View Sit-In Records</h2>
  <div class="admin-card">
    <div class="admin-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 1 .5 4"/><polyline points="3 21 3 16 8 16"/></svg>
      Sit-In History
    </div>
    <div class="admin-table-wrap">
      <div class="admin-table-toolbar">
        <div class="search-box-wrap"><label>Search:</label><input type="text" id="tableSearch" oninput="filterTable()" placeholder="Search records..."/></div>
      </div>
      <table class="admin-table-box" id="recordsTable">
        <thead><tr><th>Sit ID</th><th>ID Number</th><th>Name</th><th>Purpose</th><th>Lab</th><th>Date In</th><th>Date Out</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($records as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['sid']) ?></td>
            <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
            <td><?= htmlspecialchars($r['purpose']) ?></td>
            <td><?= htmlspecialchars($r['lab_room']) ?></td>
            <td><?= htmlspecialchars($r['date_in']) ?></td>
            <td><?= $r['date_out'] ? htmlspecialchars($r['date_out']) : '<span style="color:#aaa">—</span>' ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($records)): ?>
            <tr><td colspan="8" class="empty-state">No sit-in records yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script>
function filterTable() {
  const q = document.getElementById('tableSearch').value.toLowerCase();
  document.querySelectorAll('#recordsTable tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none'; });
}
</script>
</body>
</html>