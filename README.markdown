OAuth 1.0 (rfc5849) PHP 5.3
===========================

*by Craig Mason <craig.mason@stasismedia.com>*

A dependency-injected PHP 5.3+ **only** implementation of the OAuth 1.0 protocol.
Based on the specification available at http://tools.ietf.org/html/rfc5849

This library is in development, and is currently an 'experimental' release.
Things _will_ change.


Overview
--------

The general use of this library is as follow:

1. Create your Credential instances (Consumer, TemporaryAccess or Access)
2. Create a Request instance (TemporaryCredentials, TokenCredentials or AccessResource)
  * Set any Request-specific options (Credentials, callback URL etc)
3. Create a Signature instance
4. Create a Connector instance
5. Create a Client instance
6. Execute Client
7. Retrieve Response from Client

Each stage will require the injection of certain previous objects. Check the PHPDOC
for the required parameters.


Parameter classes
-----------------

This library makes use of a series of 'Parameter' classes. The structure is:

    Collection --[has many]--> Parameters --[has many]--> Values

An example of setting and retrieving values would be:

    $collection = new \Collection();
    $collection->add('alpha', array('bravo', 'charlie', 'delta'));

    $alpha = $collection->get('alpha')->getValues(); // array
    $alphaBravo = (string) $collection->get('alpha')->getFirst();

Note that returned 'values' are Value classes. Call get() or __toString() on them.


Example usage
-------------

### Gain Temporary Credentials

    // Create a new Consumer credential
    $consumer = new StasisMedia\OAuth\Credential\Consumer('key', 'secret' );

    // Setup the signature method and request
    $request = new \StasisMedia\OAuth\Request\TemporaryCredentials();
    $request->setConsumerCredentials($consumer);

    $request->setUrl('http://photos.example.net/initiate');
    $request->setCallbackUrl('http:///printer.example.com/ready');

    // Signature object
    $signature = new \StasisMedia\OAuth\Signature\HMAC_SHA1($request);
    $signature->setConsumerCredential($consumer);

    // CURL connector
    $connector = new \StasisMedia\OAuth\Connector\HTTP\Curl();

    // OAuth client
    $client = new \StasisMedia\OAuth\Client($connector, $request, $signature);
    $client->execute();