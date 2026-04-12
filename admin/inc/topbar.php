<div class="topbar">
    <div class="topbar-left">
        <div class="site-logo">
            <i class="fas fa-newspaper"></i>
            <span class="site-name">SysNews</span>
        </div>
    </div>
    <div class="topbar-right">
        <!-- Theme Toggle -->
        <div class="theme-toggle" onclick="toggleTheme()">
            <i class="fas fa-moon" id="themeIcon"></i>
        </div>
        <!-- User Dropdown -->
        <div class="user-dropdown">
            <div class="user-trigger" onclick="toggleUserDropdown()">
                <span class="user-name-topbar"><?php echo htmlspecialchars($nombre); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-menu">
                <a href="<?php echo URLBASE; ?>/admin/profile/" class="user-menu-item">
                    <i class="fas fa-user-circle"></i> Mi Perfil
                </a>
                <a href="<?php echo URLBASE; ?>/admin/login/logout.php" class="user-menu-item logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.topbar {
    position: relative;
    height: 60px;
    background: var(--sidebar-bg);
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 1001;
    transition: var(--transition);
    flex-shrink: 0;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.site-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary-color);
    font-weight: 700;
}

.site-logo i {
    font-size: 24px;
}

.site-name {
    font-size: 20px;
    letter-spacing: -0.5px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.theme-toggle {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--sidebar-hover);
    color: var(--sidebar-text);
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.theme-toggle:hover {
    background: var(--sidebar-accent);
    color: #f59e0b;
}

.user-dropdown {
    position: relative;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    background: var(--sidebar-hover);
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
    color: var(--sidebar-text);
}

.user-trigger:hover {
    background: var(--sidebar-accent);
}

.user-name-topbar {
    font-weight: 600;
    font-size: 14px;
}

.user-trigger i {
    font-size: 12px;
    transition: var(--transition);
}

.user-dropdown.open .user-trigger i {
    transform: rotate(180deg);
}

.user-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 200px;
    background: var(--sidebar-bg);
    border: 1px solid var(--sidebar-border);
    border-radius: 12px;
    box-shadow: var(--shadow);
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition);
}

.user-dropdown.open .user-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    color: var(--sidebar-text) !important;
    text-decoration: none;
    transition: var(--transition);
}

.user-menu-item:hover {
    background: var(--sidebar-hover);
}

.user-menu-item i {
    width: 20px;
    text-align: center;
}

.user-menu-item.logout {
    color: #dc3545 !important;
    border-top: 1px solid var(--sidebar-border);
}

.user-menu-item.logout:hover {
    background: rgba(220, 53, 69, 0.1);
}

/* Main content margin */
body {
    background: var(--content-bg);
}

.main-content {
    margin-left: 260px;
    margin-top: 60px;
    padding: 20px;
    min-height: calc(100vh - 60px);
    transition: var(--transition);
}

.main-content.menu-collapsed {
    margin-left: 70px;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
}
</style>

<script>
// Theme toggle
function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('themeIcon');
    
    if (html.getAttribute('data-theme') === 'dark') {
        html.setAttribute('data-theme', 'light');
        icon.className = 'fas fa-moon';
        localStorage.setItem('admin-theme', 'light');
    } else {
        html.setAttribute('data-theme', 'dark');
        icon.className = 'fas fa-sun';
        localStorage.setItem('admin-theme', 'dark');
    }
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('admin-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.getElementById('themeIcon').className = 'fas fa-sun';
    }
});

// User dropdown
function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown');
    dropdown.classList.toggle('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-dropdown')) {
        document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('open'));
    }
});

</script>