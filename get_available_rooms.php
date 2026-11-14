<?php
include_once('config.php');
include_once('Rooms.class.php');

if (!isset($_POST['datePicked'], $_POST['startTime'], $_POST['endTime'])) {
    http_response_code(400);
    exit;
}

$datePicked = $_POST['datePicked'];
$startTime  = $_POST['startTime'];
$endTime    = $_POST['endTime'];

// Only look up well-formed input (also keeps odd strings out of the data/ file path)
$date = DateTime::createFromFormat("Y-m-d", $datePicked);
$timeRe = '/^([01]\d|2[0-3]):[0-5]\d$/';
if (!$date || $date->format("Y-m-d") !== $datePicked || !preg_match($timeRe, $startTime) || !preg_match($timeRe, $endTime)) {
    http_response_code(400);
    exit;
}

if ($startTime >= $endTime) {
    echo '<option value="">End time must be after start</option>';
    exit;
}

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($datePicked);

$availableRooms = $rooms->getAvailableRooms($startTime, $endTime);
$availableIds   = array_map(fn($r) => explode(",", $r)[0], $availableRooms);

// Group available by location
$locations = [];
foreach (ROOMS_FEATURES as $roomId => $info) {
    if (in_array($roomId, $availableIds, true)) {
        $locations[$info['location']][] = ["id" => $roomId, "capacity" => $info['capacity']];
    }
}

if (!$locations) {
    echo '<option value="">No rooms free at that time</option>';
    exit;
}

echo '<option value="">Select a room&hellip;</option>';
foreach ($locations as $loc => $roomsList) {
    echo '<optgroup label="' . htmlspecialchars($loc) . '">';
    foreach ($roomsList as $room) {
        echo '<option value="' . htmlspecialchars($room['id']) . '">' .
             htmlspecialchars($room['id'] . " · {$room['capacity']} pax") .
             '</option>';
    }
    echo '</optgroup>';
}
