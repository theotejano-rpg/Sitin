<?php
session_start();
require_once 'db.php';
if (empty($_SESSION['admin'])) { header('Location: ../Login.php'); exit; }

$db = get_db();
$search_result = null;
$search_query  = trim($_GET['q'] ?? '');
$sitin_success = $sitin_error = '';

if ($search_query !== '') {
    $stmt = $db->prepare("SELECT * FROM students WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? LIMIT 1");
    $stmt->execute(["%$search_query%","%$search_query%","%$search_query%"]);
    $search_result = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sitin_student_id'])) {
    $sid     = (int)$_POST['sitin_student_id'];
    $lab     = trim($_POST['lab_room'] ?? '');
    $purpose = trim($_POST['purpose']  ?? '');
    $stu     = $db->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
    $stu->execute([$sid]);
    $stu = $stu->fetch();

    if (!$stu) {
        $sitin_error = 'Student not found.';
    } elseif (($stu['sessions'] - $stu['used']) <= 0) {
        $sitin_error = 'Student has no remaining sessions.';
    } else {
        $active = $db->prepare("SELECT id FROM sitin_logs WHERE student_id=? AND status='active' LIMIT 1");
        $active->execute([$sid]);
        if ($active->fetch()) {
            $sitin_error = 'Student already has an active sit-in session.';
        } else {
            $db->prepare("INSERT INTO sitin_logs (student_id, lab_room, purpose, date_in, status) VALUES (?,?,?,NOW(),'active')")
               ->execute([$sid, $lab, $purpose]);
            $db->prepare("UPDATE students SET used = used + 1 WHERE id = ?")->execute([$sid]);
            $sitin_success = "Sit-in recorded for {$stu['first_name']} {$stu['last_name']}.";
            $stmt = $db->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
            $stmt->execute([$sid]);
            $search_result = $stmt->fetch();
        }
    }
}

if (isset($_GET['end']) && is_numeric($_GET['end'])) {
    $db->prepare("UPDATE sitin_logs SET status='completed', date_out=NOW() WHERE id=?")->execute([(int)$_GET['end']]);
    header('Location: AdminSitin.php'); exit;
}

$current_sitins = $db->query("
    SELECT sl.*, s.first_name, s.last_name, s.student_id as sid, (s.sessions - s.used) as remaining
    FROM sitin_logs sl JOIN students s ON sl.student_id = s.id
    WHERE sl.status = 'active' ORDER BY sl.date_in DESC
")->fetchAll();

$purposes  = ['C / C++','Java','Python','PHP / Web Development','Database (SQL)','Networking','Research / Thesis','Other'];
$lab_rooms = ['Lab 524','Lab 526','Lab 528','Lab 530','Lab 542'];
$nav_admin_active = 'sitin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Sit-In</title>
  <link rel="stylesheet" href="../css/Style.css"/>
  <link rel="stylesheet" href="../css/Admin.css"/>
</head>
<body class="admin-page">
<?php include __DIR__ . '/nav_admin.php'; ?>
<main class="admin-main">
  <span class="section-eyebrow">Administration</span>
  <h2 class="section-title">Sit-In Management</h2>

  <div class="admin-card" style="margin-bottom:22px;">
    <div class="admin-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Search Student for Sit-In
    </div>
    <div class="admin-table-wrap">
      <form method="GET" action="AdminSitin.php" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;">
        <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Enter Student ID or Name..." style="flex:1;padding:10px 14px;border:1.5px solid #ccdeed;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.88rem;outline:none;"/>
        <button type="submit" class="admin-btn blue">Search</button>
      </form>

      <?php if ($search_query && !$search_result): ?>
        <div class="admin-alert error">&#10005; No student found for "<?= htmlspecialchars($search_query) ?>".</div>
      <?php endif; ?>

      <?php if ($search_result): ?>
        <?php if ($sitin_success): ?><div class="admin-alert success">&#10003; <?= htmlspecialchars($sitin_success) ?></div><?php endif; ?>
        <?php if ($sitin_error):   ?><div class="admin-alert error">&#10005; <?= htmlspecialchars($sitin_error) ?></div><?php endif; ?>
        <button class="admin-btn blue" onclick="document.getElementById('sitinModal').classList.add('open')">+ Sit-In this Student</button>

        <div class="modal-overlay" id="sitinModal">
          <div class="modal-box">
            <div class="modal-header">Sit In Form <button class="modal-close" onclick="document.getElementById('sitinModal').classList.remove('open')">&#10005;</button></div>
            <form method="POST" action="AdminSitin.php?q=<?= urlencode($search_query) ?>">
              <input type="hidden" name="sitin_student_id" value="<?= $search_result['id'] ?>"/>
              <div class="modal-body">
                <div class="modal-row"><span class="modal-row-label">ID Number</span><span class="modal-row-val"><?= htmlspecialchars($search_result['student_id']) ?></span></div>
                <div class="modal-row"><span class="modal-row-label">Student Name</span><span class="modal-row-val"><?= htmlspecialchars($search_result['first_name'].' '.$search_result['last_name']) ?></span></div>
                <div class="modal-row">
                  <span class="modal-row-label">Purpose</span>
                  <select name="purpose" style="flex:1;padding:6px 10px;border:1.5px solid #ccdeed;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:0.85rem;outline:none;">
                    <?php foreach ($purposes as $p): ?><option><?= $p ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="modal-row">
                  <span class="modal-row-label">Lab</span>
                  <select name="lab_room" style="flex:1;padding:6px 10px;border:1.5px solid #ccdeed;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:0.85rem;outline:none;">
                    <?php foreach ($lab_rooms as $r): ?><option><?= $r ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="modal-row"><span class="modal-row-label">Remaining Sessions</span><span class="modal-row-val"><?= $search_result['sessions'] - $search_result['used'] ?></span></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="admin-btn ghost" onclick="document.getElementById('sitinModal').classList.remove('open')">Close</button>
                <button type="submit" class="admin-btn blue">Sit In</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Current Sit-In
    </div>
    <div class="admin-table-wrap">
      <div class="admin-table-toolbar">
        <div class="search-box-wrap"><label>Search:</label><input type="text" id="tableSearch" oninput="filterTable()" placeholder="Search..."/></div>
      </div>
      <table class="admin-table-box" id="sitinTable">
        <thead><tr><th>Sit ID</th><th>ID Number</th><th>Name</th><th>Purpose</th><th>Sit Lab</th><th>Session</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($current_sitins as $log): ?>
          <tr>
            <td><?= $log['id'] ?></td>
            <td><?= htmlspecialchars($log['sid']) ?></td>
            <td><?= htmlspecialchars($log['first_name'].' '.$log['last_name']) ?></td>
            <td><?= htmlspecialchars($log['purpose']) ?></td>
            <td><?= htmlspecialchars($log['lab_room']) ?></td>
            <td><?= $log['remaining'] ?></td>
            <td><span class="badge badge-active">Active</span></td>
            <td><a href="AdminSitin.php?end=<?= $log['id'] ?>" onclick="return confirm('End this sit-in session?')" class="admin-btn red sm">End Session</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($current_sitins)): ?>
            <tr><td colspan="8" class="empty-state">No active sit-in sessions.</td></tr>
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
  document.querySelectorAll('#sitinTable tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none'; });
}
</script>
</body>
</html>