<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Support\Controller\Index' => 'Support\Controller\IndexController',
            'Support\Controller\Email' => 'Support\Controller\EmailController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'support' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/support[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Support\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                // ✅ Add this child route
                'may_terminate' => true,
                'child_routes' => array(
                    'get-confirmations' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route'    => '/get-confirmations',
                            'defaults' => array(
                                'controller' => 'Support\Controller\Index',
                                'action'     => 'getConfirmations',
                            ),
                        ),
                    ),
                ),
            ),

            'support-email' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/support/email[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Support\Controller\Email',
                        'action'     => 'sendRightsNotification',
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
        'locale' => (@$_COOKIE["SMS_LANG"] ? $_COOKIE["SMS_LANG"] : "en_US"),
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'support' => __DIR__ . '/../view',
        ),
    ),
);
