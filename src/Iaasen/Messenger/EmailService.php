<?php
/**
 * User: iaase
 * Date: 18.04.2018
 */

namespace Iaasen\Messenger;


use Iaasen\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Mailer;

class EmailService {
	protected Mailer $mailer;
	protected ?string $default_from = null;

	public function __construct(Mailer $mailer, ?string $defaultFrom = null) {
		$this->mailer = $mailer;
		$this->default_from = $defaultFrom;
	}


	public function send(Email $email) : bool {
		if(!$email->from && !$this->default_from) throw new InvalidArgumentException('Email is missing a from address, and there is no default from address');

		$symfonyEmail = new \Symfony\Component\Mime\Email();
		$symfonyEmail
			->from($email->from ?? $this->default_from)
			->subject($email->subject)
			->text($email->body)
			->html($email->body_html);
		foreach($email->to AS $to) {
			$symfonyEmail->addTo($to);
		}
		$this->mailer->send($symfonyEmail);
		return true;
	}
}