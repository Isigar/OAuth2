<?php
namespace Drahak\OAuth2\Storage\NDB;

use Drahak\OAuth2\Storage\RefreshTokens\IRefreshToken;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshTokenStorage;
use Drahak\OAuth2\Storage\RefreshTokens\RefreshToken;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\SmartObject;

/**
 * Nette database RefreshToken storage
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshTokenStorage implements IRefreshTokenStorage
{
    use SmartObject;

	/** @var Context */
	private $context;

	public function __construct(Context $context)
	{
		$this->context = $context;
	}

	/**
	 * Get authorization code table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table('oauth_refresh_token');
	}

	/******************** IRefreshTokenStorage ********************/

	/**
	 * Store refresh token
	 * @param IRefreshToken $refreshToken
	 */
	public function store(IRefreshToken $refreshToken)
	{
		$this->getTable()->insert(array(
			'refresh_token' => $refreshToken->getRefreshToken(),
			'client_id' => $refreshToken->getClientId(),
			'user_id' => $refreshToken->getUserId(),
			'expires' => $refreshToken->getExpires()
		));
	}

	/**
	 * Remove refresh token
	 * @param string $refreshToken
	 */
	public function remove($refreshToken)
	{
		$this->getTable()->where(array('refresh_token' => $refreshToken))->delete();
	}

	/**
	 * Get valid refresh token
	 * @param string $refreshToken
	 * @return IRefreshToken|NULL
	 */
	public function getValidRefreshToken($refreshToken)
	{
		$row = $this->getTable()
			->where(array('refresh_token' => $refreshToken))
			->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
			->fetch();

		if (!$row) return NULL;

		return new RefreshToken(
			$row['refresh_token'],
			new \DateTime($row['expires']),
			$row['client_id'],
			$row['user_id']
		);
	}

}