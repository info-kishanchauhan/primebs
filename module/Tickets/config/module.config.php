<?php
/**
 * module/Tickets/config/module.config.php
 */
namespace Tickets;

use Zend\ServiceManager\Factory\FactoryInterface;

return [

    // =========================
    // HTTP ROUTES
    // =========================
    'router' => [
        'routes' => [

            // ========= Private (dashboard) routes =========
            'tickets' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/tickets',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,

                // ---- Child routes under /tickets ----
                'child_routes'  => [

                    // GET /tickets/submit (form) + POST guard
                    'submit' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/submit',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'submit',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            // Lock to POST if you post the form to same path
                            'submit-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/reply
                    'reply' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/reply',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'reply',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'reply-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/merge
                    'merge' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/merge',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'merge',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'merge-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // ✅ admin feedback email trigger (button)
                    // POST /tickets/sendfeedback
                    'sendfeedback' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/sendfeedback',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'sendfeedback',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'sendfeedback-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/delete
                    'delete' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/delete',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'delete',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'delete-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/updatestatus
                    'updatestatus' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/updatestatus',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'updatestatus',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'updatestatus-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/forward
                    'forward' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/forward',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'forward',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'forward-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // POST /tickets/sendemail
                    'sendemail' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/sendemail',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'sendemail',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'sendemail-post' => [
                                'type'    => 'Method',
                                'options' => ['verb' => 'POST'],
                            ],
                        ],
                    ],

                    // GET /tickets/feedback/view
                    'feedback-view' => [
                        'type' => 'Literal',
                        'options' => [
                            'route'    => '/feedback/view',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'feedbackview',
                            ],
                        ],
                    ],

                  'mark-read' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/mark-read',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'markread', // maps to markReadAction()
                            ],
                        ],
                    ],
                 // GET /tickets/thread/:id  (Reply thread view)
                    'thread' => [
                        'type' => 'Segment',
                        'options' => [
                            'route'    => '/thread[/:id]',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => Controller\ReplyController::class,
                                'action'     => 'view',
                            ],
                        ],
                    ],
                ],
            ],

            // ========= Public (no-login) endpoint for feedback form submit =========
            // POST /feedback/submit  -> Tickets\Controller\PublicController::feedbacksubmit
            'public-feedback-submit' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/feedback/submit',
                    'defaults' => [
                        'controller' => Controller\PublicController::class,
                        'action'     => 'feedbacksubmit',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'public-feedback-submit-post' => [
                        'type'    => 'Method',
                        'options' => ['verb' => 'POST'],
                    ],
                ],
                // tip: Auth middleware/guard is controller side (token verify)
            ],
        ],
    ],

    // =========================
    // CONTROLLERS
    // =========================
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => function ($container) {
                // inject services from $container if needed
                return new Controller\IndexController();
            },
            Controller\ReplyController::class => function ($container) {
                return new Controller\ReplyController();
            },
            // ✅ public controller (token verify + save + redirect)
            Controller\PublicController::class => function ($container) {
                return new Controller\PublicController();
            },
        ],
    ],

    // =========================
    // VIEW MANAGER
    // =========================
    'view_manager' => [
        'template_path_stack' => [
            __NAMESPACE__ => __DIR__ . '/../view',
        ],
        'display_exceptions' => true,
    ],
];
