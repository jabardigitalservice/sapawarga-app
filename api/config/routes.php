<?php

use yii\rest\UrlRule;
use yii\web\GroupUrlRule;

return [
    'ping' => 'site/ping',
    'storage/version.json' => 'site/storage',
    'test-exception' => 'site/test-exception',
    'v1/cron/broadcasts' => 'v1/broadcast-cron',
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/user',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST login' => 'login',
            'OPTIONS login' => 'options',
            'POST logout' => 'logout',
            'OPTIONS logout' => 'options',
            'POST signup' => 'signup',
            'OPTIONS signup' => 'options',
            'POST confirm' => 'confirm',
            'OPTIONS confirm' => 'options',
            'POST password-reset-request' => 'password-reset-request',
            'OPTIONS password-reset-request' => 'options',
            'POST password-reset-token-verification' => 'password-reset-token-verification',
            'OPTIONS password-reset-token-verification' => 'options',
            'POST password-reset' => 'password-reset',
            'OPTIONS password-reset' => 'options',
            'GET me' => 'me',
            'POST me' => 'me-update',
            'OPTIONS me' => 'options',
            'GET me/photo' => 'me-photo',
            'POST me/photo' => 'me-photo-upload',
            'OPTIONS me/photo' => 'options',
            'POST me/change-password' => 'me-change-password',
            'OPTIONS me/change-password' => 'options',
            'POST me/change-profile' => 'me-change-profile',
            'OPTIONS me/change-profile' => 'options',
        ]
    ],
    [
        'class' => GroupUrlRule::class,
        'prefix' => 'v1/staff',
        'routePrefix' => 'v1/staff-import',
        'rules' => [
            'GET import-template' => 'download-template',
            'OPTIONS import-template' => 'options',
            'POST import' => 'import',
            'OPTIONS import' => 'options',
        ],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/staff',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST login' => 'login',
            'OPTIONS login' => 'options',
            'GET count' => 'count',
            'OPTIONS count' => 'options',
            'GET get-permissions' => 'get-permissions',
            'OPTIONS get-permissions' => 'options',
            'POST photo' => 'photo-upload',
            'OPTIONS photo' => 'options',
            'GET me' => 'me',
            'POST me' => 'me-update',
            'OPTIONS me' => 'options',
            'GET export' => 'export',
            'OPTIONS export' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/setting',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET public' => 'public',
            'OPTIONS public' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/area',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/phone-book',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET check-exist' => 'check-exist',
            'OPTIONS check-exist' => 'options',
            'GET by-user-location' => 'by-user-location',
            'OPTIONS by-user-location' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/attachment',
        'tokens' => []
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/category',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET types' => 'types',
            'OPTIONS types' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/broadcast',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/aspirasi',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET me' => 'me',
            'OPTIONS me' => 'options',
            'POST likes/{id}' => 'likes',
            'OPTIONS likes/{id}' => 'options',
            'POST approval/{id}' => 'approval',
            'OPTIONS approval/{id}' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/polling',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
            '{answerId}' => '<answerId:\d+>',
        ],
        'extraPatterns' => [
            'POST {id}/answers' => 'answer-create',
            'OPTIONS {id}/answers' => 'options',
            'PUT {id}/answers/{answerId}' => 'answer-update',
            'DELETE {id}/answers/{answerId}' => 'answer-delete',
            'OPTIONS {id}/answers/{answerId}' => 'options',
            'GET {id}/vote' => 'vote-check',
            'PUT {id}/vote' => 'vote',
            'OPTIONS {id}/vote' => 'options',
            'GET {id}/result' => 'result',
            'OPTIONS {id}/result' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/survey',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/news-channel',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/news',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET featured' => 'featured',
            'POST featured' => 'featured-update',
            'OPTIONS featured' => 'options',
            'GET related' => 'related',
            'OPTIONS related' => 'options',
            'GET stats/channel' => 'stats-channel',
            'OPTIONS stats/channel' => 'options',
            'GET statistics' => 'statistics',
            'OPTIONS statistics' => 'options',
            'POST likes/{id}' => 'likes',
            'OPTIONS likes/{id}' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/news-hoax',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/video',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET statistics' => 'statistics',
            'OPTIONS statistics' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/notification',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/page',
        'pluralize' => false,
        'tokens' => [
        ],
        'extraPatterns' => [
            'GET sse' => 'sse',
            'OPTIONS sse' => 'sse',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/dashboard',
        'pluralize' => true,
        'extraPatterns' => [
            'GET aspirasi-most-likes' => 'aspirasi-most-likes',
            'OPTIONS aspirasi-most-likes' => 'options',
            'GET aspirasi-counts' => 'aspirasi-counts',
            'OPTIONS aspirasi-counts' => 'options',
            'GET aspirasi-category-counts' => 'aspirasi-category-counts',
            'OPTIONS aspirasi-category-counts' => 'options',
            'GET aspirasi-geo' => 'aspirasi-geo',
            'OPTIONS aspirasi-geo' => 'options',
            'GET polling-latest' => 'polling-latest',
            'OPTIONS polling-latest' => 'options',
            'GET polling-counts' => 'polling-counts',
            'OPTIONS polling-counts' => 'options',
            'GET polling-participation' => 'polling-participation',
            'OPTIONS polling-participation' => 'options',
            'GET news-most-likes' => 'news-most-likes',
            'OPTIONS news-most-likes' => 'options',
            'GET videos-most-views' => 'videos-most-views',
            'OPTIONS videos-most-views' => 'options',
            'GET users-leaderboard' => 'users-leaderboard',
            'OPTIONS users-leaderboard' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/user-message',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:[A-Za-z0-9]+>',
        ],
        'extraPatterns' => [
            'POST bulk-delete' => 'bulk-delete',
            'OPTIONS bulk-delete' => 'options',
        ],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/release',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/banner',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/popup',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/news-important',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST likes/{id}' => 'likes',
            'OPTIONS likes/{id}' => 'options',
        ]
    ],
    [
        'class' => UrlRule::class,
        'controller' => ['comments' => 'v1/news-important-comment'],
        'prefix' => 'v1/news-important/<NewsImportantId:\d+>',
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/job-type',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/education-level',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
    ],
    [
        'class' => UrlRule::class,
        'controller' => 'v1/question',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'POST likes/{id}' => 'likes',
            'OPTIONS likes/{id}' => 'options',
        ]
    ],
    [
        'class' => UrlRule::class,
        'controller' => ['comments' => 'v1/question-comment'],
        'prefix' => 'v1/questions/<questionId:\d+>',
    ],
    [
        'class' => UrlRule::class,
        'controller' => 'v1/user-post',
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'POST likes/{id}' => 'likes',
            'OPTIONS likes/{id}' => 'options',
            'GET me' => 'me',
            'OPTIONS me' => 'options',
        ]
    ],
    [
        'class' => UrlRule::class,
        'controller' => ['comments' => 'v1/user-post-comment'],
        'prefix' => 'v1/user-posts/<userPostId:\d+>',
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/gamification',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST join/{id}' => 'join',
            'OPTIONS join/{id}' => 'options',
            'GET my-task/{id}' => 'my-task',
            'OPTIONS my-task/{id}' => 'options',
            'GET participant' => 'participant',
            'OPTIONS participant' => 'options',
            'GET my-badge' => 'my-badge',
            'OPTIONS my-badge' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/beneficiaries',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
            '{nik}' => '<nik:\w+>',
            '{kk}' => '<kk:\w+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET check-nik' => 'check-nik',
            'OPTIONS check-nik' => 'options',
            'GET check-kk' => 'check-kk',
            'OPTIONS check-kk' => 'options',
            'GET check-address' => 'check-address',
            'OPTIONS check-address' => 'options',
            'GET dashboard-list' => 'dashboard-list',
            'OPTIONS dashboard-list' => 'options',
            'GET dashboard-summary' => 'dashboard-summary',
            'OPTIONS dashboard-summary' => 'options',
            'GET approval' => 'approval-list',
            'OPTIONS approval' => 'options',
            'POST approval/{id}' => 'approval',
            'OPTIONS approval/{id}' => 'options',
            'POST bulk-approval' => 'bulk-approval',
            'OPTIONS bulk-approval' => 'options',
            'GET approval-dashboard' => 'approval-dashboard',
            'OPTIONS approval-dashboard' => 'options',
            'GET current-tahap' => 'current-tahap',
            'OPTIONS current-tahap' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/beneficiaries-verval-upload',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST upload' => 'upload',
            'OPTIONS upload' => 'options',
        ]
    ],
    'GET v1/beneficiaries/download' => 'v1/beneficiaries-download/download',
    'GET v1/beneficiaries/download-status' => 'v1/beneficiaries-download/download-status',
    'GET v1/beneficiaries/download-status/<history_id:\d+>' => 'v1/beneficiaries-download/download-status',
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/beneficiaries-bnba',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'GET summary' => 'summary',
            'OPTIONS summary' => 'options',
            'POST upload' => 'upload',
            'GET upload-histories' => 'upload-histories',
            'GET anomaly-download' => 'anomaly-download',
            'GET download' => 'download',
            'GET download-status' => 'download-status',
            'GET download-status/<history_id:\d+>' => 'download-status',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/beneficiaries-bnba-monitoring-upload',
        'pluralize' => true,
        'extraPatterns' => [
            'GET update-data' => 'update-data',
        ],
    ],

    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/beneficiaries-complain',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
        ]
    ],
    'GET v1/beneficiaries/allocation' => 'v1/beneficiaries-allocation',
    'GET v1/beneficiaries/allocation/<id:\d+>' => 'v1/beneficiaries-allocation/view',
    'GET v1/bansos/upload' => 'v1/bansos-upload/index',
    'POST v1/bansos/upload' => 'v1/bansos-upload/upload',
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/dinsos-job-type',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
    ],

    //  For Public Route
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/pub/beneficiaries',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET summary' => 'summary',
            'OPTIONS summary' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/pub/beneficiaries-bnba',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET statistics-by-type' => 'statistics-by-type',
            'OPTIONS statistics-by-type' => 'options',
            'GET statistics-by-area' => 'statistics-by-area',
            'OPTIONS statistics-by-area' => 'options',
            'GET statistics-update' => 'statistics-update',
            'OPTIONS statistics-update' => 'options',
            'GET flagging' => 'flagging',
            'OPTIONS flagging' => 'options',
            'GET tracking' => 'tracking',
            'OPTIONS tracking' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/pub/beneficiaries-complain',
        'pluralize' => false,
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/pub/area',
        'pluralize' => true,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options'
        ]
    ]
];
