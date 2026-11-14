<?php
// Expects: $pageTitle (string), $activePage ('book' | 'calendar')
$pageTitle  = $pageTitle  ?? "Meeting Room Booking";
$activePage = $activePage ?? "book";
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <script>
    // Apply the OS light/dark preference before first paint to avoid a flash
    (function () {
      var mq = window.matchMedia("(prefers-color-scheme: dark)");
      var apply = function () { document.documentElement.setAttribute("data-bs-theme", mq.matches ? "dark" : "light"); };
      apply();
      mq.addEventListener("change", apply);
    })();
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="topbar">
  <div class="container-xl d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand">
      <span class="brand-mark">&#9679;</span> Room Booking
    </a>
    <div class="nav-links">
      <a href="index.php" class="<?= $activePage === 'book' ? 'active' : '' ?>">Book a room</a>
      <a href="calendar.php" class="<?= $activePage === 'calendar' ? 'active' : '' ?>">Full calendar</a>
    </div>
  </div>
</nav>
