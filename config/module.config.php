<?php
namespace Iaasen;


use Iaasen\Initializer\NavigationInitializer;
use Iaasen\Initializer\ViewRendererInitializer;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

return [
	'modules' => [
	],
	'service_manager' => [
		'abstract_factories' => [
			\Laminas\Navigation\Service\NavigationAbstractServiceFactory::class,
		],
		'factories' => [
			'navigation' => \Laminas\Navigation\Service\DefaultNavigationFactory::class,
			\Oppned\Log\Logger::class => \Oppned\Log\LoggerFactory::class,
			\Oppned\Log\LogTable::class => \Oppned\Log\LogTableFactory::class,
			\Oppned\Log\LogTableGateway::class => \Oppned\Log\LogTableGatewayFactory::class,
			\Iaasen\Messenger\EmailService::class => \Iaasen\Messenger\EmailServiceFactory::class,
			\Iaasen\Service\LaminasMvcConfig::class => \Iaasen\Service\LaminasMvcConfigFactory::class
		],
		'invokables' => [
			\Iaasen\Geonorge\AddressService::class => \Iaasen\Geonorge\AddressService::class,
			\Iaasen\Messenger\SessionMessenger::class => \Iaasen\Messenger\SessionMessenger::class,
		],
		'aliases' => [
			'Logger' => \Oppned\Log\Logger::class,
		]
	],
	'controllers' => [
		'initializers' => [
			NavigationInitializer::class,
			ViewRendererInitializer::class,
		]
	],
	'controller_plugins' => [
		'factories' => [
			\Iaasen\Controller\Plugin\NavigationPlugin::class => ReflectionBasedAbstractFactory::class,
		],
		'invokables' => [
			\Iaasen\Messenger\SessionMessenger::class => \Iaasen\Messenger\SessionMessenger::class,
		],
		'aliases' => [
			'flashMessenger' => \Iaasen\Messenger\SessionMessenger::class,
			'navigation' => \Iaasen\Controller\Plugin\NavigationPlugin::class,
		],
	],
	'view_helpers' => [
		'aliases' => [
			'messages' => \Iaasen\Messenger\SessionMessengerViewHelper::class,
            'numbers' => Numbers::class
		],
        'invokables' => [
			'makeFormHorizontal' => \Oppned\View\Helper\BootstrapFormHorizontal::class,
			'makeBootstrapFormHorizontal' => \Oppned\View\Helper\BootstrapFormHorizontal::class,
			'formRowHorizontal' => \Iaasen\View\Helper\FormRowHorizontal::class,
            Numbers::class => Numbers::class,
        ],
		'factories' => [
			\Iaasen\Messenger\SessionMessengerViewHelper::class => \Iaasen\Messenger\SessionMessengerViewHelper::class,
		]
    ],
	'navigation_helpers' => [
		'factories' => [
			'mainMenu' => \Iaasen\Navigation\MainMenuFactory::class,
			'subMenu' => \Iaasen\Navigation\SubMenuFactory::class,
		],
	],
	'form_elements' => [
		'invokables' => [
			'Primary' => \Iaasen\Form\Element\Primary::class,
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
