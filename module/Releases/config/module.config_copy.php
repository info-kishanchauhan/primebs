<?php
use Releases\Controller\ExportController;
use Releases\Controller\IndexController;
use Releases\Controller\FilterController;

return array(
    'controllers' => array(
        'invokables' => array(
            'Releases\Controller\Index' => IndexController::class,
            'Releases\Controller\Filter' => FilterController::class,
        ),
        'factories' => array(
            ExportController::class => function ($container) {
                return new ExportController();
            },
        ),
    ),

    'router' => array(
        'routes' => array(

            // 🔹 Default Releases route
            'releases' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/releases[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),

            // 🔹 Export metadata
            'releases-export' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/releases/exportmetadata',
                    'defaults' => array(
                        'controller' => ExportController::class,
                        'action'     => 'metadata',
                    ),
                ),
            ),

            // 🔹 Fetch Apple links
            'releases-fetch-apple' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/releases/fetchapple',
                    'defaults' => array(
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'fetchAppleLinks',
                    ),
                ),
            ),
'allow-replace' => array(
    'type'    => 'Literal',
    'options' => array(
        'route'    => '/releases/allowreplace',
        'defaults' => array(
            'controller' => 'Releases\Controller\Index',
            'action'     => 'allowreplace',
        ),
    ),
),
          'uploadaudio' => array(
    'type' => 'Literal',
    'options' => array(
        'route' => '/releases/uploadaudio',
        'defaults' => array(
            'controller' => 'Releases\Controller\Index',
            'action' => 'uploadaudio',
        ),
    ),
),
            // 🔹 Release Filter Search
            'releases-filter' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/releases/filter',
                    'defaults' => array(
                        'controller' => 'Releases\Controller\Filter',
                        'action'     => 'releases',
                    ),
                ),
            ),

            // ✅ Bulk Move to Processing
            'releases-move-processing' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/releases/move-to-processing',
                    'defaults' => array(
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'moveToProcessing',
                    ),
                ),
            ),

			
			
            // ✅ Bulk Delete (if using)
            'releases-bulk-delete' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/releases/delete',
                    'defaults' => array(
                        'controller' => 'Releases\Controller\Index',
                        'action'     => 'delete',
                    ),
                ),
            ),
        ),
    ),

    'translator' => array(
        'locale' => (isset($_COOKIE["SMS_LANG"]) ? $_COOKIE["SMS_LANG"] : "en_US"),
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
            'releases' => __DIR__ . '/../view',
        ),
    ),

    'controller_plugins' => array(
        'invokables' => array(
            'CustomPlugin' => \Application\Controller\Plugin\CustomPlugin::class,
        ),
    ),
);
