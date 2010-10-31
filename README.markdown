OAuth 1.0 (rfc5849) PHP 5.3
===========================

Craig Mason < craig.mason@stasismedia.com >

A PHP 5.3+ **only** implementation of the OAuth 1.0 protocol
http://tools.ietf.org/html/rfc5849

This library does not handle the creation of CURL or other HTTP connections. It
will generate signatures and "Authorization: OAuth" headers which can then be
easily used in a CURL or Socket connection.

The library does not store any keys, tokens or secrets. That responsibility lives
with the application.

Example
-------

    use StasisMedia\OAuth;

    $consumerCredential = new OAuth\Credential\Consumer('key', 'secret');
    $request = new OAuth\Request\RequestToken($consumerCredential, 'http://example.com/request_token');
    $signature = new OAuth\Signature\HMAC_SHA1($request, $consumerCredential);

    $authHeader = $request->getAuthorizationHeader('realm', $signature->generateSignature() );

    echo $authHeader;
    /*
     * Authorization: OAuth realm="realm", oauth_consumer_key="key",
     * oauth_timestamp="1288534693", oauth_nonce="b0d75745b33346972e8cab2129f33bb5",
     * oauth_version="1.0", oauth_signature_method="HMAC-SHA1",
     * oauth_signature="%2B7mFGdwyRvMdQR1o%2FxJqyBcscpE%3D"
     */