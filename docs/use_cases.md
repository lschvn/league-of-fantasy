# Use Cases

## Introduction

This document lists the main use cases of the **Fantasy Esport League** application.
The wording matches the functional requirements and the UML/data model:

- `Competition` is the real esport competition.
- `Fantasy League` is the user-created contest.
- `Membership` links a user to a fantasy league.
- `Fantasy Team` is the roster owned by a membership.

## Actors

### Visitor

Creates an account and accesses the authentication flow.

### Registered User

Creates or joins fantasy leagues, participates in auctions, submits lineups,
and consults scores and rankings.

### League Owner

Creates a fantasy league and manages private access.

### System

Locks lineups, processes weekly scoring, updates standings, and enforces access rules.

## Use Case Coverage

| ID | Use Case Name | Main Actor | Objective | Covered Features | File |
| --- | --- | --- | --- | --- | --- |
| UC01 | Register an account | Visitor | Create a new user account | F01 | [docs/uc/01.md](./uc/01.md) |
| UC02 | Log in | Registered User | Authenticate and open a session | F02 | [docs/uc/02.md](./uc/02.md) |
| UC03 | Create a public fantasy league | Registered User | Create a public fantasy league linked to a competition | F03, F15, F16 | [docs/uc/03.md](./uc/03.md) |
| UC04 | Join a public fantasy league | Registered User | Join an open public fantasy league and receive a fantasy team | F04, F15, F16 | [docs/uc/04.md](./uc/04.md) |
| UC05 | Create a private fantasy league | League Owner | Create a private fantasy league and generate invitations | F05, F06, F15, F16 | [docs/uc/05.md](./uc/05.md) |
| UC06 | Join a private fantasy league | Registered User | Join a private fantasy league with a valid invitation | F07, F15, F16 | [docs/uc/06.md](./uc/06.md) |
| UC07 | Participate in auction and manage roster | Registered User | Bid on players and maintain the roster | F08, F09, F16 | [docs/uc/07.md](./uc/07.md) |
| UC08 | Submit lineup and consult standings | Registered User | Submit the weekly lineup, view rankings, and inspect statistics | F10, F13, F14 | [docs/uc/08.md](./uc/08.md) |
| UC09 | Process the weekly fantasy cycle | System | Lock lineups, calculate scores, and update standings | F11, F12, F15, F16 | [docs/uc/09.md](./uc/09.md) |

## Coverage Notes

- All features in [docs/requirements.md](./requirements.md) are covered by at least one use case.
- Creation and join flows now distinguish public and private fantasy leagues explicitly.
- Auction, lineup, invitation, and scoring behavior are described as first-class use cases instead of being implied only in text.
