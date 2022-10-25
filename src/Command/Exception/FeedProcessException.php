<?php

namespace App\Command\Exception;

/**
 * Exception throw when FeedProcessor failed
 */
class FeedProcessException extends \Exception {

	public function __construct($message, $exception) {
		parent::__construct($message, 0, $exception);
	}

}