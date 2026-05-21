@extends('layouts.app')
@section('title', 'Create User')
@section('content')
<div class="page-header">
    <h1 class="page-title">Create New User</h1>
    <p class="page-subtitle">Add a new user to the system</p>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="glass-card p-8" style="max-width:640px;">
    <form method="POST" action="{{ route('users.store') }}" id="createUserForm">
        @csrf
        <div class="mb-5">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
        </div>
        <div class="mb-5">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
        </div>
        <div class="mb-5">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-5">
            <div>
                <label class="form-label">Role</label>
                <select name="role" class="form-select" id="roleSelect" onchange="toggleStudentFields()" required>
                    <option value="student" {{ old('role')=='student'?'selected':'' }}>Student</option>
                    <option value="dorm_manager" {{ old('role')=='dorm_manager'?'selected':'' }}>Dorm Manager</option>
                    <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div id="studentFields">
            <hr style="border-color:rgba(99,102,241,0.1);margin:24px 0;">
            <h3 style="font-size:15px;font-weight:700;color:#c7d2fe;margin-bottom:16px;">Student Information</h3>
            <div class="mb-5">
                <label class="form-label">Student Number</label>
                <input type="text" name="student_number" class="form-input" value="{{ old('student_number') }}" placeholder="STU-2026-XXX">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-5">
                <div>
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-input" value="{{ old('course') }}">
                </div>
                <div>
                    <label class="form-label">Year Level</label>
                    <select name="year_level" class="form-select">
                        <option value="">Select</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
            </div>
            <div class="mb-5">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-input" value="{{ old('contact_number') }}">
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" class="btn-primary">Create User</button>
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleStudentFields() {
    document.getElementById('studentFields').style.display = document.getElementById('roleSelect').value === 'student' ? 'block' : 'none';
}
toggleStudentFields();
</script>
@endpush
@endsection
