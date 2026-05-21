<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $primaryKey = 'room_id';

    protected $fillable = [
        'room_number',
        'capacity',
        'status',
    ];

    /**
     * Attributes that should be appended to the model's array/JSON form.
     */
    protected $appends = ['available_slots'];

    /**
     * Get the allocations for this room.
     */
    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'room_id', 'room_id');
    }

    /**
     * Get active allocations for this room.
     */
    public function activeAllocations()
    {
        return $this->hasMany(Allocation::class, 'room_id', 'room_id')->where('status', 'active');
    }

    /**
     * Get pending dormitory applications for this room.
     */
    public function pendingApplications()
    {
        return $this->hasMany(DormitoryApplication::class, 'preferred_room_id', 'room_id')->where('status', 'pending');
    }

    /**
     * Get the dormitory applications preferring this room.
     */
    public function applications()
    {
        return $this->hasMany(DormitoryApplication::class, 'preferred_room_id', 'room_id');
    }

    /**
     * Dynamically compute available slots.
     *
     * available_slots = capacity - (active_allocations + pending_applications)
     *
     * This replaces the old database column and ensures the value
     * is always accurate, even under concurrent access.
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->capacity
            - $this->activeAllocations()->count()
            - $this->pendingApplications()->count()
        );
    }

    /**
     * Calculate available slots inside a transaction (uses already-loaded counts).
     * Call this only when you have locked the room row with lockForUpdate().
     */
    public function calculateAvailableSlots(): int
    {
        $active = $this->activeAllocations()->count();
        $pending = $this->pendingApplications()->count();

        return max(0, $this->capacity - $active - $pending);
    }

    /**
     * Check if the room has available slots.
     */
    public function hasAvailableSlots(): bool
    {
        return $this->available_slots > 0;
    }

    /**
     * Check if the room is full.
     */
    public function isFull(): bool
    {
        return $this->available_slots <= 0;
    }

    /**
     * Scope: only rooms that have open slots (capacity > active allocations + pending applications).
     * Uses subqueries for efficient DB-level filtering.
     */
    public function scopeHasOpenSlots($query)
    {
        return $query->where('status', 'active')
            ->whereRaw('capacity > (
                (SELECT COUNT(*) FROM allocations WHERE allocations.room_id = rooms.room_id AND allocations.status = ?)
                + (SELECT COUNT(*) FROM dormitory_applications WHERE dormitory_applications.preferred_room_id = rooms.room_id AND dormitory_applications.status = ?)
            )', ['active', 'pending']);
    }
}
