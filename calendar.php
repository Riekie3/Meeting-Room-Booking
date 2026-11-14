<?php
include_once 'config.php';
$pageTitle = "Booking Calendar";
$activePage = "calendar";
include "partials/header.php";
?>
<main class="container-xl py-4 py-lg-5">
  <div class="page-head d-flex flex-wrap align-items-end justify-content-between gap-3">
    <div>
      <h1>Booking calendar</h1>
      <p class="text-secondary mb-0">Everything that's booked, across all rooms.</p>
    </div>
    <a href="index.php" class="btn btn-primary">Book a room</a>
  </div>

  <div class="card panel">
    <div class="card-body p-3 p-md-4">
      <div class="legend mb-3">
        <?php foreach (LOCATION_COLORS as $loc => $color): ?>
          <span class="legend-item"><span class="dot" style="background: <?= htmlspecialchars($color) ?>"></span><?= htmlspecialchars($loc) ?></span>
        <?php endforeach; ?>
      </div>
      <div id="calendar"></div>
    </div>
  </div>
</main>
<?php include "partials/footer.php"; ?>
