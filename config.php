<?php
date_default_timezone_set("Asia/Kuala_Lumpur");

define("ROOMS_FEATURES", [
    // WKC
    "Board Room (WKC)"     => ["capacity" => 20, "location" => "WKC"],
    "Sales Side Room"      => ["capacity" => 10, "location" => "WKC"],
    "Finance Side Room 1"  => ["capacity" => 8, "location" => "WKC"],
    "Finance Side Room 2"  => ["capacity" => 8, "location" => "WKC"],

    // PWN
    "Board Room (PWN)"     => ["capacity" => 15, "location" => "PWN"],
    "Room 1"               => ["capacity" => 6, "location" => "PWN"],
    "Room 2"               => ["capacity" => 6, "location" => "PWN"],
    "Room 3"               => ["capacity" => 6, "location" => "PWN"],
]);

define("LOCATION_COLORS", [
    "WKC" => "#4f46e5",
    "PWN" => "#0f766e"
]);

define("DEPARTMENTS", [
    "Account & Finance", "Admin", "Business Strategy", "Customer Service",
    "Ecommerce", "Graphic", "Human Resources", "IT", "Maintenance",
    "Manufacture", "Marketing", "Operation", "Production", "R&D / QA / QC",
    "Supply Chain", "Sales", "Warehouse",
]);
