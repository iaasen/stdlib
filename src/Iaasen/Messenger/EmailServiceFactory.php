<?php
/**
 * User: iaase
 * Date: 18.04.2018
 * Time: 21:06
 */

namespace Iaasen\Messenger;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EmailServiceFactory implements FactoryInterface
{
	/**
	 * Create an object
	 *
	 * @param ContainerInterface $container
	 * @param  string $requestedName
	 * @param  null|array $options
	 * @return object
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('Config')['messenger']['email'];
		$transport = $config['transport'];

		if($config['transport']['method'] == 'smtp') {
			$swiftTransport = new \Swift_SmtpTransport($transport['host'], $transport['port'], $transport['security']);
		}
		else throw new \InvalidArgumentException("Only method 'smtp' is defined");

		if(strlen($transport['username'])) $swiftTransport->setUsername($transport['username']);
		if(strlen($transport['password'])) $swiftTransport->setPassword($transport['password']);

		return new EmailService($swiftTransport, $config['from']);
	}
}