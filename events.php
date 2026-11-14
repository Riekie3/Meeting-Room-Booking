<?php
include_once("config.php");

$events = [];
$files = glob("data/*.txt") ?: [];

foreach ($files as $file) {
    $datePart = basename($file, ".txt"); // e.g. 2025-09-26
    $lines = file($file, FILE_IGNORE_NEW_LINES) ?: [];

    foreach ($lines as $line) {
        $parts = explode(",", $line);
        if (count($parts) < 6) continue;

        list($roomId, $startTime, $endTime, $staff, $dept, $staffId) = $parts;

        $capacity = ROOMS_FEATURES[$roomId]["capacity"] ?? "";
        $loc = ROOMS_FEATURES[$roomId]["location"] ?? "";
        $color = LOCATION_COLORS[$loc] ?? "#4f46e5";

        $start = $datePart . "T" . $startTime;
        $end   = $datePart . "T" . $endTime;

        $diffMins = (strtotime($end) - strtotime($start)) / 60;
        if ($diffMins >= 540) {
            $durationLabel = "Full day";
        } elseif ($diffMins >= 60 && $diffMins % 60 == 0) {
            $hrs = $diffMins / 60;
            $durationLabel = $hrs . ($hrs == 1 ? " hour" : " hours");
        } else {
            $durationLabel = round($diffMins) . " mins";
        }

        // Plain data only: the page renders these with textContent, never as HTML
        $events[] = [
            "title" => $roomId,
            "start" => $start,
            "end"   => $end,
            "backgroundColor" => $color,
            "borderColor" => $color,
            "textColor" => "#ffffff",
            "extendedProps" => [
                "room"       => $roomId,
                "location"   => $loc,
                "capacity"   => $capacity,
                "staff"      => $staff,
                "staffId"    => $staffId,
                "department" => $dept,
                "date"       => $datePart,
                "startTime"  => $startTime,
                "endTime"    => $endTime,
                "duration"   => $durationLabel,
            ],
        ];
    }
}

header("Content-Type: application/json");
echo json_encode($events);
