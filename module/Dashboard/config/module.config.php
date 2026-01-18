<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Dashboard\Controller\Index' => 'Dashboard\Controller\IndexController',
        ),
    ),

    'router' => array(
        'routes' => array(

            // main dashboard route + all ajax sub-actions
            'dashboard' => array(
                'type'    => 'segment',
                'options' => array(
                    // /dashboard
                    // /dashboard/streams
                    // /dashboard/toptracks
                    // /dashboard/stores
                    // /dashboard/widgets
                    // /dashboard/readNotification
                    // etc...
                    'route'    => '/dashboard[/:action][/:id]',
                    'constraints' => array(
                        // action MUST start with letter (so it's safe)
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        // id is optional numeric if present
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),

            // (optional but nice) alias so /dashboard/ just redirects same
            'dashboard-root' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/dashboard',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),

    // translator (unchanged)
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
            'dashboard' => __DIR__ . '/../view',
        ),

        // IMPORTANT:
        // so that when our controller echos json and exit; we don't get layout/view errors.
        // plus helpful for debugging ajax 500s.
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
