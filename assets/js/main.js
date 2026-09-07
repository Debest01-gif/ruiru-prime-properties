/**
 * Main JavaScript - Ruiru Prime Properties
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== NAVBAR SCROLL =====
    const navbar = document.getElementById('mainNav');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }
    });

    // ===== HAMBURGER / MOBILE MENU =====
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    const navOverlay = document.getElementById('navOverlay');

    function toggleMenu(open) {
        hamburger?.classList.toggle('active', open);
        navLinks?.classList.toggle('open', open);
        navOverlay?.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    }

    hamburger?.addEventListener('click', () => {
        const isOpen = navLinks?.classList.contains('open');
        toggleMenu(!isOpen);
    });

    navOverlay?.addEventListener('click', () => toggleMenu(false));

    // Close menu on link click (mobile)
    navLinks?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => toggleMenu(false));
    });

    // ===== SCROLL TO TOP =====
    const scrollTopBtn = document.getElementById('scrollTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            scrollTopBtn?.classList.add('visible');
        } else {
            scrollTopBtn?.classList.remove('visible');
        }
    });
    scrollTopBtn?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ===== AOS ANIMATIONS =====
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 700,
            easing: 'ease-out-cubic',
            once: true,
            offset: 80,
        });
    }

    // ===== COUNTER ANIMATION =====
    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-target'), 10);
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current).toLocaleString() + (el.getAttribute('data-suffix') || '');
        }, 16);
    }

    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
    }

    // ===== HERO PARTICLES =====
    const particleContainer = document.querySelector('.hero-particles');
    if (particleContainer) {
        const colors = ['rgba(212,168,67,0.4)', 'rgba(212,168,67,0.2)', 'rgba(240,192,96,0.3)', 'rgba(255,255,255,0.15)'];
        for (let i = 0; i < 20; i++) {
            const p = document.createElement('div');
            p.classList.add('particle');
            const size = Math.random() * 6 + 2;
            p.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                left: ${Math.random() * 100}%;
                animation-duration: ${Math.random() * 15 + 8}s;
                animation-delay: ${Math.random() * 5}s;
            `;
            particleContainer.appendChild(p);
        }
    }

    // ===== SEARCH TABS =====
    const searchTabs = document.querySelectorAll('.search-tab');
    const searchTypeInput = document.getElementById('searchType');
    searchTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            searchTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            if (searchTypeInput) {
                searchTypeInput.value = tab.getAttribute('data-type');
            }
        });
    });

    // ===== LIGHTBOX =====
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');

    document.querySelectorAll('[data-lightbox]').forEach(el => {
        el.addEventListener('click', () => {
            const src = el.getAttribute('data-lightbox') || el.src || el.querySelector('img')?.src;
            if (src && lightboxImg && lightbox) {
                lightboxImg.src = src;
                lightbox.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

    function closeLightbox() {
        lightbox?.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });

    // ===== FLASH MESSAGE AUTO DISMISS =====
    const flash = document.querySelector('.alert[data-auto-dismiss]');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }

    // ===== FORM VALIDATION =====
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('[required]').forEach(field => {
                field.style.borderColor = '';
                if (!field.value.trim()) {
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                const firstError = form.querySelector('[required][value=""]');
                firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // ===== MORTGAGE CALCULATOR =====
    const calcForm = document.getElementById('mortgageForm');
    if (calcForm) {
        const loanInput = document.getElementById('loanAmount');
        const rateInput = document.getElementById('interestRate');
        const termInput = document.getElementById('loanTerm');
        const downInput = document.getElementById('downPayment');

        const loanDisplay = document.getElementById('loanDisplay');
        const rateDisplay = document.getElementById('rateDisplay');
        const termDisplay = document.getElementById('termDisplay');
        const downDisplay = document.getElementById('downDisplay');

        const monthlyEl = document.getElementById('monthlyPayment');
        const totalEl = document.getElementById('totalPayment');
        const interestEl = document.getElementById('totalInterest');
        const principalEl = document.getElementById('loanPrincipal');

        function calculate() {
            const home = parseFloat(loanInput?.value) || 5000000;
            const down = parseFloat(downInput?.value) || 20;
            const rate = parseFloat(rateInput?.value) || 13;
            const term = parseInt(termInput?.value) || 20;

            const principal = home * (1 - down / 100);
            const monthlyRate = rate / 100 / 12;
            const n = term * 12;

            const monthly = principal * (monthlyRate * Math.pow(1 + monthlyRate, n)) / (Math.pow(1 + monthlyRate, n) - 1);
            const total = monthly * n;
            const interest = total - principal;

            if (monthlyEl) monthlyEl.textContent = 'KES ' + Math.round(monthly).toLocaleString();
            if (totalEl) totalEl.textContent = 'KES ' + Math.round(total).toLocaleString();
            if (interestEl) interestEl.textContent = 'KES ' + Math.round(interest).toLocaleString();
            if (principalEl) principalEl.textContent = 'KES ' + Math.round(principal).toLocaleString();
        }

        function updateDisplays() {
            if (loanDisplay) loanDisplay.textContent = 'KES ' + (parseInt(loanInput?.value) || 0).toLocaleString();
            if (rateDisplay) rateDisplay.textContent = (rateInput?.value || 13) + '%';
            if (termDisplay) termDisplay.textContent = (termInput?.value || 20) + ' years';
            if (downDisplay) downDisplay.textContent = (downInput?.value || 20) + '%';
            calculate();
        }

        [loanInput, rateInput, termInput, downInput].forEach(el => {
            el?.addEventListener('input', updateDisplays);
        });

        updateDisplays();
    }

    // ===== PROPERTY IMAGE GALLERY SLIDER =====
    initGallery();

    function initGallery() {
        const thumbs = document.querySelectorAll('.gallery-thumb-nav img');
        const mainImg = document.getElementById('galleryMain');
        if (!thumbs.length || !mainImg) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                mainImg.src = thumb.getAttribute('data-full') || thumb.src;
                thumbs.forEach(t => t.parentElement.classList.remove('active'));
                thumb.parentElement.classList.add('active');
            });
        });
    }

    // ===== PROPERTY FILTER FORM =====
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // Auto-submit on select change
        filterForm.querySelectorAll('select').forEach(sel => {
            sel.addEventListener('change', () => filterForm.submit());
        });
    }

    // ===== IMAGE PREVIEW UPLOAD =====
    const imageInput = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            imagePreview.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.style.cssText = 'position:relative;display:inline-block;';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width:100px;height:80px;object-fit:cover;border-radius:8px;border:2px solid rgba(212,168,67,0.3);';
                    div.appendChild(img);
                    imagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ===== SMOOTH REVEAL for sections =====
    document.querySelectorAll('.reveal').forEach(el => {
        const observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                el.classList.add('revealed');
                observer.disconnect();
            }
        }, { threshold: 0.1 });
        observer.observe(el);
    });
});
