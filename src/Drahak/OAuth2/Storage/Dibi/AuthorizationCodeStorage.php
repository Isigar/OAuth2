<?php
namespace Drahak\OAuth2\Storage\Dibi;

use Drahak\OAuth2\InvalidScopeException;
use Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCode;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCodeStorage;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

/**
 * AuthorizationCode
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Martin Malek
 */
class AuthorizationCodeStorage implements IAuthorizationCodeStorage
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
		return 'oauth_authorization_code';
	}

	/**
	 * Get scope table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getScopeTable()
	{
		return 'oauth_authorization_code_scope';
	}

	/******************** IAuthorizationCodeStorage ********************/

	/**
	 * Store authorization code
	 * @param IAuthorizationCode $authorizationCode
	 * @throws InvalidScopeException
	 */
	public function store(IAuthorizationCode $authorizationCode)
	{
		$this->context->insert($this->getTable(), array(
			'authorization_code' => $authorizationCode->getAuthorizationCode(),
			'client_id' => $authorizationCode->getClientId(),
			'user_id' => $authorizationCode->getUserId(),
			'expires_at' => $authorizationCode->getExpires()
		))->execute();

		$this->context->begin();
		try {
			foreach ($authorizationCode->getScope() as $scope) {
				$this->context->insert($this->getScopeTable(), array(
					'authorization_code' => $authorizationCode->getAuthorizationCode(),
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
	 * Remove authorization code
	 * @param string $authorizationCode
	 * @return void
	 */
	public function remove($authorizationCode)
	{
		$this->context->delete($this->getTable())->where('authorization_code = %s', $authorizationCode)->execute();
	}

	/**
	 * Validate authorization code
	 * @param string $authorizationCode
	 * @return IAuthorizationCode
	 */
	public function getValidAuthorizationCode($authorizationCode)
	{
		/** @var ActiveRow $row */
		$row = $this->context->select('*')->from($this->getTable())
			->where('authorization_code = %s', $authorizationCode)
			->where('TIMEDIFF(expires_at, NOW()) >= 0')
			->fetch();

		if (!$row) return NULL;

		$scopes = $this->context->select('*')->from($this->getScopeTable())
			->where('authorization_code = %s', $authorizationCode)
			->fetchPairs('scope_name');

		return new AuthorizationCode(
			$row['authorization_code'],
			new \DateTime($row['expires_at']),
			$row['client_id'],
			$row['user_id'],
			array_keys($scopes)
		);
	}


}