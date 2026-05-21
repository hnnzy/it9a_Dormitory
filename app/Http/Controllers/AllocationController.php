<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Allocation::with(['student.user', 'room']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $allocations = $query->latest('date_assigned')->paginate(15);
        return view('allocations.index', compact('allocations'));
    }

    public function create()
    {
        $allocatedStudentIds = Allocation::pluck('student_id');
        $students = Student::with('user')
            ->whereNotIn('student_id', $allocatedStudentIds)
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('applications', function ($q) {
                $q->where('status', 'approved');
            })
            ->get();

        // Only show rooms with open slots (dynamically computed, includes pending apps)
        $rooms = Room::hasOpenSlots()->get();

        return view('allocations.create', compact('students', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,student_id'],
            'room_id' => ['required', 'exists:rooms,room_id'],
        ]);

        // Verify the student's user account is active
        $student = Student::with('user')->findOrFail($request->student_id);
        if (!$student->user || $student->user->status !== 'active') {
            return back()->with('error', 'Cannot allocate an inactive user to a room.');
        }

        $existing = Allocation::where('student_id', $request->student_id)->exists();
        if ($existing) {
            return back()->with('error', 'This student already has a room allocation. Remove the existing allocation first.');
        }

        try {
            DB::transaction(function () use ($request, $student) {
                // Lock the room row to prevent concurrent allocations
                $room = Room::where('room_id', $request->room_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($room->status !== 'active') {
                    throw new \Exception('Cannot allocate to an inactive room.');
                }

                // Check capacity: active allocations + pending applications must be less than capacity
                $availableSlots = $room->calculateAvailableSlots();

                if ($availableSlots <= 0) {
                    throw new \Exception('This room is already full. No more students can be assigned.');
                }

                Allocation::create([
                    'student_id' => $request->student_id,
                    'room_id' => $request->room_id,
                    'status' => 'active',
                    'date_assigned' => now()->toDateString(),
                ]);

                // Log the allocation activity
                $actorName = $request->user()->name;
                $studentName = $student->user->name;
                ActivityLog::log(
                    $request->user(),
                    'student_allocated',
                    "{$actorName} allocated {$studentName} to Room {$room->room_number}",
                    $student->student_id,
                    $room->room_id
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('allocations.index')->with('success', 'Student allocated to room successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $allocation = Allocation::with('room')->findOrFail($id);
        $request->validate(['status' => ['required', 'in:active,inactive']]);

        $oldStatus = $allocation->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'No changes made.');
        }

        if ($oldStatus === 'inactive' && $newStatus === 'active') {
            // Reactivation requires capacity check with locking
            try {
                DB::transaction(function () use ($request, $allocation) {
                    $room = Room::where('room_id', $allocation->room_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Check if the student's user is still active
                    $student = $allocation->student()->with('user')->first();
                    if ($student && $student->user && $student->user->status !== 'active') {
                        throw new \Exception('Cannot reactivate — the student\'s account is inactive.');
                    }

                    // Check if the room is still active
                    if ($room->status !== 'active') {
                        throw new \Exception('Cannot reactivate — the assigned room is inactive.');
                    }

                    // Check capacity dynamically
                    $activeAllocations = $room->activeAllocations()->count();
                    if ($activeAllocations >= $room->capacity) {
                        throw new \Exception('Cannot reactivate — this room is already full. No more students can be assigned.');
                    }

                    $allocation->update(['status' => 'active']);
                });
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            // Deactivation — no capacity check needed
            $allocation->update(['status' => $newStatus]);

            // Log the deactivation activity
            $actor = $request->user();
            $student = $allocation->student()->with('user')->first();
            $studentName = $student && $student->user ? $student->user->name : 'Unknown';
            ActivityLog::log(
                $actor,
                'allocation_deactivated',
                "{$actor->name} deactivated the room allocation of {$studentName}",
                $student?->student_id,
                $allocation->room->room_id
            );
        }

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->route('allocations.index')->with('success', "Allocation {$statusText} successfully.");
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Only administrators can delete allocations.');
        }

        $allocation = Allocation::with('room')->findOrFail($id);
        $allocation->delete();

        return redirect()->route('allocations.index')->with('success', 'Allocation deleted successfully.');
    }
}
