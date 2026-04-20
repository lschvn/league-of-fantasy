<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\FantasyLeagueController;
use App\Http\Controllers\FantasyTeamController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LineupController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{competition}', [CompetitionController::class, 'show']);
Route::get('/competitions/{competition}/weeks', [CompetitionController::class, 'weeks']);
Route::get('/weeks/{week}/matches', [CompetitionController::class, 'matches']);
Route::get('/matches/{match}', [CompetitionController::class, 'showMatch']);
Route::get('/matches/{match}/player-stats', [CompetitionController::class, 'playerStats']);

Route::get('/fantasy-leagues', [FantasyLeagueController::class, 'index']);
Route::get('/fantasy-leagues/{fantasyLeague}', [FantasyLeagueController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/fantasy-leagues', [FantasyLeagueController::class, 'store']);
    Route::post('/fantasy-leagues/{fantasyLeague}/join', [FantasyLeagueController::class, 'join']);
    Route::get('/fantasy-leagues/{fantasyLeague}/members', [FantasyLeagueController::class, 'members']);
    Route::get('/fantasy-leagues/{fantasyLeague}/weeks/{week}/standings', [FantasyLeagueController::class, 'standings']);

    Route::post('/fantasy-leagues/{fantasyLeague}/invitations', [InvitationController::class, 'store']);
    Route::post('/private-leagues/join', [InvitationController::class, 'join']);
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'revoke']);

    Route::get('/fantasy-teams/{team}', [FantasyTeamController::class, 'show']);
    Route::get('/fantasy-teams/{team}/roster', [FantasyTeamController::class, 'roster']);
    Route::delete('/fantasy-teams/{team}/roster/{rosterSlot}', [FantasyTeamController::class, 'release']);
    Route::get('/fantasy-teams/{team}/weeks/{week}/lineup', [FantasyTeamController::class, 'showLineup']);
    Route::post('/fantasy-teams/{team}/lineups', [LineupController::class, 'submit']);

    Route::get('/auctions/{auction}', [AuctionController::class, 'show']);
    Route::get('/auctions/{auction}/bids', [AuctionController::class, 'bids']);
    Route::post('/auctions/{auction}/bids', [BidController::class, 'store']);
    Route::post('/auctions/{auction}/close', [AuctionController::class, 'close']);
});
