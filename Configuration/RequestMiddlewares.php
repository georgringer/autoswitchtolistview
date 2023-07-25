<?php

return [
    'backend' => [
        'GeorgRinger/autoswitchlistview' => [
            'target' => \GeorgRinger\Autoswitchlistview\Middleware\ChangeToListViewMiddleware::class,
            'after' => [
                'typo3/cms-backend/backend-routing',
            ],
        ],
    ],
];
