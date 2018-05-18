<?php
namespace Drahak\OAuth2\Grant;

use Drahak\OAuth2\InvalidStateException;
use Nette\SmartObject;

/**
 * GrantContext
 * @package Drahak\OAuth2\Grant
 * @author Drahomír Hanák
 */
class GrantContext
{
    use SmartObject;

	/** @var array */
	private $grantTypes = array();

	/**
	 * Add grant type
	 * @param IGrant $grantType
	 */
	public function addGrantType(IGrant $grantType)
	{
		$this->grantTypes[$grantType->getIdentifier()] = $grantType;
	}

	/**
	 * Remove grant type from strategy context
	 * @param string $grantType
	 */
	public function removeGrantType($grantType)
	{
		unset($this->grantTypes[$grantType]);
	}

	/**
	 * Get grant type
	 * @param string $grantType
	 * @return GrantType
	 *
	 * @throws InvalidStateException
	 */
	public function getGrantType($grantType)
	{
		if (!isset($this->grantTypes[$grantType])) {
			throw new InvalidStateException('Grant type ' . $grantType . ' is not registered in GrantContext');
		}
		return $this->grantTypes[$grantType];
	}

}
