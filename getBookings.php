<?php
header('Content-Type: application/json');

$file = "bookings.json";
$events = [];

if (file_exists($file)) {
    $allBookings = json_decode(file_get_contents($file), true);

    foreach ($allBookings as $booking) {
        $dateParts = explode("/", $booking["date"]); // dd/mm/yyyy
        $isoDate = $dateParts[2] . "-" . $dateParts[1] . "-" . $dateParts[0];

        $events[] = [
            "title" => $booking["location"] . " - " . $booking["room"] . " (" . $booking["pax"] . " pax)",
            "start" => $isoDate . "T" . $booking["start"],
            "end"   => $isoDate . "T" . $booking["end"]
        ];
    }
}

echo json_encode($events);
