/**
 * Reusable staff picker modal.
 * Usage: StaffPicker.open('hiddenInputId', 'displayInputId', function(item) { ... optional callback ... });
 */
window.StaffPicker = {
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

        var searchInput = document.getElementById('staff-picker-search');
        if (searchInput) {
            searchInput.value = '';
        }

        this.fetchPage(1, '');

        var modalEl = document.getElementById('staffPickerModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    },

    fetchPage: function (page, q) {
        var tbody = document.getElementById('staff-picker-results');
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Loading...</td></tr>';

        var base = window.STAFF_PICKER_URL || '/staff/picker';
        var sep = base.indexOf('?') === -1 ? '?' : '&';
        var url = base + sep + 'q=' + encodeURIComponent(q) + '&page=' + page;

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                StaffPicker.currentPage = data.page;
                tbody.innerHTML = '';

                if (data.items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No staff found</td></tr>';
                }

                data.items.forEach(function (item) {
                    var tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.innerHTML = '<td>' + escapeHtml(item.staff_name) + '</td>'
                        + '<td>' + escapeHtml(item.department || '-') + '</td>'
                        + '<td>' + escapeHtml(item.position || '-') + '</td>';
                    tr.addEventListener('click', function () { StaffPicker.select(item); });
                    tbody.appendChild(tr);
                });

                StaffPicker.renderPagination(data.page, data.totalPages);
            });
    },

    renderPagination: function (page, totalPages) {
        var el = document.getElementById('staff-picker-pagination');
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
                    StaffPicker.fetchPage(targetPage, StaffPicker.currentQuery);
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
        document.getElementById(this.targetDisplayInput).value =
            item.staff_name + (item.department ? ' — ' + item.department : '');

        if (this.onSelectCallback) {
            this.onSelectCallback(item);
        }

        var modalEl = document.getElementById('staffPickerModal');
        bootstrap.Modal.getInstance(modalEl).hide();
    }
};

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('staff-picker-search');
    if (!searchInput) {
        return;
    }
    var debounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        var q = this.value;
        debounce = setTimeout(function () {
            StaffPicker.currentQuery = q;
            StaffPicker.fetchPage(1, q);
        }, 300);
    });
});