<div class="modal fade" id="catalogPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Catalog Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="catalog-picker-search" class="form-control mb-3"
               placeholder="Search by item name...">
        <table class="table table-hover table-sm">
          <thead>
            <tr><th>Item Name</th><th>Category</th><th>Type</th></tr>
          </thead>
          <tbody id="catalog-picker-results">
            <tr><td colspan="3" class="text-center text-muted">Type to search or browse below</td></tr>
          </tbody>
        </table>
        <nav>
          <ul class="pagination justify-content-center mb-0" id="catalog-picker-pagination"></ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<script>
    window.CATALOG_PICKER_URL = <?= json_encode(\yii\helpers\Url::to(['/item-catalog/picker'])) ?>;
    window.CATALOG_SIMILAR_URL = <?= json_encode(\yii\helpers\Url::to(['/item-catalog/similar'])) ?>;
</script>