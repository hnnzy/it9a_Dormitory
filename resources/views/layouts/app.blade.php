<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dormitory System') - DormHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Minimal fallback: ensures basic layout if Vite assets fail to load --}}
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #0f172a; color: #e2e8f0; margin: 0; }
    </style>
</head>
<body class="min-h-screen">
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:40;"></div>

    {{-- Sidebar --}}
    <aside class="sidebar fixed top-0 left-0 h-full w-64 z-50 flex flex-col" id="sidebar">
        <div class="p-6 border-b border-indigo-500/10">
            <h1 class="text-2xl font-extrabold logo-gradient">🏠 DormHub</h1>
            <p class="text-xs text-slate-500 mt-1">Allocation Management</p>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                Dashboard
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.index') || request()->routeIs('users.create') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                User Management
            </a>
            @endif
            <a href="{{ route('applications.index') }}" class="sidebar-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ auth()->user()->isStudent() ? 'My Applications' : 'Application Monitoring' }}
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isDormManager())
            <a href="{{ route('rooms.index') }}" class="sidebar-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Room Management
            </a>
            <a href="{{ route('allocations.index') }}" class="sidebar-link {{ request()->routeIs('allocations.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Room Allocation
            </a>
            @endif
            <a href="{{ route('users.edit', auth()->user()) }}" class="sidebar-link">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Profile
            </a>
        </nav>
        <div class="p-4 border-t border-indigo-500/10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full sidebar-link text-red-400 hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <div class="main-content" style="margin-left: 256px;">
        <header class="topbar sticky top-0 z-30 px-8 py-4 flex items-center justify-between">
            <button class="lg:hidden text-slate-400" onclick="toggleSidebar()">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <p class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</p>
            <span class="badge badge-info capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
        </header>
        <main class="p-8 animate-fadeIn">
            @if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error">✕ {{ session('error') }}</div>@endif
            @if(session('info'))<div class="alert alert-info">ℹ {{ session('info') }}</div>@endif
            @yield('content')
        </main>
    </div>
    <script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('mobileOverlay').classList.toggle('open');}</script>
    @stack('scripts')
</body>
</html>
