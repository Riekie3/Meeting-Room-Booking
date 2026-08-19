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

        $file = $this->dataFile($this->date);
        $this->rooms = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    }

    public function getAvailableRooms($start, $end) {
        return $this->availableRoomsFromLines($this->rooms, $start, $end);
    }

    /**
     * Full occupancy picture for every room on the loaded date: capacity,
     * location, every booked time range, and (when $start/$end are given)
     * whether the room is free for that specific slot.
     */
    public function getRoomStatuses($start = null, $end = null) {
        $statuses = [];

        foreach ($this->features as $roomId => $info) {
            $bookedRanges = $this->bookedRangesFor($roomId, $this->rooms);

            $availableForSlot = null;
            if ($start !== null && $end !== null) {
                $availableForSlot = !$this->hasConflict($bookedRanges, $start, $end);
            }

            $statuses[] = [
                "id" => $roomId,
                "capacity" => $info['capacity'],
                "location" => $info['location'],
                "bookedRanges" => $bookedRanges,
                "availableForSlot" => $availableForSlot,
            ];
        }

        return $statuses;
    }

    /**
     * Atomically checks availability and books the room, so two concurrent
     * requests can't both succeed for the same room/slot (TOCTOU race).
     * Returns true if booked, false if the slot was no longer available.
     */
    public function bookRoom($start, $end, $roomId, $staffName, $dept, $staffId) {
        if (!array_key_exists($roomId, $this->features)) {
            return false;
        }

        $file = $this->dataFile($this->date);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $handle = fopen($file, "c+");
        if ($handle === false) {
            return false;
        }

        $booked = false;
        try {
            flock($handle, LOCK_EX);

            $contents = stream_get_contents($handle);
            $lines = $contents === "" ? [] : preg_split("/\r\n|\n|\r/", rtrim($contents, "\n"));
            $lines = array_filter($lines, fn($l) => $l !== "");

            $available = $this->availableRoomsFromLines($lines, $start, $end);
            $availableIds = array_map(fn($r) => explode(",", $r)[0], $available);

            if (in_array($roomId, $availableIds, true)) {
                $line = implode(",", [
                    $roomId,
                    $start,
                    $end,
                    $this->sanitizeField($staffName),
                    $this->sanitizeField($dept),
                    $this->sanitizeField($staffId),
                ]) . "\n";

                fwrite($handle, $line);
                fflush($handle);
                $booked = true;
            }

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if ($booked) {
            $this->rooms[] = trim($line);
        }

        return $booked;
    }

    private function availableRoomsFromLines($lines, $start, $end) {
        $available = [];

        foreach ($this->features as $roomId => $info) {
            $bookedRanges = $this->bookedRangesFor($roomId, $lines);

            if (!$this->hasConflict($bookedRanges, $start, $end)) {
                $available[] = $roomId . "," . $info['capacity'];
            }
        }
        return $available;
    }

    private function bookedRangesFor($roomId, $lines) {
        $ranges = [];
        foreach ($lines as $line) {
            $parts = explode(",", $line);
            if (count($parts) < 3) continue;
            if ($parts[0] !== $roomId) continue;

            $ranges[] = ["start" => $parts[1], "end" => $parts[2]];
        }
        return $ranges;
    }

    private function hasConflict($bookedRanges, $start, $end) {
        foreach ($bookedRanges as $range) {
            if (!($end <= $range['start'] || $start >= $range['end'])) {
                return true;
            }
        }
        return false;
    }

    // Strip characters that would corrupt the comma-delimited storage format
    private function sanitizeField($value) {
        return trim(str_replace([",", "\n", "\r"], " ", (string)$value));
    }

    private function dataFile($date) {
        return "data/" . $date . ".txt";
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
