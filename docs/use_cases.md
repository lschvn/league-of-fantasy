# Use Cases

## Introduction

This document describes the main use cases of the **Fantasy Esport League** application.  
Use cases represent the interactions between users and the system to achieve specific goals within the platform.

They define how users create or join leagues, build their fantasy teams, and participate in the competition based on real esports match performances.

The purpose of this document is to clearly describe the expected behaviors of the system from the user's perspective.

## Actors

### User
A registered user of the platform who can create or join leagues, participate in auctions, manage a fantasy team, and compete with other users.

### System
The automated component responsible for enforcing the rules of the application, managing deadlines, updating fantasy scores, and controlling access to games.

---

## Use Cases

| ID   | Use Case Name                         | Main Actor | Objective                                                     | Precondition                                  | Expected Result                               |
| ---- | ------------------------------------- | ---------- | ------------------------------------------------------------- | ---------------------------------------------- | --------------------------------------------- |
| UC01 | Create a public game                  | User       | Create a game open to all users                               | Authenticated user                             | Public game created and visible               |
| UC02 | Join and play a public game           | User       | Join a public game and participate in the game                | Authenticated user, game available             | User becomes a member with an active fantasy team |
| UC03 | Create a private game                 | User       | Create an invitation-only game                                | Authenticated user                             | Private game created with an invite code/link |
| UC04 | Join and play a private game          | User       | Join a private game via invitation and participate            | Authenticated user, valid invitation           | User becomes a member with an active fantasy team |
