<?php
include_once("config.php");
include_once("Rooms.class.php");

$events = [];
$files = glob("data/*.txt");

foreach ($files as $file) {
    $datePart = basename($file, ".txt"); // e.g. 2025-09-26
    $rooms = file($file, FILE_IGNORE_NEW_LINES);

    foreach ($rooms as $line) {
        $parts = explode(",", $line);

        // Expect 6 parts only (not 7)
        if (count($parts) < 6) continue;

        list($roomId, $startTime, $endTime, $staff, $dept, $staffId) = $parts;

        $capacity = ROOMS_FEATURES[$roomId]["capacity"] ?? "";
        $loc = ROOMS_FEATURES[$roomId]["location"] ?? "";
        $color = LOCATION_COLORS[$loc] ?? "#3788d8";

        // Combine date + time
        $start = $datePart . "T" . $startTime;
        $end   = $datePart . "T" . $endTime;

        // Calculate duration type
        $startTS = strtotime($start);
        $endTS   = strtotime($end);
        $diffMins = ($endTS - $startTS) / 60;

        if ($diffMins >= 540) {
            $durationLabel = "Full Day";
        } elseif ($diffMins == 60) {
            $durationLabel = "60 mins";
        } elseif ($diffMins == 30) {
            $durationLabel = "30 mins";
        } else {
            $durationLabel = round($diffMins) . " mins";
        }

        $events[] = [
            "title" => $roomId . " – " . $durationLabel,
            "start" => $start,
            "end"   => $end,
            "backgroundColor" => $color,
            "borderColor" => $color,
            "textColor" => "#000",
            "extendedProps" => [
                "details" => "
                    <b>Room:</b> {$roomId}<br>
                    <b>Capacity:</b> {$capacity}<br>
                    <b>Staff:</b> {$staff} ({$staffId})<br>
                    <b>Department:</b> {$dept}<br>
                    <b>Time:</b> {$startTime} - {$endTime}<br>
                    <b>Date:</b> {$datePart}<br>
                    <b>Duration:</b> {$durationLabel}
                "
            ]
        ];
    }
}

header("Content-Type: application/json");
echo json_encode($events);
