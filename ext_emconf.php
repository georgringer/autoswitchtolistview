<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Autoswitch to list view',
    'description' => 'If page module and a sys folder is selected, a redirect to the list module is done.y',
    'category' => 'backend',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '2.0.3',
    'constraints' =>
        [
            'depends' => [
                'typo3' => '7.6.0-11.5.99'
            ],
            'conflicts' => [],
            'suggests' => [],
        ]
];
