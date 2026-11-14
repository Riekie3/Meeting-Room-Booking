<?php
include_once('config.php');
include_once('Rooms.class.php');

header('Content-Type: application/json');

$datePicked = $_POST['datePicked'] ?? '';
$date = DateTime::createFromFormat('Y-m-d', $datePicked);
if (!$date || $date->format('Y-m-d') !== $datePicked) {
    echo json_encode([]);
    exit;
}

$timeRe = '/^([01]\d|2[0-3]):[0-5]\d$/';
$startTime = $_POST['startTime'] ?? '';
$endTime   = $_POST['endTime'] ?? '';
// A slot only counts when both ends are valid and in order
$slotOk = preg_match($timeRe, $startTime) && preg_match($timeRe, $endTime) && $startTime < $endTime;

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($datePicked);

echo json_encode($rooms->getRoomStatuses($slotOk ? $startTime : null, $slotOk ? $endTime : null));
