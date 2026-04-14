<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_stage_id')->constrained('quest_stages')->cascadeOnDelete();
            $table->longText('html_content')->nullable();
            $table->string('content_type')->default('html'); // html, iframe, component
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('quest_stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_contents');
    }
};
