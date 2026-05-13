<?php

return [
    'creator_db' => [
        'key' => env('CREATOR_DB_API'),
        'url' => env('CREATOR_DB_URL', 'https://dev.creatordb.app'),
    ],

    'apify' => [
        'token' => env('APIFY_TOKEN'),
        'actors' => [
            'tiktok'    => env('APIFY_ACTOR_TIKTOK'),
            'instagram' => env('APIFY_ACTOR_INSTAGRAM'),
            'youtube'   => env('APIFY_ACTOR_YOUTUBE'),
            'facebook'  => env('APIFY_ACTOR_FACEBOOK'),
        ],
    ],
];
