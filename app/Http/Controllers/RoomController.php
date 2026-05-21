<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms.
     */
    public function index(Request $request)
    {
        $query = Room::query();

        if ($request->filled('search')) {
            $query->where('room_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                // Dynamically filter rooms with open slots (accounts for pending apps)
                $query->whereRaw('capacity > (
                    (SELECT COUNT(*) FROM allocations WHERE allocations.room_id = rooms.room_id AND allocations.status = ?)
                    + (SELECT COUNT(*) FROM dormitory_applications WHERE dormitory_applications.preferred_room_id = rooms.room_id AND dormitory_applications.status = ?)
                )', ['active', 'pending']);
            } elseif ($request->availability === 'full') {
                // Dynamically filter rooms with no open slots
                $query->whereRaw('capacity <= (
                    (SELECT COUNT(*) FROM allocations WHERE allocations.room_id = rooms.room_id AND allocations.status = ?)
                    + (SELECT COUNT(*) FROM dormitory_applications WHERE dormitory_applications.preferred_room_id = rooms.room_id AND dormitory_applications.status = ?)
                )', ['active', 'pending']);
            }
        }

        $rooms = $query->latest()->paginate(15);

        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('rooms.create');
    }

    /**
     * Store a newly created room.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_number' => ['required', 'string', 'unique:rooms,room_number', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        Room::create([
            'room_number' => $request->room_number,
            'capacity' => $request->capacity,
            'status' => 'active',
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room.
     */
    public function show($id)
    {
        $room = Room::with(['activeAllocations.student.user', 'pendingApplications.student.user'])->findOrFail($id);
        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit($id)
    {
        $room = Room::findOrFail($id);
        return view('rooms.edit', compact('room'));
    }

    /**
     * Update the specified room.
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'room_number' => ['required', 'string', 'unique:rooms,room_number,' . $id . ',room_id', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $activeOccupants = $room->activeAllocations()->count();
        $pendingApps = $room->pendingApplications()->count();
        $totalReserved = $activeOccupants + $pendingApps;
        $newCapacity = $request->capacity;

        // Prevent setting room to inactive if it has active allocations
        if ($request->status === 'inactive' && $activeOccupants > 0) {
            return back()->with('error', "Cannot set room to inactive — it still has {$activeOccupants} active allocation(s). Remove or deactivate them first.");
        }

        if ($newCapacity < $activeOccupants) {
            return back()->with('error', "Cannot reduce capacity below current occupants ({$activeOccupants}).");
        }

        if ($newCapacity < $totalReserved) {
            return back()->with('error', "Cannot reduce capacity below current occupants + pending applications ({$totalReserved}). Reject pending applications first.");
        }

        $room->update([
            'room_number' => $request->room_number,
            'capacity' => $newCapacity,
            'status' => $request->status,
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified room (Admin only).
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Only administrators can delete rooms.');
        }

        $room = Room::findOrFail($id);

        if ($room->activeAllocations()->count() > 0) {
            return back()->with('error', 'Cannot delete a room with active allocations.');
        }

        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
