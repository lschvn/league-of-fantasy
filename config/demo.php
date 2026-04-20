<?php

return [
    'password' => env('DEMO_USER_PASSWORD', 'password'),

    'private_invitation_code' => env('DEMO_PRIVATE_INVITATION_CODE', 'PRIVATE2026'),

    'users' => [
        'owner' => [
            'name' => 'Alice Draftmaster',
            'email' => 'owner@fantasy.test',
        ],
        'member_one' => [
            'name' => 'Bruno Baron',
            'email' => 'bruno@fantasy.test',
        ],
        'member_two' => [
            'name' => 'Chloe Carry',
            'email' => 'chloe@fantasy.test',
        ],
        'member_three' => [
            'name' => 'Diego Jungle',
            'email' => 'diego@fantasy.test',
        ],
        'private_owner' => [
            'name' => 'Eve Strategist',
            'email' => 'eve@fantasy.test',
        ],
        'private_member' => [
            'name' => 'Farah Support',
            'email' => 'farah@fantasy.test',
        ],
        'spectator' => [
            'name' => 'Gwen Reviewer',
            'email' => 'reviewer@fantasy.test',
        ],
    ],
];
