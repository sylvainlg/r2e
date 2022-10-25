<?php

namespace App\Command\Configuration;

use \Symfony\Component\Console\Style\SymfonyStyle;

class AppFetchfeedsConfiguration {

	private $_io;
	private $dryRun;
	private $feedId;
	private $userEmail;

	private $doctrine;
	private $twig;

	public function __construct(
		SymfonyStyle $io,
		\Doctrine\ORM\EntityManagerInterface $pDoctrine,
		\Twig\Environment $pTwig,
		bool $dryRun = false,
		$feedId = null,
		$userEmail = null
	) {
		$this->_io = $io;
		$this->dryRun = $dryRun;
		$this->feedId = $feedId;
		$this->userEmail = $userEmail;

		$this->doctrine = $pDoctrine;
		$this->twig = $pTwig;
	}

	public function getIo(): SymfonyStyle {
		return $this->_io;
	}

	public function isDryRun(): bool {
		return $this->dryRun;
	}

	public function getFeedId() {
		return $this->feedId;
	}

	public function getUserEmail(): ?string {
		return $this->userEmail;
	}

	public function getDoctrine(): \Doctrine\ORM\EntityManagerInterface {
		return $this->doctrine;
	}

	public function getTwig(): \Twig\Environment {
		return $this->twig;
	}

}