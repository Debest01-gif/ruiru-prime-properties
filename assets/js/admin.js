/**
 * Admin Dashboard Scripts - Ruiru Prime Properties
 */
document.addEventListener('DOMContentLoaded', () => {

    // Sidebar Mobile Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.style.display = 'inline-flex';
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            adminSidebar.classList.toggle('open');
        });
        document.addEventListener('click', (e) => {
            if (adminSidebar.classList.contains('open') && !adminSidebar.contains(e.target) && e.target !== sidebarToggle) {
                adminSidebar.classList.remove('open');
            }
        });
    }

    // Confirm Delete Prompts
    document.querySelectorAll('[data-confirm], .btn-delete, .confirm-delete').forEach(el => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // Auto-generate slug from title
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', () => {
            if (!slugInput.dataset.manualEdited) {
                slugInput.value = titleInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.manualEdited = 'true';
        });
    }

    // Image file preview
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const targetSelector = input.getAttribute('data-preview');
            const previewEl = document.querySelector(targetSelector);
            if (previewEl && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewEl.src = e.target.result;
                    previewEl.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });

    // Simple Table Live Search/Filter
    const tableFilter = document.getElementById('adminTableFilter');
    if (tableFilter) {
        tableFilter.addEventListener('input', () => {
            const term = tableFilter.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Auto dismiss alerts
    document.querySelectorAll('.admin-alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
