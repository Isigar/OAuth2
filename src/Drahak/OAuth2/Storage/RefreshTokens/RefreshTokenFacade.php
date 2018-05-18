<?php
namespace Drahak\OAuth2\Storage\RefreshTokens;

use Drahak\OAuth2\IKeyGenerator;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\InvalidRefreshTokenException;
use Drahak\OAuth2\Storage\ITokenFacade;
use Nette\SmartObject;

/**
 * RefreshToken
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 *
 * @property-read int $lifetime
 * @property-read IRefreshTokenStorage $storage
 */
class RefreshTokenFacade implements ITokenFacade
{
    use SmartObject;

	/** @var IRefreshTokenStorage */
	private $storage;

	/** @var IKeyGenerator */
	private $keyGenerator;

	/** @var int */
	private $lifetime;

	public function __construct($lifetime, IKeyGenerator $keyGenerator, IRefreshTokenStorage $storage)
	{
		$this->lifetime = $lifetime;
		$this->storage = $storage;
		$this->keyGenerator = $keyGenerator;
	}

	/**
	 * Create new refresh token
	 * @param IClient $client
	 * @param int $userId
	 * @param array $scope
	 * @return RefreshToken
	 */
	public function create(IClient $client, $userId, array $scope = array())
	{
		$expires = new \DateTime;
		$expires->modify('+' . $this->lifetime . ' seconds');
		$refreshToken = new RefreshToken(
			$this->keyGenerator->generate(),
			$expires,
			$client->getId(),
			$userId
		);
		$this->storage->store($refreshToken);

		return $refreshToken;
	}

	/**
	 * Get refresh token entity
	 * @param string $refreshToken
	 * @return IRefreshToken|NULL
	 *
	 * @throws InvalidRefreshTokenException
	 */
	public function getEntity($refreshToken)
	{
		$entity = $this->storage->getValidRefreshToken($refreshToken);
		if (!$entity) {
			$this->storage->remove($refreshToken);
			throw new InvalidRefreshTokenException;
		}
		return $entity;
	}

	/**
	 * Get token identifier name
	 * @return string
	 */
	public function getIdentifier()
	{
		return self::REFRESH_TOKEN;
	}


	/****************** Getters & setters ******************/

	/**
	 * Get token lifetime
	 * @return int
	 */
	public function getLifetime()
	{
		return $this->lifetime;
	}

	/**
	 * Get storage
	 * @return IRefreshTokenStorage
	 */
	public function getStorage()
	{
		return $this->storage;
	}

}