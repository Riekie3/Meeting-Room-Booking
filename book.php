<?php
include_once('config.php');
include_once('Rooms.class.php');

header('Content-Type: application/json');

$startTime  = $_POST['startTime'] ?? '';
$endTime    = $_POST['endTime'] ?? '';
$roomId     = $_POST['roomId'] ?? '';
$datePicked = $_POST['datePicked'] ?? '';

if ($startTime === '' || $endTime === '' || $roomId === '' || $datePicked === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if ($startTime >= $endTime) {
    echo json_encode(['success' => false, 'message' => 'End time must be after start time.']);
    exit;
}

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($datePicked);

$booked = $rooms->bookRoom(
    $startTime,
    $endTime,
    $roomId,
    $_POST['staffName'] ?? '',
    $_POST['department'] ?? '',
    $_POST['staffId'] ?? ''
);

if ($booked) {
    echo json_encode([
        'success' => true,
        'message' => "Room {$roomId} booked on {$datePicked} from {$startTime} to {$endTime}.",
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'That room is no longer available for the selected slot — someone may have just booked it.',
    ]);
}
