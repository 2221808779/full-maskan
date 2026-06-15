{{-- مسكن — القالب البسيط للزوار (صفحات تسجيل الدخول والتسجيل) --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
<script>(function(){var t=localStorage.getItem('maskan_theme');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/maskan.css') }}?v={{ filemtime(public_path('css/maskan.css')) }}">
    @stack('styles')
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-card-logo">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 5L10 35v50a10 10 0 0010 10h60a10 10 0 0010-10V35L50 5zm30 75a10 10 0 01-10 10H30a10 10 0 01-10-10V40h20v15h20V40h20v40z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="auth-card-title">{{ __('Maskan') }}</div>
                <p class="auth-card-subtitle">{{ __('Smart property and maintenance management platform') }}</p>
            </div>
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var base = '{{ request()->root() }}';
        window.langSwitchUrl = base + '/lang/';
    </script>
    <script src="{{ asset('js/maskan.js') }}?v={{ filemtime(public_path('js/maskan.js')) }}"></script>
    @if(session('auth_token'))
    <script>localStorage.setItem('auth_token', '{{ session('auth_token') }}');</script>
    @endif
    @stack('scripts')
</body>
</html>