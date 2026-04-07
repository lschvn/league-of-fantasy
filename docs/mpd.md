# MPD (Physical Data Model)

```mermaid
erDiagram
    COMPETITION {
        int id PK
        string name
        string region
        string season
    }

    WEEK {
        int id PK
        int competition_id FK
        int number
        datetime start_at
        datetime end_at
        datetime lineup_lock_at
    }

    TEAM {
        int id PK
        int competition_id FK
        string name
        string tag
        string logo_url
    }

    PLAYER {
        int id PK
        int team_id FK
        string nickname
        string role
        string status
    }

    GAME_MATCH {
        int id PK
        int week_id FK
        string status
        datetime started_at
        datetime ended_at
    }

    MATCH_TEAM {
        int id PK
        int match_id FK
        int team_id FK
        string side
    }

    PLAYER_STAT {
        int id PK
        int match_id FK
        int player_id FK
        int kills
        int deaths
        int assists
        decimal fantasy_points
    }

    USER {
        int id PK
        string name
        string email
        string password_hash
        datetime created_at
        datetime updated_at
        datetime deleted_at
    }

    FANTASY_LEAGUE {
        int id PK
        int competition_id FK
        int creator_user_id FK
        string name
        string visibility
        string status
        int max_participants
        decimal budget_cap
        datetime join_deadline
        string scoring_rule_version
        datetime created_at
        datetime updated_at
    }

    FANTASY_LEAGUE_MEMBERSHIP {
        int id PK
        int fantasy_league_id FK
        int user_id FK
        string role
        string status
        datetime joined_at
    }

    INVITATION {
        int id PK
        int fantasy_league_id FK
        string code
        datetime expires_at
        int max_uses
        int used_count
        datetime revoked_at
        datetime created_at
    }

    FANTASY_TEAM {
        int id PK
        int membership_id FK
        string name
        decimal budget_remaining
        datetime created_at
        datetime updated_at
    }

    ROSTER_SLOT {
        int id PK
        int fantasy_team_id FK
        int player_id FK
        decimal acquisition_cost
        datetime acquired_at
        datetime released_at
        string status
    }

    AUCTION {
        int id PK
        int fantasy_league_id FK
        int week_id FK
        string status
        datetime start_at
        datetime end_at
        datetime created_at
    }

    BID {
        int id PK
        int auction_id FK
        int fantasy_team_id FK
        int player_id FK
        decimal amount
        string status
        datetime placed_at
    }

    LINEUP {
        int id PK
        int fantasy_team_id FK
        int week_id FK
        string status
        datetime submitted_at
        datetime locked_at
    }

    LINEUP_SLOT {
        int id PK
        int lineup_id FK
        int roster_slot_id FK
        string position
        bool is_captain
    }

    FANTASY_TEAM_SCORE {
        int id PK
        int fantasy_team_id FK
        int week_id FK
        decimal points
        int rank
        datetime calculated_at
    }

    COMPETITION ||--o{ WEEK : organizes
    COMPETITION ||--o{ TEAM : contains
    COMPETITION ||--o{ FANTASY_LEAGUE : supports

    WEEK ||--o{ GAME_MATCH : includes
    WEEK ||--o{ AUCTION : schedules
    WEEK ||--o{ LINEUP : targets
    WEEK ||--o{ FANTASY_TEAM_SCORE : ranks

    TEAM ||--o{ PLAYER : has
    GAME_MATCH ||--o{ MATCH_TEAM : has
    TEAM ||--o{ MATCH_TEAM : participates

    GAME_MATCH ||--o{ PLAYER_STAT : generates
    PLAYER ||--o{ PLAYER_STAT : produces

    USER ||--o{ FANTASY_LEAGUE : creates
    USER ||--o{ FANTASY_LEAGUE_MEMBERSHIP : joins

    FANTASY_LEAGUE ||--o{ FANTASY_LEAGUE_MEMBERSHIP : has
    FANTASY_LEAGUE ||--o{ INVITATION : secures
    FANTASY_LEAGUE ||--o{ AUCTION : runs

    FANTASY_LEAGUE_MEMBERSHIP ||--|| FANTASY_TEAM : owns

    FANTASY_TEAM ||--o{ ROSTER_SLOT : contains
    PLAYER ||--o{ ROSTER_SLOT : selected

    AUCTION ||--o{ BID : collects
    FANTASY_TEAM ||--o{ BID : places
    PLAYER ||--o{ BID : targeted

    FANTASY_TEAM ||--o{ LINEUP : submits
    LINEUP ||--o{ LINEUP_SLOT : contains
    ROSTER_SLOT ||--o{ LINEUP_SLOT : activates

    FANTASY_TEAM ||--o{ FANTASY_TEAM_SCORE : earns
```

## Physical Constraints

- `FANTASY_LEAGUE_MEMBERSHIP (fantasy_league_id, user_id)` must be unique.
- `INVITATION.code` must be unique.
- `FANTASY_TEAM.membership_id` must be unique.
- `LINEUP (fantasy_team_id, week_id)` must be unique.
- `FANTASY_TEAM_SCORE (fantasy_team_id, week_id)` must be unique.
- `ROSTER_SLOT` should prevent two active rows for the same `(fantasy_team_id, player_id)` pair.
