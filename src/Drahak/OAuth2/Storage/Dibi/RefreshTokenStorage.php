<?php
namespace Drahak\OAuth2\Storage\Dibi;

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

	/** @var \DibiConnection */
	private $context;

	public function __construct(\DibiConnection $context)
	{
		$this->context = $context;
	}

	/**
	 * Get authorization code table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return 'oauth_refresh_token';
	}

	/******************** IRefreshTokenStorage ********************/

	/**
	 * Store refresh token
	 * @param IRefreshToken $refreshToken
	 */
	public function store(IRefreshToken $refreshToken)
	{
		$this->context->insert($this->getTable(), array(
			'refresh_token' => $refreshToken->getRefreshToken(),
			'client_id' => $refreshToken->getClientId(),
			'user_id' => $refreshToken->getUserId(),
			'expires_at' => $refreshToken->getExpires()
		))->execute();
	}

	/**
	 * Remove refresh token
	 * @param string $refreshToken
	 */
	public function remove($refreshToken)
	{
		$this->context->delete($this->getTable())->where(array('refresh_token' => $refreshToken))->execute();
	}

	/**
	 * Get valid refresh token
	 * @param string $refreshToken
	 * @return IRefreshToken|NULL
	 */
	public function getValidRefreshToken($refreshToken)
	{
		$row = $this->context->select('*')->from($this->getTable())
			->where('refresh_token = %s', $refreshToken)
			->where('TIMEDIFF(expires_at, NOW()) >= 0')
			->fetch();

		if (!$row) return NULL;

		return new RefreshToken(
			$row['refresh_token'],
			new \DateTime($row['expires_at']),
			$row['client_id'],
			$row['user_id']
		);
	}

}