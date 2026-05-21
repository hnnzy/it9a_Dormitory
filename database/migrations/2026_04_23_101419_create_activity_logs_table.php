<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_role', 20);
            $table->string('action_type', 50);
            $table->text('description');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->timestamps();

            $table->foreign('actor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('set null');
            $table->foreign('room_id')->references('room_id')->on('rooms')->onDelete('set null');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
