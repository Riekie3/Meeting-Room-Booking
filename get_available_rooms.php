<?php
include_once('config.php');
include_once('Rooms.class.php');

if (!isset($_POST['datePicked'], $_POST['startTime'], $_POST['endTime'])) {
    exit;
}

$datePicked = $_POST['datePicked'];
$startTime  = $_POST['startTime'];
$endTime    = $_POST['endTime'];

$rooms = new Rooms(ROOMS_FEATURES);
$rooms->readRoomsDatabase($datePicked); // internally normalizes date

$availableRooms = $rooms->getAvailableRooms($startTime, $endTime);
$availableIds   = array_map(fn($r) => explode(",", $r)[0], $availableRooms);

// Group available by location
$locations = [];
foreach (ROOMS_FEATURES as $roomId => $info) {
    if (in_array($roomId, $availableIds)) {
        $locations[$info['location']][] = [
            "id"       => $roomId,
            "capacity" => $info['capacity']
        ];
    }
}

// Print <option>
echo '<option value="">-- Select Room --</option>';
foreach ($locations as $loc => $roomsList) {
    echo "<optgroup label=\"" . htmlspecialchars($loc) . "\">";
    foreach ($roomsList as $room) {
        echo "<option value=\"" . htmlspecialchars($room['id']) . "\">" .
             htmlspecialchars($room['id'] . " ({$room['capacity']} pax)") .
             "</option>";
    }
    echo "</optgroup>";
}
