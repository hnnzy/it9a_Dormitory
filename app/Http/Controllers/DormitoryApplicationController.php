<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DormitoryApplication;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DormitoryApplicationController extends Controller
{
    /**
     * Display a listing of applications.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = DormitoryApplication::with(['student.user', 'preferredRoom']);

        // Students can only see their own applications
        if ($user->isStudent()) {
            $student = $user->student;
            if (!$student) {
                return redirect()->route('dashboard')->with('error', 'Student record not found.');
            }
            $query->where('student_id', $student->student_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest('applied_at')->paginate(15);

        return view('applications.index', compact('applications'));
    }

    /**
     * Show the form for creating a new application.
     */
    public function create(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found. Please contact admin.');
        }

        // Check for existing pending application
        $existingPending = DormitoryApplication::where('student_id', $student->student_id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->route('applications.index')
                ->with('error', 'You already have a pending application. Please wait for it to be reviewed.');
        }

        // Only show rooms that have open slots (accounting for both active allocations AND pending applications)
        $rooms = Room::hasOpenSlots()->get();

        return view('applications.create', compact('rooms'));
    }

    /**
     * Store a newly created application.
     *
     * Uses DB transaction + row locking to prevent race conditions
     * where multiple students apply for the last available slot.
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found.');
        }

        $request->validate([
            'preferred_room_id' => ['nullable', 'exists:rooms,room_id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // If a preferred room is specified, validate availability inside a transaction
        if ($request->preferred_room_id) {
            try {
                DB::transaction(function () use ($request, $student) {
                    // Lock the room row to prevent concurrent applications
                    $room = Room::where('room_id', $request->preferred_room_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$room) {
                        throw new \Exception('The selected room no longer exists.');
                    }

                    if ($room->status !== 'active') {
                        throw new \Exception('The selected room is currently inactive.');
                    }

                    // Calculate available slots with the lock held
                    $availableSlots = $room->calculateAvailableSlots();

                    if ($availableSlots <= 0) {
                        throw new \Exception('The selected room is no longer available. All slots are taken (including pending applications).');
                    }

                    DormitoryApplication::create([
                        'student_id' => $student->student_id,
                        'preferred_room_id' => $request->preferred_room_id,
                        'status' => 'pending',
                        'remarks' => $request->remarks,
                        'applied_at' => now(),
                    ]);
                });
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        } else {
            // No preferred room — just create the application
            DormitoryApplication::create([
                'student_id' => $student->student_id,
                'preferred_room_id' => null,
                'status' => 'pending',
                'remarks' => $request->remarks,
                'applied_at' => now(),
            ]);
        }

        return redirect()->route('applications.index')
            ->with('success', 'Application submitted successfully!');
    }

    /**
     * Display the specified application.
     */
    public function show(Request $request, $id)
    {
        $application = DormitoryApplication::with(['student.user', 'preferredRoom'])->findOrFail($id);

        // Students can only view their own applications
        if ($request->user()->isStudent()) {
            $student = $request->user()->student;
            if (!$student || $application->student_id !== $student->student_id) {
                abort(403, 'Unauthorized.');
            }
        }

        return view('applications.show', compact('application'));
    }

    /**
     * Update application status (approve/reject) - Dorm Manager & Admin only.
     *
     * Uses DB transaction + row locking on approval to prevent
     * race conditions where two managers approve beyond room capacity.
     */
    public function updateStatus(Request $request, $id)
    {
        $application = DormitoryApplication::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // When approving, validate room capacity inside a transaction
        if ($request->status === 'approved' && $application->preferred_room_id) {
            try {
                DB::transaction(function () use ($request, $application) {
                    // Lock the room row to prevent concurrent approvals
                    $room = Room::where('room_id', $application->preferred_room_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$room) {
                        throw new \Exception('The preferred room no longer exists. Please reject this application or ask the student to reapply.');
                    }

                    if ($room->status !== 'active') {
                        throw new \Exception('Cannot approve — the preferred room is currently inactive.');
                    }

                    // When approving, count active allocations against capacity.
                    // The current pending application is about to become approved,
                    // so we check if there's room for one more active allocation.
                    $activeAllocations = $room->activeAllocations()->count();

                    if ($activeAllocations >= $room->capacity) {
                        throw new \Exception('This room is already full. No more students can be assigned. Please reject this application or ask the student to choose a different room.');
                    }

                    $application->update([
                        'status' => $request->status,
                        'remarks' => $request->remarks ?? $application->remarks,
                        'reviewed_at' => now(),
                    ]);
                });
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            // Rejection or no preferred room — no locking needed
            $application->update([
                'status' => $request->status,
                'remarks' => $request->remarks ?? $application->remarks,
                'reviewed_at' => now(),
            ]);
        }

        $statusText = $request->status === 'approved' ? 'approved' : 'rejected';

        // Log accepted applications
        if ($request->status === 'approved') {
            $application->load('student.user');
            $actor = $request->user();
            $studentName = $application->student?->user?->name ?? 'Unknown';
            ActivityLog::log(
                $actor,
                'application_accepted',
                "{$actor->name} accepted the dorm application of {$studentName}",
                $application->student_id,
                $application->preferred_room_id
            );
        }

        return redirect()->route('applications.index')
            ->with('success', "Application has been {$statusText}.");
    }
}
