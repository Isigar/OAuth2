<?php
namespace Drahak\OAuth2\Storage\AccessTokens;

use Drahak\OAuth2\IKeyGenerator;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\InvalidAccessTokenException;
use Drahak\OAuth2\Storage\ITokenFacade;
use Nette\SmartObject;

/**
 * AccessToken
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 *
 * @property-read int $lifetime
 * @property-read IAccessTokenStorage $storage
 */
class AccessTokenFacade implements ITokenFacade
{
    use SmartObject;

	/** @var IAccessTokenStorage */
	private $storage;

	/** @var IKeyGenerator */
	private $keyGenerator;

	/** @var int */
	private $lifetime;

	public function __construct($lifetime, IKeyGenerator $keyGenerator, IAccessTokenStorage $accessToken)
	{
		$this->lifetime = $lifetime;
		$this->keyGenerator = $keyGenerator;
		$this->storage = $accessToken;
	}

	/**
	 * Create access token
	 * @param IClient $client
	 * @param string|int $userId
	 * @param array $scope
	 * @return AccessToken
	 */
	public function create(IClient $client, $userId, array $scope = array())
	{
		$accessExpires = new \DateTime;
		$accessExpires->modify('+' . $this->lifetime . ' seconds');

		$accessToken = new AccessToken(
			$this->keyGenerator->generate(),
			$accessExpires,
			$client->getId(),
			$userId,
			$scope
		);
		$this->storage->store($accessToken);

		return $accessToken;
	}

	/**
	 * Check access token
	 * @param string $accessToken
	 * @return IAccessToken|NULL
	 *
	 * @throws InvalidAccessTokenException
	 */
	public function getEntity($accessToken)
	{
		$entity = $this->storage->getValidAccessToken($accessToken);
		if (!$entity) {
			$this->storage->remove($accessToken);
			throw new InvalidAccessTokenException;
		}
		return $entity;
	}

	/**
	 * Get token identifier name
	 * @return string
	 */
	public function getIdentifier()
	{
		return self::ACCESS_TOKEN;
	}


	/******************** Getters & setters ********************/

	/**
	 * Returns access token lifetime
	 * @return int
	 */
	public function getLifetime()
	{
		return $this->lifetime;
	}

	/**
	 * Get access token storage
	 * @return IAccessTokenStorage
	 */
	public function getStorage()
	{
		return $this->storage;
	}

}