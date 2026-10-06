<?php
declare(strict_types=1);
require '../config/db.php';
require '../config/auth.php';
require_role('admin','login.php');
$u=current_user();
$ref=trim($_GET['ref']??$_POST['ref']??'');
$a=null;
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='confirm'){
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $s=$pdo->prepare("SELECT * FROM appointments WHERE id=? AND status='approved'");
    $s->execute([$id]);$x=$s->fetch();
    if(!$x){flash('error','Only an approved appointment can be confirmed.');}
    elseif($x['appointment_date']>date('Y-m-d')){flash('error','This appointment is scheduled for a future date and cannot be confirmed yet.');}
    else{
        $pdo->prepare("UPDATE appointments SET status='completed',qr_confirmed_at=NOW(),qr_confirmed_by=? WHERE id=?")->execute([$u['id'],$id]);
        $pdo->prepare("INSERT INTO appointment_history(appointment_id,status,note,changed_by) VALUES(?,'completed','QR/reference presented and confirmed by parish administrator',?)")->execute([$id,$u['id']]);
        $pdo->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)")->execute([$x['user_id'],'Appointment confirmed','Your appointment '.$x['reference_no'].' was verified at the parish office.']);
        log_activity($pdo,$u['id'],'Appointment QR confirmed',$x['reference_no']);
        flash('success','Appointment verified and marked completed.');$ref=$x['reference_no'];
    }
}
if($ref!==''){
    $s=$pdo->prepare("SELECT a.*,u.full_name,u.profile_image,s.name service_name,r.name resource_name FROM appointments a JOIN users u ON u.id=a.user_id JOIN services s ON s.id=a.service_id LEFT JOIN resources r ON r.id=a.resource_id WHERE a.reference_no=? LIMIT 1");
    $s->execute([$ref]);$a=$s->fetch();
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify Appointment</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><header class="topbar"><div><button class="btn light mobile-toggle" data-nav-toggle>☰</button><div class="eyebrow">Check-in</div><h1>Verify QR / reference.</h1></div></header>
<?php if($m=flash('success')):?><div class="notice"><?=e($m)?></div><?php endif;if($m=flash('error')):?><div class="notice error"><?=e($m)?></div><?php endif?>
<section class="card scanner-input-card">
  <div class="eyebrow">Barcode / QR scanner</div>
  <h2>Scan or type appointment reference</h2>
  <p class="muted">Keep this page open, then scan the parishioner's appointment QR using your physical scanner. The scanner will type the reference here and verify it automatically when it sends Enter. You can also type the reference manually.</p>
  <form method="get" id="referenceForm" class="form-grid" autocomplete="off">
    <label class="field full"><span>Appointment reference</span><input id="referenceInput" name="ref" value="<?=e($ref)?>" placeholder="SP-YYYYMMDD-XXXXXX" autocomplete="off" autocapitalize="characters" spellcheck="false" autofocus required></label>
    <div class="scanner-ready" id="scannerReady"><span class="scanner-ready-dot"></span><span>Scanner ready — scan the QR now</span></div>
    <div class="actions"><button class="btn" type="submit">Verify reference</button><button class="btn light" type="button" id="clearReference">Clear</button></div>
  </form>
  <p class="muted scanner-help">Tip: most USB/Bluetooth scanners work like a keyboard and send <b>Enter</b> after a successful scan. No camera permission is required.</p>
</section>
<?php if($ref!==''&&!$a):?><br><div class="notice error">No appointment found for that reference.</div><?php endif?>
<?php if($a):?><br><section class="card verify-result"><div class="eyebrow">Verification result</div><h2><?=e($a['service_name'])?></h2><p><b><?=e($a['reference_no'])?></b> · <?=e($a['full_name'])?></p><p><?=date('F j, Y',strtotime($a['appointment_date']))?> · <?=date('g:i A',strtotime($a['start_time']))?> · <?=e($a['resource_name']??'No room assigned')?></p><p>Status: <span class="badge <?=e($a['status'])?>"><?=e($a['status'])?></span> · Payment: <b><?=e($a['payment_method'])?></b> / <?=e(str_replace('_',' ',$a['payment_status']))?></p>
<?php if($a['status']==='approved'):?><div class="notice"><b>Valid approved appointment.</b> Confirm only after checking that the parishioner is presenting this QR/reference at the parish.</div><form method="post" onsubmit="return confirm('Confirm that this parishioner presented the appointment QR/reference?')"><?=csrf_field()?><input type="hidden" name="ref" value="<?=e($ref)?>"><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn" name="action" value="confirm">Confirm presented QR / reference</button></form>
<?php elseif($a['status']==='completed'):?><div class="notice">This QR/reference has already been confirmed and the appointment is completed.</div>
<?php elseif($a['status']==='rejected'||$a['status']==='cancelled'):?><div class="notice error">This appointment is <?=e($a['status'])?> and cannot be confirmed.</div>
<?php else:?><div class="notice error">This appointment is not approved yet and cannot be confirmed.</div><?php endif?></section><?php endif?>
</main></div><script src="../assets/js/script.js"></script>
<script>
(()=>{
 const input=document.getElementById('referenceInput');
 const form=document.getElementById('referenceForm');
 const clear=document.getElementById('clearReference');
 const ready=document.getElementById('scannerReady');
 if(!input||!form)return;
 const clean=(value)=>{
   const text=String(value||'').trim().toUpperCase();
   const match=text.match(/SP-\d{8}-[A-Z0-9]+/i);
   return match ? match[0].toUpperCase() : text;
 };
 const focusScanner=()=>{ if(document.activeElement!==input) input.focus(); };
 input.addEventListener('input',()=>{
   input.value=input.value.toUpperCase().replace(/\s+/g,'');
   ready.classList.add('active');
 });
 input.addEventListener('keydown',(event)=>{
   if(event.key==='Enter'){
     event.preventDefault();
     input.value=clean(input.value);
     if(input.value){
       ready.innerHTML='<span class="scanner-ready-dot"></span><span>Reference received — verifying…</span>';
       form.requestSubmit();
     }
   }
 });
 form.addEventListener('submit',()=>{ input.value=clean(input.value); });
 clear?.addEventListener('click',()=>{ input.value=''; focusScanner(); });
 document.addEventListener('click',(event)=>{
   if(!event.target.closest('button, a, input, select, textarea, form')) focusScanner();
 });
 setTimeout(()=>{ input.select(); focusScanner(); },100);
})();
</script></body></html>
