<?php
namespace Oppned;


return [
	'modules' => [
	],
	'service_manager' => [
		'abstract_factories' => [
			\Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
		],
		'factories' => [
			'navigation' => \Zend\Navigation\Service\DefaultNavigationFactory::class,
			\Oppned\Log\Logger::class => \Oppned\Log\LoggerFactory::class,
			\Oppned\Log\LogTable::class => \Oppned\Log\LogTableFactory::class,
			'Oppned\Log\LogTableGateway' => \Oppned\Log\LogTableGatewayFactory::class,
			\Iaasen\Messenger\EmailService::class => \Iaasen\Messenger\EmailServiceFactory::class,
		],
		'invokables' => [
		],
		'aliases' => [
			'Logger' => \Oppned\Log\Logger::class,
		]
	],
	'controller_plugins' => [
		'invokables' => [
			\Iaasen\Messenger\SessionMessenger::class => \Iaasen\Messenger\SessionMessenger::class,
		],
		'aliases' => [
			'flashMessenger' => \Iaasen\Messenger\SessionMessenger::class,
		],
	],
	'view_helpers' => [
		'aliases' => [
			'messages' => \Iaasen\Messenger\SessionMessengerViewHelper::class,
		],
        'invokables' => [
			'makeBootstrapFormHorizontal' => \Oppned\View\Helper\BootstrapFormHorizontal::class,
        ],
		'factories' => [
			\Iaasen\Messenger\SessionMessengerViewHelper::class => \Iaasen\Messenger\SessionMessengerViewHelper::class,
		]
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
	'messenger' => [
		'email' => [
			'transport' => [
				'method' => 'smtp',
				'host' => '',
				'port' => 25,
				'security' => null,
				'username' => '',
				'password' => '',
			],
			'from' => '',
		],
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
