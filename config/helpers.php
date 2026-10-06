<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function normalize_person_name(?string $value): string {
    $value=trim((string)$value);
    if($value==='') return '';
    $value=preg_replace('/\s+/u',' ',$value) ?? $value;
    $parts=preg_split('/([\s\-\'’]+)/u',$value,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($parts as &$part){
        if($part==='' || preg_match('/^[\s\-\'’]+$/u',$part)) continue;
        $hasLower=(bool)preg_match('/\p{Ll}/u',$part);
        $hasUpper=(bool)preg_match('/\p{Lu}/u',$part);
        // Preserve intentional mixed-case spellings such as McDonald or DeLaCruz.
        if($hasLower && $hasUpper && !preg_match('/^\p{Lu}\p{Ll}+$/u',$part)) continue;
        if(function_exists('mb_convert_case')) $part=mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
        else $part=ucfirst(strtolower($part));
    }
    unset($part);
    return implode('',$parts);
}
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function verify_csrf(): void { if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf'])) { http_response_code(419); exit('Invalid request token.'); } }
function redirect(string $url): never { header('Location: '.$url); exit; }
function flash(string $key, ?string $message=null): ?string { if ($message !== null) { $_SESSION['flash'][$key]=$message; return null; } $v=$_SESSION['flash'][$key]??null; unset($_SESSION['flash'][$key]); return $v; }
function appointment_ref(): string { return 'SP-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))); }
function log_activity(PDO $pdo, ?int $uid, string $action, string $details=''): void { $s=$pdo->prepare('INSERT INTO activity_logs(user_id,action,details,ip_address) VALUES(?,?,?,?)'); $s->execute([$uid,$action,$details,$_SERVER['REMOTE_ADDR']??null]); }

function secure_image_upload(array $file, string $dir, string $prefix='upload', int $maxMb=5): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Please choose a valid image file.');
    if (($file['size'] ?? 0) > $maxMb*1024*1024) throw new RuntimeException('Image must be '.$maxMb.' MB or smaller.');
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime] ?? null;
    if(!$ext) throw new RuntimeException('Only JPG, PNG, or WEBP images are allowed.');
    if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Upload directory is unavailable.');
    $name=$prefix.'-'.bin2hex(random_bytes(12)).'.'.$ext;
    if(!move_uploaded_file($file['tmp_name'],rtrim($dir,'/').'/'.$name)) throw new RuntimeException('Unable to save the uploaded image.');
    return $name;
}

function secure_document_upload(array $file, string $dir, string $prefix='document'): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Please choose a valid document file.');
    if (($file['size'] ?? 0) > 10*1024*1024) throw new RuntimeException('Document must be 10 MB or smaller.');
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
    $ext=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime] ?? null;
    if(!$ext) throw new RuntimeException('Only PDF, JPG, PNG, or WEBP documents are allowed.');
    if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Upload directory is unavailable.');
    $name=$prefix.'-'.bin2hex(random_bytes(12)).'.'.$ext;
    if(!move_uploaded_file($file['tmp_name'],rtrim($dir,'/').'/'.$name)) throw new RuntimeException('Unable to save the uploaded document.');
    return $name;
}
