<?php
// config/config.php

return [
    'app' => [
        'name'        => 'Free Discussion Class',
        'tagline'     => 'Weekly ESL Discussion Topics',
        'domain'      => 'en.class.sayid.ir',
        'url'         => 'https://en.class.sayid.ir',
        'lang'        => 'en',
        'timezone'    => 'Asia/Tehran',
        'version'     => '1.0.0',
    ],

    'paths' => [
        'root'      => dirname(__DIR__),
        'data'      => dirname(__DIR__) . '/data',
        'topics'    => dirname(__DIR__) . '/data/topics',
        'public'    => dirname(__DIR__) . '/public',
        'uploads'   => dirname(__DIR__) . '/uploads',
        'templates' => dirname(__DIR__) . '/templates',
        'logs'      => dirname(__DIR__) . '/logs',
        'cache'     => dirname(__DIR__) . '/cache',
    ],

    'telegram' => [
        'token'           => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'webhook_secret'  => getenv('TELEGRAM_WEBHOOK_SECRET') ?: '',
        'admins'          => array_filter(array_map('intval',
                                explode(',', getenv('TELEGRAM_ADMIN_IDS') ?: '')
                            )),
    ],

    'seo' => [
        'default_description' => 'Practice English through thought-provoking discussion questions, vocabulary flashcards, and interactive activities.',
        'default_image'       => '/images/og-default.png',
        'twitter_handle'      => '@saclass',
        'google_analytics'    => getenv('GA_ID') ?: '',
    ],

    'features' => [
        'pwa'            => true,
        'search'         => true,
        'flashcards'     => true,
        'discussion_wheel' => true,
        'speaking_timer' => true,
        'printable'      => true,
        'favorites'      => true,
        'reading_mode'   => true,
    ],

    'pagination' => [
        'per_page' => 12,
    ],

    'levels' => ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],

    'colors' => [
        'light'   => '#dddddd',
        'blue'    => '#0267c1',
        'red'     => '#a01332',
        'dark'    => '#1e212b',
        'darkest' => '#19171e',
    ],
];
