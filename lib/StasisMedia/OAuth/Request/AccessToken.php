<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Credential;
use StasisMedia\OAuth\Parameter;

class AccessToken extends Request
{
    public function setRequestCredential(Credential\Request $credential)
    {
        $this->setOAuthParameter(new Parameter\Parameter('oauth_token', $credential->getToken()));
    }

    public function setVerifier($verifier)
    {
        $this->setOAuthParameter(new Parameter\Parameter('oauth_verifier', $verifier));
    }

}