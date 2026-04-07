/* Artemis Theme - Main JavaScript */

// Global theme toggle function
function toggleTheme() {
    const htmlEl = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');
    const themeIconDesktop = document.getElementById('theme-icon-desktop');
    
    const currentTheme = htmlEl.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    htmlEl.setAttribute('data-theme', newTheme);
    localStorage.setItem('artemis-theme', newTheme);
    
    const iconClass = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    
    if (themeIcon) {
        themeIcon.className = iconClass;
    }
    if (themeIconDesktop) {
        themeIconDesktop.className = iconClass;
    }
    
    console.log('Theme changed to:', newTheme);
}

// Mobile menu
var mobileMenuOpen = false;
function toggleMobileMenu() {
    var mobileMenu = document.getElementById('artemis-mobile-menu');
    var mobileOverlay = document.getElementById('artemis-mobile-overlay');
    
    if (!mobileMenu) return;
    
    mobileMenuOpen = !mobileMenuOpen;
    
    if (mobileMenuOpen) {
        mobileMenu.classList.add('active');
        if (mobileOverlay) mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        mobileMenu.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Mobile dropdowns - initialize after DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Mobile dropdown toggle
    var dropdownToggles = document.querySelectorAll('.artemis-dropdown-toggle');
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var parent = this.closest('.artemis-mobile-dropdown');
            if (parent) {
                parent.classList.toggle('active');
            }
        });
    });
});

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.querySelector('.artemis-navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Mobile menu toggle
    const menuToggle = document.querySelector('#side-menu-trigger');
    const offcanvasMenu = document.querySelector('#offcanvas-body-wrapper');
    const menuClose = document.querySelector('#offcanvas-nav-close');

    if (menuToggle && offcanvasMenu) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            offcanvasMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (menuClose && offcanvasMenu) {
        menuClose.addEventListener('click', function() {
            offcanvasMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    if (offcanvasMenu) {
        offcanvasMenu.addEventListener('click', function(e) {
            if (e.target === offcanvasMenu) {
                offcanvasMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // Initialize Swiper if available
    if (typeof Swiper !== 'undefined') {
        const heroSwiper = document.querySelector('.heroSwiper');
        if (heroSwiper) {
            new Swiper('.heroSwiper', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                autoplay: { delay: 5000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
                effect: 'fade',
                fadeEffect: { crossFade: true }
            });
        }
    }

    // Search modal handling
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function() {
            const searchInput = searchModal.querySelector('input[type="text"]');
            if (searchInput) searchInput.focus();
        });
    }

    // Theme initialization - ensure it matches localStorage
    const savedTheme = localStorage.getItem('artemis-theme');
    const htmlEl = document.documentElement;
    const themeIcon = document.getElementById('theme-icon');
    const themeIconDesktop = document.getElementById('theme-icon-desktop');
    
    // If there's a saved theme, apply it
    if (savedTheme) {
        htmlEl.setAttribute('data-theme', savedTheme);
    }
    
    // Set correct icon for both mobile and desktop
    const currentTheme = htmlEl.getAttribute('data-theme') || 'light';
    const iconClass = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    
    if (themeIcon) {
        themeIcon.className = iconClass;
    }
    if (themeIconDesktop) {
        themeIconDesktop.className = iconClass;
    }

    console.log('Artemis Theme loaded - Theme:', currentTheme);
});

// Global functions for audio player
window.handlePlay = function() { console.log('Play clicked'); };
window.handlePause = function() { console.log('Pause clicked'); };
window.handleStop = function() { console.log('Stop clicked'); };