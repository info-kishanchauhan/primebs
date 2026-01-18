<?php
return [
  'controllers' => [
    'invokables' => [
      'Analytics\Controller\Index'   => 'Analytics\Controller\IndexController',
      'Analytics\Controller\Fresh'   => 'Analytics\Controller\FreshController',
      'Analytics\Controller\Stream'  => 'Analytics\Controller\StreamController',
      'Analytics\Controller\Artist'  => 'Analytics\Controller\ArtistController',
    ],
  ],

  'router' => [
    'routes' => [

      /* ---------- JSON / helper routes (put BEFORE generic /analytics segment) ---------- */

      'latest-attached-releases' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/latest-attached-releases',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'latestAttachedReleases',
          ],
        ],
        'priority' => 100,
      ],

      'analytics-creative-templates' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/creative-templates',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'creativeTemplates',
          ],
        ],
        'priority' => 100,
      ],

      'analytics-release-stats' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/release-stats',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'releaseStats',
          ],
        ],
        'priority' => 100,
      ],

      'analytics-release-audio' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/release-audio',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'releaseAudio',
          ],
        ],
        'priority' => 110,
      ],

      // Back-compat: /analytics/index/artist → ArtistController@page
      'analytics-index-artist' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/index/artist',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'page',
          ],
        ],
        'priority' => 140,
      ],

      'analytics-render-video' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/render-video',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'renderVideo',
          ],
        ],
        'priority' => 110,
      ],

      'analytics-render' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/render',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'render',
          ],
        ],
        'priority' => 100,
      ],

      'analytics-poster' => [
        'type'    => 'Segment',
        'options' => [
          'route'       => '/analytics/poster[/:id]',
          'constraints' => ['id' => '[0-9]+'],
          'defaults'    => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'poster',
          ],
        ],
        'priority' => 100,
      ],

      'analytics-creative-editor' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/creative-editor',
          'defaults' => [
            'controller' => 'Analytics\Controller\Fresh',
            'action'     => 'creativeEditor',
          ],
        ],
        'priority' => 100,
      ],

      /* ---------- STREAM ROUTES (specific FIRST, generic LAST) ---------- */

      // safe JSON fetchers
      'analytics-stream-fetch' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/stream-fetch',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'fetch',
          ],
        ],
        'priority' => 160,
      ],

      'analytics-stream-update' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/stream-update',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'update',
          ],
        ],
        'priority' => 160,
      ],

      /* ✅ NEW: top tracks JSON for the Playlists tab */
      'analytics-stream-toptracks' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/stream-toptracks',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'toptracks',
          ],
        ],
        'priority' => 155,
      ],

      /* Aliases for playlists JSON */
      'analytics-playlists' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/playlists',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'playlists',
          ],
        ],
        'priority' => 155,
      ],

      'analytics-stream-playlists' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/stream-playlists',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'playlists',
          ],
        ],
        'priority' => 155,
      ],

      /* ⚠️ generic stream fallback — keep LOWEST among stream routes */
      'analytics-stream' => [
        'type'    => 'Segment',
        'options' => [
          'route'    => '/analytics/stream[/:action]',
          'defaults' => [
            'controller' => 'Analytics\Controller\Stream',
            'action'     => 'fetch',
          ],
        ],
        'priority' => 80,   // lower than any specific stream literal
      ],

      /* ---------- ARTIST PAGES & JSON (TOP-LEVEL) ---------- */

      'analytics-artist' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'page',
          ],
        ],
        'priority' => 130,
      ],

      'analytics-artist-page' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/page',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'page',
          ],
        ],
        'priority' => 131,
      ],

      'analytics-artist-metrics' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/metrics-json',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'metricsJson',
          ],
        ],
        'priority' => 132,
      ],

      'analytics-artist-metrics-plain' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/metrics',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'metricsJson',
          ],
        ],
        'priority' => 133,
      ],

      'analytics-artist-songs' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/songs-json',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'songsJson',
          ],
        ],
        'priority' => 132,
      ],

      'analytics-artist-songs-plain' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/songs',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'songsJson',
          ],
        ],
        'priority' => 133,
      ],

      'analytics-artist-save' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/save',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'save',
          ],
        ],
        'priority' => 135,
      ],

      'analytics-artist-by-track' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/analytics/artist/by-track',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'byTrack',
          ],
        ],
        'priority' => 135,
      ],

      /* ---------- Base /artists (optional editor) ---------- */
      'artists' => [
        'type'    => 'Literal',
        'options' => [
          'route'    => '/artists',
          'defaults' => [
            'controller' => 'Analytics\Controller\Artist',
            'action'     => 'index',
          ],
        ],
        'priority' => 130,
        'may_terminate' => true,
        'child_routes' => [
          'edit' => [
            'type'    => 'Segment',
            'options' => [
              'route'       => '/edit[/:id]',
              'constraints' => ['id' => '[0-9]+'],
              'defaults'    => [
                'controller' => 'Analytics\Controller\Artist',
                'action'     => 'edit',
              ],
            ],
          ],
          'save' => [
            'type'    => 'Literal',
            'options' => [
              'route'    => '/save',
              'defaults' => [
                'controller' => 'Analytics\Controller\Artist',
                'action'     => 'save',
              ],
            ],
          ],
          'by-track' => [
            'type'    => 'Literal',
            'options' => [
              'route'    => '/by-track',
              'defaults' => [
                'controller' => 'Analytics\Controller\Artist',
                'action'     => 'byTrack',
              ],
            ],
          ],
        ],
      ],

      /* ---------- Generic /analytics fallback (KEEP VERY LAST) ---------- */
      'analytics' => [
        'type'    => 'Segment',
        'options' => [
          'route'       => '/analytics[/:action][/:id]',
          'constraints' => [
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
          ],
          'defaults' => [
            'controller' => 'Analytics\Controller\Index',
            'action'     => 'index',
          ],
        ],
        'priority' => 0,
      ],
    ],
  ],

  'translator' => [
    'locale' => (@$_COOKIE["SMS_LANG"] ? $_COOKIE["SMS_LANG"] : "en_US"),
    'translation_file_patterns' => [[
      'type'     => 'gettext',
      'base_dir' => __DIR__ . '/../language',
      'pattern'  => '%s.mo',
    ]],
  ],

  'view_manager' => [
    'template_path_stack' => [
      'analytics' => __DIR__ . '/../view',
    ],
    'strategies' => ['ViewJsonStrategy'],
  ],
];
