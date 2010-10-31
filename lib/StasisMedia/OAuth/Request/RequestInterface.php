<?php
namespace StasisMedia\OAuth\Request;

interface RequestInterface
{
    /**
     * Generate the base string http://tools.ietf.org/html/rfc5849#section-3.4.1
     * 
     * @return string 
     */
    public function getBaseString($signatureMethod);
}
