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
        Schema::create('conversations', function (Blueprint $table) {
            $table->bigIncrements('conversation_id');
            $table->uuid('conversation_token')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint (if users table exists)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
