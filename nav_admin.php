<?php
$nav_admin_active = $nav_admin_active ?? 'home';
?>
<nav class="admin-nav">
  <div class="nav-left">
    <img src="images/uclogo-removebg-preview-removebg-preview.png" alt="UC"/>
    <div class="nav-left-text">
      <strong>CCS Admin Portal</strong>
      <small>College of Computer Studies</small>
    </div>
  </div>
  <div class="admin-nav-links">
    <a href="admin.php"         class="admin-nav-link <?= $nav_admin_active==='home'        ?'active':'' ?>">Home</a>
    <a href="AdminSearch.php"   class="admin-nav-link <?= $nav_admin_active==='search'      ?'active':'' ?>">Search</a>
    <a href="AdminStudents.php" class="admin-nav-link <?= $nav_admin_active==='students'    ?'active':'' ?>">Students</a>
    <a href="AdminSitin.php"    class="admin-nav-link <?= $nav_admin_active==='sitin'       ?'active':'' ?>">Sit-In</a>
    <a href="AdminRecords.php"  class="admin-nav-link <?= $nav_admin_active==='records'     ?'active':'' ?>">View Sit-in Records</a>
    <a href="AdminReports.php"  class="admin-nav-link <?= $nav_admin_active==='reports'     ?'active':'' ?>">Sit-in Reports</a>
    <a href="Reservation.php"   class="admin-nav-link <?= $nav_admin_active==='reservation' ?'active':'' ?>">Reservation</a>
  </div>
  <div class="nav-right">
    <a href="Logout.php" class="logout-btn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Log out
    </a>
  </div>
</nav>