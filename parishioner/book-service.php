<?php
declare(strict_types=1);
require '../config/db.php';
require '../config/auth.php';
require_role('parishioner','login.php');
date_default_timezone_set('Asia/Manila');
$u=current_user();
$errors=[];
$services=$pdo->query("SELECT * FROM services WHERE status='active' ORDER BY name")->fetchAll();
$resources=$pdo->query("SELECT * FROM resources WHERE status='available' ORDER BY name")->fetchAll();
$openServiceId=(int)($_POST['service_id']??0);
$pdo->exec("INSERT IGNORE INTO payment_settings(id) VALUES(1)");
$paymentSettings=$pdo->query("SELECT * FROM payment_settings WHERE id=1")->fetch();
$onlineEnabled=!empty($paymentSettings['gcash_account_name'])&&!empty($paymentSettings['gcash_number'])&&!empty($paymentSettings['gcash_qr_path']);

// Baptism-specific details are stored separately while appointments remain the shared schedule source.
$pdo->exec("CREATE TABLE IF NOT EXISTS baptism_booking_details (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, appointment_id BIGINT UNSIGNED NOT NULL UNIQUE, child_full_name VARCHAR(245) NOT NULL, child_dob DATE NOT NULL, child_birth_place VARCHAR(255) NOT NULL, father_full_name VARCHAR(245) NOT NULL, mother_maiden_name VARCHAR(245) NOT NULL, parents_marriage_status VARCHAR(60) NOT NULL, contact_number VARCHAR(30) NOT NULL, contact_email VARCHAR(190) NOT NULL, baptism_type VARCHAR(40) NOT NULL, principal_male_godparent VARCHAR(245) NOT NULL, principal_female_godparent VARCHAR(245) NOT NULL, additional_godparents TEXT NULL, extra_godparent_count INT UNSIGNED NOT NULL DEFAULT 0, extra_godparent_fee DECIMAL(10,2) NOT NULL DEFAULT 0, psa_birth_certificate VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(appointment_id) REFERENCES appointments(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Keep browser uploads below PHP's POST ceiling so oversized requests are stopped before submission.
function ini_bytes(string $value): int {
    $value=trim($value); if($value==='') return 0;
    $last=strtolower($value[strlen($value)-1]); $n=(float)$value;
    if($last==='g') $n*=1024; if(in_array($last,['g','m'],true)) $n*=1024; if(in_array($last,['g','m','k'],true)) $n*=1024;
    return (int)$n;
}
$postLimitBytes=ini_bytes((string)ini_get('post_max_size'));
// Reserve 2 MB for normal form fields/request overhead. Never advertise more than 80 MB.
$weddingUploadLimitBytes=max(1*1024*1024,min(80*1024*1024,$postLimitBytes>0?$postLimitBytes-(2*1024*1024):35*1024*1024));

if($_SERVER['REQUEST_METHOD']==='POST'){
    $contentLength=(int)($_SERVER['CONTENT_LENGTH']??0);
    if($postLimitBytes>0 && $contentLength>$postLimitBytes){
        $errors[]='The selected uploads are too large for the server. Please use smaller files and submit again.';
    } else {
        verify_csrf();
    }
    $sid=(int)($_POST['service_id']??0);
    $rid=($_POST['resource_id']??'')!==''?(int)$_POST['resource_id']:null;
    $date=$_POST['appointment_date']??'';
    $time=$_POST['start_time']??'';
    $notes=trim($_POST['notes']??'');
    $paymentMethod=$_POST['payment_method']??'cash';
    if(!in_array($paymentMethod,['cash','online'],true))$errors[]='Choose a valid payment method.';
    if($paymentMethod==='online'&&!$onlineEnabled)$errors[]='Online payment is temporarily unavailable. Please choose cash.';
    $receiptPath=null;
    if($paymentMethod==='online'){
        try{
            if(!isset($_FILES['payment_receipt'])||($_FILES['payment_receipt']['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) throw new RuntimeException('Upload your GCash payment receipt before submitting.');
            $receiptFile=secure_image_upload($_FILES['payment_receipt'],__DIR__.'/../uploads/payment_receipts','receipt',10);
            $receiptPath='uploads/payment_receipts/'.$receiptFile;
        }catch(Throwable $ex){$errors[]=$ex->getMessage();}
    }
    $s=$pdo->prepare("SELECT * FROM services WHERE id=? AND status='active'");
    $s->execute([$sid]);
    $service=$s->fetch();
    if(!$service)$errors[]='Select a valid service.';
    $isWedding=$service && strcasecmp(trim((string)$service['name']),'Wedding')===0;
    $wedding=[];
    if($isWedding){
        $wedding=[
          'groom_full_name'=>trim(implode(' ',array_filter([normalize_person_name($_POST['groom_first_name']??''),normalize_person_name($_POST['groom_middle_name']??''),normalize_person_name($_POST['groom_last_name']??'')]))),'groom_dob'=>$_POST['groom_dob']??'','groom_religion_parish'=>trim($_POST['groom_religion_parish']??''),
          'bride_full_name'=>trim(implode(' ',array_filter([normalize_person_name($_POST['bride_first_name']??''),normalize_person_name($_POST['bride_middle_name']??''),normalize_person_name($_POST['bride_last_name']??'')]))),'bride_dob'=>$_POST['bride_dob']??'','bride_religion_parish'=>trim($_POST['bride_religion_parish']??''),
          'contact_number'=>trim($_POST['wedding_contact_number']??''),'contact_email'=>trim($_POST['wedding_contact_email']??''),
          'ceremony_type'=>trim($_POST['ceremony_type']??''),'preferred_officiant'=>trim($_POST['preferred_officiant']??''),
          'civil_requirement'=>$_POST['civil_requirement']??'license'
        ];
        foreach(['groom_full_name','groom_dob','groom_religion_parish','bride_full_name','bride_dob','bride_religion_parish','contact_number','contact_email','ceremony_type'] as $f) if($wedding[$f]==='') $errors[]='Complete all required wedding fields before submitting.';
        if($wedding['contact_number']!=='' && !preg_match('/^09\d{9}$/',$wedding['contact_number'])) $errors[]='Wedding contact number must be exactly 11 digits and start with 09.';
        if($wedding['contact_email']!=='' && !filter_var($wedding['contact_email'],FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid wedding contact email.';
        foreach(['groom_dob'=>'Groom','bride_dob'=>'Bride'] as $f=>$label){ if($wedding[$f]){ $dob=DateTime::createFromFormat('Y-m-d',$wedding[$f]); if(!$dob||$dob->format('Y-m-d')!==$wedding[$f]||$dob>new DateTime('today')) $errors[]=$label.' date of birth is invalid.'; elseif($dob->diff(new DateTime('today'))->y<18) $errors[]=$label.' must be at least 18 years old.'; }}
        if(!in_array($wedding['civil_requirement'],['license','contract'],true)) $errors[]='Choose a valid civil marriage requirement.';
    }
    $isBaptism=$service && strcasecmp(trim((string)$service['name']),'Baptism')===0;
    $baptism=[];
    if($isBaptism){
        $baptism=[
          'child_full_name'=>trim(implode(' ',array_filter([normalize_person_name($_POST['child_first_name']??''),normalize_person_name($_POST['child_middle_name']??''),normalize_person_name($_POST['child_last_name']??'')]))),'child_dob'=>$_POST['child_dob']??'',
          'child_birth_place'=>trim($_POST['child_birth_place']??implode(', ',array_filter([trim($_POST['birth_barangay']??''),trim($_POST['birth_city_municipality']??''),trim($_POST['birth_province']??'')]))),
          'father_full_name'=>trim(implode(' ',array_filter([normalize_person_name($_POST['father_first_name']??''),normalize_person_name($_POST['father_middle_name']??''),normalize_person_name($_POST['father_last_name']??'')]))),'mother_maiden_name'=>trim(implode(' ',array_filter([normalize_person_name($_POST['mother_first_name']??''),normalize_person_name($_POST['mother_middle_name']??''),normalize_person_name($_POST['mother_maiden_last_name']??'')]))),'parents_marriage_status'=>trim($_POST['parents_marriage_status']??''),
          'contact_number'=>trim($_POST['baptism_contact_number']??''),'contact_email'=>trim($_POST['baptism_contact_email']??''),'baptism_type'=>trim($_POST['baptism_type']??''),
          'principal_male_godparent'=>normalize_person_name($_POST['principal_male_godparent']??''),'principal_female_godparent'=>normalize_person_name($_POST['principal_female_godparent']??'')
        ];
        foreach(['child_full_name','child_dob','child_birth_place','father_full_name','mother_maiden_name','parents_marriage_status','contact_number','contact_email','baptism_type','principal_male_godparent','principal_female_godparent'] as $f) if($baptism[$f]==='') $errors[]='Complete all required Baptism fields before submitting.';
        if($baptism['contact_number']!=='' && !preg_match('/^09\d{9}$/',$baptism['contact_number'])) $errors[]='Baptism contact number must be exactly 11 digits and start with 09.';
        if($baptism['contact_email']!=='' && !filter_var($baptism['contact_email'],FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid Baptism contact email.';
        if(!in_array($baptism['parents_marriage_status'],['Married in Catholic Church','Civilly Married','Unmarried'],true)) $errors[]='Choose a valid parents’ civil marriage status.';
        if(!in_array($baptism['baptism_type'],['Mass Baptism','Solo / Special Baptism'],true)) $errors[]='Choose a valid Baptism type.';
        if($baptism['child_dob']){ $dob=DateTime::createFromFormat('Y-m-d',$baptism['child_dob']); if(!$dob||$dob->format('Y-m-d')!==$baptism['child_dob']||$dob>new DateTime('today')) $errors[]='Child date of birth is invalid.'; }
    }
    if(!$date||$date<date('Y-m-d'))$errors[]='Choose today or a future date.';
    if($isWedding && $date && $date < date('Y-m-d',strtotime('+30 days'))) $errors[]='Wedding bookings must be scheduled at least 30 days in advance.';
    if(!$time)$errors[]='Choose a start time.';
    if(!$errors){
        $start=new DateTime("$date $time");
        $end=(clone $start)->modify('+'.(int)$service['duration_minutes'].' minutes');
        $startTime=$start->format('H:i:s');$endTime=$end->format('H:i:s');
        // Final server-side availability check prevents duplicate/overlapping bookings even if the UI is bypassed.
        if($rid){
            $c=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE resource_id=? AND appointment_date=? AND status IN ('pending','approved') AND start_time < ? AND end_time > ?");
            $c->execute([$rid,$date,$endTime,$startTime]);
        }else{
            $c=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date=? AND status IN ('pending','approved') AND start_time < ? AND end_time > ?");
            $c->execute([$date,$endTime,$startTime]);
        }
        if($c->fetchColumn())$errors[]='That date and time is already occupied. Please choose an available time slot.';
        // Events are part of the same parish schedule and cannot be bypassed by posting the form directly.
        $eventConflict=$pdo->prepare("SELECT title,start_time,end_time FROM events WHERE event_date=? AND start_time IS NOT NULL AND end_time IS NOT NULL AND start_time < ? AND end_time > ? ORDER BY start_time LIMIT 1");
        $eventConflict->execute([$date,$endTime,$startTime]);
        if($event=$eventConflict->fetch()){
            $errors[]='This time is unavailable because a parish event is scheduled from '.date('g:i A',strtotime($event['start_time'])).' to '.date('g:i A',strtotime($event['end_time'])).'. Please choose another time.';
        }
        if(!$errors){
            $ref=appointment_ref();
            $s=$pdo->prepare("INSERT INTO appointments(reference_no,user_id,service_id,resource_id,appointment_date,start_time,end_time,notes,status,payment_method,receipt_path,payment_status) VALUES(?,?,?,?,?,?,?,?, 'pending',?,?,?)");
            $paymentStatus=$paymentMethod==='online'?'pending':'not_required';
            $s->execute([$ref,$u['id'],$sid,$rid,$date,$startTime,$endTime,$notes,$paymentMethod,$receiptPath,$paymentStatus]);
            $aid=(int)$pdo->lastInsertId();
            if($isWedding){
                $docPaths=[];
                $docFields=['psa_birth_certificates'=>'psa','cenomar'=>'cenomar','baptism_confirmation'=>'baptism-confirmation','pmc_certificate'=>'pmc','marriage_document'=>'marriage'];
                try{
                    // Each wedding requirement accepts multiple files. Paths are stored as a JSON array
                    // in the existing wedding_booking_details columns (TEXT after the migration below).
                    foreach($docFields as $field=>$prefix){
                        $saved=[];
                        if(isset($_FILES[$field]) && is_array($_FILES[$field]['name']??null)){
                            foreach($_FILES[$field]['name'] as $i=>$originalName){
                                $error=$_FILES[$field]['error'][$i]??UPLOAD_ERR_NO_FILE;
                                if($error===UPLOAD_ERR_NO_FILE || $originalName==='') continue;
                                $one=[
                                    'name'=>$originalName,
                                    'type'=>$_FILES[$field]['type'][$i]??'',
                                    'tmp_name'=>$_FILES[$field]['tmp_name'][$i]??'',
                                    'error'=>$error,
                                    'size'=>$_FILES[$field]['size'][$i]??0,
                                ];
                                $file=secure_document_upload($one,__DIR__.'/../uploads/wedding_documents',$prefix);
                                $saved[]='uploads/wedding_documents/'.$file;
                            }
                        }
                        $docPaths[$field]=$saved ? json_encode($saved,JSON_UNESCAPED_SLASHES) : null;
                    }
                    $ws=$pdo->prepare("INSERT INTO wedding_booking_details(appointment_id,groom_full_name,groom_dob,groom_religion_parish,bride_full_name,bride_dob,bride_religion_parish,contact_number,contact_email,ceremony_type,preferred_officiant,civil_requirement,psa_birth_certificates,cenomar,baptism_confirmation,pmc_certificate,marriage_document) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $ws->execute([$aid,$wedding['groom_full_name'],$wedding['groom_dob'],$wedding['groom_religion_parish'],$wedding['bride_full_name'],$wedding['bride_dob'],$wedding['bride_religion_parish'],$wedding['contact_number'],$wedding['contact_email'],$wedding['ceremony_type'],$wedding['preferred_officiant']?:null,$wedding['civil_requirement'],$docPaths['psa_birth_certificates'],$docPaths['cenomar'],$docPaths['baptism_confirmation'],$docPaths['pmc_certificate'],$docPaths['marriage_document']]);
                }catch(Throwable $ex){
                    $pdo->prepare('DELETE FROM appointments WHERE id=?')->execute([$aid]);
                    $errors[]='Wedding documents/details could not be saved: '.$ex->getMessage();
                }
            }
            if($isBaptism){
                try{
                    $psaPath=null;
                    if(isset($_FILES['baptism_psa']) && ($_FILES['baptism_psa']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
                        $file=secure_document_upload($_FILES['baptism_psa'],__DIR__.'/../uploads/baptism_documents','psa-birth');
                        $psaPath='uploads/baptism_documents/'.$file;
                    }
                    $additional=array_values(array_filter(array_map('normalize_person_name',$_POST['additional_godparents']??[]),fn($v)=>$v!==''));
                    $extraCount=count($additional); $extraFee=$extraCount*50;
                    $bs=$pdo->prepare("INSERT INTO baptism_booking_details(appointment_id,child_full_name,child_dob,child_birth_place,father_full_name,mother_maiden_name,parents_marriage_status,contact_number,contact_email,baptism_type,principal_male_godparent,principal_female_godparent,additional_godparents,extra_godparent_count,extra_godparent_fee,psa_birth_certificate) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $bs->execute([$aid,$baptism['child_full_name'],$baptism['child_dob'],$baptism['child_birth_place'],$baptism['father_full_name'],$baptism['mother_maiden_name'],$baptism['parents_marriage_status'],$baptism['contact_number'],$baptism['contact_email'],$baptism['baptism_type'],$baptism['principal_male_godparent'],$baptism['principal_female_godparent'],$additional?json_encode($additional,JSON_UNESCAPED_UNICODE):null,$extraCount,$extraFee,$psaPath]);
                }catch(Throwable $ex){ $errors[]='Baptism details could not be saved: '.$ex->getMessage(); }
            }
            if(!$errors){
            $pdo->prepare("INSERT INTO appointment_history(appointment_id,status,note,changed_by) VALUES(?,'pending','Booking submitted',?)")->execute([$aid,$u['id']]);
            log_activity($pdo,$u['id'],'Appointment booked',$ref);
            flash('booking_success','Booking submitted successfully.|Your request has been sent to the parish office. Please wait for administrator approval.|'.$ref);
            redirect('book-service.php');
            }
        }
    }
}
$pageTitle='Book a service.';$eyebrow='Appointments';
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Book Service</title><link rel="stylesheet" href="../assets/css/style.css?v=20261007-baptism-layout-fix-1"></head><body><div class="shell"><?php include 'includes/sidebar.php'?><main class="main"><?php include 'includes/header.php'?>
<?php if($errors):?><div class="notice error"><?php foreach($errors as $x):?><div>• <?=e($x)?></div><?php endforeach?></div><?php endif?>
<section class="grid three" id="serviceGrid">
<?php foreach($services as $s): $sid=(int)$s['id']; ?>
<article class="card service-card booking-card" data-service-id="<?=$sid?>" data-service-name="<?=e($s['name'])?>" data-service-fee="<?=number_format((float)$s['fee'],2,'.','')?>">
  <div class="card-click-area" role="button" tabindex="0" aria-haspopup="dialog">
    <div class="service-icon">✦</div>
    <h2><?=e($s['name'])?></h2>
    <p class="muted"><?=e($s['description'])?></p>
    <small><?=(int)$s['duration_minutes']?> minutes<?php if((float)$s['fee']>0):?> · ₱<?=number_format((float)$s['fee'],2)?><?php endif?></small>
    <div class="booking-hint">Click to book this service</div>
  </div>
</article>
<?php endforeach?>
</section>

<div class="booking-modal<?=$openServiceId?' is-open':''?>" id="bookingModal" aria-hidden="<?=$openServiceId?'false':'true'?>">
  <div class="booking-modal-backdrop" data-close-modal></div>
  <section class="booking-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">
    <button type="button" class="booking-modal-close" data-close-modal aria-label="Close booking form">×</button>
    <div class="eyebrow">Appointment request</div>
    <h2 id="bookingModalTitle">Book a service</h2>
    <p class="muted" id="bookingModalDescription">Choose your preferred schedule and provide any details the parish office should know.</p>
    <form method="post" enctype="multipart/form-data" class="form-grid" id="bookingForm">
      <?=csrf_field()?>
      <input type="hidden" name="service_id" id="modalServiceId" value="<?=$openServiceId?:''?>">
      <div class="wedding-progress field full" id="weddingProgress" hidden>
        <button type="button" class="wedding-step-dot is-active" data-step-jump="1"><b>1</b><span>Couple</span></button><i></i>
        <button type="button" class="wedding-step-dot" data-step-jump="2"><b>2</b><span>Schedule</span></button><i></i>
        <button type="button" class="wedding-step-dot" data-step-jump="3"><b>3</b><span>Requirements</span></button><i></i>
        <button type="button" class="wedding-step-dot" data-step-jump="4"><b>4</b><span>Payment</span></button>
      </div>
      <div class="wedding-progress field full" id="baptismProgress" hidden>
        <button type="button" class="wedding-step-dot baptism-step-dot is-active"><b>1</b><span>Child & Parents</span></button><i></i>
        <button type="button" class="wedding-step-dot baptism-step-dot"><b>2</b><span>Schedule</span></button><i></i>
        <button type="button" class="wedding-step-dot baptism-step-dot"><b>3</b><span>Godparents</span></button><i></i>
        <button type="button" class="wedding-step-dot baptism-step-dot"><b>4</b><span>Requirements & Payment</span></button>
      </div>

      <section class="wedding-step field full baptism-step" data-baptism-step="1" hidden>
        <div class="step-heading"><span>Section 1 of 4</span><h3>Child & parents information</h3><p>Details required for the parish baptismal registry (Libro ng Binyag).</p></div>
        <div class="wizard-grid baptism-section-one">
          <div class="field full baptism-info-columns">
            <div class="baptism-detail-card">
              <div class="baptism-card-heading"><span class="baptism-card-icon">C</span><div><h4>Child's legal information</h4><small>Name and date of birth</small></div></div>
              <div class="baptism-name-stack baptism-child-name-grid">
                <label class="field"><span>First name *</span><input name="child_first_name" placeholder="First Name" value="<?=e($_POST['child_first_name']??'')?>"></label>
                <label class="field"><span>Middle name <small>(optional)</small></span><input name="child_middle_name" placeholder="Middle Name" value="<?=e($_POST['child_middle_name']??'')?>"></label>
                <label class="field"><span>Last name *</span><input name="child_last_name" placeholder="Last Name" value="<?=e($_POST['child_last_name']??'')?>"></label>
                <label class="field"><span>Date of birth *</span><input type="date" name="child_dob" max="<?=date('Y-m-d')?>" value="<?=e($_POST['child_dob']??'')?>"></label>
              </div>
            </div>
            <div class="baptism-detail-card">
              <div class="baptism-card-heading"><span class="baptism-card-icon">P</span><div><h4>Place of birth</h4><small>Philippine location details</small></div></div>
              <div class="address-fields baptism-birth-place baptism-birth-stack" data-address-selector data-province-code="<?=e($_POST['birth_province_code']??'')?>" data-city-code="<?=e($_POST['birth_city_municipality_code']??'')?>" data-barangay-code="<?=e($_POST['birth_barangay_code']??'')?>">
                <label class="field"><span>Province *</span><select name="birth_province_code" data-province disabled><option value="">Loading provinces…</option></select></label>
                <label class="field"><span>City / Municipality *</span><select name="birth_city_municipality_code" data-city disabled><option value="">Select province first</option></select></label>
                <label class="field"><span>Barangay *</span><select name="birth_barangay_code" data-barangay disabled><option value="">Select city first</option></select></label>
                <input type="hidden" name="birth_province" value="<?=e($_POST['birth_province']??'')?>" data-province-name>
                <input type="hidden" name="birth_city_municipality" value="<?=e($_POST['birth_city_municipality']??'')?>" data-city-name>
                <input type="hidden" name="birth_barangay" value="<?=e($_POST['birth_barangay']??'')?>" data-barangay-name>
                <div class="address-help" data-address-status></div>
              </div>
            </div>
          </div>

          <div class="field full parent-name-columns baptism-parent-columns">
            <div class="person-name-card baptism-detail-card">
              <div class="baptism-card-heading"><span class="baptism-card-icon">F</span><div><h4>Father's details</h4><small>Legal name information</small></div></div>
              <div class="baptism-name-stack baptism-parent-name-grid"><label class="field"><span>First name *</span><input name="father_first_name" placeholder="First Name" value="<?=e($_POST['father_first_name']??'')?>"></label><label class="field"><span>Middle name <small>(optional)</small></span><input name="father_middle_name" placeholder="Middle Name" value="<?=e($_POST['father_middle_name']??'')?>"></label><label class="field baptism-name-last"><span>Last name *</span><input name="father_last_name" placeholder="Last Name" value="<?=e($_POST['father_last_name']??'')?>"></label></div>
            </div>
            <div class="person-name-card baptism-detail-card">
              <div class="baptism-card-heading"><span class="baptism-card-icon">M</span><div><h4>Mother's details</h4><small>Maiden name information</small></div></div>
              <div class="baptism-name-stack baptism-parent-name-grid"><label class="field"><span>First name *</span><input name="mother_first_name" placeholder="First Name" value="<?=e($_POST['mother_first_name']??'')?>"></label><label class="field"><span>Middle name <small>(optional)</small></span><input name="mother_middle_name" placeholder="Middle Name" value="<?=e($_POST['mother_middle_name']??'')?>"></label><label class="field baptism-name-last"><span>Maiden last name *</span><input name="mother_maiden_last_name" placeholder="Maiden Last Name" value="<?=e($_POST['mother_maiden_last_name']??'')?>"></label></div>
            </div>
          </div>

          <label class="field full"><span>Parents' civil marriage status *</span><select name="parents_marriage_status"><option value="">Choose status</option><?php foreach(['Married in Catholic Church','Civilly Married','Unmarried'] as $x):?><option <?=($_POST['parents_marriage_status']??'')===$x?'selected':''?>><?=e($x)?></option><?php endforeach?></select></label>
          <label class="field"><span>Contact number *</span><input name="baptism_contact_number" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" value="<?=e($_POST['baptism_contact_number']??$u['contact_number']??'')?>" placeholder="09XXXXXXXXX"></label>
          <label class="field"><span>Contact email *</span><input type="email" name="baptism_contact_email" value="<?=e($_POST['baptism_contact_email']??$u['email']??'')?>"></label>        </div>
      </section>

      <section class="wedding-step field full baptism-step" data-baptism-step="2" hidden>
        <div class="step-heading"><span>Section 2 of 4</span><h3>Baptism type & schedule</h3><p>Choose Mass Baptism or a Solo / Special Baptism, then select an available parish schedule.</p></div>
        <div class="payment-options baptism-type-options">
          <label><input type="radio" name="baptism_type" value="Mass Baptism" <?=($_POST['baptism_type']??'Mass Baptism')==='Mass Baptism'?'checked':''?>> <b>Mass Baptism</b><small>Choose from available parish time slots.</small></label>
          <label><input type="radio" name="baptism_type" value="Solo / Special Baptism" <?=($_POST['baptism_type']??'')==='Solo / Special Baptism'?'checked':''?>> <b>Solo / Special Baptism</b><small>Requested schedule remains subject to priest/admin approval.</small></label>
        </div>
      </section>

      <section class="wedding-step field full baptism-step" data-baptism-step="3" hidden>
        <div class="step-heading"><span>Section 3 of 4</span><h3>Godparents (Ninong & Ninang)</h3><p>Add the principal godparents and any additional godparents. Each additional godparent adds ₱50.</p></div>
        <div class="wizard-grid"><label class="field"><span>Principal male godparent *</span><input name="principal_male_godparent" value="<?=e($_POST['principal_male_godparent']??'')?>"></label><label class="field"><span>Principal female godparent *</span><input name="principal_female_godparent" value="<?=e($_POST['principal_female_godparent']??'')?>"></label></div>
        <div class="additional-godparents"><div class="additional-godparents-head"><div><b>Additional godparents</b><small>Optional · ₱50 each</small></div><button type="button" class="btn light small" id="addGodparent">+ Add godparent</button></div><div id="godparentList"></div></div>
        <div class="wedding-fee-summary"><span>Additional godparent fee</span><strong id="godparentFeeText">₱0.00</strong></div>
      </section>

      <section class="wedding-step field full baptism-step" data-baptism-step="4" hidden>
        <div class="step-heading"><span>Section 4 of 4</span><h3>Requirements upload & payment</h3><p>Upload the child's PSA Birth Certificate if available, then choose your payment method.</p></div>
        <div class="requirements-list"><label><span><b>PSA Birth Certificate</b><small>JPG, PNG, WEBP or PDF · optional during initial booking</small></span><input type="file" name="baptism_psa" accept=".pdf,image/png,image/jpeg,image/webp"></label></div>
        <div class="notice wedding-warning"><b>Requirement reminder:</b> If the document is not uploaded now, the parish office may request it before the Baptism schedule is finalized.</div>
      </section>

      <section class="wedding-step field full" data-wedding-step="1" hidden>
        <div class="step-heading"><span>Section 1 of 4</span><h3>Couple details</h3><p>Legal and parish details for the groom and bride.</p></div>
        <div class="couple-columns">
          <div class="couple-person-card">
            <div class="couple-person-heading"><span class="couple-number">G</span><div><h4>Groom details</h4><p>Legal and parish information</p></div></div>
            <div class="registration-name-grid"><label class="field"><span>First name *</span><input name="groom_first_name" value="<?=e($_POST['groom_first_name']??'')?>" placeholder="First Name"></label><label class="field"><span>Middle name <small>(optional)</small></span><input name="groom_middle_name" value="<?=e($_POST['groom_middle_name']??'')?>" placeholder="Middle Name"></label><label class="field name-last"><span>Last name *</span><input name="groom_last_name" value="<?=e($_POST['groom_last_name']??'')?>" placeholder="Last Name"></label></div>
            <label class="field"><span>Date of birth *</span><input type="date" name="groom_dob" class="wedding-dob" data-age-output="groomAge" max="<?=date('Y-m-d')?>" value="<?=e($_POST['groom_dob']??'')?>"><small id="groomAge" class="muted"></small></label>
            <label class="field"><span>Religion / home parish *</span><input name="groom_religion_parish" value="<?=e($_POST['groom_religion_parish']??'')?>" placeholder="Religion and home parish"></label>
          </div>
          <div class="couple-person-card">
            <div class="couple-person-heading"><span class="couple-number">B</span><div><h4>Bride details</h4><p>Legal and parish information</p></div></div>
            <div class="registration-name-grid"><label class="field"><span>First name *</span><input name="bride_first_name" value="<?=e($_POST['bride_first_name']??'')?>" placeholder="First Name"></label><label class="field"><span>Middle name <small>(optional)</small></span><input name="bride_middle_name" value="<?=e($_POST['bride_middle_name']??'')?>" placeholder="Middle Name"></label><label class="field name-last"><span>Last name *</span><input name="bride_last_name" value="<?=e($_POST['bride_last_name']??'')?>" placeholder="Last Name"></label></div>
            <label class="field"><span>Date of birth *</span><input type="date" name="bride_dob" class="wedding-dob" data-age-output="brideAge" max="<?=date('Y-m-d')?>" value="<?=e($_POST['bride_dob']??'')?>"><small id="brideAge" class="muted"></small></label>
            <label class="field"><span>Religion / home parish *</span><input name="bride_religion_parish" value="<?=e($_POST['bride_religion_parish']??'')?>" placeholder="Religion and home parish"></label>
          </div>
        </div>
        <div class="couple-contact-block">
          <div class="couple-contact-heading"><h4>Contact information</h4><p>Primary contact details for this wedding request.</p></div>
          <div class="wizard-grid">
            <label class="field"><span>Contact number *</span><input name="wedding_contact_number" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" value="<?=e($_POST['wedding_contact_number']??$u['contact_number']??'')?>" placeholder="09XXXXXXXXX"></label>
            <label class="field"><span>Contact email *</span><input type="email" name="wedding_contact_email" value="<?=e($_POST['wedding_contact_email']??$u['email']??'')?>"></label>
          </div>
        </div>
      </section>

      <section class="wedding-step field full" data-wedding-step="2" hidden>
        <div class="step-heading"><span>Section 2 of 4</span><h3>Ceremony & schedule</h3><p>Choose the wedding type, chapel/resource, date and an available time.</p></div>
        <div class="wizard-grid">
          <label class="field"><span>Ceremony type *</span><select name="ceremony_type"><option value="">Choose ceremony</option><?php foreach(['Standard Nuptial Mass','Private Wedding','Kasalang Bayan (Mass Wedding)'] as $ct):?><option <?=($_POST['ceremony_type']??'')===$ct?'selected':''?>><?=e($ct)?></option><?php endforeach?></select></label>
          <label class="field"><span>Preferred officiant (optional)</span><input name="preferred_officiant" value="<?=e($_POST['preferred_officiant']??'')?>" placeholder="Parish priest or guest priest"></label>
        </div>
      </section>

      <div class="standard-booking-fields">
        <label class="field"><span>Resource / Room <span class="wedding-only-label">/ Chapel</span> (optional)</span><select name="resource_id" id="bookingResource"><option value="">No specific room</option><?php foreach($resources as $r):?><option value="<?=$r['id']?>" <?=((int)($_POST['resource_id']??0)===(int)$r['id'])?'selected':''?>><?=e($r['name'])?><?=$r['capacity']?' · '.$r['capacity'].' pax':''?></option><?php endforeach?></select></label>
        <label class="field"><span>Date</span><input type="date" id="bookingDate" name="appointment_date" min="<?=date('Y-m-d')?>" value="<?=e($_POST['appointment_date']??'')?>" required></label>
        <div class="field full"><span>Available time</span><input type="hidden" name="start_time" id="bookingStartTime" value="<?=e($_POST['start_time']??'')?>" required><div class="slot-help" id="slotHelp">Select a date to see available times.</div><div class="time-slots" id="timeSlots" aria-live="polite"></div><div class="slot-legend"><span><i class="slot-dot available"></i> Available</span><span><i class="slot-dot booked"></i> Booked / unavailable</span></div></div>
      </div>

      <section class="wedding-step field full" data-wedding-step="3" hidden>
        <div class="step-heading"><span>Section 3 of 4</span><h3>Civil & canonical requirements</h3><p>Uploads are optional during initial booking. Documents must be submitted in person or uploaded at least 30 days before the wedding date.</p></div>
        <div class="requirements-list">
          <label><span><b>PSA Birth Certificates</b><small>Groom & Bride</small></span><input type="file" name="psa_birth_certificates[]" multiple accept=".pdf,image/png,image/jpeg,image/webp"></label>
          <label><span><b>PSA CENOMAR</b><small>Certificate of No Marriage Record</small></span><input type="file" name="cenomar[]" multiple accept=".pdf,image/png,image/jpeg,image/webp"></label>
          <label><span><b>Baptism & Confirmation Certificates</b><small>Annotated “For Marriage Purposes”</small></span><input type="file" name="baptism_confirmation[]" multiple accept=".pdf,image/png,image/jpeg,image/webp"></label>
          <label><span><b>Pre-Marriage Counseling (PMC) Certificate</b></span><input type="file" name="pmc_certificate[]" multiple accept=".pdf,image/png,image/jpeg,image/webp"></label>
          <div class="civil-choice"><b>Civil document</b><label><input type="radio" name="civil_requirement" value="license" <?=($_POST['civil_requirement']??'license')==='license'?'checked':''?>> Civil Marriage License / Notice</label><label><input type="radio" name="civil_requirement" value="contract" <?=($_POST['civil_requirement']??'')==='contract'?'checked':''?>> Civil Marriage Contract (already civilly married)</label><input type="file" name="marriage_document[]" multiple accept=".pdf,image/png,image/jpeg,image/webp"></div>
        </div>
        <div class="wedding-upload-meter" id="weddingUploadMeter" data-max-bytes="<?=$weddingUploadLimitBytes?>" aria-live="polite">
          <div><b>Upload size</b><span id="weddingUploadSize">0 MB / <?=number_format($weddingUploadLimitBytes/1048576,0)?> MB</span></div>
          <div class="upload-meter-track"><i id="weddingUploadBar"></i></div>
          <small>Each document can be up to 10 MB. The total includes all wedding documents and any payment receipt.</small>
        </div>
        <div class="notice wedding-warning"><b>Document reminder:</b> Missing documents can be completed later, but must be submitted at least 30 days before the wedding date.</div>
      </section>

      <section class="wedding-step field full" data-wedding-step="4" hidden>
        <div class="step-heading"><span>Section 4 of 4</span><h3>Payment & special instructions</h3><p>Review the service fee, choose a payment method, and add any special requests.</p></div>
        <div class="wedding-fee-summary"><span>Wedding service</span><strong id="weddingFeeText">Fee based on selected service</strong></div>
      </section>

      <div class="payment-and-notes">
        <div class="field full payment-choice"><span>Payment option</span><div class="payment-options"><label><input type="radio" name="payment_method" value="cash" <?=($_POST['payment_method']??'cash')==='cash'?'checked':''?>> <b>Cash</b><small>Pay at the parish office.</small></label><label class="<?=$onlineEnabled?'':'is-disabled'?>"><input type="radio" name="payment_method" value="online" <?=($_POST['payment_method']??'')==='online'?'checked':''?> <?=$onlineEnabled?'':'disabled'?>> <b>Online payment (GCash)</b><small><?=$onlineEnabled?'Pay now and upload your receipt.':'Temporarily unavailable — admin has not configured GCash.'?></small></label></div></div>
        <div class="field full gcash-panel" id="gcashPanel" hidden><?php if($onlineEnabled):?><div class="gcash-box"><button type="button" class="gcash-qr-trigger" id="gcashQrTrigger" aria-label="Open larger GCash QR code"><img src="../<?=e($paymentSettings['gcash_qr_path'])?>" alt="GCash payment QR"><span>Click QR to enlarge</span></button><div><div class="eyebrow">GCash payment</div><h3><?=e($paymentSettings['gcash_account_name'])?></h3><div class="gcash-number-row"><p class="gcash-number" id="gcashNumber"><?=e($paymentSettings['gcash_number'])?></p><button type="button" class="copy-number-btn" id="copyGcashNumber" data-number="<?=e($paymentSettings['gcash_number'])?>">Copy</button></div><p class="muted">Scan the QR or send payment to the number above. Then upload a clear screenshot/photo of your successful transaction.</p></div></div><label class="field"><span>Upload payment receipt <b>*</b></span><input type="file" name="payment_receipt" id="paymentReceipt" accept="image/png,image/jpeg,image/webp"><small class="muted">JPG, PNG or WEBP, maximum 10 MB.</small></label><?php endif?></div>
        <label class="field full"><span>Notes / special details</span><textarea name="notes" rows="4" placeholder="Add information the parish office should know."><?=e($_POST['notes']??'')?></textarea></label>
      </div>

      <div class="field full modal-actions standard-actions"><button class="btn" type="submit" id="bookingSubmit">Submit booking request</button><button class="btn light" type="button" data-close-modal>Cancel</button></div>
      <div class="field full wedding-wizard-actions" hidden><button class="btn light wedding-cancel" type="button" data-close-modal>Cancel</button><button class="btn light" type="button" id="weddingBack">← Back</button><button class="btn" type="button" id="weddingNext">Next →</button><button class="btn" type="submit" id="weddingSubmit" hidden>Submit Wedding Booking</button></div>
      <div class="field full wedding-wizard-actions" id="baptismWizardActions" hidden><button class="btn light wedding-cancel" type="button" data-close-modal>Cancel</button><button class="btn light" type="button" id="baptismBack">← Back</button><button class="btn" type="button" id="baptismNext">Next →</button><button class="btn" type="submit" id="baptismSubmit" hidden>Submit Baptism Booking</button></div>
    </form>
  </section>
</div>
<?php if($onlineEnabled): ?>
<div class="qr-lightbox" id="gcashQrLightbox" aria-hidden="true">
  <div class="qr-lightbox-backdrop" data-close-qr></div>
  <section class="qr-lightbox-dialog" role="dialog" aria-modal="true" aria-label="GCash QR code">
    <button type="button" class="qr-lightbox-close" data-close-qr aria-label="Close QR preview">×</button>
    <div class="eyebrow">GCash payment</div>
    <h2>Scan to pay</h2>
    <img src="../<?=e($paymentSettings['gcash_qr_path'])?>" alt="Full-size GCash payment QR" class="gcash-qr-full">
    <p><strong><?=e($paymentSettings['gcash_account_name'])?></strong></p>
    <div class="gcash-number-row centered"><p class="gcash-number"><?=e($paymentSettings['gcash_number'])?></p><button type="button" class="copy-number-btn copy-number-secondary" data-number="<?=e($paymentSettings['gcash_number'])?>">Copy</button></div>
    <p class="muted">Use your GCash app to scan this QR code. Close this preview after payment and upload your receipt.</p>
  </section>
</div>
<?php endif; ?>
</main></div>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/address-selector.js"></script>
<script>
(()=>{
  const modal=document.getElementById('bookingModal');
  const serviceId=document.getElementById('modalServiceId');
  const title=document.getElementById('bookingModalTitle');
  const submit=document.getElementById('bookingSubmit');
  const bookingForm=document.getElementById('bookingForm');
  let lastTrigger=null;
  let isWedding=false, isBaptism=false, weddingStep=1, baptismStep=1;
  const weddingProgress=document.getElementById('weddingProgress');
  const weddingSteps=[...document.querySelectorAll('[data-wedding-step]')];
  const baptismProgress=document.getElementById('baptismProgress');
  const baptismSteps=[...document.querySelectorAll('[data-baptism-step]')];
  const standardFields=document.querySelector('.standard-booking-fields');
  const paymentNotes=document.querySelector('.payment-and-notes');
  const standardActions=document.querySelector('.standard-actions');
  const wizardActions=document.querySelector('.wedding-wizard-actions');
  const weddingBack=document.getElementById('weddingBack'), weddingNext=document.getElementById('weddingNext'), weddingSubmit=document.getElementById('weddingSubmit');
  const baptismActions=document.getElementById('baptismWizardActions'), baptismBack=document.getElementById('baptismBack'), baptismNext=document.getElementById('baptismNext'), baptismSubmit=document.getElementById('baptismSubmit');
  const setRequired=(selector,on)=>document.querySelectorAll(selector).forEach(el=>el.required=on);
  const showWeddingStep=(n)=>{
    weddingStep=n; weddingSteps.forEach(x=>x.hidden=Number(x.dataset.weddingStep)!==n);
    weddingProgress.querySelectorAll('.wedding-step-dot').forEach((x,i)=>x.classList.toggle('is-active',i+1===n));
    standardFields.hidden=n!==2; paymentNotes.hidden=n!==4;
    weddingBack.hidden=n===1; weddingNext.hidden=n===4; weddingSubmit.hidden=n!==4;
    modal.querySelector('.booking-modal-dialog').scrollTo({top:0,behavior:'smooth'});
  };
  const showBaptismStep=(n)=>{
    baptismStep=n; baptismSteps.forEach(x=>x.hidden=Number(x.dataset.baptismStep)!==n);
    document.querySelectorAll('.baptism-step-dot').forEach((x,i)=>x.classList.toggle('is-active',i+1===n));
    standardFields.hidden=n!==2; paymentNotes.hidden=n!==4;
    baptismBack.hidden=n===1; baptismNext.hidden=n===4; baptismSubmit.hidden=n!==4;
    // Force the Baptism footer to mirror Wedding: Back/Next during the wizard,
    // and Submit only on the final (Requirements & Payment) step.
    baptismBack.style.setProperty('display', n===1 ? 'none' : '', n===1 ? 'important' : '');
    baptismNext.style.setProperty('display', n===4 ? 'none' : '', n===4 ? 'important' : '');
    baptismSubmit.style.setProperty('display', n===4 ? '' : 'none', n===4 ? '' : 'important');
    modal.querySelector('.booking-modal-dialog').scrollTo({top:0,behavior:'smooth'});
  };
  const configureBookingMode=(card)=>{
    isWedding=(card.dataset.serviceName||'').trim().toLowerCase()==='wedding';
    isBaptism=(card.dataset.serviceName||'').trim().toLowerCase()==='baptism';
    modal.querySelector('.booking-modal-dialog').classList.toggle('wedding-mode',isWedding||isBaptism);
    // Show exactly one wizard progress header. The modal layout has !important display rules,
    // so use inline !important here to prevent the inactive service header from leaking through.
    weddingProgress.hidden=!isWedding;
    baptismProgress.hidden=!isBaptism;
    weddingProgress.style.setProperty('display', isWedding ? 'flex' : 'none', 'important');
    baptismProgress.style.setProperty('display', isBaptism ? 'flex' : 'none', 'important');
    wizardActions.hidden=!isWedding; baptismActions.hidden=!isBaptism; standardActions.hidden=isWedding||isBaptism;
    weddingSteps.forEach(x=>x.hidden=true); baptismSteps.forEach(x=>x.hidden=true);
    standardFields.hidden=isWedding||isBaptism; paymentNotes.hidden=isWedding||isBaptism;
    setRequired('[name="groom_first_name"],[name="groom_last_name"],[name="groom_dob"],[name="groom_religion_parish"],[name="bride_first_name"],[name="bride_last_name"],[name="bride_dob"],[name="bride_religion_parish"],[name="wedding_contact_number"],[name="wedding_contact_email"],[name="ceremony_type"]',isWedding);
    setRequired('[name="child_first_name"],[name="child_last_name"],[name="child_dob"],[name="father_first_name"],[name="father_last_name"],[name="mother_first_name"],[name="mother_maiden_last_name"],[name="parents_marriage_status"],[name="baptism_contact_number"],[name="baptism_contact_email"],[name="baptism_type"],[name="principal_male_godparent"],[name="principal_female_godparent"],[name="birth_province_code"],[name="birth_city_municipality_code"],[name="birth_barangay_code"]',isBaptism);
    dateInput.min=isWedding?'<?=date('Y-m-d',strtotime('+30 days'))?>':'<?=date('Y-m-d')?>';
    if(isWedding){ document.getElementById('weddingFeeText').textContent=Number(card.dataset.serviceFee)>0?'₱'+Number(card.dataset.serviceFee).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}):'No base fee'; showWeddingStep(1); }
    else if(isBaptism){ showBaptismStep(1); }
    else { standardFields.hidden=false; paymentNotes.hidden=false; }
  };

  const openModal=(card)=>{
    lastTrigger=card.querySelector('.card-click-area');
    serviceId.value=card.dataset.serviceId;
    title.textContent='Book '+card.dataset.serviceName;
    submit.textContent='Submit '+card.dataset.serviceName+' booking';
    configureBookingMode(card);
    if(dateInput?.value) setTimeout(loadAvailability,0);
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden','false');
    document.body.classList.add('modal-open');
    setTimeout(()=>modal.querySelector('select, input, textarea, button')?.focus(),50);
  };
  const closeModal=()=>{
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden','true');
    document.body.classList.remove('modal-open');
    lastTrigger?.focus();
  };
  document.querySelectorAll('.booking-card').forEach(card=>{
    const trigger=card.querySelector('.card-click-area');
    trigger.addEventListener('click',()=>openModal(card));
    trigger.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();openModal(card);}});
  });
  modal.querySelectorAll('[data-close-modal]').forEach(el=>el.addEventListener('click',closeModal));
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&modal.classList.contains('is-open'))closeModal();});

  const dateInput=document.getElementById('bookingDate');
  const resourceInput=document.getElementById('bookingResource');
  const startInput=document.getElementById('bookingStartTime');
  const slotsBox=document.getElementById('timeSlots');
  const slotHelp=document.getElementById('slotHelp');
  let availabilityController=null;

  const loadAvailability=async()=>{
    slotsBox.innerHTML='';
    startInput.value='';
    if(!serviceId.value||!dateInput.value){slotHelp.textContent='Select a date to see available times.';return;}
    slotHelp.textContent='Checking schedule…';
    availabilityController?.abort(); availabilityController=new AbortController();
    const qs=new URLSearchParams({service_id:serviceId.value,date:dateInput.value,resource_id:resourceInput.value});
    try{
      const response=await fetch('booking-availability.php?'+qs.toString(),{signal:availabilityController.signal,headers:{'Accept':'application/json'}});
      const data=await response.json();
      if(!response.ok||!data.ok) throw new Error(data.message||'Unable to check availability.');
      const available=data.slots.filter(s=>s.available).length;
      slotHelp.textContent=available?`${available} available start time${available===1?'':'s'} for this ${data.duration}-minute service.`:'No available times remain for this date. Please choose another date.';
      data.slots.forEach(slot=>{
        const b=document.createElement('button'); b.type='button'; b.className='time-slot '+(slot.available?'is-available':'is-booked');
        b.textContent=slot.label; b.disabled=!slot.available; b.title=slot.reason; b.setAttribute('aria-label',`${slot.label} — ${slot.reason}`);
        if(slot.available)b.addEventListener('click',()=>{document.querySelectorAll('.time-slot.is-selected').forEach(x=>x.classList.remove('is-selected'));b.classList.add('is-selected');startInput.value=slot.value;});
        slotsBox.appendChild(b);
      });
    }catch(err){if(err.name!=='AbortError')slotHelp.textContent=err.message||'Could not load availability.';}
  };
  dateInput.addEventListener('change',loadAvailability);
  resourceInput.addEventListener('change',loadAvailability);


  const validateStep=()=>{ const section=document.querySelector(`[data-wedding-step="${weddingStep}"]`); if(!section)return true; const fields=[...section.querySelectorAll('input[required],select[required],textarea[required]')]; for(const f of fields){if(!f.reportValidity())return false;} if(weddingStep===2){ if(!dateInput.reportValidity())return false; if(!startInput.value){slotHelp.textContent='Choose one of the available times before continuing.';return false;} } return true; };
  weddingNext?.addEventListener('click',()=>{if(validateStep())showWeddingStep(Math.min(4,weddingStep+1));});
  weddingBack?.addEventListener('click',()=>showWeddingStep(Math.max(1,weddingStep-1)));
  const validateBaptismStep=()=>{ const section=document.querySelector(`[data-baptism-step="${baptismStep}"]`); if(!section)return true; const fields=[...section.querySelectorAll('input[required],select[required],textarea[required]')]; for(const f of fields){if(!f.reportValidity())return false;} if(baptismStep===2){if(!dateInput.reportValidity())return false;if(!startInput.value){slotHelp.textContent='Choose one of the available times before continuing.';return false;}} return true; };
  baptismNext?.addEventListener('click',()=>{if(validateBaptismStep())showBaptismStep(Math.min(4,baptismStep+1));});
  baptismBack?.addEventListener('click',()=>showBaptismStep(Math.max(1,baptismStep-1)));
  document.querySelector('[name="baptism_contact_number"]')?.addEventListener('input',e=>e.target.value=e.target.value.replace(/\D/g,'').slice(0,11));
  const godparentList=document.getElementById('godparentList'), godparentFeeText=document.getElementById('godparentFeeText');
  const refreshGodparentFee=()=>{const n=godparentList?.querySelectorAll('.godparent-row').length||0;if(godparentFeeText)godparentFeeText.textContent='₱'+(n*50).toFixed(2);};
  document.getElementById('addGodparent')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='godparent-row';row.innerHTML='<input name="additional_godparents[]" placeholder="Additional godparent full name"><button type="button" class="btn light small">Remove</button>';row.querySelector('button').addEventListener('click',()=>{row.remove();refreshGodparentFee();});godparentList.appendChild(row);refreshGodparentFee();});
  document.querySelectorAll('.wedding-dob').forEach(input=>{const update=()=>{const out=document.getElementById(input.dataset.ageOutput);if(!input.value){out.textContent='';return;}const d=new Date(input.value+'T00:00:00'),t=new Date();let age=t.getFullYear()-d.getFullYear();if(t<new Date(t.getFullYear(),d.getMonth(),d.getDate()))age--;out.textContent=age>=0?`Age: ${age}${age<18?' — must be 18 or older':''}`:'';};input.addEventListener('change',update);update();});
  document.querySelector('[name="wedding_contact_number"]')?.addEventListener('input',e=>e.target.value=e.target.value.replace(/\D/g,'').slice(0,11));
  const uploadMeter=document.getElementById('weddingUploadMeter');
  const uploadSizeText=document.getElementById('weddingUploadSize');
  const uploadBar=document.getElementById('weddingUploadBar');
  const maxWeddingUploadBytes=Number(uploadMeter?.dataset.maxBytes||0);
  const formatMB=bytes=>(bytes/1048576).toFixed(bytes>=10485760?1:2).replace(/\.0+$/,'')+' MB';
  const totalSelectedUploadBytes=()=>[...bookingForm.querySelectorAll('input[type="file"]')].reduce((total,input)=>total+[...input.files].reduce((n,f)=>n+f.size,0),0);
  const refreshUploadMeter=()=>{
    if(!uploadMeter)return true;
    const total=totalSelectedUploadBytes(), over=maxWeddingUploadBytes>0&&total>maxWeddingUploadBytes;
    uploadSizeText.textContent=`${formatMB(total)} / ${formatMB(maxWeddingUploadBytes)}`;
    uploadMeter.classList.toggle('is-over',over);
    uploadBar.style.width=Math.min(100,maxWeddingUploadBytes?total/maxWeddingUploadBytes*100:0)+'%';
    return !over;
  };
  bookingForm.addEventListener('submit',e=>{
    if(isWedding && !validateStep()){
      e.preventDefault();
      return;
    }
    if(isBaptism && !validateBaptismStep()){
      e.preventDefault();
      return;
    }
    if(isWedding&&!refreshUploadMeter()){
      e.preventDefault(); showWeddingStep(3);
      alert(`Your selected files total ${formatMB(totalSelectedUploadBytes())}. The server can accept up to ${formatMB(maxWeddingUploadBytes)} in one booking. Remove or reduce some files, then submit again.`);
    }
  });

  // Wedding requirement uploads are additive: choosing more files later keeps the files already selected.
  // This is clearer than the browser's default multiple-file picker, which replaces the selection on each open.
  document.querySelectorAll('.requirements-list input[type="file"][multiple]').forEach(input=>{
    const selected=[];
    const key=file=>`${file.name}::${file.size}::${file.lastModified}`;
    const holder=document.createElement('div');
    holder.className='selected-document-list';
    input.insertAdjacentElement('afterend',holder);

    const syncInput=()=>{
      const dt=new DataTransfer();
      selected.forEach(item=>dt.items.add(item.file));
      input.files=dt.files;
    };
    const render=()=>{
      holder.innerHTML='';
      if(!selected.length){
        const empty=document.createElement('small');
        empty.className='file-count';
        empty.textContent='No documents selected yet. You can add files one at a time or select several together.';
        holder.appendChild(empty);
        return;
      }
      const count=document.createElement('small');
      count.className='file-count has-files';
      count.textContent=`${selected.length} file${selected.length===1?'':'s'} ready to upload`;
      holder.appendChild(count);
      const list=document.createElement('div');
      list.className='selected-document-items';
      selected.forEach((item,index)=>{
        const row=document.createElement('div'); row.className='selected-document-item';
        const name=document.createElement('span'); name.textContent=item.file.name; name.title=item.file.name;
        const remove=document.createElement('button'); remove.type='button'; remove.className='remove-document'; remove.textContent='Remove';
        remove.addEventListener('click',()=>{selected.splice(index,1);syncInput();render();refreshUploadMeter();});
        row.append(name,remove); list.appendChild(row);
      });
      holder.appendChild(list);
    };
    input.addEventListener('change',()=>{
      const tooLarge=[];
      [...input.files].forEach(file=>{
        if(file.size>10*1024*1024){tooLarge.push(file.name);return;}
        if(!selected.some(item=>item.key===key(file)))selected.push({key:key(file),file});
      });
      syncInput(); render(); refreshUploadMeter();
      if(tooLarge.length) alert('These files were not added because each document must be 10 MB or smaller:\n\n'+tooLarge.join('\n'));
    });
    render();
  });

  const paymentRadios=document.querySelectorAll('input[name="payment_method"]');
  const gcashPanel=document.getElementById('gcashPanel');
  const receiptInput=document.getElementById('paymentReceipt');
  const syncPayment=()=>{
    const online=document.querySelector('input[name="payment_method"]:checked')?.value==='online';
    if(gcashPanel) gcashPanel.hidden=!online;
    if(receiptInput){
      receiptInput.required=online;
      if(!online) receiptInput.value='';
    }
  };
  paymentRadios.forEach(r=>r.addEventListener('change',syncPayment));
  receiptInput?.addEventListener('change',refreshUploadMeter);
  refreshUploadMeter();
  // Cash is the default payment method on a fresh booking. GCash details only appear when Online is selected.
  <?php if(!$openServiceId): ?>
  const cashPayment=document.querySelector('input[name="payment_method"][value="cash"]');
  if(cashPayment) cashPayment.checked=true;
  <?php endif; ?>
  syncPayment();

  const qrTrigger=document.getElementById('gcashQrTrigger');
  const qrLightbox=document.getElementById('gcashQrLightbox');
  const openQr=()=>{if(!qrLightbox)return;qrLightbox.classList.add('is-open');qrLightbox.setAttribute('aria-hidden','false');};
  const closeQr=()=>{if(!qrLightbox)return;qrLightbox.classList.remove('is-open');qrLightbox.setAttribute('aria-hidden','true');qrTrigger?.focus();};
  qrTrigger?.addEventListener('click',openQr);
  qrLightbox?.querySelectorAll('[data-close-qr]').forEach(el=>el.addEventListener('click',closeQr));
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&qrLightbox?.classList.contains('is-open'))closeQr();});

  const copyNumber=async(button)=>{
    const number=button.dataset.number||'';
    try{
      await navigator.clipboard.writeText(number);
    }catch(e){
      const temp=document.createElement('textarea');temp.value=number;temp.style.position='fixed';temp.style.opacity='0';document.body.appendChild(temp);temp.select();document.execCommand('copy');temp.remove();
    }
    const old=button.textContent;button.textContent='Copied!';button.classList.add('is-copied');setTimeout(()=>{button.textContent=old;button.classList.remove('is-copied');},1600);
  };
  document.querySelectorAll('.copy-number-btn').forEach(btn=>btn.addEventListener('click',()=>copyNumber(btn)));

  <?php if($openServiceId): ?>
  const failedCard=document.querySelector('.booking-card[data-service-id="<?=$openServiceId?>"]');
  if(failedCard){
    title.textContent='Book '+failedCard.dataset.serviceName;
    submit.textContent='Submit '+failedCard.dataset.serviceName+' booking';
    configureBookingMode(failedCard);
    document.body.classList.add('modal-open');
    if(dateInput.value) loadAvailability();
  }
  <?php endif; ?>
})();
</script></body></html>