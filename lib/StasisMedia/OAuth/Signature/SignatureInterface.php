<?php
namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request;

interface SignatureInterface
{
    /**
     * @return string
     */
    public function generateSignature();
}