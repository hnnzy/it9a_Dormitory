<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Room;
use App\Models\DormitoryApplication;
use App\Models\Allocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@dorm.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create Dorm Manager
        User::create([
            'name' => 'Dorm Manager',
            'email' => 'manager@dorm.com',
            'password' => Hash::make('password'),
            'role' => 'dorm_manager',
            'status' => 'active',
        ]);

        // Create Students
        $students = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan@student.com', 'number' => 'STU-2026-001', 'course' => 'BS Computer Science', 'year' => '3rd Year', 'contact' => '09171234567'],
            ['name' => 'Maria Santos', 'email' => 'maria@student.com', 'number' => 'STU-2026-002', 'course' => 'BS Information Technology', 'year' => '2nd Year', 'contact' => '09181234567'],
            ['name' => 'Pedro Reyes', 'email' => 'pedro@student.com', 'number' => 'STU-2026-003', 'course' => 'BS Computer Engineering', 'year' => '4th Year', 'contact' => '09191234567'],
            ['name' => 'Ana Garcia', 'email' => 'ana@student.com', 'number' => 'STU-2026-004', 'course' => 'BS Computer Science', 'year' => '1st Year', 'contact' => '09201234567'],
            ['name' => 'Carlos Tan', 'email' => 'carlos@student.com', 'number' => 'STU-2026-005', 'course' => 'BS Information Technology', 'year' => '3rd Year', 'contact' => '09211234567'],
        ];

        foreach ($students as $s) {
            $user = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_number' => $s['number'],
                'course' => $s['course'],
                'year_level' => $s['year'],
                'contact_number' => $s['contact'],
            ]);
        }

        // Create Rooms
        $rooms = [
            ['number' => 'RM-101', 'capacity' => 4],
            ['number' => 'RM-102', 'capacity' => 4],
            ['number' => 'RM-201', 'capacity' => 2],
            ['number' => 'RM-202', 'capacity' => 2],
            ['number' => 'RM-301', 'capacity' => 6],
        ];

        foreach ($rooms as $r) {
            Room::create([
                'room_number' => $r['number'],
                'capacity' => $r['capacity'],
                'status' => 'active',
            ]);
        }

        // Create sample applications
        $student1 = Student::where('student_number', 'STU-2026-001')->first();
        $student2 = Student::where('student_number', 'STU-2026-002')->first();
        $room1 = Room::where('room_number', 'RM-101')->first();

        DormitoryApplication::create([
            'student_id' => $student1->student_id,
            'preferred_room_id' => $room1->room_id,
            'status' => 'approved',
            'remarks' => 'Approved for Room RM-101',
            'applied_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(3),
        ]);

        DormitoryApplication::create([
            'student_id' => $student2->student_id,
            'preferred_room_id' => $room1->room_id,
            'status' => 'pending',
            'remarks' => 'Requesting accommodation for the semester',
            'applied_at' => now()->subDay(),
        ]);

        // Create sample allocation
        Allocation::create([
            'student_id' => $student1->student_id,
            'room_id' => $room1->room_id,
            'status' => 'active',
            'date_assigned' => now()->subDays(3)->toDateString(),
        ]);

    }
}
