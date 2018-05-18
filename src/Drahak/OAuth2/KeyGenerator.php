<?php
namespace Drahak\OAuth2;

use Nette\SmartObject;

/**
 * KeyGenerator
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
class KeyGenerator implements IKeyGenerator
{
    use SmartObject;

	/** Key generator algorithm */
	const ALGORITHM = 'sha256';

	/**
	 * Generate random token
	 * @param int $length
	 * @return string
	 */
	public function generate($length = 40)
	{
		$bytes = openssl_random_pseudo_bytes($length);
		return hash(self::ALGORITHM, $bytes);
	}

}