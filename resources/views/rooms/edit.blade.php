@extends('layouts.app')
@section('title', 'Edit Room')
@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Room {{ $room->room_number }}</h1>
    <p class="page-subtitle">Update room details and configuration</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:500px;">
    <form method="POST" action="{{ route('rooms.update', $room->room_id) }}">
        @csrf @method('PUT')
        <div class="mb-5">
            <label class="form-label">Room Number</label>
            <input type="text" name="room_number" class="form-input" value="{{ old('room_number', $room->room_number) }}" required>
        </div>
        <div class="mb-5">
            <label class="form-label">Capacity</label>
            <input type="number" name="capacity" class="form-input" min="1" max="20" value="{{ old('capacity', $room->capacity) }}" required>
        </div>
        <div class="mb-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="active" {{ $room->status=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ $room->status=='inactive'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('rooms.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
