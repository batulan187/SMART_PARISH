<?php
declare(strict_types=1);
require '../config/db.php';
require '../config/auth.php';
require_role('parishioner','login.php');
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json; charset=utf-8');

$serviceId=(int)($_GET['service_id']??0);
$resourceId=($_GET['resource_id']??'')!==''?(int)$_GET['resource_id']:null;
$date=(string)($_GET['date']??'');

if(!$serviceId || !$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date) || $date<date('Y-m-d')){
    http_response_code(422);
    echo json_encode(['ok'=>false,'message'=>'Choose a valid date.']); exit;
}
$stmt=$pdo->prepare("SELECT duration_minutes FROM services WHERE id=? AND status='active'");
$stmt->execute([$serviceId]);
$duration=(int)($stmt->fetchColumn()?:0);
if($duration<1){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'Service not found.']);exit;}

// Parish booking hours. Slots start every 30 minutes from 8:00 AM; a service must finish by 5:00 PM.
$dayStart=new DateTime("$date 08:00:00");
$dayEnd=new DateTime("$date 17:00:00");
$now=new DateTime();
$slots=[];
for($slot=clone $dayStart; $slot<$dayEnd; $slot->modify('+30 minutes')){
    $end=(clone $slot)->modify("+$duration minutes");
    if($end>$dayEnd) continue;
    $startTime=$slot->format('H:i:s'); $endTime=$end->format('H:i:s');
    $past=$slot<=$now;
    if($resourceId){
        $q=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE resource_id=? AND appointment_date=? AND status IN ('pending','approved') AND start_time < ? AND end_time > ?");
        $q->execute([$resourceId,$date,$endTime,$startTime]);
    }else{
        // With no room selected, protect the parish schedule itself from duplicate/overlapping bookings.
        $q=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date=? AND status IN ('pending','approved') AND start_time < ? AND end_time > ?");
        $q->execute([$date,$endTime,$startTime]);
    }
    $booked=(bool)$q->fetchColumn();
    // Parish events block the whole parish schedule for their configured time range.
    // Exact overlap rule: requested start < event end AND requested end > event start.
    $eventQ=$pdo->prepare("SELECT title FROM events WHERE event_date=? AND start_time IS NOT NULL AND end_time IS NOT NULL AND start_time < ? AND end_time > ? ORDER BY start_time LIMIT 1");
    $eventQ->execute([$date,$endTime,$startTime]);
    $eventTitle=$eventQ->fetchColumn();
    $eventBlocked=$eventTitle!==false;
    $available=!$past&&!$booked&&!$eventBlocked;
    $reason=$past?'Past':($eventBlocked?'Parish event — unavailable':($booked?'Booked':'Available'));
    $slots[]=['value'=>$slot->format('H:i'),'label'=>$slot->format('g:i A'),'available'=>$available,'reason'=>$reason];
}
echo json_encode(['ok'=>true,'duration'=>$duration,'slots'=>$slots],JSON_UNESCAPED_SLASHES);
