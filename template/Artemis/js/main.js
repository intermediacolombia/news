/* Artemis Theme - Main JavaScript */

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

    // Close menu when clicking outside
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
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                }
            });
        }
    }

    // Ticker animation
    const ticker = document.getElementById('ticker');
    if (ticker) {
        const items = ticker.querySelectorAll('li');
        if (items.length > 1) {
            let currentIndex = 0;
            
            function rotateTicker() {
                items[currentIndex].style.opacity = '0';
                currentIndex = (currentIndex + 1) % items.length;
                items[currentIndex].style.opacity = '1';
            }
            
            setInterval(rotateTicker, 4000);
        }
    }

    // Search modal handling
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
        searchModal.addEventListener('shown.bs.modal', function() {
            const searchInput = searchModal.querySelector('input[type="text"]');
            if (searchInput) {
                searchInput.focus();
            }
        });
    }

    // Add fade-in animation to elements
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

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

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#/') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Form validation styling
    const formInputs = document.querySelectorAll('.search-input');
    formInputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary)';
        });
        input.addEventListener('blur', function() {
            this.style.borderColor = 'rgba(255,255,255,0.1)';
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    console.log('Artemis Theme loaded successfully');
});

// Theme Toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    if (themeToggle) {
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('artemis-theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme === 'dark' ? '' : 'light');
            localStorage.setItem('artemis-theme', newTheme === 'dark' ? '' : newTheme);
            updateThemeIcon(newTheme);
        });
    }
    
    function updateThemeIcon(theme) {
        if (themeIcon) {
            if (theme === 'light') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        }
    }
});

// Global functions for audio player
window.handlePlay = function() {
    console.log('Play clicked');
};

window.handlePause = function() {
    console.log('Pause clicked');
};

window.handleStop = function() {
    console.log('Stop clicked');
};