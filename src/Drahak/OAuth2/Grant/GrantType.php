<?php
namespace Drahak\OAuth2\Grant;

use Drahak\OAuth2\Http\IInput;
use Drahak\OAuth2\InvalidClientException;
use Drahak\OAuth2\Storage\AccessToken;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Drahak\OAuth2\Storage\RefreshTokenFacade;
use Drahak\OAuth2\Storage\TokenContext;
use Drahak\OAuth2\UnauthorizedClientException;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * GrantType
 * @package Drahak\OAuth2\Grant
 * @author Drahomír Hanák
 *
 * @property-read string $identifier
 */
abstract class GrantType implements IGrant
{
    use SmartObject;

	const SCOPE_KEY = 'scope';
	const CLIENT_ID_KEY = 'client_id';
	const CLIENT_SECRET_KEY = 'client_secret';
	const GRANT_TYPE_KEY = 'grant_type';

	/** @var IClientStorage */
	protected $clientStorage;

	/** @var TokenContext */
	protected $token;

	/** @var IInput */
	protected $input;

	/** @var User */
	protected $user;

	/** @var IClient */
	private $client;

	/**
	 * @param IInput $input
	 * @param TokenContext $token
	 * @param IClientStorage $clientStorage
	 * @param User $user
	 */
	public function __construct(IInput $input, TokenContext $token, IClientStorage $clientStorage, User $user)
	{
		$this->user = $user;
		$this->input = $input;
		$this->token = $token;
		$this->clientStorage = $clientStorage;
	}

	/**
	 * Get client
	 * @return IClient
	 */
	protected function getClient()
	{
		if (!$this->client) {
			$clientId = $this->input->getParameter(self::CLIENT_ID_KEY);
			$clientSecret = $this->input->getParameter(self::CLIENT_SECRET_KEY);
			$this->client = $this->clientStorage->getClient($clientId, $clientSecret);
		}
		return $this->client;
	}

	/**
	 * Get scope as array - allowed separators: ',' AND ' '
	 * @return array
	 */
	protected function getScope()
	{
		$scope = $this->input->getParameter(self::SCOPE_KEY);
		return !is_array($scope) ?
			array_filter(explode(',', str_replace(' ', ',', $scope))) :
			$scope;
	}

	/****************** IGrant interface ******************/

	/**
	 * Get access token
	 * @return string
	 * @throws UnauthorizedClientException
	 */
	public final function getAccessToken()
	{
		if (!$this->getClient()) {
			throw new UnauthorizedClientException('Client is not found');
		}

		$this->verifyGrantType();
		$this->verifyRequest();
		return $this->generateAccessToken();
	}

	/****************** Access token template methods ******************/

	/**
	 * Verify grant type
	 * @throws UnauthorizedClientException
	 * @throws InvalidGrantTypeException
	 */
	protected function verifyGrantType()
	{
		$grantType = $this->input->getParameter(self::GRANT_TYPE_KEY);
		if (!$grantType) {
			throw new InvalidGrantTypeException;
		}

		if (!$this->clientStorage->canUseGrantType($this->getClient()->getId(), $grantType)) {
			throw new UnauthorizedClientException;
		}
	}

	/**
	 * Verify request
	 * @return void
	 */
	protected abstract function verifyRequest();

	/**
	 * Generate access token
	 * @return string
	 */
	protected abstract function generateAccessToken();

}