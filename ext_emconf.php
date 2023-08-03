<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Autoswitch to list view',
    'description' => 'If page module and a sys folder is selected, a redirect to the list module is done.y',
    'category' => 'backend',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '2.0.4',
    'constraints' =>
        [
            'depends' => [
                'php' => '8.1.0-8.9.99',
                'typo3' => '12.4.0-12.4.99'
            ],
            'conflicts' => [],
            'suggests' => [],
        ]
];
