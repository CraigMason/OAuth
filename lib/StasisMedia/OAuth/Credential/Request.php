<?php
namespace StasisMedia\OAuth\Credential;

/**
 * Request(Temporary Credential):
 *   An unauthorized Token and Secret issued by the Service Provider
 *
 * The Request Credential is exchanged for an Access Credential (Token)
 * upon authorization by the User
 *
 * @author  Craig Mason <craig.mason@stasismedia.com>
 * @package OAuth
 * @version 1.0
 */
class Request extends Token
{}