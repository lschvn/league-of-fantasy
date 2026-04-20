<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region', 50);
            $table->string('season', 50);
            $table->timestamps();
        });

        Schema::create('weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamp('lineup_lock_at');
            $table->timestamps();
            $table->unique(['competition_id', 'number']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('tag', 10);
            $table->string('logo_url')->nullable();
            $table->timestamps();
            $table->unique(['competition_id', 'tag']);
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('nickname');
            $table->string('role', 10);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['team_id', 'nickname']);
        });

        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $table->string('status', 20)->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('match_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('game_matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('side', 10)->nullable();
            $table->timestamps();
            $table->unique(['match_id', 'team_id']);
        });

        Schema::create('player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('game_matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('kills')->default(0);
            $table->unsignedSmallInteger('deaths')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->decimal('fantasy_points', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['match_id', 'player_id']);
        });

        Schema::create('fantasy_leagues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('visibility', 20);
            $table->string('status', 20)->default('open');
            $table->unsignedSmallInteger('max_participants');
            $table->decimal('budget_cap', 8, 2);
            $table->timestamp('join_deadline');
            $table->string('scoring_rule_version', 50)->default('v1');
            $table->timestamps();
            $table->index(['visibility', 'status']);
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_league_id')->constrained('fantasy_leagues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->string('status', 20)->default('active');
            $table->timestamp('joined_at');
            $table->timestamps();
            $table->unique(['fantasy_league_id', 'user_id']);
        });

        Schema::create('fantasy_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->unique()->constrained('memberships')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('budget_remaining', 8, 2);
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_league_id')->constrained('fantasy_leagues')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_league_id')->constrained('fantasy_leagues')->cascadeOnDelete();
            $table->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $table->string('status', 20)->default('scheduled');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->cascadeOnDelete();
            $table->foreignId('fantasy_team_id')->constrained('fantasy_teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamp('placed_at');
            $table->timestamps();
            $table->index(['auction_id', 'status']);
        });

        Schema::create('roster_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_team_id')->constrained('fantasy_teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->decimal('acquisition_cost', 8, 2);
            $table->timestamp('acquired_at');
            $table->timestamp('released_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['fantasy_team_id', 'status']);
        });

        Schema::create('lineups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_team_id')->constrained('fantasy_teams')->cascadeOnDelete();
            $table->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['fantasy_team_id', 'week_id']);
        });

        Schema::create('lineup_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lineup_id')->constrained('lineups')->cascadeOnDelete();
            $table->foreignId('roster_slot_id')->constrained('roster_slots')->cascadeOnDelete();
            $table->string('position', 20);
            $table->boolean('is_captain')->default(false);
            $table->timestamps();
            $table->unique(['lineup_id', 'position']);
        });

        Schema::create('fantasy_team_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_team_id')->constrained('fantasy_teams')->cascadeOnDelete();
            $table->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $table->decimal('points', 8, 2);
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->unique(['fantasy_team_id', 'week_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fantasy_team_scores');
        Schema::dropIfExists('lineup_slots');
        Schema::dropIfExists('lineups');
        Schema::dropIfExists('roster_slots');
        Schema::dropIfExists('bids');
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('fantasy_teams');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('fantasy_leagues');
        Schema::dropIfExists('player_stats');
        Schema::dropIfExists('match_team');
        Schema::dropIfExists('game_matches');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('weeks');
        Schema::dropIfExists('competitions');
    }
};
