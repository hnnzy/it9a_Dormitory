@extends('layouts.app')
@section('title', 'Archived Users')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
        <h1 class="page-title">Archived Users</h1>
        <p class="page-subtitle">Users that have been archived from the system</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn-secondary">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Users
    </a>
</div>

{{-- Search --}}
<div class="glass-card p-4 mb-6">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Search archived users..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn-secondary">Search</button>
        <a href="{{ route('users.archived') }}" class="btn-secondary">Reset</a>
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Archived On</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($users as $user)
        <tr>
            <td style="font-weight:600;">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-info capitalize">{{ str_replace('_',' ',$user->role) }}</span></td>
            <td><span class="badge badge-danger">Archived</span></td>
            <td>{{ $user->deleted_at->format('M d, Y') }}</td>
            <td style="display:flex;gap:8px;">
                <form method="POST" action="{{ route('users.restore', $user->id) }}" onsubmit="return confirm('Restore this user? They will return to the active users list.')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-success btn-sm" style="display:inline-flex;align-items:center;gap:4px;">
                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Restore
                    </button>
                </form>
                <form method="POST" action="{{ route('users.forceDelete', $user->id) }}" onsubmit="return confirm('⚠️ PERMANENTLY delete this user? This action cannot be undone!')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm" style="display:inline-flex;align-items:center;gap:4px;">
                        <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Permanently
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="empty-state">
            <div style="padding:20px 0;">
                <svg style="width:48px;height:48px;color:#475569;margin:0 auto 16px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <p style="font-size:16px;font-weight:600;color:#94a3b8;margin-bottom:4px;">No archived users</p>
                <p style="font-size:13px;color:#64748b;">Archived users will appear here.</p>
            </div>
        </td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $users->withQueryString()->links() }}</div>
@endsection
