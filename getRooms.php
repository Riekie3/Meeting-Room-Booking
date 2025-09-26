<?php
require_once "Rooms.class.php";
require_once "config.php";

if (isset($_POST["datePicked"], $_POST["startTime"], $_POST["endTime"], $_POST["location"])) {
    $roomsObj = new Rooms(ROOMS_FEATURES);
    $roomsObj->readRoomsDatabase($_POST["datePicked"]);

    $availableRooms = $roomsObj->getAvailableRooms($_POST["location"], $_POST["startTime"], $_POST["endTime"]);

    echo json_encode(array_values($availableRooms));
}
