<?php

use App\Http\Controllers\Web\AuctionController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BidController;
use App\Http\Controllers\Web\CompetitionController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FantasyLeagueController;
use App\Http\Controllers\Web\FantasyTeamController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\LineupController;
use App\Http\Controllers\Web\MatchController;
use App\Http\Controllers\Web\RosterController;
use App\Http\Controllers\Web\WeekController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CompetitionController::class, 'landing'])->name('landing');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'loginForm')->name('auth.loginForm');
    Route::post('/login', 'login')->name('auth.login');
    Route::get('/register', 'registerForm')->name('auth.registerForm');
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/logout', 'logout')->name('auth.logout');
});

Route::controller(CompetitionController::class)->group(function () {
    Route::get('/competitions', 'index')->name('competitions.index');
    Route::get('/competitions/{competition}', 'show')->name('competitions.show');
});

Route::get('/weeks/{week}', [WeekController::class, 'show'])->name('weeks.show');
Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
Route::get('/fantasy-leagues', [FantasyLeagueController::class, 'index'])->name('leagues.index');

Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/fantasy-leagues/create', [FantasyLeagueController::class, 'create'])->name('leagues.create');
    Route::post('/fantasy-leagues', [FantasyLeagueController::class, 'store'])->name('leagues.store');
    Route::get('/fantasy-leagues/{fantasyLeague}', [FantasyLeagueController::class, 'show'])->name('leagues.show');
    Route::post('/fantasy-leagues/{fantasyLeague}/join', [FantasyLeagueController::class, 'join'])->name('leagues.join');
    Route::get('/private-leagues/join', [FantasyLeagueController::class, 'joinPrivateForm'])->name('leagues.joinPrivateForm');
    Route::post('/private-leagues/join', [FantasyLeagueController::class, 'joinPrivate'])->name('leagues.joinPrivate');

    Route::post('/fantasy-leagues/{fantasyLeague}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    Route::get('/fantasy-teams/{team}', [FantasyTeamController::class, 'show'])->name('teams.show');
    Route::get('/fantasy-teams/{team}/weeks/{week}/lineup', [LineupController::class, 'edit'])->name('lineups.edit');
    Route::post('/fantasy-teams/{team}/lineups', [LineupController::class, 'store'])->name('lineups.store');
    Route::delete('/fantasy-teams/{team}/roster/{rosterSlot}', [RosterController::class, 'destroy'])->name('roster.destroy');

    Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');
    Route::post('/auctions/{auction}/bids', [BidController::class, 'store'])->name('bids.store');
    Route::post('/auctions/{auction}/close', [AuctionController::class, 'close'])->name('auctions.close');
});
