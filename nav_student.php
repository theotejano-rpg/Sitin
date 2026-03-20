<?php

$nav_student_active = $nav_student_active ?? 'home';
?>
<nav class="site-nav">
  <div class="nav-left">
    <img src="images/uclogo-removebg-preview-removebg-preview.png" alt="University of Cebu"/>
    <div class="nav-left-text">
      <strong>CCS Student Portal</strong>
      <small>University of Cebu</small>
    </div>
  </div>

  <div class="nav-links">
    <a href="Students.php"  class="nav-link <?= $nav_student_active === 'home'      ? 'active' : '' ?>">Home</a>
    <a href="Community.php" class="nav-link <?= $nav_student_active === 'community' ? 'active' : '' ?>">Community</a>
    <a href="About.php"     class="nav-link <?= $nav_student_active === 'about'     ? 'active' : '' ?>">About</a>
  </div>

  <div class="nav-right student-nav-right">

    <a href="Notifications.php" class="sn-icon-btn" title="Notifications">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-dot"></span>
    </a>

    <a href="Students.php" class="sn-icon-btn <?= $nav_student_active === 'home' ? 'active' : '' ?>" title="Home">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
    </a>

    <a href="EditProfile.php" class="sn-icon-btn" title="Edit Profile">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
    </a>

    <a href="History.php" class="sn-icon-btn" title="History">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="12 8 12 12 14 14"/>
        <path d="M3.05 11a9 9 0 1 1 .5 4"/>
        <polyline points="3 21 3 16 8 16"/>
      </svg>
    </a>

    <a href="Reservation.php" class="sn-icon-btn" title="Reservation">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8"  y1="2" x2="8"  y2="6"/>
        <line x1="3"  y1="10" x2="21" y2="10"/>
      </svg>
    </a>

    <div class="sn-divider"></div>

    <a href="Logout.php" class="nav-btn solid logout-btn" title="Log Out">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Log Out
    </a>
  </div>
</nav>