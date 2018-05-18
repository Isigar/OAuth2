<?php
namespace Drahak\OAuth2\Storage;

use Drahak\OAuth2\InvalidStateException;
use Nette\SmartObject;

/**
 * TokenContext
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 */
class TokenContext
{
    use SmartObject;

	/** @var array */
	private $tokens = array();

	/**
	 * Add identifier to collection
	 * @param ITokenFacade $token
	 */
	public function addToken(ITokenFacade $token)
	{
		$this->tokens[$token->getIdentifier()] = $token;
	}

	/**
	 * Get token
	 * @param string $identifier
	 * @return ITokenFacade
	 *
	 * @throws InvalidStateException
	 */
	public function getToken($identifier)
	{
		if 	(!isset($this->tokens[$identifier])) {
			throw new InvalidStateException('Token called "' . $identifier . '" not found in Token context');
		}

		return $this->tokens[$identifier];
	}

}