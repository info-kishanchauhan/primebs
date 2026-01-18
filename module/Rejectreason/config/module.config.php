<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Rejectreason\Controller\Index' => 'Rejectreason\Controller\IndexController',
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'rejectreason' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/rejectreason[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Rejectreason\Controller\Index',
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
            'rejectreason' => __DIR__ . '/../view',
        ),
    ),
);