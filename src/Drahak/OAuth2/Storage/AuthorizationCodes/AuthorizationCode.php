<?php
namespace Drahak\OAuth2\Storage\AuthorizationCodes;

use DateTime;
use Nette\SmartObject;

/**
 * Base AuthorizationCode entity
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 *
 * @property-read string $authorizationCode
 * @property-read DateTime $expires
 * @property-read string|int $clientId
 * @property-read array $scope
 */
class AuthorizationCode implements IAuthorizationCode
{
    use SmartObject;

	/** @var string */
	private $authorizationCode;

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
		$this->authorizationCode = $accessToken;
		$this->expires = $expires;
		$this->clientId = $clientId;
		$this->userId = $userId;
		$this->scope = $scope;
	}

	/**
	 * @return string
	 */
	public function getAuthorizationCode()
	{
		return $this->authorizationCode;
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