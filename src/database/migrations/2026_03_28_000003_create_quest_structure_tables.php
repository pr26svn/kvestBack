<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_stages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('order')->default(1);
            $table->string('stage_type')->nullable();
            $table->date('deadline_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('quest_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_stage_id')->constrained('quest_stages')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('task_type')->nullable();
            $table->string('difficulty')->nullable();
            $table->unsignedSmallInteger('order')->default(1);
            $table->decimal('max_score', 5, 2)->unsigned()->default(0);
            $table->json('payload')->nullable();
            $table->boolean('required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_tasks');
        Schema::dropIfExists('quest_stages');
    }
};
