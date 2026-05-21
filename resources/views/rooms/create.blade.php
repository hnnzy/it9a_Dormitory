@extends('layouts.app')
@section('title', 'Add Room')
@section('content')
<div class="page-header">
    <h1 class="page-title">Add New Room</h1>
    <p class="page-subtitle">Create a new dormitory room</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:500px;">
    <form method="POST" action="{{ route('rooms.store') }}">
        @csrf
        <div class="mb-5">
            <label class="form-label">Room Number</label>
            <input type="text" name="room_number" class="form-input" placeholder="e.g. RM-101" value="{{ old('room_number') }}" required>
        </div>
        <div class="mb-6">
            <label class="form-label">Capacity</label>
            <input type="number" name="capacity" class="form-input" min="1" max="20" placeholder="Number of beds" value="{{ old('capacity') }}" required>
            <p style="font-size:12px;color:#64748b;margin-top:6px;">Maximum number of students this room can hold.</p>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-primary">Create Room</button>
            <a href="{{ route('rooms.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
