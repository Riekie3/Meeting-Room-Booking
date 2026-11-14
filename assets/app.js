(function () {
  "use strict";

  var calendarEl = document.getElementById("calendar");
  var form = document.getElementById("bookingForm");
  var calendar = null;

  /* ---------- Small DOM helper (text is always set via textContent, never parsed as HTML) ---------- */
  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  /* ---------- Booking details modal ---------- */
  function showDetails(event) {
    var p = event.extendedProps;
    var badge = document.getElementById("modalLocation");

    document.getElementById("eventModalTitle").textContent = p.room;
    badge.textContent = p.location || "";
    badge.style.display = p.location ? "" : "none";
    badge.style.background = event.backgroundColor || "";

    var rows = [
      ["When", p.date + ", " + p.startTime + " – " + p.endTime],
      ["Duration", p.duration],
      ["Booked by", p.staff + (p.staffId ? " (" + p.staffId + ")" : "")],
      ["Department", p.department],
      ["Capacity", p.capacity ? p.capacity + " pax" : "—"]
    ];

    var dl = document.getElementById("modalBody");
    dl.textContent = "";
    rows.forEach(function (r) {
      dl.appendChild(el("dt", "", r[0]));
      dl.appendChild(el("dd", "", r[1]));
    });

    bootstrap.Modal.getOrCreateInstance(document.getElementById("eventModal")).show();
  }

  /* ---------- Calendar (both pages) ---------- */
  if (calendarEl) {
    var isPhone = window.matchMedia("(max-width: 767.98px)").matches;

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: isPhone ? "listWeek" : "dayGridMonth",
      height: "auto",
      firstDay: 1,
      nowIndicator: true,
      dayMaxEvents: 3,
      eventDisplay: "block",
      slotMinTime: "07:00:00",
      slotMaxTime: "21:00:00",
      scrollTime: "08:00:00",
      // 24-hour times, matching the rest of the app (09:00–11:00)
      eventTimeFormat: { hour: "2-digit", minute: "2-digit", hour12: false },
      slotLabelFormat: { hour: "2-digit", minute: "2-digit", hour12: false },
      // Overlapping bookings sit side by side instead of stacking on top of each other
      slotEventOverlap: false,
      // Week view columns are narrow: show just the start time (the full range is in the details modal)
      views: { timeGridWeek: { displayEventEnd: false } },
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,listWeek"
      },
      buttonText: { today: "Today", month: "Month", week: "Week", list: "List" },
      noEventsContent: "No bookings in this period",
      events: "events.php",
      eventClick: function (info) {
        info.jsEvent.preventDefault();
        showDetails(info.event);
      },
      // On the booking page, clicking a day pre-fills the form date
      dateClick: form ? function (info) {
        // Local "today" (toISOString would give the UTC date)
        var today = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
        var clicked = info.dateStr.slice(0, 10);
        if (clicked < today) return;
        var dateInput = document.getElementById("datePicked");
        dateInput.value = clicked;
        dateInput.dispatchEvent(new Event("change", { bubbles: true }));
        dateInput.scrollIntoView({ behavior: "smooth", block: "center" });
        document.getElementById("startTime").focus({ preventScroll: true });
      } : undefined
    });
    calendar.render();
  }

  /* ---------- Booking page only ---------- */
  if (!form) return;

  var dateEl  = document.getElementById("datePicked");
  var startEl = document.getElementById("startTime");
  var endEl   = document.getElementById("endTime");
  var roomEl  = document.getElementById("roomDropdown");
  var hintEl  = document.getElementById("roomHint");
  var btn     = document.getElementById("bookBtn");
  var alertEl = document.getElementById("bookingAlert");
  var boardEl = document.getElementById("roomStatusBoard");
  var dateLabelEl = document.getElementById("statusDateLabel");
  var colors  = window.LOCATION_COLORS || {};
  var roomRequest = 0, statusRequest = 0;

  function postForm(url, data) {
    return fetch(url, { method: "POST", body: new URLSearchParams(data) });
  }
  function slotParams() {
    return { datePicked: dateEl.value, startTime: startEl.value, endTime: endEl.value };
  }

  /* Alerts */
  function showAlert(message, ok) {
    alertEl.textContent = "";
    var box = el("div", "alert alert-dismissible fade show " + (ok ? "alert-success" : "alert-danger"));
    box.setAttribute("role", ok ? "status" : "alert");
    box.appendChild(el("span", "", message));
    var close = el("button", "btn-close");
    close.type = "button";
    close.setAttribute("data-bs-dismiss", "alert");
    close.setAttribute("aria-label", "Close");
    box.appendChild(close);
    alertEl.appendChild(box);
  }

  /* Room dropdown: only rooms that are free for the chosen slot */
  function setRoomsPlaceholder(text, hint) {
    roomEl.textContent = "";
    var o = el("option", "", text);
    o.value = "";
    roomEl.appendChild(o);
    roomEl.disabled = true;
    hintEl.textContent = hint || "";
  }

  function loadRooms() {
    if (!dateEl.value || !startEl.value || !endEl.value) {
      setRoomsPlaceholder("Pick a date and time first");
      return Promise.resolve();
    }
    if (startEl.value >= endEl.value) {
      setRoomsPlaceholder("End time must be after start");
      return Promise.resolve();
    }

    var mine = ++roomRequest;
    var keep = roomEl.value;
    roomEl.disabled = true;
    hintEl.textContent = "Checking availability…";

    return postForm("get_available_rooms.php", slotParams())
      .then(function (res) { if (!res.ok) throw new Error("bad response"); return res.text(); })
      .then(function (html) {
        if (mine !== roomRequest) return; // a newer request superseded this one
        roomEl.innerHTML = html;          // markup comes from our own server; values are escaped there
        var hasRooms = roomEl.querySelectorAll("option[value]:not([value=''])").length > 0;
        roomEl.disabled = !hasRooms;
        hintEl.textContent = hasRooms ? "" : "Try a different time or date.";
        roomEl.value = hasRooms ? keep : "";
        if (roomEl.value !== keep) roomEl.value = "";
      })
      .catch(function () {
        if (mine !== roomRequest) return;
        setRoomsPlaceholder("Couldn't load rooms", "Please check your connection and try again.");
      });
  }

  /* Room status board */
  function statusInfo(r) {
    if (r.availableForSlot === true)  return ["free", "Available"];
    if (r.availableForSlot === false) return ["busy", "Occupied"];
    if (r.bookedRanges.length)        return ["partial", "Booked today"];
    return ["free", "Free all day"];
  }

  function renderStatusBoard(rooms) {
    boardEl.textContent = "";
    if (!rooms.length) {
      boardEl.appendChild(el("p", "text-secondary small mb-0", "Pick a valid date to see room status."));
      return;
    }

    var groups = {};
    rooms.forEach(function (r) { (groups[r.location] = groups[r.location] || []).push(r); });

    Object.keys(groups).forEach(function (loc) {
      var section = el("div", "status-group");
      var title = el("div", "status-loc");
      var dot = el("span", "dot");
      dot.style.background = colors[loc] || "#6c757d";
      title.appendChild(dot);
      title.appendChild(document.createTextNode(loc));
      section.appendChild(title);

      var grid = el("div", "status-grid");
      groups[loc].forEach(function (r) {
        var info = statusInfo(r);
        var card = el("div", "status-card");
        var left = el("div", "status-main");
        left.appendChild(el("div", "status-name", r.id));
        var ranges = r.bookedRanges.length
          ? r.bookedRanges.map(function (b) { return b.start + "–" + b.end; }).join(", ")
          : "No bookings";
        left.appendChild(el("div", "status-meta", r.capacity + " pax · " + ranges));
        card.appendChild(left);
        card.appendChild(el("span", "status-pill pill-" + info[0], info[1]));
        grid.appendChild(card);
      });
      section.appendChild(grid);
      boardEl.appendChild(section);
    });
  }

  function loadStatus() {
    if (!dateEl.value) return Promise.resolve();
    var mine = ++statusRequest;
    dateLabelEl.textContent = dateEl.value;

    return postForm("room_status.php", slotParams())
      .then(function (res) { if (!res.ok) throw new Error("bad response"); return res.json(); })
      .then(function (rooms) { if (mine === statusRequest) renderStatusBoard(rooms); })
      .catch(function () {
        if (mine !== statusRequest) return;
        boardEl.textContent = "";
        boardEl.appendChild(el("p", "text-secondary small mb-0", "Couldn't load room status."));
      });
  }

  function refreshSlot() { loadRooms(); loadStatus(); }

  /* Convenience: default the end time to one hour after the start time */
  startEl.addEventListener("change", function () {
    if (startEl.value && (!endEl.value || endEl.value <= startEl.value)) {
      var parts = startEl.value.split(":");
      var h = parseInt(parts[0], 10) + 1;
      if (h <= 23) endEl.value = String(h).padStart(2, "0") + ":" + parts[1];
    }
  });
  [dateEl, startEl, endEl].forEach(function (input) { input.addEventListener("change", refreshSlot); });

  /* Submit over AJAX */
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    btn.disabled = true;
    btn.textContent = "Booking…";

    postForm("book.php", new FormData(form))
      .then(function (res) { return res.json(); })
      .then(function (res) {
        showAlert(res.message, res.success);
        if (res.success) {
          // Keep the date/time so the same person can book another room for it;
          // only clear the "who" fields and the chosen room.
          roomEl.value = "";
          ["staffName", "staffId", "department"].forEach(function (name) { form.elements[name].value = ""; });
          if (calendar) calendar.refetchEvents();
        }
        refreshSlot(); // also refreshes after a failure: the slot may have just been taken
      })
      .catch(function () {
        showAlert("Something went wrong booking the room. Please try again.", false);
      })
      .then(function () {
        btn.disabled = false;
        btn.textContent = "Book room";
      });
  });

  loadStatus();
})();
