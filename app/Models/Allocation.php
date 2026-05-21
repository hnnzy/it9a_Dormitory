<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $primaryKey = 'allocation_id';

    protected $fillable = [
        'student_id',
        'room_id',
        'status',
        'date_assigned',
    ];

    protected function casts(): array
    {
        return [
            'date_assigned' => 'date',
        ];
    }

    /**
     * Get the student for this allocation.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the room for this allocation.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * Check if the allocation is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
