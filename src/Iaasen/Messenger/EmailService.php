<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 18.04.2018
 * Time: 21:05
 */

namespace Iaasen\Messenger;


class EmailService
{
	/** @var  \Swift_SmtpTransport */
	protected $transport;
	/** @var \Swift_Mailer  */
	protected $mailer;
	/** @var  string */
	protected $default_from = 'ikkesvar@nteb.no';

	public function __construct($transport, $defaultFrom = null)
	{
		$this->transport = $transport;
		$this->mailer = new \Swift_Mailer($transport);
		if($defaultFrom) $this->default_from = $defaultFrom;
	}

	/**
	 * @param Email $email
	 */
	public function send($email) {
		$swiftMessage = new \Swift_Message($email->subject);

		if(!strlen($email->from)) $email->from = $this->default_from;
		$swiftMessage->setFrom($this->default_from);

		$swiftMessage->setBody($email->body);
		if($email->body_html) $swiftMessage->addPart($email->body_html, 'text/html');

		try {
			foreach($email->to AS $to) {
				$swiftMessage->addTo($to);
			}
			$result = $this->mailer->send($swiftMessage);
		}
		catch(\Swift_RfcComplianceException  $e) {
			throw new \InvalidArgumentException('Malformed email address', 400);
		}
		catch(\Swift_TransportException $e) {
			throw new \Exception('Unable to send email. Is the server unavailable?', 503);
		}

		return ($result > 0);
	}
}