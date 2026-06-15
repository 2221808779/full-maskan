// Maskan - Main JavaScript

// Global sidebar toggle (called from inline onclick)
window.toggleSidebar = function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
    if (overlay) {
        overlay.classList.toggle('show');
    }
    document.body.classList.toggle('sidebar-open');
};

// ===== THEME TOGGLE (Dark/Light Mode) =====
(function () {
    var theme = localStorage.getItem('maskan_theme') || 'light';
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    window.toggleTheme = function () {
        var html = document.documentElement;
        var isDark = html.getAttribute('data-theme') === 'dark';
        if (isDark) {
            html.removeAttribute('data-theme');
            localStorage.setItem('maskan_theme', 'light');
        } else {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('maskan_theme', 'dark');
        }
        updateThemeIcon();
    };

    window.updateThemeIcon = function () {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('.theme-toggle-btn i').forEach(function (icon) {
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        });
        document.querySelectorAll('.theme-toggle-btn').forEach(function (btn) {
            btn.title = isDark ? (btn.dataset.day || 'Day Mode') : (btn.dataset.night || 'Night Mode');
        });
    };
})();

// ===== LANGUAGE TOGGLE =====
window.switchLanguage = function () {
    var html = document.documentElement;
    var currentLang = html.getAttribute('lang') || 'ar';
    var newLang = currentLang === 'ar' ? 'en' : 'ar';
    var baseUrl = window.langSwitchUrl || '/lang/';
    window.location.href = baseUrl + newLang;
};

$(document).ready(function () {
    // Auto-hide alerts after 5 seconds
    setTimeout(function () {
        $('.alert').fadeOut(500);
    }, 5000);

    // Confirm delete actions
    $('[data-confirm]').on('click', function (e) {
        var msg = $(this).data('confirm') || document.body.dataset.confirmDefault || 'Are you sure?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    // Sidebar active link highlighting
    var currentPath = window.location.pathname;
    $('.main-sidebar .nav-link').each(function () {
        if ($(this).attr('href') === currentPath) {
            $(this).addClass('active');
        }
    });

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });

    // Close sidebar on overlay click
    $(document).on('click', '#sidebarOverlay', function () {
        toggleSidebar();
    });

    // Close sidebar on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#sidebar').hasClass('show')) {
            toggleSidebar();
        }
    });

    // Update theme icons on page load
    updateThemeIcon();

    // Image uploader preview
    $(document).on('change', '.image-uploader input[type="file"]', function () {
        var files = this.files;
        var list = $(this).siblings('.image-preview-list');
        var uploader = $(this).closest('.image-uploader');
        uploader.addClass('has-files');

        for (var i = 0; i < files.length; i++) {
            (function (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var item = $('<div class="preview-item">' +
                        '<img src="' + e.target.result + '" alt="Preview">' +
                        '<button type="button" class="remove-preview" onclick="this.parentElement.remove()">&times;</button>' +
                        '</div>');
                    list.append(item);
                };
                reader.readAsDataURL(file);
            })(files[i]);
        }
    });
});
