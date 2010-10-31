<?php
namespace StasisMedia\OAuth\Request;

use StasisMedia\OAuth\Parameter;

class RequestToken extends Request
{
    public function setCallbackUrl($url)
    {
        $this->setOAuthParameter(new Parameter\Parameter('oauth_callback_url', $url));
    }

}