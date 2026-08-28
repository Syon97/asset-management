/**
 * Reusable catalog item picker modal.
 * Usage: CatalogPicker.open('hiddenInputId', 'displayInputId', function(item) { ... optional callback ... });
 */
window.CatalogPicker = {
    targetHiddenInput: null,
    targetDisplayInput: null,
    onSelectCallback: null,
    currentPage: 1,
    currentQuery: '',

    open: function (hiddenInputId, displayInputId, onSelect) {
        this.targetHiddenInput = hiddenInputId;
        this.targetDisplayInput = displayInputId;
        this.onSelectCallback = typeof onSelect === 'function' ? onSelect : null;
        this.currentPage = 1;
        this.currentQuery = '';

        var searchInput = document.getElementById('catalog-picker-search');
        if (searchInput) {
            searchInput.value = '';
        }

        this.fetchPage(1, '');

        var modalEl = document.getElementById('catalogPickerModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    },

    fetchPage: function (page, q) {
        var tbody = document.getElementById('catalog-picker-results');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>';

        var base = window.CATALOG_PICKER_URL || '/item-catalog/picker';
        var sep = base.indexOf('?') === -1 ? '?' : '&';
        var url = base + sep + 'q=' + encodeURIComponent(q) + '&page=' + page;

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                CatalogPicker.currentPage = data.page;
                tbody.innerHTML = '';

                if (data.items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No items found</td></tr>';
                }

                data.items.forEach(function (item) {
                    var tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.innerHTML = '<td>' + escapeHtmlCatalog(item.item_name) + '</td>'
                        + '<td>' + escapeHtmlCatalog(item.category || '-') + '</td>'
                        + '<td>' + escapeHtmlCatalog(item.item_type || '-') + '</td>';
                    tr.addEventListener('click', function () { CatalogPicker.select(item); });
                    tbody.appendChild(tr);
                });

                CatalogPicker.renderPagination(data.page, data.totalPages);
            });
    },

    renderPagination: function (page, totalPages) {
        var el = document.getElementById('catalog-picker-pagination');
        el.innerHTML = '';
        if (totalPages <= 1) {
            return;
        }

        function makeItem(label, targetPage, disabled, active) {
            var li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = label;
            a.addEventListener('click', function (e) {
                e.preventDefault();
                if (!disabled) {
                    CatalogPicker.fetchPage(targetPage, CatalogPicker.currentQuery);
                }
            });
            li.appendChild(a);
            return li;
        }

        el.appendChild(makeItem('Prev', page - 1, page <= 1, false));
        var start = Math.max(1, page - 2);
        var end = Math.min(totalPages, page + 2);
        for (var p = start; p <= end; p++) {
            el.appendChild(makeItem(String(p), p, false, p === page));
        }
        el.appendChild(makeItem('Next', page + 1, page >= totalPages, false));
    },

    select: function (item) {
        document.getElementById(this.targetHiddenInput).value = item.id;
        document.getElementById(this.targetDisplayInput).value = item.item_name;

        if (this.onSelectCallback) {
            this.onSelectCallback(item);
        }

        var modalEl = document.getElementById('catalogPickerModal');
        bootstrap.Modal.getInstance(modalEl).hide();
    }
};

function escapeHtmlCatalog(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
}

/**
 * Clears a catalog selection back to empty (free-text mode) without
 * touching any other field in the row.
 */
function clearCatalogSelection(hiddenInputId, displayInputId) {
    var hiddenInput = document.getElementById(hiddenInputId);
    var displayInput = document.getElementById(displayInputId);
    if (hiddenInput) {
        hiddenInput.value = '';
    }
    if (displayInput) {
        displayInput.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('catalog-picker-search');
    if (searchInput) {
        var debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            var q = this.value;
            debounce = setTimeout(function () {
                CatalogPicker.currentQuery = q;
                CatalogPicker.fetchPage(1, q);
            }, 300);
        });
    }
});

/**
 * "Did you mean...?" - call this on a free-text description input to warn
 * when something similarly-named already exists in the catalog, so people
 * don't accidentally create near-duplicate catalog entries.
 *
 * Usage: attachCatalogSimilarityCheck('input-id-of-description-field');
 */
function attachCatalogSimilarityCheck(descriptionInputId) {
    var input = document.getElementById(descriptionInputId);
    if (!input) {
        return;
    }

    var warningBox = document.createElement('div');
    warningBox.className = 'alert alert-warning py-2 px-3 mt-1 mb-0 small';
    warningBox.style.display = 'none';
    input.insertAdjacentElement('afterend', warningBox);

    var debounce;
    input.addEventListener('input', function () {
        clearTimeout(debounce);
        var value = input.value.trim();
        if (value.length < 3) {
            warningBox.style.display = 'none';
            return;
        }
        debounce = setTimeout(function () {
            var base = window.CATALOG_SIMILAR_URL || '/item-catalog/similar';
            var sep = base.indexOf('?') === -1 ? '?' : '&';
            fetch(base + sep + 'name=' + encodeURIComponent(value))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.matches || data.matches.length === 0) {
                        warningBox.style.display = 'none';
                        return;
                    }
                    var names = data.matches.map(function (m) { return escapeHtmlCatalog(m.item_name); }).join(', ');
                    warningBox.innerHTML = '⚠ Similar item(s) already in catalog: <strong>' + names + '</strong>. Consider using the picker above instead of free text.';
                    warningBox.style.display = 'block';
                });
        }, 400);
    });
}