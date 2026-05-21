<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_role',
        'action_type',
        'description',
        'student_id',
        'room_id',
    ];

    /**
     * Get the user who performed the action.
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the student involved (if any).
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the room involved (if any).
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * Log an activity.
     */
    public static function log(User $actor, string $actionType, string $description, ?int $studentId = null, ?int $roomId = null): self
    {
        return static::create([
            'actor_id' => $actor->id,
            'actor_role' => $actor->role,
            'action_type' => $actionType,
            'description' => $description,
            'student_id' => $studentId,
            'room_id' => $roomId,
        ]);
    }
}
