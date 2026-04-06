<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица команд
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Таблица членов команды
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Таблица прогресса команды по этапам
        Schema::create('team_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('stage_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('stage_progress')->default(0);
            $table->integer('user_progress')->default(0);
            $table->timestamps();
            $table->unique(['team_id', 'stage_id', 'user_id']);
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('stage_id')->references('id')->on('quest_stages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Добавляем role_user для админов
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_progress');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
