<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';

    protected $fillable = [
        'user_id',
        'student_number',
        'course',
        'year_level',
        'contact_number',
    ];

    /**
     * Get the user that owns the student record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the dormitory applications for the student.
     */
    public function applications()
    {
        return $this->hasMany(DormitoryApplication::class, 'student_id', 'student_id');
    }

    /**
     * Get the room allocations for the student.
     */
    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'student_id', 'student_id');
    }

    /**
     * Get the active allocation for the student.
     */
    public function activeAllocation()
    {
        return $this->hasOne(Allocation::class, 'student_id', 'student_id')->where('status', 'active');
    }
}
