<?php
include_once('config.php');
include_once('Rooms.class.php');

header('Content-Type: application/json');

function respond($success, $message, $status = 200) {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request.', 405);
}

$in = [];
foreach (['datePicked', 'startTime', 'endTime', 'roomId', 'staffName', 'department', 'staffId'] as $k) {
    $in[$k] = trim((string)($_POST[$k] ?? ''));
}
$in['staffName'] = mb_substr($in['staffName'], 0, 100);
$in['staffId']   = mb_substr($in['staffId'], 0, 50);

$timeOk = fn($t) => (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
$date   = DateTime::createFromFormat('Y-m-d', $in['datePicked']);
$dateOk = $date && $date->format('Y-m-d') === $in['datePicked'];

if (in_array('', $in, true)) {
    respond(false, 'Please fill in all fields.');
}
if (!$dateOk || !$timeOk($in['startTime']) || !$timeOk($in['endTime'])) {
    respond(false, 'Please enter a valid date and time.');
}
if ($in['datePicked'] < date('Y-m-d')) {
    respond(false, "You can't book a room in the past.");
}
if ($in['startTime'] >= $in['endTime']) {
    respond(false, 'End time must be after start time.');
}
if (!in_array($in['department'], DEPARTMENTS, true)) {
    respond(false, 'Please choose a department from the list.');
}

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($in['datePicked']);

$booked = $rooms->bookRoom(
    $in['startTime'], $in['endTime'], $in['roomId'],
    $in['staffName'], $in['department'], $in['staffId']
);

if ($booked) {
    respond(true, "{$in['roomId']} is booked on {$in['datePicked']}, {$in['startTime']}–{$in['endTime']}.");
}
respond(false, 'That room is no longer available for the selected slot. Someone may have just booked it.');
