@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-header">
    <h1 class="page-title">Welcome back, {{ $user->name }}!</h1>
    <p class="page-subtitle">Here's what's happening in the dormitory system</p>
</div>

{{-- Admin / Dorm Manager Stats --}}
@if($user->isAdmin() || $user->isDormManager())
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:32px;">
    @if($user->isAdmin())
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Total Users</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(99,102,241,0.15);display:flex;align-items:center;justify-content:center;">
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </span>
        </div>
        <p style="font-size:32px;font-weight:800;color:#e0e7ff;">{{ $totalUsers }}</p>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Total Students</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(139,92,246,0.15);display:flex;align-items:center;justify-content:center;">
                <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            </span>
        </div>
        <p style="font-size:32px;font-weight:800;color:#e0e7ff;">{{ $totalStudents }}</p>
    </div>
    @endif
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Total Rooms</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;">
                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </span>
        </div>
        <p style="font-size:32px;font-weight:800;color:#e0e7ff;">{{ $totalRooms }}</p>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Active Allocations</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center;">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </span>
        </div>
        <p style="font-size:32px;font-weight:800;color:#e0e7ff;">{{ $totalAllocations }}</p>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Pending Applications</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;">
                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>
        <p style="font-size:32px;font-weight:800;color:#e0e7ff;">{{ $pendingApplications }}</p>
    </div>
</div>

{{-- Recent Tables --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="glass-card p-6">
        <h3 style="font-size:16px;font-weight:700;color:#e0e7ff;margin-bottom:16px;">Recent Applications</h3>
        @if($recentApplications->count())
        <div style="space-y:12px;">
            @foreach($recentApplications as $app)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(51,65,85,0.5);">
                <div>
                    <p style="font-size:14px;font-weight:600;color:#cbd5e1;">{{ $app->student->user->name ?? 'N/A' }}</p>
                    <p style="font-size:12px;color:#64748b;">{{ $app->applied_at?->diffForHumans() }}</p>
                </div>
                <span class="badge {{ $app->status==='approved'?'badge-success':($app->status==='rejected'?'badge-danger':'badge-warning') }}">{{ ucfirst($app->status) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p style="color:#64748b;font-size:14px;">No applications yet.</p>
        @endif
    </div>
    <div class="glass-card p-6">
        <h3 style="font-size:16px;font-weight:700;color:#e0e7ff;margin-bottom:16px;">Recent Allocations</h3>
        @if($recentAllocations->count())
        @foreach($recentAllocations as $alloc)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(51,65,85,0.5);">
            <div>
                <p style="font-size:14px;font-weight:600;color:#cbd5e1;">{{ $alloc->student->user->name ?? 'N/A' }}</p>
                <p style="font-size:12px;color:#64748b;">Room {{ $alloc->room->room_number ?? 'N/A' }}</p>
            </div>
            <span class="badge {{ $alloc->status==='active'?'badge-success':'badge-danger' }}">{{ ucfirst($alloc->status) }}</span>
        </div>
        @endforeach
        @else
        <p style="color:#64748b;font-size:14px;">No allocations yet.</p>
        @endif
    </div>
</div>
@endif

{{-- Admin-Only: Recent Activities --}}
@if($user->isAdmin() && isset($activityLogs))
<div class="glass-card p-6" style="margin-top:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="font-size:16px;font-weight:700;color:#e0e7ff;margin:0;">Recent Activities</h3>
        <span style="font-size:12px;color:#64748b;font-weight:500;">Last 20 actions</span>
    </div>

    @if($activityLogs->count())
    <div style="display:flex;flex-direction:column;gap:2px;">
        @foreach($activityLogs as $log)
        @php
            $iconBg = match($log->action_type) {
                'student_allocated' => 'rgba(16,185,129,0.15)',
                'application_accepted' => 'rgba(99,102,241,0.15)',
                'allocation_deactivated' => 'rgba(239,68,68,0.15)',
                default => 'rgba(148,163,184,0.15)',
            };
            $iconColor = match($log->action_type) {
                'student_allocated' => '#34d399',
                'application_accepted' => '#818cf8',
                'allocation_deactivated' => '#f87171',
                default => '#94a3b8',
            };
            $icon = match($log->action_type) {
                'student_allocated' => '→',
                'application_accepted' => '✓',
                'allocation_deactivated' => '✕',
                default => '•',
            };
        @endphp
        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid rgba(51,65,85,0.4);">
            <span style="flex-shrink:0;width:32px;height:32px;border-radius:8px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;">{{ $icon }}</span>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;color:#cbd5e1;line-height:1.5;margin:0;">{{ $log->description }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0 0;">{{ $log->created_at->format('M d, Y – g:i A') }}</p>
            </div>
            <span style="flex-shrink:0;font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(99,102,241,0.1);color:#a5b4fc;font-weight:600;">{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</span>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:32px 0;">
        <p style="font-size:14px;color:#64748b;">No activities recorded yet.</p>
    </div>
    @endif
</div>
@endif

{{-- Student View --}}
@if($user->isStudent())
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-bottom:32px;">
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">My Room</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;">🏠</span>
        </div>
        @if(isset($myAllocation) && $myAllocation)
        <p style="font-size:24px;font-weight:800;color:#34d399;">{{ $myAllocation->room->room_number }}</p>
        <p style="font-size:12px;color:#64748b;margin-top:4px;">Assigned {{ $myAllocation->date_assigned->format('M d, Y') }}</p>
        @else
        <p style="font-size:16px;font-weight:600;color:#64748b;">Not assigned yet</p>
        @endif
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Applications</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(99,102,241,0.15);display:flex;align-items:center;justify-content:center;">📋</span>
        </div>
        <p style="font-size:24px;font-weight:800;color:#a5b4fc;">{{ isset($myApplications) ? $myApplications->count() : 0 }}</p>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:13px;color:#94a3b8;font-weight:600;">Quick Action</span>
            <span style="width:40px;height:40px;border-radius:12px;background:rgba(139,92,246,0.15);display:flex;align-items:center;justify-content:center;">⚡</span>
        </div>
        <a href="{{ route('applications.create') }}" class="btn-primary" style="width:100%;justify-content:center;margin-top:8px;">Apply for Dorm</a>
    </div>
</div>

@if(isset($myApplications) && $myApplications->count())
<div class="glass-card p-6">
    <h3 style="font-size:16px;font-weight:700;color:#e0e7ff;margin-bottom:16px;">My Application History</h3>
    <table class="data-table">
        <thead><tr><th>Date</th><th>Preferred Room</th><th>Status</th><th>Reviewed</th></tr></thead>
        <tbody>
        @foreach($myApplications as $app)
        <tr>
            <td>{{ $app->applied_at?->format('M d, Y') }}</td>
            <td>{{ $app->preferredRoom->room_number ?? 'Any' }}</td>
            <td><span class="badge {{ $app->status==='approved'?'badge-success':($app->status==='rejected'?'badge-danger':'badge-warning') }}">{{ ucfirst($app->status) }}</span></td>
            <td>{{ $app->reviewed_at?->format('M d, Y') ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endif
@endsection
