<?php
namespace Drahak\OAuth2\Storage\NDB;

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
		return $this->context->table('oauth_access_token');
	}

	/**
	 * Get scope table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getScopeTable()
	{
		return $this->context->table('oauth_access_token_scope');
	}

	/******************** IAccessTokenStorage ********************/

	/**
	 * Store access token
	 * @param IAccessToken $accessToken
	 * @throws InvalidScopeException
	 */
	public function store(IAccessToken $accessToken)
	{
		$connection = $this->getTable()->getConnection();
		$connection->beginTransaction();
		$this->getTable()->insert(array(
			'access_token' => $accessToken->getAccessToken(),
			'client_id' => $accessToken->getClientId(),
			'user_id' => $accessToken->getUserId(),
			'expires' => $accessToken->getExpires()
		));

		try {
			foreach ($accessToken->getScope() as $scope) {
				$this->getScopeTable()->insert(array(
					'access_token' => $accessToken->getAccessToken(),
					'scope_name' => $scope
				));
			}
		} catch (\PDOException $e) {
			// MySQL error 1452 - Cannot add or update a child row: a foreign key constraint fails
			if (in_array(1452, $e->errorInfo)) {
				throw new InvalidScopeException;
			}
			throw $e;
		}
		$connection->commit();
	}

	/**
	 * Remove access token
	 * @param string $accessToken
	 */
	public function remove($accessToken)
	{
		$this->getTable()->where(array('access_token' => $accessToken))->delete();
	}

	/**
	 * Get valid access token
	 * @param string $accessToken
	 * @return IAccessToken|NULL
	 */
	public function getValidAccessToken($accessToken)
	{
		/** @var ActiveRow $row */
		$row = $this->getTable()
			->where(array('access_token' => $accessToken))
			->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
			->fetch();

		if (!$row) return NULL;

		$scopes = $this->getScopeTable()
			->where(array('access_token' => $accessToken))
			->fetchPairs('scope_name');

		return new AccessToken(
			$row['access_token'],
			new \DateTime($row['expires']),
			$row['client_id'],
			$row['user_id'],
			array_keys($scopes)
		);
	}


}