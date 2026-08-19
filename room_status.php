<?php
include_once('config.php');
include_once('Rooms.class.php');

header('Content-Type: application/json');

if (!isset($_POST['datePicked']) || $_POST['datePicked'] === '') {
    echo json_encode([]);
    exit;
}

$startTime = ($_POST['startTime'] ?? '') !== '' ? $_POST['startTime'] : null;
$endTime   = ($_POST['endTime'] ?? '') !== '' ? $_POST['endTime'] : null;

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($_POST['datePicked']);

echo json_encode($rooms->getRoomStatuses($startTime, $endTime));
