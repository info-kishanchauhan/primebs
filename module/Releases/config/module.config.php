<?php
use Releases\Controller\IndexController;
use Releases\Controller\FilterController;

return [
    'controllers' => [
        'invokables' => [
            'Releases\Controller\Index'  => IndexController::class,
            'Releases\Controller\Filter' => FilterController::class,
        ],
    ],

    'router' => [
        'routes' => [

            // Default Releases route
            'releases' => [
                'type'    => 'segment',
                'options' => [
                    'route'       => '/releases[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],

            // Export metadata -> IndexController::exportmetaAction
            'releases-export' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/exportmetadata',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'exportmeta',
                    ],
                ],
            ],

            // Fetch Apple links
            'releases-fetch-apple' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/fetchapple',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'fetchAppleLinks',
                    ],
                ],
            ],

            // Allow replace
            'allow-replace' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/allowreplace',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'allowreplace',
                    ],
                ],
            ],

            // Upload audio
            'uploadaudio' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/uploadaudio',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'uploadaudio',
                    ],
                ],
            ],

            // Release Filter Search
            'releases-filter' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/filter',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Filter',
                        'action'     => 'releases',
                    ],
                ],
            ],

            // Bulk Move to Processing
            'releases-move-processing' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/move-to-processing',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'moveToProcessing',
                    ],
                ],
            ],

            // Bulk Delete
            'releases-bulk-delete' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/delete',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'delete',
                    ],
                ],
            ],
          'newrelease-submission' => [
    'type'    => 'Literal',
    'options' => [
        'route'    => '/newrelease/submission',
        'defaults' => [
            'controller' => 'Releases\Controller\Index',
            'action'     => 'view',   // viewAction will use ?edit=ID
        ],
    ],
],

            // ------------------------------
            // ✅ FTP transfer flow routes
            // ------------------------------

            // Start transfer (POST) -> transfertrackAction
            'ftp-start' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/transfertrack',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'transfertrack',
                    ],
                ],
            ],

            // Poll progress (GET ?transfer_id=...) -> ftpProgressAction
            'ftp-progress' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/ftp-progress',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'ftpProgress',
                    ],
                ],
            ],

            // Cancel transfer (POST) -> ftpCancelAction
            'ftp-cancel' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/ftp-cancel',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'ftpCancel',
                    ],
                ],
            ],

            // Save Believe folder id (POST) -> savebelievefolderAction
            'save-believe-folder' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/releases/savebelievefolder',
                    'defaults' => [
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'savebelievefolder',
                    ],
                ],
            ],
        ],
    ],

    'translator' => [
        'locale' => (isset($_COOKIE['SMS_LANG']) ? $_COOKIE['SMS_LANG'] : 'en_US'),
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'releases' => __DIR__ . '/../view',
        ],
    ],

    'controller_plugins' => [
        'invokables' => [
            'CustomPlugin' => \Application\Controller\Plugin\CustomPlugin::class,
        ],
    ],
];
