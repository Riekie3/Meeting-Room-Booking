<?php
include_once 'config.php';
include_once 'Rooms.class.php';

$pageTitle = "Book a Meeting Room";
$activePage = "book";
include "partials/header.php";

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES);
?>
<main class="container-xl py-4 py-lg-5">
  <div class="page-head">
    <h1>Book a meeting room</h1>
    <p class="text-secondary mb-0">See which rooms are free, then book one in seconds.</p>
  </div>

  <div class="row g-4">
    <!-- Booking form -->
    <div class="col-lg-4">
      <div class="card panel sticky-lg-top">
        <div class="card-body p-4">
          <div id="bookingAlert" aria-live="polite"></div>

          <form id="bookingForm" novalidate>
            <div class="mb-3">
              <label class="form-label" for="datePicked">Date</label>
              <input type="date" class="form-control" id="datePicked" name="datePicked"
                     min="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>" required>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label" for="startTime">Start</label>
                <input type="time" class="form-control" id="startTime" name="startTime" step="900" required>
              </div>
              <div class="col-6">
                <label class="form-label" for="endTime">End</label>
                <input type="time" class="form-control" id="endTime" name="endTime" step="900" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="roomDropdown">Room</label>
              <select class="form-select" id="roomDropdown" name="roomId" required disabled>
                <option value="">Pick a date and time first</option>
              </select>
              <div class="form-text" id="roomHint"></div>
            </div>

            <hr class="my-4">

            <div class="mb-3">
              <label class="form-label" for="staffName">Your name</label>
              <input type="text" class="form-control" id="staffName" name="staffName" maxlength="100" autocomplete="name" required>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-5">
                <label class="form-label" for="staffId">Staff ID</label>
                <input type="text" class="form-control" id="staffId" name="staffId" maxlength="50" required>
              </div>
              <div class="col-7">
                <label class="form-label" for="department">Department</label>
                <select class="form-select" id="department" name="department" required>
                  <option value="">Select&hellip;</option>
                  <?php foreach (DEPARTMENTS as $dept): ?>
                    <option value="<?= $h($dept) ?>"><?= $h($dept) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="bookBtn">Book room</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8 d-flex flex-column gap-4">
      <!-- Live room status -->
      <div class="card panel">
        <div class="card-body p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-baseline mb-3">
            <h2 class="h5 fw-bold mb-0">Room status</h2>
            <span class="text-secondary small" id="statusDateLabel"></span>
          </div>
          <div id="roomStatusBoard">
            <p class="text-secondary small mb-0">Loading room status&hellip;</p>
          </div>
        </div>
      </div>

      <!-- Calendar -->
      <div class="card panel">
        <div class="card-body p-3 p-md-4">
          <div class="legend mb-3">
            <?php foreach (LOCATION_COLORS as $loc => $color): ?>
              <span class="legend-item"><span class="dot" style="background: <?= $h($color) ?>"></span><?= $h($loc) ?></span>
            <?php endforeach; ?>
            <span class="text-secondary small ms-auto d-none d-md-inline">Click a date to start a booking</span>
          </div>
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include "partials/footer.php"; ?>
