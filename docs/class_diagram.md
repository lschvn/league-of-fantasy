# Domain Class Diagram

```mermaid
classDiagram

class Competition {
    id
    name
    region
    season
    getTeams()
    getWeeks()
}

class Week {
    id
    number
    start_at
    end_at
    lineup_lock_at
    getMatches()
}

class Match {
    id
    status
    started_at
    ended_at
    getTeams()
    getPlayerStats()
}

class Team {
    id
    name
    tag
    logo_url
    getPlayers()
}

class Player {
    id
    nickname
    role
    status
    getTeam()
}

class PlayerStat {
    id
    kills
    deaths
    assists
    fantasy_points
    getFantasyPoints()
}

class User {
    id
    name
    email
    register()
    logIn()
}

class FantasyLeague {
    id
    name
    visibility
    status
    max_participants
    budget_cap
    join_deadline
    scoring_rule_version
    addMember()
    createInvitation()
    openAuction()
}

class Membership {
    id
    role
    status
    joined_at
    canManageLeague()
}

class Invitation {
    id
    code
    expires_at
    max_uses
    used_count
    revoked_at
    validate()
    revoke()
}

class FantasyTeam {
    id
    name
    budget_remaining
    getRoster()
    createLineup()
}

class RosterSlot {
    id
    acquisition_cost
    acquired_at
    released_at
    status
}

class Auction {
    id
    status
    start_at
    end_at
    close()
}

class Bid {
    id
    amount
    status
    placed_at
    win()
}

class Lineup {
    id
    status
    submitted_at
    locked_at
    submit()
    lock()
}

class LineupSlot {
    id
    position
    is_captain
}

class FantasyTeamScore {
    id
    points
    rank
    calculated_at
}

Competition "1" --> "0..*" Team : contains
Competition "1" --> "0..*" Week : schedules
Competition "1" --> "0..*" FantasyLeague : supports

Week "1" --> "0..*" Match : includes
Week "1" --> "0..*" Lineup : targets
Week "1" --> "0..*" FantasyTeamScore : scores

Match "1" --> "2..*" Team : opposes
Match "1" --> "0..*" PlayerStat : generates

Team "1" --> "0..*" Player : has

Player "1" --> "0..*" PlayerStat : records
Player "1" --> "0..*" RosterSlot : selected_in
Player "1" --> "0..*" Bid : targeted_by

User "1" --> "0..*" FantasyLeague : creates
User "1" --> "0..*" Membership : joins

FantasyLeague "1" --> "0..*" Membership : has
FantasyLeague "1" --> "0..*" Invitation : secures
FantasyLeague "1" --> "0..*" Auction : runs

Membership "1" --> "1" FantasyTeam : owns

FantasyTeam "1" --> "0..*" RosterSlot : contains
FantasyTeam "1" --> "0..*" Bid : places
FantasyTeam "1" --> "0..*" Lineup : submits
FantasyTeam "1" --> "0..*" FantasyTeamScore : earns

Auction "1" --> "0..*" Bid : collects

Lineup "1" --> "1..*" LineupSlot : contains
RosterSlot "1" --> "0..*" LineupSlot : activates
```

## Notes

- `Membership` is the canonical participation object. It replaces the ambiguous direct participation relation between `User` and `FantasyLeague`.
- `Invitation`, `Lineup`, and `FantasyTeamScore` are explicit because the functional requirements depend on them.
- `RosterSlot` models roster ownership separately from `LineupSlot`, which models weekly activation.
