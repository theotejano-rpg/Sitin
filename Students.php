<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['student'])) {
    header('Location: Login.php');
    exit;
}

$db   = get_db();
$stmt = $db->prepare('SELECT * FROM students WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['student']['id']]);
$student = $stmt->fetch();

if (!$student) {
    session_destroy();
    header('Location: Login.php');
    exit;
}

$remaining = $student['sessions'] - $student['used'];
$pct       = ($student['sessions'] > 0) ? round(($student['used'] / $student['sessions']) * 100) : 0;

$announcements = [
    ['date'=>'March 10, 2026','title'=>'Mid-Term Examination Schedule Released','body'=>'The official mid-term examination schedule for AY 2025-2026 has been posted on the CCS bulletin board. Please review your assigned rooms and time slots carefully.','tag'=>'Academics','tag_cls'=>'tag--blue'],
    ['date'=>'March 8, 2026', 'title'=>'SitIn Lab Hours Extended Until April',  'body'=>'Lab sit-in hours are extended until 8:00 PM on weekdays for the remainder of the semester. Students must present their valid ID and log in through the portal.','tag'=>'Sit-In','tag_cls'=>'tag--violet'],
    ['date'=>'March 5, 2026', 'title'=>'CCS Research Colloquium - Call for Papers','body'=>'The College of Computer Studies invites all undergraduate and graduate students to submit research abstracts for the Annual CCS Research Colloquium on April 18, 2026.','tag'=>'Event','tag_cls'=>'tag--gold'],
];

$rules = [
    ['num'=>'01','title'=>'Valid ID Required',         'body'=>'Students must present a valid University of Cebu ID before entering the laboratory. No ID, no entry.'],
    ['num'=>'02','title'=>'Prior Reservation',         'body'=>'All sit-in sessions must be reserved through the portal at least one (1) hour before the intended session start time.'],
    ['num'=>'03','title'=>'Session Time Limit',        'body'=>'Each sit-in session is limited to a maximum of three (3) hours. Students who exceed the time limit will be asked to vacate.'],
    ['num'=>'04','title'=>'No Food or Drink',          'body'=>'Eating and drinking inside the laboratory are strictly prohibited. Violations may result in suspension of sit-in privileges.'],
    ['num'=>'05','title'=>'Proper Use of Equipment',   'body'=>'Students are responsible for equipment they use. Damage caused by negligence will be subject to replacement fees.'],
    ['num'=>'06','title'=>'Session Limit Per Semester','body'=>'Each student is allotted a maximum of 30 sit-in sessions per semester. Sessions do not carry over to the next semester.'],
    ['num'=>'07','title'=>'Dress Code',                'body'=>'Students must observe the University dress code at all times inside the laboratory. Proper school attire is mandatory.'],
    ['num'=>'08','title'=>'Silence & Discipline',      'body'=>'Maintain silence and discipline inside the laboratory. Mobile phones must be set to silent mode at all times.'],
];

$nav_student_active = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UC CCS &mdash; Student Portal</title>
  <link rel="stylesheet" href="css/Style.css"/>
  <link rel="stylesheet" href="css/Students.css"/>
</head>
<body class="student-page">

<?php include __DIR__ . '/nav_student.php'; ?>

  <main class="student-main">

    <aside class="profile-sidebar">
      <div class="profile-card">
        <div class="profile-avatar-ring">
          <div class="profile-avatar">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
        <p class="profile-id"><?= htmlspecialchars($student['student_id']) ?></p>
        <div class="profile-details">
          <div class="profile-row">
            <span class="profile-label">Name</span>
            <span class="profile-value"><?= htmlspecialchars($student['first_name'] . ($student['middle_name'] ? ' '.$student['middle_name'] : '') . ' ' . $student['last_name']) ?></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">Course</span>
            <span class="profile-value"><?= htmlspecialchars($student['course']) ?></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">Year</span>
            <span class="profile-value"><?= htmlspecialchars($student['level']) ?></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">Email</span>
            <span class="profile-value"><?= htmlspecialchars($student['email']) ?></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">Address</span>
            <span class="profile-value"><?= htmlspecialchars($student['address']) ?></span>
          </div>
        </div>
      </div>

      <div class="session-card">
        <div class="session-header">
          <span class="session-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
          </span>
          <span class="session-title">Sit-In Sessions</span>
        </div>
        <div class="session-counter">
          <span class="session-remaining"><?= $remaining ?></span>
          <span class="session-total">/ <?= $student['sessions'] ?> remaining</span>
        </div>
        <div class="session-bar-wrap">
          <div class="session-bar">
            <div class="session-bar-fill" style="width:<?= $pct ?>%"></div>
          </div>
          <span class="session-pct"><?= $student['used'] ?> used</span>
        </div>
        <a href="Reservation.php" class="session-cta">Reserve a Session &rarr;</a>
      </div>
    </aside>

    <div class="student-content">

      <section class="content-section" id="announcements">
        <div class="section-header">
          <div class="section-title-group">
            <span class="section-eyebrow">From the Administration</span>
            <h2 class="section-title">Announcements</h2>
          </div>
          <a href="#" class="section-link">View all &rarr;</a>
        </div>
        <div class="announcements-list">
          <?php foreach ($announcements as $ann): ?>
          <article class="announcement-card">
            <div class="ann-top">
              <span class="ann-tag <?= $ann['tag_cls'] ?>"><?= htmlspecialchars($ann['tag']) ?></span>
              <span class="ann-date"><?= htmlspecialchars($ann['date']) ?></span>
            </div>
            <h3 class="ann-title"><?= htmlspecialchars($ann['title']) ?></h3>
            <p class="ann-body"><?= htmlspecialchars($ann['body']) ?></p>
          </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="content-section" id="rules">
        <div class="section-header">
          <div class="section-title-group">
            <span class="section-eyebrow">Laboratory Policies</span>
            <h2 class="section-title">Rules &amp; Regulations</h2>
          </div>
        </div>
        <div class="rules-grid">
          <?php foreach ($rules as $rule): ?>
          <div class="rule-card">
            <div class="rule-num"><?= $rule['num'] ?></div>
            <div class="rule-body">
              <h4 class="rule-title"><?= htmlspecialchars($rule['title']) ?></h4>
              <p class="rule-text"><?= htmlspecialchars($rule['body']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

    </div>
  </main>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>