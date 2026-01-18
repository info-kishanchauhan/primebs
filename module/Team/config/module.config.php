<?php
return array(
    'controllers' => array(
    'invokables' => array(
        'Team\Controller\Index' => 'Team\Controller\IndexController',
    ),
),

    'router' => array(
        'routes' => array(
            'team' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/team[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Team\Controller\Index',
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
            'team' => __DIR__ . '/../view',
        ),
    ),
);