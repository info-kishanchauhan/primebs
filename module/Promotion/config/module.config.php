<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Promotion\Controller\Index' => 'Promotion\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'promotion' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/promotion',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Promotion\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Default controller/action/id route
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action[/:id]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'         => '[0-9]+',
                            ),
                            'defaults' => array(),
                        ),
                    ),

                    // Direct shortcut for /promotion/attachmcn
                    'attachmcn' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/attachmcn',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action'     => 'attachmcn',
                            ),
                        ),
                    ),

                    // Direct shortcut for /promotion/submitmcn
                    'submitmcn' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/submitmcn',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action'     => 'submitmcn',
                            ),
                        ),
                    ),

                    // ✅ NEW: Direct shortcut for /promotion/cancelmcn
                    'cancelmcn' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/cancelmcn',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action'     => 'cancelmcn',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'promotion' => __DIR__ . '/../view',
        ),
    ),
);
