<?php
namespace Oppned;

use Oppned\View\Helper\MessageWidget;

return [
	'service_manager' => [
		'abstract_factories' => [
			\Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
		],
		'factories' => [
			//'navigation' => \Oppned\Navigation\NavigationFactory::class,
			'navigation' => \Zend\Navigation\Service\DefaultNavigationFactory::class,
			\Oppned\Log\Logger::class => \Oppned\Log\LoggerFactory::class,
			\Oppned\Log\LogTable::class => \Oppned\Log\LogTableFactory::class,
			'Oppned\Log\LogTableGateway' => \Oppned\Log\LogTableGatewayFactory::class,
		],
		'invokables' => [
		],
		'aliases' => [
			'Logger' => \Oppned\Log\Logger::class,
		]
	],
	'view_helpers' => [
        'invokables' => [
            'messages' => MessageWidget::class,
        ],
    ],
	'navigation_helpers' => [
		'factories' => [
			'mainMenu' => \Oppned\Navigation\MainMenuFactory::class,
			'menu' => \Oppned\Navigation\MenuFactory::class,
		],
	],
	'navigation' => [
		'default' => [
			'home' => [
				'label' => 'Forside',
				'route' => 'home',
			],
		],
		'login' => [
			'login' => [
				'label' => 'Logg inn',
				'route' => 'auth/login',
			],
		]
	],
];
