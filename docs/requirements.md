# Functional Requirements

## Introduction

This document describes the functional requirements of the **Fantasy Esport League** application.
The system allows users to create or join fantasy leagues based on real esports competitions and manage their own fantasy teams.

Users can participate in leagues, build a roster of professional players through an auction system, manage their lineup, and compete against other participants based on the real-world performances of esports players.

The platform supports both **public leagues**, accessible to all users, and **private leagues**, accessible only through invitation. The system automatically calculates fantasy scores based on statistics collected from real matches.

The goal of the application is to provide an interactive and competitive environment where users can follow esports competitions while engaging in strategic team management.

## Actors

### User

A registered user of the platform. The user can create or join leagues, manage a fantasy team, participate in player auctions, set lineups, and view rankings and statistics.

### System

The automated backend responsible for managing game logic and enforcing rules. The system handles tasks such as generating invitations, locking lineups after deadlines, updating fantasy scores based on real match statistics, and controlling access rights.

## Features

| ID  | Feature                     | Description                                                           | Main Actor    |
| --- | --------------------------- | --------------------------------------------------------------------- | ------------- |
| F01 | Register                    | Allow a user to create an account                                     | User          |
| F02 | Log in                      | Allow an authenticated user to access the system                      | User          |
| F03 | Create public game          | Create a game that is publicly visible and accessible                 | User          |
| F04 | Join public game            | Allow a user to join an existing public game                          | User          |
| F05 | Create private game         | Create a game accessible only through invitation                      | User          |
| F06 | Generate private invitation | Generate an access code or link for a private game                    | System / User |
| F07 | Join private game           | Allow a user to join a private game via invitation                    | User          |
| F08 | Create fantasy team         | Create the fantasy team associated with the user within a game        | User / System |
| F09 | Participate in auctions     | Allow a user to bid on players                                        | User          |
| F10 | Manage roster               | Add, remove, and organize players within the fantasy team             | User          |
| F11 | Set lineup                  | Select the active players for a given period                          | User          |
| F12 | Lock lineup                 | Prevent lineup modifications after a defined deadline                 | System        |
| F13 | View leaderboard            | Display the ranking of the game                                       | User          |
| F14 | View statistics             | Display real and fantasy performance statistics of players            | User          |
| F15 | Update scores               | Automatically calculate fantasy scores based on real match statistics | System        |
| F16 | Manage access rights        | Control permissions depending on the game type                        | System        |
