<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Meeting Room Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<div class="container mt-4">
  <h1>Meeting Room Booking System</h1>

  <?php
  include_once('config.php');
  include_once('Rooms.class.php');

  if (isset($_POST["bookRoom"])) {
      $rooms = new Rooms(ROOMS_FEATURES);
      $rooms->readRoomsDatabase($_POST["datePicked"]);

      $availableRooms = $rooms->getAvailableRooms($_POST["startTime"], $_POST["endTime"]);
      $availableIds = array_map(fn($r) => explode(",", $r)[0], $availableRooms);

      if (in_array($_POST["roomId"], $availableIds)) {
          $rooms->writeRoomsDatabase(
              $_POST["startTime"],
              $_POST["endTime"],
              $_POST["roomId"],
              $_POST["staffName"],
              $_POST["department"],
              $_POST["staffId"]
          );
          echo '<div class="alert alert-success">Room <b>' . $_POST["roomId"] . '</b> booked on <b>' . $_POST["datePicked"] . '</b> from ' . $_POST["startTime"] . ' to ' . $_POST["endTime"] . '.</div>';
      } else {
          echo '<div class="alert alert-danger">Room unavailable for that slot.</div>';
      }
  }
  ?>

  <h3>Booking Form</h3>
  <form method="post" name="myForm" id="bookingForm">
    <input type="hidden" name="bookRoom" value="1">
    <div class="row mb-3">
      <div class="col">
        <label>Date</label>
        <input type="text" name="datePicked" id="datepicker" class="form-control" autocomplete="off" required>
      </div>
      <div class="col">
        <label>Start Time</label>
        <input type="time" name="startTime" id="startTime" class="form-control" required>
      </div>
      <div class="col">
        <label>End Time</label>
        <input type="time" name="endTime" id="endTime" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label>Room</label>
      <select name="roomId" class="form-select" id="roomDropdown" required>
        <option value="">-- Select Room --</option>
        <?php
        $locations = [];
        foreach (ROOMS_FEATURES as $roomId => $info) {
            $locations[$info['location']][] = [
                "id" => $roomId,
                "capacity" => $info['capacity']
            ];
        }
        foreach ($locations as $loc => $roomsList) {
            echo "<optgroup label=\"$loc\">";
            foreach ($roomsList as $room) {
                echo "<option value=\"{$room['id']}\">{$room['id']} ({$room['capacity']} pax)</option>";
            }
            echo "</optgroup>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Staff Name</label>
      <input type="text" name="staffName" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Department</label>
      <select name="department" class="form-select" required>
        <option value="">-- Select Department --</option>
        <option>Account & Finance</option>
        <option>Admin</option>
        <option>Business Strategy</option>
        <option>Customer Service</option>
        <option>Ecommerce</option>
        <option>Graphic</option>
        <option>Human Resources</option>
        <option>IT</option>
        <option>Maintenance</option>
        <option>Manufacture</option>
        <option>Marketing</option>
        <option>Operation</option>
        <option>Production</option>
        <option>R&D / QA / QC</option>
        <option>Supply Chain</option>
        <option>Sales</option>
        <option>Warehouse</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Staff ID</label>
      <input type="text" name="staffId" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Book</button>
  </form>

  <hr>
  <h3>Calendar</h3>
  <div class="mb-3">
    <?php foreach (LOCATION_COLORS as $loc => $color): ?>
      <span style="display:inline-block; width:15px; height:15px; background-color:<?= $color ?>; border:1px solid #000;"></span> <?= $loc ?>&nbsp;&nbsp;
    <?php endforeach; ?>
  </div>
  <div id="calendar"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Booking Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function() {
  // ✅ Save in YYYY-MM-DD format
  $("#datepicker").datepicker({ dateFormat: "yy-mm-dd" });

  function updateRoomDropdown() {
    let datePicked = $("#datepicker").val();
    let startTime = $("#startTime").val();
    let endTime   = $("#endTime").val();

    if(datePicked && startTime && endTime){
      $.ajax({
        url: "get_available_rooms.php",
        method: "POST",
        data: { datePicked, startTime, endTime },
        success: function(res){
          $("#roomDropdown").html(res);
        }
      });
    }
  }
  $("#datepicker, #startTime, #endTime").on("change", updateRoomDropdown);
});

// FullCalendar
document.addEventListener('DOMContentLoaded', function() {
  var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    events: { url: 'events.php', method: 'GET' },
    eventClick: function(info) {
      let details = info.event.extendedProps.details;
      $("#modalBody").html(details);
      var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
      myModal.show();
    }
  });
  calendar.render();

  $("#bookingForm").on("submit", function() {
    setTimeout(() => calendar.refetchEvents(), 500);
  });
});
</script>
</body>
</html>
