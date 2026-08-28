<div class="modal fade" id="staffPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="staff-picker-search" class="form-control mb-3"
               placeholder="Search by name, staff ID, department, or position...">
        <table class="table table-hover table-sm">
          <thead>
            <tr><th>Name</th><th>Department</th><th>Position</th></tr>
          </thead>
          <tbody id="staff-picker-results">
            <tr><td colspan="3" class="text-center text-muted">Type to search or browse below</td></tr>
          </tbody>
        </table>
        <nav>
          <ul class="pagination justify-content-center mb-0" id="staff-picker-pagination"></ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<script>
    window.STAFF_PICKER_URL = <?= json_encode(\yii\helpers\Url::to(['/staff/picker'])) ?>;
</script>