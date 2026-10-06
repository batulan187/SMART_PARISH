<?php
declare(strict_types=1);
require '../config/db.php';
require '../config/auth.php';
require_role('admin', 'login.php');
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $adminNote = trim($_POST['admin_note'] ?? '');
    $allowed = ['approve' => 'approved', 'reject' => 'rejected', 'complete' => 'completed'];
    if($action==='verify_payment'){
        $s=$pdo->prepare("SELECT * FROM appointments WHERE id=? AND payment_method='online' AND receipt_path IS NOT NULL");$s->execute([$id]);$pay=$s->fetch();
        if($pay){$pdo->prepare("UPDATE appointments SET payment_status='verified',payment_verified_at=NOW(),payment_verified_by=? WHERE id=?")->execute([$u['id'],$id]);log_activity($pdo,$u['id'],'Online payment verified',$pay['reference_no']);flash('success','Payment receipt verified.');}else flash('error','No online payment receipt was found.');redirect('appointments.php');
    }

    if (isset($allowed[$action])) {
        $s = $pdo->prepare('SELECT * FROM appointments WHERE id=?');
        $s->execute([$id]);
        $a = $s->fetch();

        if ($a) {
            if ($action === 'reject' && $adminNote === '') {
                flash('error', 'A rejection reason is required before rejecting an appointment.');
                redirect('appointments.php');
            }

            if($action==='approve' && $a['payment_method']==='online' && $a['payment_status']!=='verified'){flash('error','Verify the online payment receipt before approving this booking.');redirect('appointments.php');}
            if ($action === 'approve' && $a['resource_id']) {
                $c = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE id<>? AND resource_id=? AND appointment_date=? AND status='approved' AND start_time < ? AND end_time > ?");
                $c->execute([$id, $a['resource_id'], $a['appointment_date'], $a['end_time'], $a['start_time']]);
                if ($c->fetchColumn()) {
                    flash('error', 'Conflict detected: this resource already has an approved appointment in that time range.');
                    redirect('appointments.php');
                }
            }

            $status = $allowed[$action];
            $noteToStore = $action === 'reject' ? $adminNote : ($adminNote !== '' ? $adminNote : null);
            $pdo->prepare('UPDATE appointments SET status=?, admin_note=? WHERE id=?')->execute([$status, $noteToStore, $id]);
            $pdo->prepare('INSERT INTO appointment_history(appointment_id,status,note,changed_by) VALUES(?,?,?,?)')->execute([$id, $status, $noteToStore, $u['id']]);

            $message = 'Your appointment '.$a['reference_no'].' is now '.$status.'.';
            if ($action === 'reject') {
                $message .= ' Reason: '.$adminNote;
            }
            $pdo->prepare('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)')->execute([$a['user_id'], 'Appointment update', $message]);
            log_activity($pdo, $u['id'], 'Appointment '.$status, $a['reference_no'].($action === 'reject' ? ' — Reason: '.$adminNote : ''));
            flash('success', $action === 'reject' ? 'Appointment rejected and the reason was sent to the parishioner.' : 'Appointment updated.');
        }
    }
    redirect('appointments.php');
}

$q = '';
$status = '';
$sql = "SELECT a.*,s.name service_name,r.name resource_name,u.full_name person,u.email FROM appointments a JOIN services s ON s.id=a.service_id JOIN users u ON u.id=a.user_id LEFT JOIN resources r ON r.id=a.resource_id WHERE 1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (a.reference_no LIKE ? OR u.full_name LIKE ? OR s.name LIKE ?)';
    $like = "%$q%";
    $params = [$like,$like,$like];
}
if (in_array($status, ['pending','approved','rejected','completed','cancelled'], true)) {
    $sql .= ' AND a.status=?';
    $params[] = $status;
}
$sql .= ' ORDER BY a.appointment_date DESC,a.start_time DESC';
$s = $pdo->prepare($sql);
$s->execute($params);
$rows = $s->fetchAll();
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Appointments</title><link rel="stylesheet" href="../assets/css/style.css">
<style>
.reject-backdrop{position:fixed;inset:0;background:rgba(17,24,39,.48);display:none;align-items:center;justify-content:center;padding:20px;z-index:1000}.reject-backdrop.open{display:flex}.reject-modal{width:min(560px,100%);background:#fffdf9;border:1px solid var(--line);border-radius:22px;padding:26px;box-shadow:0 25px 70px rgba(17,24,39,.2)}.reject-modal h2{margin:0 0 8px}.reject-modal textarea{width:100%;min-height:125px;resize:vertical;margin-top:8px}.reject-help{font-size:13px;color:#77736c;margin:6px 0 18px}.reason-preview{max-width:260px;margin-top:6px;font-size:12px;color:#8a4b4b}
</style></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><header class="topbar"><div><button class="btn light mobile-toggle" data-nav-toggle>☰</button><div class="eyebrow">Management</div><h1>Appointments.</h1></div></header>
<?php if($m=flash('success')):?><div class="notice"><?=e($m)?></div><?php endif;if($m=flash('error')):?><div class="notice error"><?=e($m)?></div><?php endif;?>
<section class="card table-wrap" data-live-scope><div class="live-filter-bar"><label class="field live-search-field">Search<input class="search" type="search" data-live-search placeholder="Search reference, parishioner, service, resource..."></label><label class="field">Status<select data-live-filter="status"><option value="">All statuses</option><?php foreach(['pending','approved','rejected','completed','cancelled'] as $x):?><option value="<?=$x?>"><?=ucfirst($x)?></option><?php endforeach?></select></label><label class="field">Payment<select data-live-filter="payment"><option value="">All payments</option><option value="cash">Cash</option><option value="online">Online</option></select></label><button type="button" class="btn light" data-live-clear>Clear</button><span class="live-filter-meta" data-live-count></span></div><table class="table"><thead><tr><th>Reference / Person</th><th>Service</th><th>Schedule</th><th>Resource</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead><tbody data-live-list><tr data-live-empty class="live-empty" hidden><td colspan="7">No appointments match your search or filters.</td></tr>
<?php foreach($rows as $a):?><tr data-live-item data-status="<?=e(strtolower($a['status']))?>" data-payment="<?=e(strtolower($a['payment_method']??'cash'))?>" data-search="<?=e(strtolower($a['reference_no'].' '.$a['person'].' '.$a['email'].' '.$a['service_name'].' '.($a['resource_name']??'').' '.$a['appointment_date']))?>"><td><b><?=e($a['reference_no'])?></b><br><?=e($a['person'])?><?php if($a['status']==='rejected' && $a['admin_note']):?><div class="reason-preview"><b>Reason:</b> <?=e($a['admin_note'])?></div><?php endif?></td><td><?=e($a['service_name'])?><?php if(strcasecmp($a['service_name'],'Wedding')===0):?><br><a class="btn light small" href="wedding-details.php?id=<?=$a['id']?>">Wedding details</a><?php endif?><?php if(strcasecmp($a['service_name'],'Baptism')===0):?><br><a class="btn light small" href="baptism-details.php?id=<?=$a['id']?>">Baptism details</a><?php endif?></td><td><?=date('M j, Y',strtotime($a['appointment_date']))?><br><?=date('g:i A',strtotime($a['start_time']))?>–<?=date('g:i A',strtotime($a['end_time']))?></td><td><?=e($a['resource_name']??'—')?></td><td><b><?=ucfirst(e($a['payment_method']??'cash'))?></b><br><small class="muted"><?=e($a['payment_status']??'not_required')?></small><?php if(!empty($a['receipt_path'])):?><br><a class="btn light small" target="_blank" href="receipt.php?id=<?=$a['id']?>">View receipt</a><?php endif?><?php if(($a['payment_method']??'')==='online'&&($a['payment_status']??'')==='pending'):?><form method="post" style="margin-top:6px"><?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn small" name="action" value="verify_payment">Verify payment</button></form><?php endif?></td><td><span class="badge <?=e($a['status'])?>"><?=e($a['status'])?></span></td><td>
<?php if($a['status']==='pending'):?><form method="post" class="actions"><?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn small" name="action" value="approve">Approve</button><button type="button" class="btn danger small reject-open" data-id="<?=$a['id']?>" data-ref="<?=e($a['reference_no'])?>" data-person="<?=e($a['person'])?>">Reject</button></form><?php elseif($a['status']==='approved'):?><form method="post" class="actions"><?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn small" name="action" value="complete">Complete</button></form><?php endif?>
</td></tr><?php endforeach?></tbody></table></section></main></div>

<div class="reject-backdrop" id="rejectBackdrop" aria-hidden="true"><div class="reject-modal" role="dialog" aria-modal="true" aria-labelledby="rejectTitle"><div class="eyebrow">Appointment decision</div><h2 id="rejectTitle">Reject appointment</h2><p class="muted" id="rejectContext"></p><form method="post" id="rejectForm"><?=csrf_field()?><input type="hidden" name="id" id="rejectId"><input type="hidden" name="action" value="reject"><label class="field"><b>Reason for rejection</b><textarea name="admin_note" id="rejectReason" required maxlength="1000" placeholder="Explain why this appointment cannot be approved and, when possible, tell the parishioner what they should do next."></textarea></label><div class="reject-help">This reason will be visible to the parishioner in Appointment Details and their status timeline.</div><div class="actions"><button type="button" class="btn light" id="rejectCancel">Cancel</button><button type="submit" class="btn danger">Confirm rejection</button></div></form></div></div>
<script src="../assets/js/script.js"></script><script>
const rb=document.getElementById('rejectBackdrop'),ri=document.getElementById('rejectId'),rr=document.getElementById('rejectReason'),rc=document.getElementById('rejectContext');
document.querySelectorAll('.reject-open').forEach(b=>b.addEventListener('click',()=>{ri.value=b.dataset.id;rc.textContent=b.dataset.ref+' • '+b.dataset.person;rr.value='';rb.classList.add('open');rb.setAttribute('aria-hidden','false');setTimeout(()=>rr.focus(),50)}));
function closeReject(){rb.classList.remove('open');rb.setAttribute('aria-hidden','true')}
document.getElementById('rejectCancel').addEventListener('click',closeReject);rb.addEventListener('click',e=>{if(e.target===rb)closeReject()});document.addEventListener('keydown',e=>{if(e.key==='Escape')closeReject()});
</script></body></html>
