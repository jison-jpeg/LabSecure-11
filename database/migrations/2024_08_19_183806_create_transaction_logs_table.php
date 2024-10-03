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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');  // e.g., 'create', 'update', 'delete', 'in', 'out'
            $table->string('model');   // e.g., 'Laboratory', 'Attendance'
            $table->unsignedBigInteger('model_id'); // ID of the model being acted upon
            $table->text('details')->nullable(); // Any additional details (e.g., old values, new values)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
