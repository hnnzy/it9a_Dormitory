@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="page-header">
    <h1 class="page-title">{{ auth()->id() === $user->id ? 'My Profile' : 'Edit User' }}</h1>
    <p class="page-subtitle">Update account information</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:640px;">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="mb-5">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-5">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="mb-5">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current">
        </div>

        @if(auth()->user()->isAdmin())
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-5">
            <div>
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                    <option value="student" {{ $user->role=='student'?'selected':'' }}>Student</option>
                    <option value="dorm_manager" {{ $user->role=='dorm_manager'?'selected':'' }}>Dorm Manager</option>
                    <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
                </select>
                @if(auth()->id() === $user->id)
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p style="font-size:11px;color:#f59e0b;margin-top:4px;">You cannot change your own role.</p>
                @endif
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                    <option value="active" {{ $user->status=='active'?'selected':'' }}>Active</option>
                    <option value="inactive" {{ $user->status=='inactive'?'selected':'' }}>Inactive</option>
                </select>
                @if(auth()->id() === $user->id)
                    <input type="hidden" name="status" value="{{ $user->status }}">
                    <p style="font-size:11px;color:#f59e0b;margin-top:4px;">You cannot change your own status.</p>
                @endif
            </div>
        </div>
        @endif

        @if($user->isStudent() && $user->student)
        <hr style="border-color:rgba(99,102,241,0.1);margin:24px 0;">
        <h3 style="font-size:15px;font-weight:700;color:#c7d2fe;margin-bottom:16px;">Student Information</h3>
        <div class="mb-5">
            <label class="form-label">Student Number</label>
            <input type="text" name="student_number" class="form-input" value="{{ old('student_number', $user->student->student_number) }}">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-5">
            <div>
                <label class="form-label">Course</label>
                <input type="text" name="course" class="form-input" value="{{ old('course', $user->student->course) }}">
            </div>
            <div>
                <label class="form-label">Year Level</label>
                <select name="year_level" class="form-select">
                    @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $yr)
                    <option value="{{ $yr }}" {{ $user->student->year_level==$yr?'selected':'' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-5">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-input" value="{{ old('contact_number', $user->student->contact_number) }}">
        </div>
        @endif

        <div style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" class="btn-primary">Save Changes</button>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            @else
            <a href="{{ route('dashboard') }}" class="btn-secondary">Cancel</a>
            @endif
        </div>
    </form>
</div>

{{-- Admin-Only: Recent Activities on Profile Page --}}
@if(auth()->user()->isAdmin() && auth()->id() === $user->id && isset($activityLogs))
<div class="glass-card p-8" style="max-width:900px;margin-top:32px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(139,92,246,0.15));display:flex;align-items:center;justify-content:center;">
                <svg style="width:20px;height:20px;color:#a5b4fc;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <h3 style="font-size:18px;font-weight:700;color:#e0e7ff;margin:0;">Recent Activities</h3>
                <p style="font-size:12px;color:#64748b;margin:2px 0 0 0;">Last 20 system actions</p>
            </div>
        </div>
        <span class="badge badge-info">Admin Only</span>
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
        <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid rgba(51,65,85,0.4);transition:all 0.2s ease;" onmouseenter="this.style.background='rgba(99,102,241,0.04)';this.style.paddingLeft='8px'" onmouseleave="this.style.background='transparent';this.style.paddingLeft='0'">
            <span style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;">{{ $icon }}</span>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13.5px;color:#cbd5e1;line-height:1.6;margin:0;">{{ $log->description }}</p>
                <p style="font-size:11px;color:#64748b;margin:4px 0 0 0;">{{ $log->created_at->format('M d, Y – g:i A') }}</p>
            </div>
            <span style="flex-shrink:0;font-size:11px;padding:3px 10px;border-radius:8px;background:{{ $iconBg }};color:{{ $iconColor }};font-weight:600;white-space:nowrap;">{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</span>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:40px 0;">
        <div style="width:56px;height:56px;border-radius:16px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:24px;height:24px;color:#64748b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p style="font-size:14px;color:#64748b;font-weight:500;">No activities recorded yet.</p>
        <p style="font-size:12px;color:#475569;margin-top:4px;">Actions like room allocations and application approvals will appear here.</p>
    </div>
    @endif
</div>
@endif
@endsection
