<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Newrelease\Controller\Index' => 'Newrelease\Controller\IndexController',
        ),
    ),

    'router' => array(
        'routes' => array(
            // main
            'newrelease' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/newrelease[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),

            // lookups / picker endpoints
            'artist-lookup-by-name' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/lookup-by-name',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'lookupArtistByName',
                    ),
                ),
            ),
            'artist-suggest' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/suggest',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'artistSuggestionList',
                    ),
                ),
            ),
            'artist-create-local' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/create-local',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'createLocalArtist',
                    ),
                ),
            ),
            'artist-search-spotify' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/search',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'searchArtistSpotify',
                    ),
                ),
            ),
            // map both legacy and new routes to the existing controller action
            'artist-link-spotify' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/link-spotify',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'linkSpotifyToArtist',
                    ),
                ),
            ),
            'artist-link-spotify-to' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/artists/link-spotify-to',
                    'defaults' => array(
                        'controller' => 'Newrelease\Controller\Index',
                        'action'     => 'linkSpotifyToArtist',
                    ),
                ),
            ),

            // (removed artist-open-spotify because controller action doesn't exist)
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
            'newrelease' => __DIR__ . '/../view',
        ),
    ),
);
