<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') - DormHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Minimal fallback: ensures basic layout if Vite assets fail to load --}}
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold logo-gradient">🏠 DormHub</h1>
            <p class="text-slate-500 text-sm mt-2">Dormitory Allocation System</p>
        </div>
        @yield('content')
    </div>
</body>
</html>
