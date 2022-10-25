<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;

class GuzzleForFeedIoFactory
{
	public static function create(): Client
	{

		$stack = HandlerStack::create();
		$stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
			$utf8Stream = new GuzzleForceUTF8Stream($response->getBody());
			return $response->withBody($utf8Stream);
		}));

		$client = new Client(['handler' => $stack]);

		return $client;
	}
}
