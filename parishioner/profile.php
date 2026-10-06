<?php
declare(strict_types=1); require '../config/db.php'; require '../config/auth.php'; require_role('parishioner','login.php');
$sessionUser=current_user(); $uid=(int)$sessionUser['id']; $s=$pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$uid]); $user=$s->fetch(); $errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $action=$_POST['action']??'profile';
 if($action==='profile'){
  $profileImage=$user['profile_image']??null;
  $first=normalize_person_name($_POST['first_name']??''); $middle=normalize_person_name($_POST['middle_name']??''); $last=normalize_person_name($_POST['last_name']??''); $name=trim(implode(' ',array_filter([$first,$middle,$last]))); $email=strtolower(trim($_POST['email']??'')); $contact=trim($_POST['contact_number']??'');
  $provinceCode=trim($_POST['province_code']??''); $province=trim($_POST['province']??''); $cityCode=trim($_POST['city_municipality_code']??''); $city=trim($_POST['city_municipality']??''); $barangayCode=trim($_POST['barangay_code']??''); $barangay=trim($_POST['barangay']??''); $street=trim($_POST['street_purok']??'');
  $birthday=$_POST['birthday']??null; $gender=$_POST['gender']??null;
  if(strlen($first)<2)$errors[]='Enter your first name.'; if(strlen($last)<2)$errors[]='Enter your last name.'; if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email.'; elseif(!preg_match('/^[A-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$/i',$email))$errors[]='Email must use @gmail.com, @yahoo.com, or @outlook.com.'; if(!preg_match('/^09\d{9}$/',$contact))$errors[]='Contact number must be exactly 11 digits and start with 09.';
  if(!$provinceCode||!$province)$errors[]='Select your province.'; if(!$cityCode||!$city)$errors[]='Select your city or municipality.'; if(!$barangayCode||!$barangay)$errors[]='Select your barangay.'; if(!$street)$errors[]='Enter your purok, street, or house address.';
  if(!in_array($gender,['Male','Female','Other'],true))$errors[]='Select a valid gender.';
  if(isset($_FILES['profile_image']) && ($_FILES['profile_image']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
   $file=$_FILES['profile_image'];
   if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)$errors[]='Profile picture upload failed. Please try again.';
   elseif(($file['size']??0)>5*1024*1024)$errors[]='Profile picture must be 5 MB or smaller.';
   else{
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($allowed[$mime]))$errors[]='Profile picture must be a JPG, PNG, or WEBP image.';
    else{
     $dir=dirname(__DIR__).'/assets/uploads/profiles'; if(!is_dir($dir))mkdir($dir,0755,true);
     $filename='profile_'.$uid.'_'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
     if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$filename))$errors[]='Could not save the profile picture.';
     else{
      $newPath='assets/uploads/profiles/'.$filename;
      $oldPath=(string)($user['profile_image']??'');
      if($oldPath && str_starts_with($oldPath,'assets/uploads/profiles/')){ $oldFile=dirname(__DIR__).'/'.$oldPath; if(is_file($oldFile))@unlink($oldFile); }
      $profileImage=$newPath;
     }
    }
   }
  }
  $x=$pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?'); $x->execute([$email,$uid]); if($x->fetch())$errors[]='That email is already used by another account.';
  if(!$errors){
   $address=implode(', ',array_filter([$street,$barangay,$city,$province]));
   $pdo->prepare('UPDATE users SET first_name=?,middle_name=?,last_name=?,full_name=?,email=?,contact_number=?,province_code=?,province=?,city_municipality_code=?,city_municipality=?,barangay_code=?,barangay=?,street_purok=?,address=?,birthday=?,gender=?,profile_image=? WHERE id=?')->execute([$first,$middle?:null,$last,$name,$email,$contact,$provinceCode,$province,$cityCode,$city,$barangayCode,$barangay,$street,$address,$birthday,$gender,$profileImage,$uid]);
   $user=array_merge($user,['first_name'=>$first,'middle_name'=>$middle,'last_name'=>$last,'full_name'=>$name,'email'=>$email,'contact_number'=>$contact,'province_code'=>$provinceCode,'province'=>$province,'city_municipality_code'=>$cityCode,'city_municipality'=>$city,'barangay_code'=>$barangayCode,'barangay'=>$barangay,'street_purok'=>$street,'address'=>$address,'birthday'=>$birthday,'gender'=>$gender,'profile_image'=>$profileImage]);
   login_user($user); log_activity($pdo,$uid,'Profile updated'); flash('success','Profile updated.'); redirect('profile.php');
  }
 } elseif($action==='password'){
  $current=$_POST['current_password']??''; $new=$_POST['new_password']??''; $confirm=$_POST['confirm_password']??'';
  if(!password_verify($current,$user['password']))$errors[]='Current password is incorrect.'; if(strlen($new)<8)$errors[]='New password must be at least 8 characters.'; if(!preg_match('/[A-Z]/',$new))$errors[]='New password must contain at least one uppercase letter.'; if(!preg_match('/[^A-Za-z0-9]/',$new))$errors[]='New password must contain at least one special character.'; if($new!==$confirm)$errors[]='New passwords do not match.';
  if(!$errors){$pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$uid]);log_activity($pdo,$uid,'Password changed');flash('success','Password changed successfully.');redirect('profile.php');}
 }
}
$pageTitle='My profile.';$eyebrow='Account';
$provinceCode=$user['province_code']??''; $cityCode=$user['city_municipality_code']??''; $barangayCode=$user['barangay_code']??'';
$street=$user['street_purok']??''; if(!$street && empty($user['province_code'])) $street=$user['address']??'';
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Profile</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><?php include 'includes/header.php'?><?php if($errors):?><div class="notice error"><?php foreach($errors as $x):?><div>• <?=e($x)?></div><?php endforeach?></div><?php endif?><section class="grid two"><div class="card"><h2>Personal information</h2><form method="post" enctype="multipart/form-data" class="form-grid"><?=csrf_field()?><input type="hidden" name="action" value="profile">
<div class="field full profile-photo-field"><span>Profile picture</span><div class="profile-photo-editor"><?php if(!empty($user['profile_image'])):?><img class="profile-photo-preview" src="../<?=e($user['profile_image'])?>" alt="Profile picture"><?php else:?><div class="profile-photo-preview profile-photo-fallback"><?=e(strtoupper(substr($user['first_name']??$user['full_name']??'P',0,1)))?></div><?php endif?><div><input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp"><div class="muted profile-photo-help">JPG, PNG or WEBP, maximum 5 MB. Uploading a new photo replaces the current one.</div></div></div></div>
<label class="field"><span>First name</span><input name="first_name" value="<?=e($user['first_name']??'')?>" required></label><label class="field"><span>Middle name</span><input name="middle_name" value="<?=e($user['middle_name']??'')?>"></label><label class="field full"><span>Last name</span><input name="last_name" value="<?=e($user['last_name']??'')?>" required></label><label class="field full"><span>Email</span><input type="email" name="email" value="<?=e($user['email'])?>" required pattern="[A-Za-z0-9._%+\-]+@(gmail\.com|yahoo\.com|outlook\.com)"></label><label class="field full"><span>Contact number</span><input type="tel" name="contact_number" value="<?=e($user['contact_number'])?>" required inputmode="numeric" minlength="11" maxlength="11" pattern="09[0-9]{9}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"></label>
<div class="field full"><span>Address</span><div class="address-fields" data-address-selector data-province-code="<?=e($provinceCode)?>" data-city-code="<?=e($cityCode)?>" data-barangay-code="<?=e($barangayCode)?>">
<label class="field"><span>Province</span><select name="province_code" data-province required disabled><option>Loading provinces…</option></select></label><label class="field"><span>City / Municipality</span><select name="city_municipality_code" data-city required disabled><option>Select province first</option></select></label><label class="field"><span>Barangay</span><select name="barangay_code" data-barangay required disabled><option>Select city first</option></select></label><label class="field"><span>Purok / Street / House No.</span><input name="street_purok" value="<?=e($street)?>" required placeholder="e.g. Purok 3, M.L. Quezon St."></label>
<input type="hidden" name="province" value="<?=e($user['province']??'')?>" data-province-name><input type="hidden" name="city_municipality" value="<?=e($user['city_municipality']??'')?>" data-city-name><input type="hidden" name="barangay" value="<?=e($user['barangay']??'')?>" data-barangay-name><div class="address-help" data-address-status></div></div></div>
<label class="field"><span>Birthday</span><input type="date" name="birthday" value="<?=e($user['birthday'])?>" max="<?=date('Y-m-d')?>" required></label><label class="field"><span>Gender</span><select name="gender" required><?php foreach(['Male','Female','Other'] as $g):?><option <?= $user['gender']===$g?'selected':'' ?>><?=e($g)?></option><?php endforeach?></select></label><div class="field full"><button class="btn">Save profile</button></div></form></div>
<div class="card"><h2>Change password</h2><form method="post" class="grid"><?=csrf_field()?><input type="hidden" name="action" value="password"><label class="field"><span>Current password</span><input type="password" name="current_password" required></label><label class="field"><span>New password</span><input type="password" name="new_password" minlength="8" pattern="(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}" title="At least 8 characters with 1 uppercase and 1 special character" required></label><label class="field"><span>Confirm new password</span><input type="password" name="confirm_password" minlength="8" required></label><button class="btn">Change password</button></form></div></section></main></div><script src="../assets/js/script.js"></script><script src="../assets/js/address-selector.js"></script></body></html>
