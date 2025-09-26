<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Bookings Calendar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body { padding:20px; }
    #calendar { max-width: 1000px; margin: auto; }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mb-4"><i class="fas fa-calendar-alt"></i> Booking Calendar</h1>
    <div id="calendar"></div>
    <a href="index.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Back to Booking</a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        events: 'getBookings.php',
        eventColor: '#0d6efd',
        eventTextColor: '#fff',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
      });

      calendar.render();
    });
  </script>
</body>
</html>
