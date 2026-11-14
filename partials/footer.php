<!-- Booking details modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <span class="badge-location" id="modalLocation"></span>
          <h5 class="modal-title mt-2" id="eventModalTitle"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="detail-list" id="modalBody"></dl>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>window.LOCATION_COLORS = <?= json_encode(LOCATION_COLORS) ?>;</script>
<script src="assets/app.js"></script>
</body>
</html>
