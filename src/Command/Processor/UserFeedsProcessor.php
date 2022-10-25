<?php

namespace App\Command\Processor;

use App\Command\Configuration\AppFetchfeedsConfiguration;
use App\Command\Processor\FeedProcessor;
use App\Command\Exception\FeedProcessException;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UserFeedsProcessor
{

	private $conf;

	private $io;

	private $errors = [];

	public function __construct(AppFetchfeedsConfiguration $configuration, private User $user, private MailerInterface $mailer)
	{
		$this->conf = $configuration;

		$this->io = $this->conf->getIo();
	}

	public function run()
	{

		$this->io->section('Process user : ' . $this->user->getEmail());

		foreach ($this->user->getFeeds() as $feed) {
			$this->_processFeed($feed);
		}

		if ($this->user->getGroupErrorMail() && !empty($this->errors)) {
			$this->sendErrorMail(
				$this->user->getEmail(),
				'Erreur de lecture de flux RSS',
				implode("\n\n", $this->errors)
			);
		}
	}

	private function _processFeed($feed)
	{

		try {
			(new FeedProcessor($this->conf, $feed, $this->mailer))->run();
		} catch (FeedProcessException $e) {
			$this->io->caution($e->getMessage());
			$this->io->caution($e->getTraceAsString());

			$this->handleError($feed, $e->getPrevious());
		} finally {
			$this->conf->getDoctrine()->persist($feed);
		}
	}

	private function handleError($feed, $err)
	{

		$details = 'Error with feed ' . $feed->getUrl() . ' : ' . $err->getMessage() .
			($err instanceof \FeedIo\Reader\ReadErrorException) ? "\n" . $err->getTraceAsString() : '';

		if ($this->user->getSendEmailOnError() && $this->user->getGroupErrorMail()) {
			$this->errors[$feed->getId()] = $details;
		} elseif ($this->user->getSendEmailOnError()) {
			$this->sendErrorMail(
				$this->user->getEmail(),
				'Erreur de lecture de flux RSS',
				'Flux : ' . $feed->getUrl() . "\n" .
					$details
			);
		} else {
			// Nothing
			$this->io->note('The user don\'t want to be notified by mail');
		}
	}

	private function sendErrorMail($email, $title, $message)
	{

		if ($this->conf->isDryRun()) {
			echo "Error mail :\n";
			echo $message . "\n";
			return;
		}

		$body = $this->conf->getTwig()->render(
			'emails/error.html.twig',
			[
				'title' => $title,
				'message' => $message
			]
		);

		$email = (new Email())->to($email)->html($body);
		$this->mailer->send($email);
	}
}
