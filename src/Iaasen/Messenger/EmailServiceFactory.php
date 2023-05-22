<?php
/**
 * User: iaase
 * Date: 18.04.2018
 */

namespace Iaasen\Messenger;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class EmailServiceFactory implements FactoryInterface
{
	/**
	 * @param ContainerInterface $container
	 * @param  string $requestedName
	 * @param  null|array $options
	 * @return EmailService
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('Config')['messenger']['email'];
		$transportConfig = $config['transport'];

		if($config['transport']['method'] == 'smtp') {
			if($transportConfig['username']) $transport = Transport::fromDsn('smtp://' . $transportConfig['username'] . ':' . $transportConfig['password'] . '@' . $transportConfig['host'] . ':' . $transportConfig['port'] ?? 25);
			else $transport = Transport::fromDsn('smtp://' . $transportConfig['host'] . ':' . $transportConfig['port'] ?? 25);
		}
		else throw new \InvalidArgumentException("Only method 'smtp' is available");

		$mailer = new Mailer($transport);
		$defaultFrom = (isset($config['from'])) ? $config['from'] : null;
		return new EmailService($mailer, $defaultFrom);
	}
}