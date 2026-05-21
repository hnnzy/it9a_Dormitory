@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage all system users and their accounts</p>
    </div>
    <div style="display:flex;gap:12px;">
        <a href="{{ route('users.archived') }}" class="btn-secondary">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            View Archived
        </a>
        <a href="{{ route('users.create') }}" class="btn-primary">+ Add User</a>
    </div>
</div>

{{-- Filters --}}
<div class="glass-card p-4 mb-6">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Search by name or email..." value="{{ request('search') }}">
        </div>
        <div style="width:160px;">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
                <option value="dorm_manager" {{ request('role')=='dorm_manager'?'selected':'' }}>Dorm Manager</option>
                <option value="student" {{ request('role')=='student'?'selected':'' }}>Student</option>
            </select>
        </div>
        <div style="width:140px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($users as $user)
        <tr>
            <td style="font-weight:600;">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-info capitalize">{{ str_replace('_',' ',$user->role) }}</span></td>
            <td><span class="badge {{ $user->status==='active'?'badge-success':'badge-danger' }}">{{ ucfirst($user->status) }}</span></td>
            <td>{{ $user->created_at->format('M d, Y') }}</td>
            <td style="display:flex;gap:8px;">
                <a href="{{ route('users.edit', $user) }}" class="btn-secondary btn-sm">Edit</a>
                @if(auth()->user()->isAdmin() && auth()->id() !== $user->id)
                    @if($user->status === 'active')
                    <span title="Deactivate this user before archiving" class="btn-warning btn-sm" style="opacity:0.4;cursor:not-allowed;display:inline-flex;align-items:center;gap:4px;pointer-events:none;">
                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Archive
                    </span>
                    @else
                    <form method="POST" action="{{ route('users.archive', $user) }}" onsubmit="return confirm('Archive this user? They will be hidden from the system but can be restored later.')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-warning btn-sm" style="display:inline-flex;align-items:center;gap:4px;">
                            <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            Archive
                        </button>
                    </form>
                    @endif
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="empty-state">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $users->withQueryString()->links() }}</div>
@endsection
