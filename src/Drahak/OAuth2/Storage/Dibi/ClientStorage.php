<?php
namespace Drahak\OAuth2\Storage\Dibi;

use Drahak\OAuth2\Storage\Clients\Client;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Nette\Database\Context;
use Nette\SmartObject;

/**
 * Nette database client storage
 * @package Drahak\OAuth2\Storage\Clients
 * @author Drahomír Hanák
 */
class ClientStorage implements IClientStorage
{
    use SmartObject;

	/** @var \DibiConnection */
	private $context;

	public function __construct(\DibiConnection $context)
	{
		$this->context = $context;
	}

	/**
	 * Get client table selection
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return 'oauth_client';
	}

	/**
	 * Find client by ID and/or secret key
	 * @param string $clientId
	 * @param string|null $clientSecret
	 * @return IClient
	 */
	public function getClient($clientId, $clientSecret = NULL)
	{
		if (!$clientId) return NULL;

		$selection = $this->context->select('*')->from($this->getTable())->where('client_id = %s', $clientId);
		if ($clientSecret) {
			$selection->where('secret = %s', $clientSecret);
		}
		$data = $selection->fetch();
		if (!$data) return NULL;
		return new Client($data['client_id'], $data['secret'], $data['redirect_url']);
	}

	/**
	 * Can client use given grant type
	 * @param string $clientId
	 * @param string $grantType
	 * @return bool
	 */
	public function canUseGrantType($clientId, $grantType)
	{
		$result = $this->context->query('
			SELECT g.name
			FROM oauth_client_grant AS cg
			RIGHT JOIN oauth_grant AS g ON cg.grant_id = cg.grant_id AND g.name = %s
			WHERE cg.client_id = %i
		', $grantType, $clientId);

		return (bool) $result->fetch();
	}
}