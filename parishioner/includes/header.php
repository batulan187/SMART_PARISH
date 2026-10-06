<?php $u=current_user(); ?>
<header class="topbar"><div><button class="btn light mobile-toggle" type="button" data-nav-toggle>☰</button><div class="eyebrow"><?=e($eyebrow??'Parishioner Portal')?></div><h1><?=e($pageTitle??'Smart Parish')?></h1></div><div class="user-chip"><?php if(!empty($u['profile_image'])):?><img class="user-avatar" src="../<?=e($u['profile_image'])?>" alt="<?=e($u['full_name']??'Parishioner')?> profile picture"><?php else:?><span class="user-avatar user-avatar-fallback"><?=e(strtoupper(substr($u['full_name']??'P',0,1)))?></span><?php endif?><span><?=e($u['full_name']??'Parishioner')?></span></div></header>
<?php if($bookingSuccess=flash('booking_success')):
    $bookingParts=explode('|',$bookingSuccess,3);
    $bookingTitle=$bookingParts[0]??'Booking submitted successfully.';
    $bookingReference=$bookingParts[2]??'';
?>
<div class="submission-success-overlay" data-submission-success data-success-redirect="my-appointments.php" role="dialog" aria-modal="true" aria-labelledby="submissionSuccessTitle">
  <div class="submission-success-card">
    <div class="submission-success-icon" aria-hidden="true"><span>✓</span></div>
    <div class="eyebrow">Request received</div>
    <h2 id="submissionSuccessTitle"><?=e($bookingTitle)?></h2>
    <p>Your booking request has been sent to the parish office and is now waiting for approval.</p>
    <?php if($bookingReference):?><div class="submission-reference"><span>Reference number</span><strong><?=e($bookingReference)?></strong></div><?php endif;?>
    <a href="my-appointments.php" class="btn submission-success-btn" data-submission-success-close>Okay</a>
  </div>
</div>
<?php endif;?>
<?php if($m=flash('success')):?><div class="notice"><?=e($m)?></div><?php endif;?>
<?php if($m=flash('error')):?><div class="notice error"><?=e($m)?></div><?php endif;?>
