<?php
class Rooms {
    private $features;
    private $rooms = [];
    private $date;

    public function __construct($features) {
        $this->features = $features;
    }

    public function readRoomsDatabase($date) {
        // Convert dd/mm/yy → YYYY-MM-DD for storage
        $this->date = $this->normalizeDate($date);

        $file = "data/" . $this->date . ".txt";
        $this->rooms = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    }

    public function getAvailableRooms($start, $end) {
        $available = [];

        foreach ($this->features as $roomId => $info) {
            $conflict = false;

            foreach ($this->rooms as $line) {
                $parts = explode(",", $line);
                if (count($parts) < 3) continue;

                if ($parts[0] == $roomId) {
                    $s = $parts[1];
                    $e = $parts[2];
                    // Overlap check
                    if (!($end <= $s || $start >= $e)) {
                        $conflict = true;
                        break;
                    }
                }
            }

            if (!$conflict) {
                $available[] = $roomId . "," . $info['capacity'];
            }
        }
        return $available;
    }

    public function writeRoomsDatabase($start, $end, $roomId, $staffName, $dept, $staffId) {
        $file = "data/" . $this->date . ".txt";
        $line = implode(",", [$roomId, $start, $end, $staffName, $dept, $staffId]) . "\n";

        file_put_contents($file, $line, FILE_APPEND);
    }

    private function normalizeDate($inputDate) {
        // Accepts "dd/mm/yy" from datepicker
        if (strpos($inputDate, "/") !== false) {
            $parts = explode("/", $inputDate); // dd/mm/yy
            if (count($parts) === 3) {
                $day   = str_pad($parts[0], 2, "0", STR_PAD_LEFT);
                $month = str_pad($parts[1], 2, "0", STR_PAD_LEFT);
                $year  = (strlen($parts[2]) === 2) ? "20".$parts[2] : $parts[2];
                return $year . "-" . $month . "-" . $day;
            }
        }
        return $inputDate; // already in YYYY-MM-DD
    }
}
