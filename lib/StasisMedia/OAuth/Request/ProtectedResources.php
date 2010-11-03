<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Parameter;
use StasisMedia\OAuth;

class ProtectedResources extends Request
{
    public function setAccessCredential(OAuth\Credential\Access $credential)
    {
        $this->setOAuthParameter(new Parameter\Parameter('oauth_token', $credential->getToken()));
    }
}