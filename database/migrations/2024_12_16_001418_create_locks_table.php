<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->id();
            $table->string('lockable_type'); // e.g. "App\Models\User"
            $table->unsignedBigInteger('lockable_id'); // e.g. user_id, attendance_id, etc.
            $table->unsignedBigInteger('locked_by'); // user_id of the person who locked it
            $table->timestamp('lock_expires_at')->nullable();
            $table->timestamps();

            // Optional index for quick lookups
            $table->index(['lockable_type', 'lockable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locks');
    }
};
