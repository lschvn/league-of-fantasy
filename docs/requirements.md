# Functional Requirements

## Introduction

This document defines the functional scope of the **Fantasy Esport League** application.
The product allows users to create and join fantasy leagues built on top of a real-world
esport competition, draft professional players through auctions, submit weekly lineups,
and compete through automatically calculated fantasy scores.

The documentation uses one stable distinction:

- `Competition`: the real esport competition that provides teams, players, weeks, matches, and statistics.
- `Fantasy League`: the user-created fantasy contest linked to one competition.
- `Membership`: a user's participation in a fantasy league.
- `Fantasy Team`: the roster owned by one membership.
- `Lineup`: the subset of rostered players submitted for one week.

## Actors

### Visitor

An unauthenticated person who can create an account and access the log-in screen.

### Registered User

An authenticated user who can create or join fantasy leagues, manage a fantasy team,
bid in auctions, submit lineups, and consult standings and statistics.

### League Owner

A registered user with owner rights in a fantasy league. The owner creates the league,
configures access, and manages invitations for private leagues.

### System

The automated backend responsible for enforcing deadlines, validating access rules,
locking lineups, processing auction outcomes, importing match statistics, and
calculating fantasy scores and rankings.

## Scope Assumptions

- One fantasy league is linked to exactly one competition.
- A user can have at most one active membership in a given fantasy league.
- Each membership owns exactly one fantasy team.
- Private leagues are accessed through invitations.
- Lineups are submitted per week and locked at a deadline.
- Leaderboards are based on persisted fantasy score snapshots.

## Features

| ID  | Feature                          | Description                                                                      | Main Actor      |
| --- | -------------------------------- | -------------------------------------------------------------------------------- | --------------- |
| F01 | Register                         | Allow a visitor to create an account                                             | Visitor         |
| F02 | Log in                           | Allow a registered user to authenticate and open a session                       | Registered User |
| F03 | Create public fantasy league     | Create a fantasy league visible in the public catalog                            | Registered User |
| F04 | Join public fantasy league       | Join a public fantasy league before the join deadline                            | Registered User |
| F05 | Create private fantasy league    | Create a fantasy league accessible only through invitation                       | League Owner    |
| F06 | Manage private invitations       | Generate, revoke, and validate invitation codes or links                         | League Owner    |
| F07 | Join private fantasy league      | Join a private fantasy league with a valid invitation                            | Registered User |
| F08 | Participate in auctions          | Bid on professional players during an open auction phase                         | Registered User |
| F09 | Manage roster                    | Consult and maintain the fantasy team roster acquired through auctions           | Registered User |
| F10 | Submit lineup                    | Select the active roster for one week before the lock deadline                   | Registered User |
| F11 | Lock lineup                      | Prevent lineup changes after the weekly deadline                                 | System          |
| F12 | Calculate fantasy scores         | Compute fantasy points from real match statistics                                | System          |
| F13 | View leaderboard                 | Display the ranking of fantasy teams in a league                                 | Registered User |
| F14 | View statistics                  | Display real statistics and fantasy scoring details for players and teams        | Registered User |
| F15 | Manage membership and access     | Control visibility, invitations, owner rights, and join permissions              | System / Owner  |
| F16 | Enforce league rules and timing  | Apply participation limits, budget rules, deadlines, and scoring rule versions   | System          |

## Business Rules

- `BR01` A fantasy league must have exactly one owner.
- `BR02` A user can join a fantasy league only once.
- `BR03` A private fantasy league must not appear in the public catalog.
- `BR04` Invitation codes must be unique within the application.
- `BR05` An invitation can be expired, revoked, or exhausted after a maximum number of uses.
- `BR06` A fantasy team must belong to exactly one membership.
- `BR07` A player cannot be rostered by more than one fantasy team in the same fantasy league at the same time.
- `BR08` Bids must respect the remaining budget of the fantasy team.
- `BR09` A lineup must contain only players currently rostered by the submitting fantasy team.
- `BR10` A fantasy team can submit at most one lineup per week.
- `BR11` A locked lineup becomes read-only.
- `BR12` Weekly standings must be calculated from persisted weekly fantasy team scores.
- `BR13` Score calculation must use the scoring rule version configured on the fantasy league.

## Traceability Summary

| Area                     | Main Features         | Main Domain Objects                                  |
| ------------------------ | --------------------- | ---------------------------------------------------- |
| Authentication           | F01, F02              | User                                                 |
| League creation/joining  | F03, F04, F05, F06, F07, F15 | FantasyLeague, Membership, Invitation         |
| Roster acquisition       | F08, F09, F16         | Auction, Bid, FantasyTeam, RosterSlot                |
| Weekly gameplay          | F10, F11, F12, F16    | Week, Lineup, LineupSlot, PlayerStat, FantasyTeamScore |
| Read models              | F13, F14              | FantasyTeamScore, PlayerStat, Competition, FantasyTeam |
