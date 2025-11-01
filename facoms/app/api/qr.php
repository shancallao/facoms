<?php
// File: api/qr.php
require_once __DIR__ . '/../app/models/QRModel.php';
require_once __DIR__ . '/../app/models/ScheduleModel.php';
require_once __DIR__ . '/../app/models/AttendanceModel.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

header('Content-Type: application/json');

$qrModel = new QRModel();
$scheduleModel = new ScheduleModel();
$attendanceModel = new AttendanceModel();

if($method === 'POST' && $path === 'scan'){
  // expected JSON: { "qr_value":"...", "faculty_id": 3 }
  $input = json_decode(file_get_contents('php://input'), true);
  $qr_value = $input['qr_value'] ?? null;
  $faculty_id = $input['faculty_id'] ?? null;
  if(!$qr_value || !$faculty_id){
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
  }
  $qr = $qrModel->findByValue($qr_value);
  if(!$qr){
    echo json_encode(['status'=>'error','message'=>'Invalid QR']);
    exit;
  }
  $room_id = $qr['room_id'];
  $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
  $day = $now->format('l'); // Monday, Tuesday...
  $time = $now->format('H:i:s');
  // find matching schedule in this room for this day and time
  $schedule = $scheduleModel->findActiveByRoomDayTime($room_id, $day, $time);
  if(!$schedule){
    echo json_encode(['status'=>'error','message'=>'No active schedule at this time']);
    exit;
  }
  // authorize: only assigned faculty or DH can start
  if($schedule['faculty_id'] != $faculty_id){
    echo json_encode(['status'=>'error','message'=>'You are not the assigned faculty for this schedule']);
    exit;
  }
  // Determine whether to Start or End based on existing attendance logs
  $lastAction = $attendanceModel->getLastAction($schedule['id'], $faculty_id);
  if(!$lastAction || $lastAction['action'] === 'End'){
    // create Start log
    $attendanceModel->log($schedule['id'], $faculty_id, 'Start', 'Started via QR');
    // change schedule status to Occupied
    $scheduleModel->updateStatus($schedule['id'], 'Occupied');
    echo json_encode(['status'=>'ok','message'=>'Class started','schedule'=>$schedule]);
    exit;
  } else {
    // last action was Start -> End
    $attendanceModel->log($schedule['id'], $faculty_id, 'End', 'Ended via QR');
    $scheduleModel->updateStatus($schedule['id'], 'Class Done');
    echo json_encode(['status'=>'ok','message'=>'Class ended','schedule'=>$schedule]);
    exit;
  }
}

if($method === 'POST' && $path === 'generate'){
  // POST: { "room_id":1 }
  $input = json_decode(file_get_contents('php://input'), true);
  $room_id = $input['room_id'] ?? null;
  if(!$room_id){
    echo json_encode(['status'=>'error','message'=>'Missing room_id']);
    exit;
  }
  // create a unique token value, store in qrcodes table and return a PNG path or data
  $value = bin2hex(random_bytes(12)).'|room:'.$room_id;
  $fileName = 'qrcodes/qr_'.$room_id.'_'.time().'.png';
  // use a server-side QR generator (you'll need php-qrcode library) - here return value for client generator
  $qrModel->create($room_id, $value, $fileName);
  echo json_encode(['status'=>'ok','qr_value'=>$value,'file_path'=>$fileName]);
  exit;
}

echo json_encode(['status'=>'error','message'=>'Invalid endpoint']);
