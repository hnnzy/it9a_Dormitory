<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Exclude students who have no approved dorm application
        $query->where(function ($q) {
            $q->where('role', '!=', 'student')
              ->orWhereHas('student.applications', function ($appQ) {
                  $appQ->where('status', 'approved');
              });
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,dorm_manager,student'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        // If creating a student, also create student record
        if ($request->role === 'student') {
            $request->validate([
                'student_number' => ['required', 'string', 'unique:students,student_number'],
                'course' => ['required', 'string', 'max:255'],
                'year_level' => ['required', 'string', 'max:50'],
                'contact_number' => ['nullable', 'string', 'max:20'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_number' => $request->student_number,
                'course' => $request->course,
                'year_level' => $request->year_level,
                'contact_number' => $request->contact_number,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user)
    {
        $user->load('student');

        $activityLogs = null;

        // Load activity logs when admin views their own profile
        if ($request->user()->isAdmin() && $request->user()->id === $user->id) {
            $activityLogs = ActivityLog::with(['actor', 'student.user', 'room'])
                ->latest()
                ->take(20)
                ->get();
        }

        return view('users.edit', compact('user', 'activityLogs'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $authUser = $request->user();

        // Non-admin users can only edit their own profile
        if (!$authUser->isAdmin() && $authUser->id !== $user->id) {
            abort(403, 'You can only edit your own profile.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ];

        // Only admin can change role and status
        if ($authUser->isAdmin()) {
            $rules['role'] = ['required', 'in:admin,dorm_manager,student'];
            $rules['status'] = ['required', 'in:active,inactive'];
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($authUser->isAdmin()) {
            // Rule 3: Admins cannot modify their own role or status
            if ($authUser->id === $user->id) {
                if ($request->has('role') && $request->role !== $user->role) {
                    return back()->with('error', 'You cannot change your own role.');
                }
                if ($request->has('status') && $request->status !== $user->status) {
                    return back()->with('error', 'You cannot deactivate your own account.');
                }
            }

            // Prevent setting user to inactive if they have active allocations
            if ($request->status === 'inactive' && $user->status === 'active' && $user->student) {
                $activeAllocations = $user->student->allocations()->where('status', 'active')->count();
                if ($activeAllocations > 0) {
                    return back()->with('error', "Cannot set user to inactive — they have {$activeAllocations} active room allocation(s). Remove or deactivate the allocation first.");
                }
            }

            // When reactivating a user, auto-deactivate any allocations tied to inactive rooms
            if ($request->status === 'active' && $user->status === 'inactive' && $user->student) {
                $staleAllocations = $user->student->allocations()
                    ->where('status', 'active')
                    ->whereHas('room', function ($q) {
                        $q->where('status', 'inactive');
                    })
                    ->with('room')
                    ->get();

                foreach ($staleAllocations as $allocation) {
                    $allocation->update(['status' => 'inactive']);
                }
            }

            $user->role = $request->role;
            $user->status = $request->status;
        }

        $user->save();

        // Update student record if exists
        if ($user->isStudent() && $user->student) {
            $request->validate([
                'student_number' => ['sometimes', 'string', 'unique:students,student_number,' . $user->student->student_id . ',student_id'],
                'course' => ['sometimes', 'string', 'max:255'],
                'year_level' => ['sometimes', 'string', 'max:50'],
                'contact_number' => ['nullable', 'string', 'max:20'],
            ]);

            $user->student->update($request->only(['student_number', 'course', 'year_level', 'contact_number']));
        }

        $redirect = $authUser->isStudent() ? route('dashboard') : route('users.index');
        return redirect($redirect)->with('success', 'User updated successfully.');
    }

    /**
     * Archive the specified user (soft delete - Admin only).
     */
    public function archive(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Only administrators can archive user accounts.');
        }

        // Rule 3: Prevent admin from archiving themselves
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot archive your own account.');
        }

        // Rule 1: Block archiving if user has active room allocations
        if ($user->hasActiveAllocations()) {
            return back()->with('error', 'This user cannot be archived because they are still assigned to a dorm room. Remove or deactivate their allocation first.');
        }

        // Rule 2: Must be inactive before archiving
        if ($user->status === 'active') {
            return back()->with('error', 'Deactivate the user before archiving.');
        }

        $user->delete(); // Soft delete

        return redirect()->route('users.index')->with('success', 'User archived successfully.');
    }

    /**
     * Display archived users (Admin only).
     */
    public function archived(Request $request)
    {
        $query = User::onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('deleted_at')->paginate(15);

        return view('users.archived', compact('users'));
    }

    /**
     * Restore an archived user (Admin only).
     */
    public function restore(Request $request, $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $user->restore();

        return redirect()->route('users.archived')->with('success', "User \"{$user->name}\" has been restored successfully.");
    }

    /**
     * Permanently delete an archived user (Admin only).
     */
    public function forceDelete(Request $request, $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // Rule 3: Prevent self-deletion
        if ($request->user()->id == $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Rule 1: Cannot permanently delete user with active allocations
        if ($user->hasActiveAllocations()) {
            return back()->with('error', 'This user cannot be deleted because they are still assigned to a dorm room.');
        }

        $user->forceDelete();

        return redirect()->route('users.archived')->with('success', 'User permanently deleted.');
    }
}

