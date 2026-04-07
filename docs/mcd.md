# MCD (Conceptual Data Model)

```mermaid
classDiagram

class Competition {
    name
    region
    season
}

class Week {
    number
    start_at
    end_at
    lineup_lock_at
}

class Match {
    status
    started_at
    ended_at
}

class Team {
    name
    tag
    logo_url
}

class Player {
    nickname
    role
    status
}

class PlayerStat {
    kills
    deaths
    assists
    fantasy_points
}

class User {
    name
    email
    password_hash
    created_at
}

class FantasyLeague {
    name
    visibility
    status
    max_participants
    budget_cap
    join_deadline
    scoring_rule_version
}

class Membership {
    role
    status
    joined_at
}

class Invitation {
    code
    expires_at
    max_uses
    used_count
    revoked_at
}

class FantasyTeam {
    name
    budget_remaining
}

class RosterSlot {
    acquisition_cost
    acquired_at
    released_at
    status
}

class Auction {
    status
    start_at
    end_at
}

class Bid {
    amount
    status
    placed_at
}

class Lineup {
    status
    submitted_at
    locked_at
}

class LineupSlot {
    position
    is_captain
}

class FantasyTeamScore {
    points
    rank
    calculated_at
}

Competition "1" -- "0..*" Team : contains
Competition "1" -- "0..*" Week : organizes
Competition "1" -- "0..*" FantasyLeague : hosts

Week "1" -- "0..*" Match : includes
Week "1" -- "0..*" Lineup : receives
Week "1" -- "0..*" FantasyTeamScore : ranks

Match "1" -- "2..*" Team : opposes
PlayerStat "0..*" -- "1" Match : concerns
PlayerStat "0..*" -- "1" Player : describes

Team "1" -- "0..*" Player : has

User "1" -- "0..*" FantasyLeague : creates
User "1" -- "0..*" Membership : owns

FantasyLeague "1" -- "0..*" Membership : groups
FantasyLeague "1" -- "0..*" Invitation : authorizes
FantasyLeague "1" -- "0..*" Auction : schedules

Membership "1" -- "1" FantasyTeam : owns

FantasyTeam "1" -- "0..*" RosterSlot : contains
FantasyTeam "1" -- "0..*" Bid : places
FantasyTeam "1" -- "0..*" Lineup : submits
FantasyTeam "1" -- "0..*" FantasyTeamScore : receives

RosterSlot "0..*" -- "1" Player : assigns
Auction "1" -- "0..*" Bid : contains
Bid "0..*" -- "1" Player : targets

Lineup "1" -- "1..*" LineupSlot : contains
LineupSlot "0..*" -- "1" RosterSlot : activates
```

## Conceptual Constraints

- A fantasy league can be public or private, but not both.
- A membership is mandatory before a fantasy team exists.
- A private league is joined through invitations, not through the public catalog.
- A lineup is created for a specific week and activates rostered players only.
- Standings are derived from stored weekly fantasy team scores.
