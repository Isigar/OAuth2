<?php
namespace Drahak\OAuth2\Storage\Dibi;

use Drahak\OAuth2\InvalidScopeException;
use Drahak\OAuth2\Storage\AccessTokens\AccessToken;
use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Drahak\OAuth2\Storage\AccessTokens\IAccessTokenStorage;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

/**
 * AccessTokenStorage
 * @package Drahak\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
class AccessTokenStorage implements IAccessTokenStorage
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
		return 'oauth_access_token';
	}

	/**
	 * Get scope table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getScopeTable()
	{
		return 'oauth_access_token_scope';
	}

	/******************** IAccessTokenStorage ********************/

	/**
	 * Store access token
	 * @param IAccessToken $accessToken
	 * @throws InvalidScopeException
	 */
	public function store(IAccessToken $accessToken)
	{
		$this->context->begin();
		$this->context->insert($this->getTable(), array(
			'access_token' => $accessToken->getAccessToken(),
			'client_id' => $accessToken->getClientId(),
			'user_id' => $accessToken->getUserId(),
			'expires_at' => $accessToken->getExpires()
		))->execute();

		try {
			foreach ($accessToken->getScope() as $scope) {
				$this->context->insert($this->getScopeTable(), array(
					'access_token' => $accessToken->getAccessToken(),
					'scope_name' => $scope
				))->execute();
			}
		} catch (\PDOException $e) {
			// MySQL error 1452 - Cannot add or update a child row: a foreign key constraint fails
			if (in_array(1452, $e->errorInfo)) {
				throw new InvalidScopeException;
			}
			throw $e;
		}
		$this->context->commit();
	}

	/**
	 * Remove access token
	 * @param string $accessToken
	 */
	public function remove($accessToken)
	{
		//$this->context->delete($this->getTable())->where('access_token = %s', $accessToken)->execute();
	}

	/**
	 * Get valid access token
	 * @param string $accessToken
	 * @return IAccessToken|NULL
	 */
	public function getValidAccessToken($accessToken)
	{
		/** @var ActiveRow $row */
		$row = $this->context->select('*')->from($this->getTable())
			->where('access_token = %s', $accessToken)
			->where('TIMEDIFF(expires_at, NOW()) >= 0')
			->fetch();

		if (!$row) return NULL;

		$scopes = $this->context->select('*')->from($this->getScopeTable())
			->where('access_token = %s', $accessToken)
			->fetchPairs('scope_name');

		return new AccessToken(
			$row['access_token'],
			new \DateTime($row['expires_at']),
			$row['client_id'],
			$row['user_id'],
			array_keys($scopes)
		);
	}


}