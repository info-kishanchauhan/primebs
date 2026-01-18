<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Payments\Controller\Index' => 'Payments\Controller\IndexController',
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'payments' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/payments[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Payments\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
        'locale' => (@$_COOKIE["SMS_LANG"] ? $_COOKIE["SMS_LANG"]:"en_US"),
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
            'payments' => __DIR__ . '/../view',
        ),
    ),
);