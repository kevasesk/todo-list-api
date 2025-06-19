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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->enum('status', ['todo', 'done'])->default('todo')->index();
            $table->integer('priority')->default(5)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('completed_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
