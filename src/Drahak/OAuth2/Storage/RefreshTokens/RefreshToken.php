<?php
namespace Drahak\OAuth2\Storage\RefreshTokens;

use DateTime;
use Nette\SmartObject;

/**
 * RefreshToken
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 *
 * @property-read string $refreshToken
 * @property-read DateTime $expires
 * @property-read string|int $clientId
 */
class RefreshToken implements IRefreshToken
{
    use SmartObject;

	/** @var string */
	private $refreshToken;

	/** @var \DateTime */
	private $expires;

	/** @var string|int */
	private $clientId;

	/** @var string|int */
	private $userId;

	public function __construct($refreshToken, DateTime $expires, $clientId, $userId)
	{
		$this->refreshToken = $refreshToken;
		$this->clientId = $clientId;
		$this->expires = $expires;
		$this->userId = $userId;
	}

	/**
	 * Get refresh token
	 * @return string
	 */
	public function getRefreshToken()
	{
		return $this->refreshToken;
	}

	/**
	 * Get expire time
	 * @return \DateTime
	 */
	public function getExpires()
	{
		return $this->expires;
	}

	/**
	 * Get client id
	 * @return string|int
	 */
	public function getClientId()
	{
		return $this->clientId;
	}


	/**
	 * @return int|string
	 */
	public function getUserId()
	{
		return $this->userId;
	}

}