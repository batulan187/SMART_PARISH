<?php
require '../config/db.php';
require '../config/auth.php';
require_role('admin','login.php');
$u=current_user();
$errors=[];
$superConfig=require '../config/super_admin.php';
$superAuthorized=false;
$superActionToken='';

/*
 * Super Administrator access is intentionally one-page only.
 * We DO NOT store an "unlocked" flag in the session. A successful password
 * POST renders this page once; any browser refresh/navigation is a new GET
 * and therefore shows the password gate again.
 *
 * A short-lived, single-use action token is stored only so that a Create or
 * Activate/Deactivate action submitted from this authorized page can finish.
 * The token is consumed immediately, and the redirect returns to the locked
 * gate.
 */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='super_unlock'){
    verify_csrf();
    $candidate=(string)($_POST['super_password']??'');
    if(password_verify($candidate,(string)($superConfig['password_hash']??''))){
        session_regenerate_id(true);
        $superAuthorized=true;
        $superActionToken=bin2hex(random_bytes(32));
        $_SESSION['super_admin_action_token']=$superActionToken;
        log_activity($pdo,$u['id'],'Unlocked administrator management','One-page super password authorization verified');
    } else {
        $errors[]='Incorrect super password.';
    }
}

// Administrator-management actions are allowed only from the single page
// produced by a successful super-password check. Consume the token now so it
// cannot be replayed or survive the following redirect/reload.
$isAdminAction=$_SERVER['REQUEST_METHOD']==='POST' && in_array(($_POST['action']??''),['create','toggle'],true);
if($isAdminAction){
    $postedToken=(string)($_POST['super_action_token']??'');
    $sessionToken=(string)($_SESSION['super_admin_action_token']??'');
    if($postedToken==='' || $sessionToken==='' || !hash_equals($sessionToken,$postedToken)){
        unset($_SESSION['super_admin_action_token']);
        flash('error','Super administrator authorization expired. Enter the super password again.');
        redirect('admins.php');
    }
    unset($_SESSION['super_admin_action_token']);
    $superAuthorized=true;
}

if(!$superAuthorized){
    // A GET (including refresh, returning from another sidebar page, or a
    // direct URL visit) always clears any unused privilege token.
    if($_SERVER['REQUEST_METHOD']==='GET') unset($_SESSION['super_admin_action_token']);
    $success=flash('success');
    $gateError=flash('error');
    if($gateError) $errors[]=$gateError;
    ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administrator Access</title><link rel="stylesheet" href="../assets/css/style.css?v=20261002-super-admin-once"><style>
.super-gate-wrap{max-width:560px;margin:70px auto}.super-gate-card{padding:34px}.super-gate-icon{width:52px;height:52px;border-radius:50%;display:grid;place-items:center;background:#152238;color:#fff;font-size:24px;margin-bottom:18px}.super-gate-actions{display:flex;gap:10px;align-items:center;margin-top:18px}.super-gate-actions .btn{min-width:150px}@media(max-width:700px){.super-gate-wrap{margin:24px auto}.super-gate-card{padding:24px}.super-gate-actions{align-items:stretch;flex-direction:column}.super-gate-actions .btn{width:100%}}
</style></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><header class="topbar"><div><button class="btn light mobile-toggle" data-nav-toggle>☰</button><div class="eyebrow">Restricted access</div><h1>Administrators.</h1></div><div class="user-chip"><?=e($u['full_name'])?> · Administrator</div></header>
<?php if($success):?><div class="alert success"><?=e($success)?></div><?php endif?><?php if($errors):?><div class="alert error"><?=e(implode(' ',$errors))?></div><?php endif?>
<div class="super-gate-wrap"><section class="card super-gate-card"><div class="super-gate-icon">♙</div><div class="eyebrow">Super administrator</div><h2>Authorization required</h2><p class="muted">Only the priest or authorized super administrator can manage administrator accounts. For security, this password is required again whenever this page is reloaded or reopened.</p><form method="post" autocomplete="off"><?=csrf_field()?><input type="hidden" name="action" value="super_unlock"><label class="field">Super password<input type="password" name="super_password" required autofocus autocomplete="current-password" placeholder="Enter super password"></label><div class="super-gate-actions"><button class="btn" type="submit">Unlock access</button><a class="btn light" href="dashboard.php">Cancel</a></div></form></section></div></main></div><script src="../assets/js/script.js"></script></body></html><?php
    exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'create';
    if($action==='create'){
        $first=normalize_person_name($_POST['first_name']??'');
        $middle=normalize_person_name($_POST['middle_name']??'');
        $last=normalize_person_name($_POST['last_name']??'');
        $email=strtolower(trim($_POST['email']??''));
        $contact=preg_replace('/\s+/','',trim($_POST['contact_number']??''));
        $password=(string)($_POST['password']??'');
        $confirm=(string)($_POST['confirm_password']??'');
        if(strlen($first)<2) $errors[]='Enter a valid first name.';
        if(strlen($last)<2) $errors[]='Enter a valid last name.';
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
        elseif(!preg_match('/^[A-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$/i',$email)) $errors[]='Email must use @gmail.com, @yahoo.com, or @outlook.com.';
        if(!preg_match('/^09\d{9}$/',$contact)) $errors[]='Contact number must be exactly 11 digits and start with 09.';
        if(strlen($password)<8 || !preg_match('/[A-Z]/',$password) || !preg_match('/[^A-Za-z0-9]/',$password)) $errors[]='Password must be at least 8 characters with an uppercase letter and a special character.';
        if($password!==$confirm) $errors[]='Passwords do not match.';
        $check=$pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');$check->execute([$email]);
        if($check->fetch()) $errors[]='That email address is already registered.';
        if(!$errors){
            $full=trim($first.' '.($middle!==''?$middle.' ':'').$last);
            try{
                $stmt=$pdo->prepare("INSERT INTO users(first_name,middle_name,last_name,full_name,email,contact_number,address,birthday,gender,password,role,status) VALUES(?,?,?,?,?,?,'Parish Administration','1900-01-01','Other',?,'admin','active')");
                $stmt->execute([$first,$middle!==''?$middle:null,$last,$full,$email,$contact,password_hash($password,PASSWORD_DEFAULT)]);
                log_activity($pdo,$u['id'],'Created administrator account',$full.' ('.$email.')');
                flash('success','Administrator account created successfully.');
                redirect('admins.php');
            }catch(PDOException $e){$errors[]=$e->getCode()==='23000'?'That email address is already registered.':'Unable to create the administrator account.';}
        }
    }elseif($action==='toggle'){
        $id=(int)($_POST['id']??0);
        if($id===(int)$u['id']) $errors[]='You cannot deactivate your own signed-in account.';
        elseif($id>0){
            $stmt=$pdo->prepare("UPDATE users SET status=IF(status='active','inactive','active') WHERE id=? AND role='admin'");$stmt->execute([$id]);
            log_activity($pdo,$u['id'],'Changed administrator status','Admin user #'.$id);
            flash('success','Administrator status updated.');redirect('admins.php');
        }
    }
}
$success=flash('success');
$admins=$pdo->query("SELECT id,full_name,email,contact_number,status,created_at FROM users WHERE role='admin' ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administrators</title><link rel="stylesheet" href="../assets/css/style.css?v=20261002-admin-account-polish"><style>
/* Critical Administrators layout kept here so browser cache cannot leave this page in the old stacked layout. */
@media (min-width: 901px){
  .admin-account-grid{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;gap:20px!important;align-items:start!important}
  .admin-create-card,.admin-list-card{min-width:0!important}
  .admin-name-grid{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;gap:14px!important}
}
@media (max-width:900px){.admin-account-grid{display:grid!important;grid-template-columns:1fr!important}}
</style></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><header class="topbar"><div><button class="btn light mobile-toggle" data-nav-toggle>☰</button><div class="eyebrow">Access control</div><h1>Administrators.</h1></div><div class="user-chip"><?=e($u['full_name'])?> · Administrator</div></header>
<?php if($success):?><div class="alert success"><?=e($success)?></div><?php endif?><?php if($errors):?><div class="alert error"><?=e(implode(' ',$errors))?></div><?php endif?>
<div class="admin-account-grid">
<section class="card admin-create-card">
<div class="admin-card-heading"><div><div class="eyebrow">New account</div><h2>Add administrator</h2></div><p class="muted">Create a secure account with full administrator access.</p></div>
<form method="post" autocomplete="off" id="adminCreateForm"><?=csrf_field()?><input type="hidden" name="super_action_token" value="<?=e($superActionToken)?>"><input type="hidden" name="action" value="create">
<div class="admin-name-grid"><label class="field">First name<input name="first_name" required minlength="2" maxlength="80" autocomplete="given-name" value="<?=e($_POST['first_name']??'')?>" placeholder="First name"></label><label class="field">Middle name <span class="muted">(optional)</span><input name="middle_name" maxlength="80" autocomplete="additional-name" value="<?=e($_POST['middle_name']??'')?>" placeholder="Middle name"></label></div>
<div class="admin-name-grid"><label class="field">Last name<input name="last_name" required minlength="2" maxlength="80" autocomplete="family-name" value="<?=e($_POST['last_name']??'')?>" placeholder="Last name"></label><label class="field">Contact number<input type="tel" name="contact_number" required inputmode="numeric" minlength="11" maxlength="11" pattern="09[0-9]{9}" title="Enter exactly 11 digits starting with 09" placeholder="09XXXXXXXXX" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)" value="<?=e($_POST['contact_number']??'')?>"></label></div>
<label class="field">Email address<input type="email" name="email" required autocomplete="email" pattern="[A-Za-z0-9._%+\-]+@(gmail\.com|yahoo\.com|outlook\.com)" title="Use a Gmail, Yahoo, or Outlook email address" placeholder="name@gmail.com" value="<?=e($_POST['email']??'')?>"><small class="field-help">Gmail, Yahoo, or Outlook accounts only.</small></label>
<div class="admin-name-grid"><label class="field">Password<input type="password" name="password" required minlength="8" pattern="(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}" title="At least 8 characters with 1 uppercase letter and 1 special character" autocomplete="new-password" placeholder="Create password"></label><label class="field">Confirm password<input type="password" name="confirm_password" required minlength="8" autocomplete="new-password" placeholder="Repeat password"></label></div>
<div class="password-rules"><span>8+ characters</span><span>1 uppercase letter</span><span>1 special character</span></div>
<div class="admin-create-actions"><button class="btn" type="submit">Create administrator</button></div></form></section>
<section class="card admin-list-card" data-live-scope>
<div class="section-head admin-list-heading"><div><div class="eyebrow">Access list</div><h2>Administrator accounts</h2></div><span class="admin-total"><?=count($admins)?> <?=count($admins)===1?'account':'accounts'?></span></div>
<div class="live-filter-bar admin-filter-bar"><label class="field live-search-field"><span>Search</span><input type="search" data-live-search placeholder="Name, email or phone..."></label><label class="field"><span>Status</span><select data-live-filter="status"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label><button type="button" class="btn light" data-live-clear>Clear</button><span class="live-filter-meta" data-live-count></span></div>
<div data-live-list class="admin-account-list"><div data-live-empty class="live-empty" hidden>No administrator accounts match your search.</div><?php foreach($admins as $a):?><article class="admin-account-row" data-live-item data-status="<?=e(strtolower($a['status']))?>" data-search="<?=e(strtolower($a['full_name'].' '.$a['email'].' '.$a['contact_number']))?>"><div class="admin-account-main"><div class="admin-account-title"><strong><?=e($a['full_name'])?></strong></div><div class="admin-account-contact"><span><?=e($a['email'])?></span><span><?=e($a['contact_number']?:'No contact number')?></span></div><small class="muted">Added <?=date('M j, Y',strtotime($a['created_at']))?></small></div><div class="admin-account-controls"><div class="admin-account-badges"><span class="badge"><?=e(ucfirst($a['status']))?></span><?php if((int)$a['id']===(int)$u['id']):?><span class="badge subtle">You</span><?php endif?></div><?php if((int)$a['id']!==(int)$u['id']):?><form method="post" class="admin-account-action"><?=csrf_field()?><input type="hidden" name="super_action_token" value="<?=e($superActionToken)?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn light small" type="submit"><?=$a['status']==='active'?'Deactivate':'Activate'?></button></form><?php endif?></div></article><?php endforeach?></div></section></div>
</main></div><script src="../assets/js/script.js"></script><script>
const adminForm=document.getElementById('adminCreateForm');
if(adminForm){adminForm.addEventListener('submit',function(e){const p=this.elements.password,c=this.elements.confirm_password;if(p.value!==c.value){e.preventDefault();c.setCustomValidity('Passwords do not match.');c.reportValidity();}else c.setCustomValidity('');});adminForm.elements.confirm_password.addEventListener('input',function(){this.setCustomValidity(this.value===adminForm.elements.password.value?'':'Passwords do not match.');});}
</script></body></html>
