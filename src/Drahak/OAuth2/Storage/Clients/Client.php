<?php
namespace Drahak\OAuth2\Storage\Clients;

use Nette\SmartObject;

/**
 * OAuth2 base client caret
 * @package Drahak\OAuth2\Storage\Entity
 * @author Drahomír Hanák
 *
 * @property-read string|int $id
 * @property-read string $secret
 * @property-read string $redirectUrl
 */
class Client implements IClient
{
    use SmartObject;

	/** @var string|int */
	private $id;

	/** @var string */
	private $secret;

	/** @var string */
	private $redirectUrl;

	public function __construct($id, $secret, $redirectUrl)
	{
		$this->id = $id;
		$this->secret = $secret;
		$this->redirectUrl = $redirectUrl;
	}

	/**
	 * @return int|string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}

	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}

}