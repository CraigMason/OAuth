<?php
namespace StasisMedia\OAuth\Request;

class MockRequest extends Request implements RequestInterface
{
    public function parseResponse(\StasisMedia\OAuth\Response\HTTP $response)
    {
    }

}