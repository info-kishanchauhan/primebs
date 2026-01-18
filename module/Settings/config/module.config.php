<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Settings\Controller\Index' => 'Settings\Controller\IndexController',
        ),
    ),

    'router' => array(
        'routes' => array(

            // =======================
            // /settings  (parent)
            // =======================
            'settings' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/settings',
                    'defaults' => array(
                        'controller' => 'Settings\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,

                'child_routes' => array(

                    // generic fallback: /settings/:action/:id
                    'actions' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:action][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults'    => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    // ===== User UI pages =====
                    'agreements' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreements',
                            ),
                        ),
                    ),
                    'agreements-admin' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements-admin',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsAdmin',
                            ),
                        ),
                    ),

                    // ✅ NEW ROUTE: Banking / Payout Page
                    'bankinformation' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/bankinformation',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'bankinformation',
                            ),
                        ),
                    ),

                    // ===== JSON API (canonical) =====
                    'agreements-list' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/list',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsList',
                            ),
                        ),
                    ),
                    'agreements-upload' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/upload',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsUpload',
                            ),
                        ),
                    ),
                    'agreements-save' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/save',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsSave',
                            ),
                        ),
                    ),
                    'agreements-renew' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/renew',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsRenew',
                            ),
                        ),
                    ),
                    'agreements-terminate' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/terminate',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsTerminate',
                            ),
                        ),
                    ),
                    'agreements-delete' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/delete',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsDelete',
                            ),
                        ),
                    ),
                    'agreements-status' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/status',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsStatus',
                            ),
                        ),
                    ),

                    // ===== JSON API (compat aliases for older JS) =====
                    'compat-agreements-list' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementslist',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsList',
                            ),
                        ),
                    ),
                    'compat-agreements-upload' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementsupload',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsUpload',
                            ),
                        ),
                    ),
                    'compat-agreements-save' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementssave',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsSave',
                            ),
                        ),
                    ),
                    'compat-agreements-renew' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementsrenew',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsRenew',
                            ),
                        ),
                    ),
                    'compat-agreements-terminate' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementsterminate',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsTerminate',
                            ),
                        ),
                    ),
                    'compat-agreements-delete' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementsdelete',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsDelete',
                            ),
                        ),
                    ),
                    'compat-agreements-status' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/agreementsstatus',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsStatus',
                            ),
                        ),
                    ),
                ),
            ),

            // =======================
            // /admin (top-level)
            // =======================
            'admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        'controller' => 'Settings\Controller\Index',
                        'action'     => 'agreementsAdmin',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'agreements' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/agreements',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsAdmin',
                            ),
                        ),
                    ),
                    'users-list' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/users/list',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'adminUsersList',
                            ),
                        ),
                    ),
                    'agreements-list' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/list',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'adminAgreementsList',
                            ),
                        ),
                    ),
                    // status endpoint reuse:
                    'agreements-status' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route'    => '/agreements/status',
                            'defaults' => array(
                                'controller' => 'Settings\Controller\Index',
                                'action'     => 'agreementsStatus',
                            ),
                        ),
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
            'settings' => __DIR__ . '/../view',
        ),
        // JSON responses ke liye:
        'strategies' => array('ViewJsonStrategy'),
    ),
);
