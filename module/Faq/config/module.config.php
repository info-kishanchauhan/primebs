<?php
return [
    'controllers' => [
        'invokables' => [
            'Faq\Controller\Index' => 'Faq\Controller\IndexController',
        ],
    ],
    'router' => [
        'routes' => [
            'faq' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/faq',
                    'defaults' => [
                        '__NAMESPACE__' => 'Faq\Controller',
                        'controller'    => 'Index',
                        'action'        => 'categories',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'category' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/category/:id',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'category',
                            ],
                        ],
                    ],
                    'add' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'save' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/save',
                            'defaults' => [
                                'action' => 'save',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/delete',
                            'defaults' => [
                                'action' => 'delete',
                            ],
                        ],
                    ],
                    'article' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/article/:id',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'article',
                            ],
                        ],
                    ],
                    'add-category' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/add-category',
                            'defaults' => [
                                'controller' => 'Index',
                                'action'     => 'addCategory',
                            ],
                        ],
                    ],
                    'delete-category' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/delete-category',
                            'defaults' => [
                                'controller' => 'Index',
                                'action'     => 'deleteCategory',
                            ],
                        ],
                    ],
                    'rate' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/rate',
                            'defaults' => [
                                'controller' => 'Index',
                                'action'     => 'rate',
                            ],
                        ],
                    ],

                    // 🔎 NEW: Advanced FAQ search
                    'search' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/search',
                            'defaults' => [
                                'action' => 'search', // Faq\Controller\IndexController::searchAction
                            ],
                        ],
                    ],

                    'update-category' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/update-category',
                            'defaults' => [
                                'controller' => 'Index',
                                'action'     => 'updateCategory',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
