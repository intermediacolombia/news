/* Artemis Theme - Main JavaScript */

// Theme Toggle - Combined with main initialization
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

    // Ticker animation - handled by CSS now

    // Search modal handling
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function() {
            const searchInput = searchModal.querySelector('input[type="text"]');
            if (searchInput) searchInput.focus();
        });
    }

    // Theme Toggle - FIXED
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    // Check saved preference or use default
    const savedTheme = localStorage.getItem('artemis-theme');
    const htmlEl = document.documentElement;
    
    if (savedTheme) {
        htmlEl.setAttribute('data-theme', savedTheme);
    } else {
        // Default is light, already set in HTML
    }
    
    // Update icon based on current theme
    const currentTheme = htmlEl.getAttribute('data-theme') || 'light';
    if (themeIcon) {
        themeIcon.className = currentTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = htmlEl.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('artemis-theme', newTheme);
            
            if (themeIcon) {
                themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            }
            
            console.log('Theme changed to:', newTheme);
        });
    }

    // Fade-in animations
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.news-card, .columnist-card, .category-card').forEach(function(card) {
        observer.observe(card);
    });

    console.log('Artemis Theme loaded successfully');
});

// Global functions for audio player
window.handlePlay = function() { console.log('Play clicked'); };
window.handlePause = function() { console.log('Pause clicked'); };
window.handleStop = function() { console.log('Stop clicked'); };