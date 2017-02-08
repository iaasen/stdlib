<?php
namespace Oppned;


return [
	'service_manager' => [
		'abstract_factories' => [
			\Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
		],
		'factories' => [
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
            'messages' => \Oppned\View\Helper\MessageWidget::class,
			'makeBootstrapFormHorizontal' => \Oppned\View\Helper\BootstrapFormHorizontal::class,
        ],
    ],
	'navigation_helpers' => [
		'factories' => [
			'mainMenu' => \Oppned\Navigation\MainMenuFactory::class,
			'menu' => \Oppned\Navigation\MenuFactory::class,
		],
	],
	'form_elements' => [
		'invokables' => [
			'Primary' => \Oppned\Form\Element\Primary::class,
		],
		'factories' => [

		],
		'abstract_factories' => [
		]
	],
//	'navigation' => [
//		'default' => [
//			'home' => [
//				'label' => 'Forside',
//				'route' => 'home',
//			],
//		],
//		'login' => [
//			'login' => [
//				'label' => 'Logg inn',
//				'route' => 'auth/login',
//			],
//		]
//	],
];
