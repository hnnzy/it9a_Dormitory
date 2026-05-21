<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DormitoryApplication extends Model
{
    protected $primaryKey = 'application_id';

    protected $table = 'dormitory_applications';

    protected $fillable = [
        'student_id',
        'preferred_room_id',
        'status',
        'remarks',
        'applied_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the student that owns the application.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the preferred room.
     */
    public function preferredRoom()
    {
        return $this->belongsTo(Room::class, 'preferred_room_id', 'room_id');
    }

    /**
     * Check if the application is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
