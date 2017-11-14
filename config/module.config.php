<?php

namespace ZfThreeSocialAuth;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'social' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/social/:action/:provider',
                    'defaults' => [
                        'controller' => 'social',
                        'action' => 'login-start',
                    ],
                ],
            ],
            'social-start-pages' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/social/:action',
                    'defaults' => [
                        'controller' => 'social',
                        'action' => 'login',
                    ],
                ],
            ],
            'failed-social-login' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/social/not-logged-in',
                    'defaults' => [
                        'controller' => 'social',
                        'action' => 'failed-login',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\SocialController::class => Controller\Factory\SocialControllerFactory::class,
        ],
        'aliases' => [
            'social' => Controller\SocialController::class,
        ],
    ],
    'access_filter' => [
        'controllers' => [
            // Controller\SocialController::class => [
            'social' => [
                // Give access to "start-login", "start-registration", "start-login-or-registration" and 'redirected' actions
                // to anyone.
                ['actions' => [ 'startLogin', 'startRegistration', 'startLoginOrRegistration', 'redirected', 'register', 'loginOrRegister', 'failedLogin'], 'allow' => '*'],
            ],
        ]
    ],
    'session_containers' => [
        'social_saved_state'
    ],
    'service_manager' => [
        'factories' => [
            Service\SocialManager::class => Service\Factory\SocialManagerFactory::class,
            Service\SocialAuthManager::class => Service\Factory\SocialAuthManagerFactory::class,
            Options\ModuleOptions::class => Options\Factory\ModuleOptionsFactory::class,
        ],
        'aliases' => [
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\SocialProvider::class => View\Helper\Factory\SocialProviderFactory::class,
        ],
        'aliases' => [
            'socialProvider' => View\Helper\SocialProvider::class,
        ],
    ],
];
