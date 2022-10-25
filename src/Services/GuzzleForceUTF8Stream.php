<?php declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

use \ForceUTF8\Encoding;

class GuzzleForceUTF8Stream implements StreamInterface {

  use StreamDecoratorTrait {
	  getContents as getContentsTrait;
  }

  public function getContents() {

	$contents = (string) $this->getContentsTrait();
	
	try {
		$invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
		$contents = preg_replace($invalid_characters, '', $contents );
		$utf8_string = Encoding::toUTF8($contents);
	} catch(\Exception $e) {
		$utf8_string = $contents;
	} finally {
		return $utf8_string;
	}

  }

}
