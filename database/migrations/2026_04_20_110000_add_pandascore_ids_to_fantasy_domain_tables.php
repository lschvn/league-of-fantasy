<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->unsignedBigInteger('pandascore_id')->nullable()->unique()->after('id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('pandascore_id')->nullable()->unique()->after('id');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('pandascore_id')->nullable()->unique()->after('id');
        });

        Schema::table('game_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('pandascore_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->dropUnique('game_matches_pandascore_id_unique');
            $table->dropColumn('pandascore_id');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique('players_pandascore_id_unique');
            $table->dropColumn('pandascore_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique('teams_pandascore_id_unique');
            $table->dropColumn('pandascore_id');
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropUnique('competitions_pandascore_id_unique');
            $table->dropColumn('pandascore_id');
        });
    }
};
