<?php
include_once('config.php');
include_once('Rooms.class.php');
$today = date('Y-m-d');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meeting Room Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <style>
    body { background-color: #f6f7f9; }
    .page-header { padding: 1.75rem 0 1.25rem; }
    .page-header h1 { font-weight: 600; font-size: 1.75rem; margin-bottom: .25rem; }
    .page-header p { color: #6c757d; margin-bottom: 0; }
    .card { border: 1px solid #e9ecef; border-radius: .75rem; }
    .room-status-card { background: #fff; transition: box-shadow .15s ease; }
    .room-status-card:hover { box-shadow: 0 .125rem .5rem rgba(0,0,0,.06); }
    .legend-dot { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:6px; vertical-align:middle; }
    #calendar { max-width: 100%; }
    .status-loc-label { letter-spacing:.04em; font-size:.75rem; }
  </style>
</head>
<body>
<div class="container py-4">
  <div class="page-header">
    <h1>Meeting Room Booking</h1>
    <p>See which rooms are free right now, then book one in seconds.</p>
  </div>

  <div id="bookingAlert"></div>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 card-title mb-3">Book a Room</h2>
          <form id="bookingForm">
            <div class="row g-2 mb-3">
              <div class="col-12">
                <label class="form-label small">Date</label>
                <input type="text" name="datePicked" id="datepicker" class="form-control" autocomplete="off" value="<?= htmlspecialchars($today) ?>" required>
              </div>
              <div class="col-6">
                <label class="form-label small">Start Time</label>
                <input type="time" name="startTime" id="startTime" class="form-control" required>
              </div>
              <div class="col-6">
                <label class="form-label small">End Time</label>
                <input type="time" name="endTime" id="endTime" class="form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Room</label>
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
                    echo "<optgroup label=\"" . htmlspecialchars($loc) . "\">";
                    foreach ($roomsList as $room) {
                        echo "<option value=\"" . htmlspecialchars($room['id']) . "\">" .
                             htmlspecialchars($room['id'] . " ({$room['capacity']} pax)") .
                             "</option>";
                    }
                    echo "</optgroup>";
                }
                ?>
              </select>
              <div class="form-text">Pick date + times first — unavailable rooms drop out automatically.</div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Staff Name</label>
              <input type="text" name="staffName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label small">Department</label>
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
              <label class="form-label small">Staff ID</label>
              <input type="text" name="staffId" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="bookBtn">Book Room</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 card-title mb-0">Room Status</h2>
            <span class="text-muted small" id="statusDateLabel"></span>
          </div>
          <div id="roomStatusBoard">
            <p class="text-muted small mb-0">Loading room status…</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h2 class="h5 card-title mb-0">Calendar</h2>
        <a href="calendar.php" class="btn btn-sm btn-outline-secondary">Full Calendar View</a>
      </div>
      <div class="mb-3">
        <?php foreach (LOCATION_COLORS as $loc => $color): ?>
          <span class="legend-dot" style="background-color:<?= htmlspecialchars($color) ?>; border:1px solid rgba(0,0,0,.15);"></span><span class="me-3"><?= htmlspecialchars($loc) ?></span>
        <?php endforeach; ?>
      </div>
      <div id="calendar"></div>
    </div>
  </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const LOCATION_COLORS = <?= json_encode(LOCATION_COLORS) ?>;

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

function renderStatusBoard(rooms) {
  const board = $('#roomStatusBoard');
  if (!rooms.length) {
    board.html('<p class="text-muted small mb-0">No rooms configured.</p>');
    return;
  }

  const groups = {};
  rooms.forEach(r => {
    if (!groups[r.location]) groups[r.location] = [];
    groups[r.location].push(r);
  });

  let html = '';
  for (const loc in groups) {
    const color = LOCATION_COLORS[loc] || '#6c757d';
    html += `<div class="mb-3"><div class="fw-semibold status-loc-label text-uppercase mb-2" style="color:${color}">${escapeHtml(loc)}</div><div class="row g-2">`;

    groups[loc].forEach(r => {
      let badgeClass, badgeText;
      if (r.availableForSlot === true) {
        badgeClass = 'bg-success'; badgeText = 'Available';
      } else if (r.availableForSlot === false) {
        badgeClass = 'bg-danger'; badgeText = 'Occupied';
      } else if (r.bookedRanges.length) {
        badgeClass = 'bg-warning text-dark'; badgeText = 'Booked today';
      } else {
        badgeClass = 'bg-success'; badgeText = 'Free all day';
      }

      const ranges = r.bookedRanges.length
        ? r.bookedRanges.map(b => `${escapeHtml(b.start)}–${escapeHtml(b.end)}`).join(', ')
        : 'No bookings today';

      html += `
        <div class="col-md-6">
          <div class="border rounded p-2 h-100 d-flex justify-content-between align-items-start room-status-card">
            <div>
              <div class="fw-medium">${escapeHtml(r.id)}</div>
              <div class="text-muted small">${r.capacity} pax &middot; ${ranges}</div>
            </div>
            <span class="badge ${badgeClass}">${badgeText}</span>
          </div>
        </div>`;
    });

    html += '</div></div>';
  }

  board.html(html);
}

function fetchRoomStatus() {
  const datePicked = $('#datepicker').val();
  const startTime = $('#startTime').val();
  const endTime = $('#endTime').val();

  if (!datePicked) return;

  $('#statusDateLabel').text(datePicked);

  $.ajax({
    url: 'room_status.php',
    method: 'POST',
    dataType: 'json',
    data: { datePicked, startTime, endTime },
    success: renderStatusBoard
  });
}

function updateRoomDropdown() {
  const datePicked = $('#datepicker').val();
  const startTime = $('#startTime').val();
  const endTime = $('#endTime').val();

  if (datePicked && startTime && endTime) {
    $.ajax({
      url: 'get_available_rooms.php',
      method: 'POST',
      data: { datePicked, startTime, endTime },
      success: function(res) {
        const current = $('#roomDropdown').val();
        $('#roomDropdown').html(res);
        if ($('#roomDropdown option[value="' + current + '"]').length) {
          $('#roomDropdown').val(current);
        }
      }
    });
  }
}

function showAlert(message, ok) {
  const cls = ok ? 'alert-success' : 'alert-danger';
  $('#bookingAlert').html(
    `<div class="alert ${cls} alert-dismissible fade show" role="alert">${escapeHtml(message)}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`
  );
}

$(function() {
  $('#datepicker').datepicker({ dateFormat: 'yy-mm-dd', minDate: 0 });

  $('#datepicker, #startTime, #endTime').on('change', function() {
    updateRoomDropdown();
    fetchRoomStatus();
  });

  fetchRoomStatus();

  $('#bookingForm').on('submit', function(e) {
    e.preventDefault();

    const $btn = $('#bookBtn');
    $btn.prop('disabled', true).text('Booking…');

    $.ajax({
      url: 'book.php',
      method: 'POST',
      dataType: 'json',
      data: $(this).serialize()
    }).done(function(res) {
      showAlert(res.message, res.success);
      if (res.success) {
        // Keep the date/time slot so the same person can book another room
        // for it right away; only clear the "who" fields.
        $('#roomDropdown').val('');
        $('input[name="staffName"]').val('');
        $('select[name="department"]').val('');
        $('input[name="staffId"]').val('');
        updateRoomDropdown();
        fetchRoomStatus();
        if (window.calendar) window.calendar.refetchEvents();
      }
    }).fail(function() {
      showAlert('Something went wrong booking the room. Please try again.', false);
    }).always(function() {
      $btn.prop('disabled', false).text('Book Room');
    });
  });

  window.calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    height: 'auto',
    events: { url: 'events.php', method: 'GET' },
    eventClick: function(info) {
      $('#modalBody').html(info.event.extendedProps.details);
      new bootstrap.Modal(document.getElementById('eventModal')).show();
    }
  });
  window.calendar.render();
});
</script>
</body>
</html>
