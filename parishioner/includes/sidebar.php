<?php $page=basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
  <a class="brand" href="dashboard.php"><span class="brand-mark">✝</span><span>Smart Parish</span></a>
  <nav class="nav">
    <a class="<?= $page==='dashboard.php'?'active':'' ?>" href="dashboard.php">⌂ Dashboard</a>
    <a class="<?= $page==='book-service.php'?'active':'' ?>" href="book-service.php">＋ Book Service</a>
    <a class="<?= in_array($page,['my-appointments.php','appointment-details.php'],true)?'active':'' ?>" href="my-appointments.php">◷ My Appointments</a>
    <a class="<?= $page==='calendar.php'?'active':'' ?>" href="calendar.php">□ Calendar</a>
    <a class="<?= $page==='announcements.php'?'active':'' ?>" href="announcements.php">◇ Announcements</a>
    <a class="<?= $page==='profile.php'?'active':'' ?>" href="profile.php">○ Profile</a>
    <a class="bottom" href="logout.php">↪ Logout</a>
  </nav>
</aside>
