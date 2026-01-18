<?php
namespace Tickets;

return [
    'router' => [
        'routes' => [
            'tickets' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/tickets',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'submit' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/submit',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'submit',
                            ],
                        ],
                    ],
                    'reply' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/reply',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'reply',
                            ],
                        ],
                    ],
                    'merge' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/merge',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'merge',
                            ],
                        ],
                    ],
                  'sendfeedback' => [   // 👈 add this
        'type' => 'Literal',
        'options' => [
            'route' => '/sendfeedback',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'sendfeedback',
            ],
        ],
    ],
                    'delete' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/delete',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'delete',
                            ],
                        ],
                    ],
                    'updatestatus' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/updatestatus',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'updatestatus',
                            ],
                        ],
                    ],
                    'forward' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/forward',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'forward',
                            ],
                        ],
                    ],
                    'sendemail' => [ // ✅ NEW
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/sendemail',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'sendemail',
                            ],
                        ],
                    ],
                    'thread' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/thread[/:id]',
                            'defaults' => [
                                'controller' => Controller\ReplyController::class,
                                'action' => 'view',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => function($container) {
                return new Controller\IndexController();
            },
            Controller\ReplyController::class => function($container) {
                return new Controller\ReplyController();
            },
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __NAMESPACE__ => __DIR__ . '/../view',
        ],
        'display_exceptions' => true,
    ],
];
