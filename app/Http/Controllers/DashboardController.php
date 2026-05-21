<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Room;
use App\Models\DormitoryApplication;
use App\Models\Allocation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $data = [
            'user' => $user,
        ];

        if ($user->isAdmin()) {
            $data['totalUsers'] = User::count();
            $data['totalStudents'] = Student::count();
            $data['totalRooms'] = Room::count();
            $data['totalAllocations'] = Allocation::where('status', 'active')->count();
            $data['pendingApplications'] = DormitoryApplication::where('status', 'pending')->count();
            $data['recentApplications'] = DormitoryApplication::with(['student.user', 'preferredRoom'])
                ->latest('applied_at')->take(5)->get();
            $data['recentAllocations'] = Allocation::with(['student.user', 'room'])
                ->latest('date_assigned')->take(5)->get();
            $data['activityLogs'] = ActivityLog::with(['actor', 'student.user', 'room'])
                ->latest()->take(20)->get();
        } elseif ($user->isDormManager()) {
            $data['totalRooms'] = Room::count();
            $data['availableRooms'] = Room::hasOpenSlots()->count();
            $data['totalAllocations'] = Allocation::where('status', 'active')->count();
            $data['pendingApplications'] = DormitoryApplication::where('status', 'pending')->count();
            $data['recentApplications'] = DormitoryApplication::with(['student.user', 'preferredRoom'])
                ->latest('applied_at')->take(5)->get();
            $data['recentAllocations'] = Allocation::with(['student.user', 'room'])
                ->latest('date_assigned')->take(5)->get();
        } else {
            // Student
            $student = $user->student;
            $data['student'] = $student;

            if ($student) {
                $data['myApplications'] = DormitoryApplication::where('student_id', $student->student_id)
                    ->with('preferredRoom')->latest('applied_at')->get();
                $data['myAllocation'] = Allocation::where('student_id', $student->student_id)
                    ->where('status', 'active')->with('room')->first();
            }
        }

        return view('dashboard', $data);
    }
}
