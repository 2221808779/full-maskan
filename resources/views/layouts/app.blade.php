{{-- مسكن — القالب الرئيسي للتطبيق مع التنقل العلوي والسفلي ولوحة التحكم --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
<script>(function(){var t=localStorage.getItem('maskan_theme');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', __('Maskan')) - {{ __('Property Management System') }}</title>
    @if(app()->getLocale() === 'ar')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/maskan.css') }}?v={{ filemtime(public_path('css/maskan.css')) }}">
    @stack('styles')
</head>
<body>
    <div class="app-layout">
        @auth
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28">
                        <path d="M50 5L10 35v50a10 10 0 0010 10h60a10 10 0 0010-10V35L50 5zm30 75a10 10 0 01-10 10H30a10 10 0 01-10-10V40h20v15h20V40h20v40z" fill="currentColor"/>
                    </svg>
                </div>
                <span class="sidebar-logo-text">{{ __('Maskan') }}</span>
            </div>

            <nav>
                <div class="sidebar-section-title">{{ __('Home') }}</div>
                @php $isAdmin = auth()->user()?->user_type === 'admin'; $isOwner = auth()->user()?->user_type === 'owner'; @endphp

                @if($isAdmin)
                <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie icon"></i> {{ __('Dashboard') }}
                </a>
                @elseif($isOwner)
                <a href="{{ route('owner.dashboard') }}" class="sidebar-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie icon"></i> {{ __('Dashboard') }}
                </a>
                @else
                <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') && !request()->routeIs('admin.*') && !request()->routeIs('owner.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie icon"></i> {{ __('Dashboard') }}
                </a>
                @endif

                @if($isAdmin)
                {{-- === Admin Sidebar === --}}
                <div class="sidebar-section-title">{{ __('Administration') }}</div>
                <a href="{{ route('admin.users') }}" class="sidebar-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users icon"></i> {{ __('Users') }}
                </a>
                <a href="{{ route('admin.properties') }}" class="sidebar-item {{ request()->routeIs('admin.properties') && !request()->routeIs('admin.properties.pending') ? 'active' : '' }}">
                    <i class="fas fa-building icon"></i> {{ __('All Properties') }}
                </a>
                <a href="{{ route('admin.properties.pending') }}" class="sidebar-item {{ request()->routeIs('admin.properties.pending') || request()->routeIs('admin.properties.review') ? 'active' : '' }}">
                    <i class="fas fa-clock icon"></i> {{ __('Pending Properties') }}
                </a>
                <a href="{{ route('admin.bookings') }}" class="sidebar-item {{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check icon"></i> {{ __('All Bookings') }}
                </a>
                <a href="{{ route('admin.maintenance') }}" class="sidebar-item {{ request()->routeIs('admin.maintenance') ? 'active' : '' }}">
                    <i class="fas fa-tools icon"></i> {{ __('All Maintenance') }}
                </a>

                <div class="sidebar-section-title">{{ __('Reports & Communication') }}</div>
                <a href="{{ route('admin.reports') }}" class="sidebar-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar icon"></i> {{ __('Reports') }}
                </a>

                <a href="{{ route('admin.notifications.broadcast') }}" class="sidebar-item {{ request()->routeIs('admin.notifications.broadcast') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn icon"></i> {{ __('Broadcast Notification') }}
                </a>
                <a href="{{ route('complaints.index') }}" class="sidebar-item {{ request()->routeIs('complaints*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle icon"></i> {{ __('Complaints') }}
                </a>

                <div class="sidebar-section-title">{{ __('System') }}</div>
                <a href="{{ route('admin.settings') }}" class="sidebar-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog icon"></i> {{ __('Settings') }}
                </a>
                <a href="{{ route('admin.archive') }}" class="sidebar-item {{ request()->routeIs('admin.archive') ? 'active' : '' }}">
                    <i class="fas fa-archive icon"></i> {{ __('Archive') }}
                </a>

                @elseif($isOwner)
                {{-- === Owner Sidebar === --}}
                <div class="sidebar-section-title">{{ __('My Properties') }}</div>
                <a href="{{ route('owner.properties') }}" class="sidebar-item {{ request()->routeIs('owner.properties') ? 'active' : '' }}">
                    <i class="fas fa-building icon"></i> {{ __('My Properties') }}
                </a>
                <a href="{{ route('properties.create') }}" class="sidebar-item">
                    <i class="fas fa-plus-circle icon"></i> {{ __('Add Property') }}
                </a>

                <div class="sidebar-section-title">{{ __('Bookings & Maintenance') }}</div>
                <a href="{{ route('owner.bookings') }}" class="sidebar-item {{ request()->routeIs('owner.bookings*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check icon"></i> {{ __('Bookings') }}
                </a>
                <a href="{{ route('owner.timeline') }}" class="sidebar-item {{ request()->routeIs('owner.timeline') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar icon"></i> {{ __('Booking Timeline') }}
                </a>
                <a href="{{ route('owner.maintenance') }}" class="sidebar-item {{ request()->routeIs('owner.maintenance*') ? 'active' : '' }}">
                    <i class="fas fa-tools icon"></i> {{ __('Maintenance Requests') }}
                </a>

                <div class="sidebar-section-title">{{ __('Finance') }}</div>
                <a href="{{ route('owner.reports') }}" class="sidebar-item {{ request()->routeIs('owner.reports') ? 'active' : '' }}">
                    <i class="fas fa-chart-line icon"></i> {{ __('Financial Reports') }}
                </a>
                <a href="{{ route('owner.invoices') }}" class="sidebar-item {{ request()->routeIs('owner.invoices') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice icon"></i> {{ __('Invoices') }}
                </a>

                @else
                {{-- === Tenant / Technician Sidebar === --}}
                <div class="sidebar-section-title">{{ __('Services') }}</div>
                <a href="{{ route('properties.index') }}" class="sidebar-item {{ request()->routeIs('properties.index') ? 'active' : '' }}">
                    <i class="fas fa-search icon"></i> {{ __('Browse Properties') }}
                </a>
                <a href="{{ route('bookings.index') }}" class="sidebar-item {{ request()->routeIs('bookings*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check icon"></i> {{ __('My Bookings') }}
                </a>
                <a href="{{ route('maintenance.index') }}" class="sidebar-item {{ request()->routeIs('maintenance*') ? 'active' : '' }}">
                    <i class="fas fa-tools icon"></i> {{ __('Maintenance Requests') }}
                </a>
                <a href="{{ route('complaints.index') }}" class="sidebar-item {{ request()->routeIs('complaints*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle icon"></i> {{ __('Complaints') }}
                </a>

                @endif

                <div class="sidebar-section-title">{{ __('General') }}</div>
                <a href="{{ route('profile.index') }}" class="sidebar-item {{ request()->routeIs('profile*') ? 'active' : '' }}">
                    <i class="fas fa-user icon"></i> {{ __('Profile') }}
                </a>
                @if(!$isOwner && !$isAdmin)
                <a href="{{ route('favorites.index') }}" class="sidebar-item {{ request()->routeIs('favorites*') ? 'active' : '' }}">
                    <i class="fas fa-heart icon"></i> {{ __('Favorites') }}
                </a>
                @endif
                @unless(auth()->user()?->user_type === 'admin')
                <a href="{{ route('payments.index') }}" class="sidebar-item {{ request()->routeIs('payments*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave icon"></i> {{ __('Payments') }}
                </a>
                @endunless
                @unless(auth()->user()?->user_type === 'admin')
                <a href="{{ route('messages.index') }}" class="sidebar-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope icon"></i> {{ __('Messages') }}
                </a>
                @endunless
                <a href="{{ route('notifications.index') }}" class="sidebar-item {{ request()->routeIs('notifications*') ? 'active' : '' }}">
                    <i class="fas fa-bell icon"></i> {{ __('Notifications') }}
                </a>
            </nav>
        </aside>

        <!-- Sidebar Overlay (mobile) -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>
        @endauth

        <!-- Main Wrap -->
        <div class="main-wrap">
            <!-- Navbar -->
            @auth
            <header class="navbar">
                <button class="navbar-toggle" onclick="toggleSidebar()" aria-label="{{ __('Toggle sidebar') }}">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="navbar-breadcrumb">
                    @yield('breadcrumb', '')
                </div>

                @unless(auth()->user()?->user_type === 'admin')
                <a href="{{ route('messages.index') }}" class="navbar-notif" title="{{ __('Messages') }}">
                    <i class="fas fa-envelope"></i>
                    <span class="navbar-notif-badge" id="messageBadge"
                          style="{{ $unreadMessagesCount ?? 0 > 0 ? '' : 'display: none;' }}">
                        {{ $unreadMessagesCount ?? 0 }}
                    </span>
                </a>
                @endunless

                <button class="navbar-icon-btn theme-toggle-btn" onclick="toggleTheme()" title="{{ __('Night Mode') }}" data-day="{{ __('Day Mode') }}" data-night="{{ __('Night Mode') }}">
                    <i class="fas fa-moon"></i>
                </button>

                <button class="navbar-lang-btn" onclick="switchLanguage()">
                    <i class="fas fa-globe"></i>
                    <span>{{ app()->getLocale() === 'ar' ? 'EN' : __('Ar') }}</span>
                </button>

                <a href="{{ route('notifications.index') }}" class="navbar-notif">
                    <i class="fas fa-bell"></i>
                    <span class="navbar-notif-badge" id="notificationBadge"
                          style="{{ $unreadNotificationsCount ?? 0 > 0 ? '' : 'display: none;' }}">
                        {{ $unreadNotificationsCount ?? 0 }}
                    </span>
                </a>

                <div class="dropdown">
                    <div class="navbar-avatar-wrap" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="navbar-avatar-initials">
                            {{ substr(auth()->user()?->full_name ?? 'U', 0, 1) }}
                        </div>
                        <div class="navbar-user-info">
                            <span class="navbar-username">{{ auth()->user()?->full_name ?? 'User' }}</span>
                            <span class="navbar-role">{{ auth()->user()?->user_type }}</span>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user ms-2"></i>{{ __('Profile') }}</a></li>
                        @if($isAdmin)
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fas fa-cog ms-2"></i>{{ __('Settings') }}</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                {{ __('Logout') }}
                            </button>
                        </li>
                    </ul>
                </div>
            </header>
            @endauth

            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    @auth
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">{{ __('Confirm Logout') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-0">{{ __('Are you sure you want to logout?') }}</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-maskan-danger">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Footer -->
    <footer class="main-footer">
        &copy; {{ date('Y') }} {{ __("Maskan") }} &mdash; {{ __("All rights reserved") }}
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @vite(['resources/js/app.js', 'resources/js/realtime.js'])
    <script>
        var base = '{{ request()->root() }}';
        window.langSwitchUrl = base + '/lang/';
    </script>
    <script src="{{ asset('js/maskan.js') }}?v={{ filemtime(public_path('js/maskan.js')) }}"></script>
    @stack('scripts')
</body>
</html>

