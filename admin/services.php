<?php
require '../config/db.php';
require '../config/auth.php';
require_role('admin','login.php');
$u=current_user();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $a=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    if($a==='save'){
        $name=trim($_POST['name']??'');
        $desc=trim($_POST['description']??'');
        $dur=max(1,(int)($_POST['duration_minutes']??60));
        $fee=max(0,(float)($_POST['fee']??0));
        if($name==='') flash('error','Service name is required.');
        elseif($id){
            $pdo->prepare('UPDATE services SET name=?,description=?,duration_minutes=?,fee=? WHERE id=?')->execute([$name,$desc,$dur,$fee,$id]);
            log_activity($pdo,$u['id'],'Updated service',$name);
            flash('success','Service updated.');
        } else {
            $pdo->prepare('INSERT INTO services(name,description,duration_minutes,fee) VALUES(?,?,?,?)')->execute([$name,$desc,$dur,$fee]);
            log_activity($pdo,$u['id'],'Added service',$name);
            flash('success','Service added.');
        }
    } elseif($a==='toggle'&&$id){
        $pdo->prepare("UPDATE services SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$id]);
        log_activity($pdo,$u['id'],'Toggled service status','Service #'.$id);
        flash('success','Service status updated.');
    } elseif($a==='delete'&&$id){
        $s=$pdo->prepare('SELECT COUNT(*) FROM appointments WHERE service_id=?');$s->execute([$id]);
        if($s->fetchColumn()) flash('error','This service has appointment history and cannot be deleted. Set it inactive instead.');
        else {$pdo->prepare('DELETE FROM services WHERE id=?')->execute([$id]);flash('success','Service deleted.');}
    }
    redirect('services.php');
}

$edit=null;
if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM services WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$rows=$pdo->query('SELECT s.*,(SELECT COUNT(*) FROM appointments a WHERE a.service_id=s.id) appointment_count FROM services s ORDER BY s.status DESC,s.name')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Services</title><link rel="stylesheet" href="../assets/css/style.css?v=services-modal-20261002"><style>
/* Critical modal layout: kept here so browser cache cannot render the form as page content. */
#serviceModal.service-modal{position:fixed!important;inset:0!important;z-index:5000!important;display:none!important;align-items:center!important;justify-content:center!important;padding:24px!important;background:rgba(18,24,33,.52)!important;backdrop-filter:blur(4px)}
#serviceModal.service-modal.open{display:flex!important}
#serviceModal .service-modal-dialog{position:relative!important;width:min(680px,calc(100vw - 32px))!important;max-height:calc(100dvh - 48px)!important;overflow:auto!important;margin:0!important;background:#fffdf9!important;border:1px solid #e5dccf!important;border-radius:22px!important;padding:26px!important;box-shadow:0 28px 80px rgba(17,24,39,.25)!important}
#serviceModal .service-modal-head{display:flex!important;align-items:flex-start!important;justify-content:space-between!important;gap:18px!important;margin-bottom:18px!important}
#serviceModal .service-modal-close{width:38px!important;height:38px!important;display:grid!important;place-items:center!important;border:0!important;border-radius:50%!important;background:#f1e8da!important;cursor:pointer!important;font-size:24px!important}
#serviceModal .form-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:14px!important}
#serviceModal .field.full,#serviceModal .full{grid-column:1/-1!important}
#serviceModal .modal-actions{display:flex!important;justify-content:flex-end!important;gap:10px!important;margin-top:4px!important}
@media(max-width:700px){#serviceModal.service-modal{padding:0!important;align-items:flex-end!important}#serviceModal .service-modal-dialog{width:100%!important;max-height:92dvh!important;border-radius:22px 22px 0 0!important;padding:22px 16px!important}#serviceModal .form-grid{grid-template-columns:1fr!important}#serviceModal .field.full,#serviceModal .full{grid-column:auto!important}#serviceModal .modal-actions{flex-direction:column-reverse!important}#serviceModal .modal-actions .btn{width:100%!important}}
</style></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><header class="topbar"><div><button class="btn light mobile-toggle" data-nav-toggle>☰</button><div class="eyebrow">Configuration</div><h1>Parish services.</h1></div><div class="topbar-actions"><button type="button" class="btn" id="openServiceModal">+ Add Service</button></div></header>
<?php if($m=flash('success')):?><div class="notice"><?=e($m)?></div><?php endif;if($m=flash('error')):?><div class="notice error"><?=e($m)?></div><?php endif;?>
<section class="card service-manager-card services-full-card">
<div class="service-manager-head"><div><div class="eyebrow">Manage</div><h2>Current services</h2><p class="muted">Search, update availability, or edit a service. Use Add Service when you need a new one.</p></div><span class="service-count"><?=count($rows)?> services</span></div>
<div class="live-filter-bar service-filter"><label class="field live-search-field">Search<input class="search" type="search" placeholder="Search services..." data-live-search></label><label class="field">Status<select data-live-filter="status"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label><button type="button" class="btn light" data-live-clear>Clear</button></div>
<div class="live-filter-meta"><span data-live-count><?=count($rows)?></span> service<?=count($rows)===1?'':'s'?> shown</div>
<div class="service-manage-list" data-live-list>
<?php foreach($rows as $r): $fee=(float)$r['fee']; $search=strtolower($r['name'].' '.$r['description'].' '.$r['status']); ?>
<article class="service-manage-item" data-live-item data-search="<?=e($search)?>" data-status="<?=e($r['status'])?>">
<div class="service-manage-main"><div class="service-manage-title"><h3><?=e($r['name'])?></h3><span class="badge <?=e($r['status'])?>"><?=e($r['status'])?></span></div><p><?=e($r['description'] ?: 'No description added yet.')?></p><div class="service-meta"><span>◷ <?=$r['duration_minutes']?> min</span><span>•</span><span><?=$fee>0?'₱'.number_format($fee,2):'Free'?></span><span>•</span><span><?=number_format((int)$r['appointment_count'])?> booking<?=$r['appointment_count']==1?'':'s'?></span></div></div>
<div class="service-manage-actions"><button type="button" class="btn light small service-edit-btn" data-id="<?=$r['id']?>" data-name="<?=e($r['name'])?>" data-duration="<?=$r['duration_minutes']?>" data-fee="<?=e($r['fee'])?>" data-description="<?=e($r['description'])?>">Edit</button><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn light small"><?=$r['status']==='active'?'Deactivate':'Activate'?></button></form><?php if((int)$r['appointment_count']===0):?><form method="post" onsubmit="return confirm('Delete <?=e(addslashes($r['name']))?>? This cannot be undone.')"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn danger small">Delete</button></form><?php endif?></div>
</article>
<?php endforeach?>
<div class="live-empty" data-live-empty hidden>No services match your search or filter.</div>
</div></section>

<div class="service-modal" id="serviceModal" aria-hidden="true"><div class="service-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="serviceModalTitle"><div class="service-modal-head"><div><div class="eyebrow" id="serviceModalEyebrow">New service</div><h2 id="serviceModalTitle">Add service</h2><p class="muted" id="serviceModalHelp">Create a service parishioners can book.</p></div><button type="button" class="service-modal-close" id="closeServiceModal" aria-label="Close">×</button></div>
<form method="post" class="form-grid" id="serviceForm"><?=csrf_field()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="serviceId" value="0"><label class="field full">Service name<input name="name" id="serviceName" required placeholder="e.g. Baptism"></label><label class="field">Duration <span class="muted">(minutes)</span><input type="number" name="duration_minutes" id="serviceDuration" value="60" min="1" required></label><label class="field">Fee <span class="muted">(PHP)</span><input type="number" step="0.01" min="0" name="fee" id="serviceFee" value="0"></label><label class="field full">Description<textarea name="description" id="serviceDescription" rows="4" placeholder="Briefly describe this parish service."></textarea></label><div class="modal-actions full"><button class="btn light" type="button" id="cancelServiceModal">Cancel</button><button class="btn" id="serviceSubmit">Add service</button></div></form></div></div>
<script>
(()=>{const modal=document.getElementById('serviceModal'),form=document.getElementById('serviceForm'),id=document.getElementById('serviceId'),name=document.getElementById('serviceName'),duration=document.getElementById('serviceDuration'),fee=document.getElementById('serviceFee'),description=document.getElementById('serviceDescription'),title=document.getElementById('serviceModalTitle'),eyebrow=document.getElementById('serviceModalEyebrow'),help=document.getElementById('serviceModalHelp'),submit=document.getElementById('serviceSubmit');
function show(edit){if(edit){id.value=edit.dataset.id;name.value=edit.dataset.name||'';duration.value=edit.dataset.duration||60;fee.value=edit.dataset.fee||0;description.value=edit.dataset.description||'';eyebrow.textContent='Editing service';title.textContent='Edit service';help.textContent='Update this service without leaving the service list.';submit.textContent='Save changes'}else{form.reset();id.value='0';duration.value='60';fee.value='0';eyebrow.textContent='New service';title.textContent='Add service';help.textContent='Create a service parishioners can book.';submit.textContent='Add service'}modal.classList.add('open');modal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');setTimeout(()=>name.focus(),50)}
function hide(){modal.classList.remove('open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open')}
document.getElementById('openServiceModal').addEventListener('click',()=>show());document.querySelectorAll('.service-edit-btn').forEach(b=>b.addEventListener('click',()=>show(b)));document.getElementById('closeServiceModal').addEventListener('click',hide);document.getElementById('cancelServiceModal').addEventListener('click',hide);modal.addEventListener('click',e=>{if(e.target===modal)hide()});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&modal.classList.contains('open'))hide()});
<?php if($edit): ?>show(document.querySelector('.service-edit-btn[data-id="<?=$edit['id']?>"]'));<?php endif; ?>
})();
</script></main></div><script src="../assets/js/script.js"></script></body></html>
