<?php
declare(strict_types=1);
require '../config/db.php'; require '../config/auth.php';
if (($u=current_user()) && $u['role']==='parishioner') redirect('dashboard.php');
$errors=[]; $success='';
$firstName=$middleName=$lastName=$email=$contact=$birthday=$gender='';
$provinceCode=$province=$cityCode=$city=$barangayCode=$barangay=$street='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $firstName=normalize_person_name($_POST['first_name']??''); $middleName=normalize_person_name($_POST['middle_name']??''); $lastName=normalize_person_name($_POST['last_name']??''); $email=strtolower(trim($_POST['email']??''));
    $contact=trim($_POST['contact_number']??''); $birthday=trim($_POST['birthday']??''); $gender=trim($_POST['gender']??'');
    $provinceCode=trim($_POST['province_code']??''); $province=trim($_POST['province']??'');
    $cityCode=trim($_POST['city_municipality_code']??''); $city=trim($_POST['city_municipality']??'');
    $barangayCode=trim($_POST['barangay_code']??''); $barangay=trim($_POST['barangay']??'');
    $street=trim($_POST['street_purok']??'');
    $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
    if(strlen($firstName)<2)$errors[]='Enter your first name.';
    if(strlen($lastName)<2)$errors[]='Enter your last name.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
    elseif(!preg_match('/^[A-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$/i',$email)) $errors[]='Email must use @gmail.com, @yahoo.com, or @outlook.com.';
    if(!preg_match('/^09\d{9}$/',$contact))$errors[]='Contact number must be exactly 11 digits and start with 09.';
    if(!$provinceCode||!$province)$errors[]='Select your province.';
    if(!$cityCode||!$city)$errors[]='Select your city or municipality.';
    if(!$barangayCode||!$barangay)$errors[]='Select your barangay.';
    if($street==='')$errors[]='Enter your purok, street, or house address.';
    if (!$birthday) {
        $errors[] = 'Please enter your birthday.';
    } else {
        $birthDate = DateTime::createFromFormat('Y-m-d', $birthday);
        $today = new DateTime('today');
        if (!$birthDate || $birthDate->format('Y-m-d') !== $birthday || $birthDate > $today) {
            $errors[] = 'Please enter a valid birthday.';
        } elseif ($birthDate->diff($today)->y < 18) {
            $errors[] = 'You must be at least 18 years old to create an account.';
        }
    }
    if(!in_array($gender,['Male','Female','Other'],true))$errors[]='Select your gender.';
    if(strlen($password)<8)$errors[]='Password must be at least 8 characters.';
    if(!preg_match('/[A-Z]/',$password))$errors[]='Password must contain at least one uppercase letter.';
    if(!preg_match('/[^A-Za-z0-9]/',$password))$errors[]='Password must contain at least one special character.';
    if($password!==$confirm)$errors[]='Passwords do not match.';
    if(!$errors){
        $s=$pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1'); $s->execute([$email]);
        if($s->fetch()) $errors[]='An account with that email already exists.';
        else {
            try {
                $fullName=trim(implode(' ',array_filter([$firstName,$middleName,$lastName])));
                $address=implode(', ',array_filter([$street,$barangay,$city,$province]));
                $hash=password_hash($password,PASSWORD_DEFAULT);
                $s=$pdo->prepare("INSERT INTO users(first_name,middle_name,last_name,full_name,email,contact_number,province_code,province,city_municipality_code,city_municipality,barangay_code,barangay,street_purok,address,birthday,gender,password,role,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'parishioner','active')");
                $s->execute([$firstName,$middleName?:null,$lastName,$fullName,$email,$contact,$provinceCode,$province,$cityCode,$city,$barangayCode,$barangay,$street,$address,$birthday,$gender,$hash]);
                $id=(int)$pdo->lastInsertId(); log_activity($pdo,$id,'Parishioner registered');
                $success='Account created successfully. You can now log in.';
                $firstName=$middleName=$lastName=$email=$contact=$birthday=$gender=$provinceCode=$province=$cityCode=$city=$barangayCode=$barangay=$street='';
            } catch(PDOException $e) {
                if($e->getCode()==='23000')$errors[]='An account with that email already exists.';
                else { error_log($e->getMessage()); $errors[]='Registration failed. Please try again.'; }
            }
        }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Account</title><link rel="stylesheet" href="../assets/css/style.css"></head><body class="login-page"><main class="card auth-card" style="width:min(720px,100%)"><div class="brand" style="justify-content:center"><span class="brand-mark">✝</span><span>Smart Parish</span></div><div style="text-align:center"><div class="eyebrow">Parishioner Registration</div><h1>Create account.</h1><p class="muted">Register to book and track parish services.</p></div>
<?php if($errors):?><div class="notice error"><?php foreach($errors as $x):?><div>• <?=e($x)?></div><?php endforeach?></div><?php endif?>
<?php if($success):?><div class="notice"><?=e($success)?> <a href="login.php"><b>Login now</b></a></div><?php endif?>
<form method="post" class="form-grid"><?=csrf_field()?>
<label class="field"><span>First Name</span><input name="first_name" value="<?=e($firstName)?>" required maxlength="80" autocomplete="given-name" placeholder="First Name"></label>
<label class="field"><span>Middle Name <small class="muted">(optional)</small></span><input name="middle_name" value="<?=e($middleName)?>" maxlength="80" autocomplete="additional-name" placeholder="Middle Name"></label>
<label class="field full"><span>Last Name</span><input name="last_name" value="<?=e($lastName)?>" required maxlength="80" autocomplete="family-name" placeholder="Last Name"></label>
<label class="field full"><span>Email Address</span><input type="email" name="email" value="<?=e($email)?>" required autocomplete="email" pattern="[A-Za-z0-9._%+\-]+@(gmail\.com|yahoo\.com|outlook\.com)" title="Use a Gmail, Yahoo, or Outlook email address" placeholder="name@gmail.com"></label>
<label class="field full"><span>Contact Number</span><input type="tel" name="contact_number" value="<?=e($contact)?>" required inputmode="numeric" minlength="11" maxlength="11" pattern="09[0-9]{9}" title="Enter exactly 11 digits starting with 09" placeholder="09XXXXXXXXX" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"></label>
<div class="field full"><span>Address</span><div class="address-fields" data-address-selector data-province-code="<?=e($provinceCode)?>" data-city-code="<?=e($cityCode)?>" data-barangay-code="<?=e($barangayCode)?>">
<label class="field"><span>Province</span><select name="province_code" data-province required disabled><option value="">Loading provinces…</option></select></label>
<label class="field"><span>City / Municipality</span><select name="city_municipality_code" data-city required disabled><option value="">Select province first</option></select></label>
<label class="field"><span>Barangay</span><select name="barangay_code" data-barangay required disabled><option value="">Select city first</option></select></label>
<label class="field"><span>Purok / Street / House No.</span><input name="street_purok" value="<?=e($street)?>" required placeholder="e.g. Purok 3, M.L. Quezon St."></label>
<input type="hidden" name="province" value="<?=e($province)?>" data-province-name><input type="hidden" name="city_municipality" value="<?=e($city)?>" data-city-name><input type="hidden" name="barangay" value="<?=e($barangay)?>" data-barangay-name>
<div class="address-help" data-address-status></div></div></div>
<label class="field"><span>Birthday</span><input type="date" name="birthday" value="<?=e($birthday)?>" max="<?=date('Y-m-d', strtotime('-18 years'))?>" required></label>
<label class="field"><span>Gender</span><select name="gender" required><option value="">Select gender</option><?php foreach(['Male','Female','Other'] as $g):?><option <?= $gender===$g?'selected':'' ?>><?=e($g)?></option><?php endforeach?></select></label>
<label class="field"><span>Password</span><input type="password" name="password" minlength="8" pattern="(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}" title="At least 8 characters with 1 uppercase letter and 1 special character" required autocomplete="new-password" placeholder="8+ chars, uppercase & special"></label><label class="field"><span>Confirm Password</span><input type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Repeat password"></label><button class="btn field full" type="submit">Register</button></form><div style="text-align:center;margin-top:20px" class="muted">Already have an account? <a href="login.php"><b>Login</b></a></div></main><script src="../assets/js/address-selector.js"></script></body></html>
