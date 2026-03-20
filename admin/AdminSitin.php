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
  <style>
    body.admin-page a { text-decoration: none !important; }

    .sitin-search-bar {
      display: flex;
      gap: 10px;
      align-items: center;
      padding: 16px 20px;
    }

    .sitin-search-bar input {
      width: 280px;
      padding: 9px 14px;
      border: 1.5px solid #ccdeed;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem;
      outline: none;
      transition: border-color 0.2s;
    }

    .sitin-search-bar input:focus { border-color: #1877c9; }

    .sitin-table-wrap { padding: 0 20px 20px; }

    .sitin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
    }

    .sitin-table th {
      padding: 12px 16px;
      text-align: left;
      background: rgba(10,77,140,0.06);
      color: var(--blue-deep);
      font-weight: 600;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid rgba(10,77,140,0.1);
      white-space: nowrap;
    }

    .sitin-table td {
      padding: 14px 16px;
      color: var(--ink);
      border-bottom: 1px solid rgba(204,222,237,0.4);
      vertical-align: middle;
      white-space: nowrap;
    }

    .sitin-table tr:last-child td { border-bottom: none; }
    .sitin-table tr:hover td { background: rgba(10,77,140,0.02); }

    .inline-search-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      border-bottom: 1px solid rgba(204,222,237,0.4);
    }

    .inline-search-wrap label { font-size: 0.8rem; color: var(--ink-soft); }
    .inline-search-wrap input {
      width: 200px;
      padding: 6px 12px;
      border: 1.5px solid #ccdeed;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.82rem;
      outline: none;
    }
    .inline-search-wrap input:focus { border-color: #1877c9; }
  </style>
</head>
<body class="admin-page" style="display:flex;flex-direction:column;min-height:100vh;">
<?php include __DIR__ . '/nav_admin.php'; ?>
<main class="admin-main" style="flex:1;">
  <span class="section-eyebrow">Administration</span>
  <h2 class="section-title">Sit-In Management</h2>



  <!-- Current Sit-ins -->
  <div class="admin-card">
    <div class="admin-card-header">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Current Sit-In
    </div>
    <div class="inline-search-wrap">
      <label>Search:</label>
      <input type="text" id="tableSearch" oninput="filterTable()" placeholder="Search..."/>
    </div>
    <div class="sitin-table-wrap">
      <table class="sitin-table" id="sitinTable">
        <thead>
          <tr>
            <th>Sit ID</th>
            <th>ID Number</th>
            <th>Name</th>
            <th>Purpose</th>
            <th>Sit Lab</th>
            <th>Sessions Left</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
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
  document.querySelectorAll('#sitinTable tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>