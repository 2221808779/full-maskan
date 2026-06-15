{{-- مسكن — الصفحة الترحيبية الرئيسية للزوار مع العقارات المميزة --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
<script>(function(){var t=localStorage.getItem('maskan_theme');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Maskan') }} - {{ __('Property Management System') }}</title>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="preloader-logo">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 5L10 35v50a10 10 0 0010 10h60a10 10 0 0010-10V35L50 5zm30 75a10 10 0 01-10 10H30a10 10 0 01-10-10V40h20v15h20V40h20v40z" fill="#C49A2B"/>
            </svg>
        </div>
        <div class="preloader-bar"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 5L10 35v50a10 10 0 0010 10h60a10 10 0 0010-10V35L50 5zm30 75a10 10 0 01-10 10H30a10 10 0 01-10-10V40h20v15h20V40h20v40z" fill="currentColor"/>
            </svg>
            <span>{{ __('Maskan') }}</span>
        </a>

        <ul class="nav-links">
            <li><a href="#hero" class="nav-link">{{ __('Home') }}</a></li>
            <li><a href="#services" class="nav-link">{{ __('Features') }}</a></li>
            <li><a href="#about" class="nav-link">{{ __('Who Uses It') }}</a></li>
            <li><a href="#contact" class="nav-link">{{ __('Contact Us') }}</a></li>
        </ul>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('login') }}" class="btn-nav btn-nav-outline desktop-only">{{ __('Login') }}</a>
            <a href="{{ route('register') }}" class="btn-nav btn-nav-primary desktop-only">{{ __('Get Started') }}</a>

            <button class="mobile-toggle" id="mobileToggle" aria-label="{{ __('Menu') }}">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu" id="mobileMenu">
        <ul class="mobile-nav-links">
            <li><a href="#hero" class="mobile-nav-link">{{ __('Home') }}</a></li>
            <li><a href="#services" class="mobile-nav-link">{{ __('Features') }}</a></li>
            <li><a href="#about" class="mobile-nav-link">{{ __('Who Uses It') }}</a></li>
            <li><a href="#contact" class="mobile-nav-link">{{ __('Contact Us') }}</a></li>
            <li><a href="{{ route('login') }}" class="mobile-nav-link">{{ __('Login') }}</a></li>
            <li><a href="{{ route('register') }}" class="btn-nav btn-nav-primary mobile-cta">{{ __('Get Started') }}</a></li>

        </ul>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-bg-overlay"></div>
        <div class="hero-content">
            <div class="hero-grid">
                <div class="hero-text">
                    <div class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        {{ __('Integrated Digital Property Management System') }}
                    </div>

                    <h1 class="hero-title">
                        <span class="line">
                            <span class="line-inner" style="--line-delay: 0.6s">{{ __('Find') }}</span>
                        </span>
                        <span class="line">
                            <span class="line-inner" style="--line-delay: 0.8s"><span class="gold">{{ __('Ideal Home') }}</span></span>
                        </span>
                        <span class="line">
                            <span class="line-inner" style="--line-delay: 1s">{{ __('With Ease') }}</span>
                        </span>
                    </h1>

                    <p class="hero-description">
                        {{ __('An integrated digital platform connecting owners, tenants, and technicians in one place') }}
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('register') }}" class="btn-primary" style="position: relative; overflow: hidden;">
                            {{ __('Get Started') }} <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div>
                            <div class="hero-stat-number">+<span class="counter" data-target="{{ $stats['properties'] }}">0</span></div>
                            <div class="hero-stat-label">{{ __('Registered Properties') }}</div>
                        </div>
                        <div>
                            <div class="hero-stat-number">+<span class="counter" data-target="{{ $stats['activeUsers'] }}">0</span></div>
                            <div class="hero-stat-label">{{ __('Active Users') }}</div>
                        </div>
                        <div>
                            <div class="hero-stat-number">+<span class="counter" data-target="{{ $stats['completedBookings'] }}">0</span></div>
                            <div class="hero-stat-label">{{ __('Completed Bookings') }}</div>
                        </div>
                        <div>
                            <div class="hero-stat-number"><span class="counter" data-target="{{ $stats['satisfaction'] }}">0</span>%</div>
                            <div class="hero-stat-label">{{ __('User Satisfaction') }}</div>
                        </div>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="hero-main-image">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=800" alt="{{ __('Luxury Villa') }}">
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Features Section -->
    <section class="section" id="services">
        <div class="container">
            <div class="section-header section-reveal">
                <div class="section-label">{{ __('Why Maskan?') }}</div>
                <h2 class="section-title">{{ __('System Features') }}</h2>
                <p class="section-subtitle">{{ __('A comprehensive set of tools and features that make property management easier than ever') }}</p>
            </div>

            <div class="services-grid">
                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="service-title">{{ __('Property Management') }}</h3>
                    <p class="service-text">{{ __('Add and manage your properties easily with interactive map display') }}</p>
                </div>

                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="service-title">{{ __('Smart Booking') }}</h3>
                    <p class="service-text">{{ __('Integrated booking system with interactive calendar and availability management') }}</p>
                </div>

                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="service-title">{{ __('AI-Powered Maintenance') }}</h3>
                    <p class="service-text">{{ __('Automatic classification of maintenance requests and assigning the right technician') }}</p>
                </div>

                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="service-title">{{ __('Secure Online Payment') }}</h3>
                    <p class="service-text">{{ __('Pay securely with downloadable digital invoices') }}</p>
                </div>

                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="service-title">{{ __('Instant Communication') }}</h3>
                    <p class="service-text">{{ __('Real-time chat and messaging between all parties') }}</p>
                </div>

                <div class="service-card section-reveal">
                    <div class="service-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3 class="service-title">{{ __('Interactive Maps') }}</h3>
                    <p class="service-text">{{ __('Browse available properties on an interactive map visually and easily') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section section-light" id="properties">
        <div class="container">
            <div class="section-header section-reveal">
                <div class="section-label">{{ __('How It Works') }}</div>
                <h2 class="section-title">{{ __('Three Simple Steps to Start') }}</h2>
                <p class="section-subtitle">{{ __('Start your journey with Maskan in minutes') }}</p>
            </div>

            <div class="how-grid">
                <div class="how-card section-reveal">
                    <div class="how-number">1</div>
                    <div class="how-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="how-title">{{ __('Create Your Account') }}</h3>
                    <p class="how-text">{{ __('Create an account as owner, tenant, or maintenance technician') }}</p>
                </div>

                <div class="how-card section-reveal">
                    <div class="how-number">2</div>
                    <div class="how-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="how-title">{{ __('Use the System') }}</h3>
                    <p class="how-text">{{ __('Add your properties or find a suitable home') }}</p>
                </div>

                <div class="how-card section-reveal">
                    <div class="how-number">3</div>
                    <div class="how-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="how-title">{{ __('Track Everything') }}</h3>
                    <p class="how-text">{{ __('Bookings, payments, and maintenance from one place') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Who Uses It Section -->
    <section class="section" id="about">
        <div class="container">
            <div class="section-header section-reveal">
                <div class="section-label">{{ __('Who Uses Maskan?') }}</div>
                <h2 class="section-title">{{ __('Designed for all parties in the real estate ecosystem') }}</h2>
                <p class="section-subtitle">{{ __('Integrated solutions that suit all users') }}</p>
            </div>

            <div class="about-grid section-reveal" style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                <div class="service-card role-card" style="text-align: center;">
                    <div class="service-icon" style="margin: 0 auto 1.5rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3 class="service-title">{{ __('Admin') }}</h3>
                    <p class="service-text">{{ __('Full management of the system, users, reports, and notifications') }}</p>
                </div>

                <div class="service-card role-card" style="text-align: center;">
                    <div class="service-icon" style="margin: 0 auto 1.5rem;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="service-title">{{ __('Property Owner') }}</h3>
                    <p class="service-text">{{ __('Add properties, manage bookings, and track maintenance requests') }}</p>
                </div>

                <div class="service-card role-card" style="text-align: center;">
                    <div class="service-icon" style="margin: 0 auto 1.5rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="service-title">{{ __('Tenant') }}</h3>
                    <p class="service-text">{{ __('Search, book, pay online, and request maintenance') }}</p>
                </div>

                <div class="service-card role-card" style="text-align: center;">
                    <div class="service-icon" style="margin: 0 auto 1.5rem;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="service-title">{{ __('Technician') }}</h3>
                    <p class="service-text">{{ __('Receive and execute maintenance requests and update their status') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section section-light" id="testimonials">
        <div class="container">
            <div class="section-header section-reveal">
                <div class="section-label">{{ __('User Reviews') }}</div>
                <h2 class="section-title">{{ __('What They Say About Maskan?') }}</h2>
                <p class="section-subtitle">{{ __('Real experiences from our users') }}</p>
            </div>

            <div class="testimonials-grid">
                @php
                $testimonials = [
                    ['name' => __('Ahmed Al-Mansouri'), 'role' => __('Property Owner'), 'text' => __('The system made managing my properties so easy, I can track every booking and maintenance request from one place.')],
                    ['name' => __('Fatima Al-Zahrani'), 'role' => __('Tenant'), 'text' => __('I searched for an apartment, booked it, and paid everything from the app — a wonderful and smooth experience.')],
                    ['name' => __('Khaled Al-Baraq'), 'role' => __('Maintenance Technician'), 'text' => __('The AI-powered automatic classification of maintenance requests saved us a lot of time in distribution.')],
                ];
                @endphp

                @foreach($testimonials as $testimonial)
                <div class="testimonial-card section-reveal">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"{{ $testimonial['text'] }}"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">{{ mb_substr($testimonial['name'], 0, 1) }}</div>
                        <div>
                            <div class="testimonial-name">{{ $testimonial['name'] }}</div>
                            <div class="testimonial-role">{{ $testimonial['role'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="contact">
        <div class="cta-content section-reveal">
            <h2 class="cta-title">{{ __('Join now and experience smarter property management') }}</h2>
            <p class="cta-text">{{ __('Maskan platform') }} — {{ __('the integrated digital solution for property management, rental, and maintenance') }}</p>

            <div class="cta-buttons">
                @if(!Auth::check())
                    <a href="{{ route('register') }}" class="btn-primary" style="position: relative; overflow: hidden;">
                        <i class="fas fa-user-plus"></i> {{ __('Start Free') }}
                    </a>
                    <a href="{{ route('login') }}" class="btn-outline-white">
                        {{ __('Login') }}
                    </a>
                @else
                    <a href="{{ url('/dashboard') }}" class="btn-primary" style="position: relative; overflow: hidden;">
                        <i class="fas fa-tachometer-alt"></i> {{ __('Dashboard') }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="{{ route('home') }}" class="footer-brand">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M50 5L10 35v50a10 10 0 0010 10h60a10 10 0 0010-10V35L50 5zm30 75a10 10 0 01-10 10H30a10 10 0 01-10-10V40h20v15h20V40h20v40z" fill="#C49A2B"/>
                        </svg>
                        <span>{{ __('Maskan') }}</span>
                    </a>
                    <p class="footer-text">{{ __('A digital platform connecting owners, tenants, and technicians in one place') }}</p>
                </div>

                <div>
                    <h4 class="footer-title">{{ __('Quick Links') }}</h4>
                    <ul class="footer-links">
                        <li><a href="#hero">{{ __('Home') }}</a></li>
                        <li><a href="#services">{{ __('Features') }}</a></li>
                        <li><a href="#about">{{ __('Who Uses It') }}</a></li>
                        <li><a href="#contact">{{ __('Contact Us') }}</a></li>
                        <li><a href="{{ route('login') }}">{{ __('Login') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">{{ __('Contact Us') }}</h4>
                    <div class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ config('settings.contact_phone', '+218 91 000 0000') }}</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ config('settings.contact_address', __('Tripoli, Libya')) }}</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ __('Maskan') }} — {{ __('All rights reserved') }}</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize theme from localStorage
        (function() {
            const stored = localStorage.getItem('maskan_theme');
            if (stored) document.documentElement.setAttribute('data-theme', stored);
        })();

        // Preloader
        window.addEventListener('load', () => {
            setTimeout(() => {
                const preloader = document.getElementById('preloader');
                preloader.classList.add('fade-out');
            }, 1500);
        });

        // Normal navigation — no scene wipe

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Section reveal on scroll with staggered children
        const revealElements = document.querySelectorAll('.section-reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Stagger children if they exist
                    const children = entry.target.querySelectorAll(':scope > *');
                    children.forEach((child, index) => {
                        if (!child.classList.contains('section-reveal')) return;
                        child.style.transitionDelay = `${index * 0.1}s`;
                        child.classList.add('visible');
                    });
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        revealElements.forEach(el => revealObserver.observe(el));

        // Counter animation
        const counters = document.querySelectorAll('.counter');
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.target);
                    const duration = 2000;
                    const startTime = performance.now();

                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const easeOut = 1 - Math.pow(1 - progress, 3);
                        counter.textContent = Math.floor(easeOut * target).toLocaleString('ar-SA');

                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        }
                    }

                    requestAnimationFrame(updateCounter);
                    counterObserver.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => counterObserver.observe(counter));

        // Smooth anchor scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offset = 80;
                    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        });

        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link, .mobile-cta');

        mobileToggle.addEventListener('click', () => {
            mobileToggle.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });

        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileToggle.classList.remove('active');
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Add stagger delay to grid item reveals
        document.querySelectorAll('.services-grid .section-reveal, .how-grid .section-reveal, .testimonials-grid .section-reveal').forEach((el, i) => {
            el.style.setProperty('--stagger-delay', `${i * 0.1}s`);
        });
    </script>
</body>
</html>
