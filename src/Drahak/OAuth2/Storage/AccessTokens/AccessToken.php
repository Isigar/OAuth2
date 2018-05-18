<?php
namespace Drahak\OAuth2\Storage\AccessTokens;

use DateTime;
use Nette\SmartObject;

/**
 * Base AccessToken entity
 * @package Drahak\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 *
 * @property-read string $accessToken
 * @property-read DateTime $expires
 * @property-read string|int $clientId
 * @property-read array $scope
 */
class AccessToken implements IAccessToken
{
    use SmartObject;

	/** @var string */
	private $accessToken;

	/** @var DateTime */
	private $expires;

	/** @var string|int */
	private $clientId;

	/** @var string|int */
	private $userId;

	/** @var array */
	private $scope;

	public function __construct($accessToken, DateTime $expires, $clientId, $userId, array $scope)
	{
		$this->accessToken = $accessToken;
		$this->expires = $expires;
		$this->clientId = $clientId;
		$this->userId = $userId;
		$this->scope = $scope;
	}

	/**
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @return int|string
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

	/**
	 * @return \DateTime
	 */
	public function getExpires()
	{
		return $this->expires;
	}

	/**
	 * @return array
	 */
	public function getScope()
	{
		return $this->scope;
	}

}