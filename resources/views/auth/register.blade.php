@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<h2 class="text-xl font-bold text-white text-center mb-6">Create Student Account</h2>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;">
    <ul style="list-style:none;margin:0;padding:0;">
        @foreach($errors->all() as $error)
        <li>• {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="mb-4">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" placeholder="Juan Dela Cruz" value="{{ old('name') }}" required>
    </div>
    <div class="mb-4">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" placeholder="you@email.com" value="{{ old('email') }}" required>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mb-4">
        <div>
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required>
        </div>
        <div>
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm" required>
        </div>
    </div>
    <div class="mb-4">
        <label class="form-label">Student Number</label>
        <input type="text" name="student_number" class="form-input" placeholder="STU-2026-001" value="{{ old('student_number') }}" required>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mb-4">
        <div>
            <label class="form-label">Course</label>
            <input type="text" name="course" class="form-input" placeholder="BS Computer Science" value="{{ old('course') }}" required>
        </div>
        <div>
            <label class="form-label">Year Level</label>
            <select name="year_level" class="form-select" required>
                <option value="">Select</option>
                <option value="1st Year" {{ old('year_level')=='1st Year'?'selected':'' }}>1st Year</option>
                <option value="2nd Year" {{ old('year_level')=='2nd Year'?'selected':'' }}>2nd Year</option>
                <option value="3rd Year" {{ old('year_level')=='3rd Year'?'selected':'' }}>3rd Year</option>
                <option value="4th Year" {{ old('year_level')=='4th Year'?'selected':'' }}>4th Year</option>
            </select>
        </div>
    </div>
    <div class="mb-6">
        <label class="form-label">Contact Number (Optional)</label>
        <input type="text" name="contact_number" class="form-input" placeholder="09171234567" value="{{ old('contact_number') }}">
    </div>
    <button type="submit" class="btn-primary">Create Account</button>
</form>

<p class="text-center text-slate-500 text-sm mt-6">
    Already have an account?
    <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold">Sign In</a>
</p>
@endsection
