document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllRows');
    const rowChecks = Array.from(document.querySelectorAll('.row-check'));
    if (selectAll && rowChecks.length > 0) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });

        rowChecks.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const checkedCount = rowChecks.filter(function (item) { return item.checked; }).length;
                selectAll.checked = checkedCount === rowChecks.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
            });
        });
    }

    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const bulkAssigneeSelect = document.getElementById('bulkAssigneeSelect');
    if (bulkActionSelect && bulkAssigneeSelect) {
        const toggleAssignee = function () {
            const needsAssignee = bulkActionSelect.value === 'assign_selected';
            bulkAssigneeSelect.disabled = !needsAssignee;
            if (!needsAssignee) {
                bulkAssigneeSelect.value = '';
            }
        };
        toggleAssignee();
        bulkActionSelect.addEventListener('change', toggleAssignee);
    }

    const copyButtons = Array.from(document.querySelectorAll('[data-copy-text]'));
    if (copyButtons.length > 0 && navigator.clipboard) {
        copyButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const original = button.textContent;
                navigator.clipboard.writeText(button.getAttribute('data-copy-text') || '').then(function () {
                    button.textContent = 'Copied';
                    window.setTimeout(function () {
                        button.textContent = original;
                    }, 1200);
                }).catch(function () {
                    button.textContent = 'Copy failed';
                    window.setTimeout(function () {
                        button.textContent = original;
                    }, 1200);
                });
            });
        });
    }
});
