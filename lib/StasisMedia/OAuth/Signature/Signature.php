<?php
Namespace StasisMedia\OAuth\Signature;

use StasisMedia\OAuth\Request\RequestInterface;

require_once dirname(__FILE__) . '/../../../php-utf8/utf8.inc';
/**
 * OAuth 1.0 signature base class
 * http://tools.ietf.org/html/rfc5849#section-3.4
 *
 * @author      Craig Mason <craig.mason@stasismedia.com>
 * @package     OAuth
 */
abstract class Signature implements SignatureInterface
{
    /**
     *
     * @var RequestInterface
     */
    protected $_request;

    public function __construct(RequestInterface $request)
    {
        $this->_request = $request;

        $this->_request->setParameter(
                'oauth_signature_method',
                $this->getSignatureMethod()
        );
    }


    protected function _getBaseString()
    {
        $parts = array();

        // 1. Request method
        $parts[0] = rawurlencode($this->_getBaseStringRequestMethod());

        // 2. Base string URI
        $parts[1] = rawurlencode($this->_getBaseStringURI());

        // 3. Request parameters
        $parts[2] = rawurlencode($this->_getNormalizedParameters());

        return implode($parts, '&');
    }

    private function _getBaseStringRequestMethod()
    {
        return strtoupper($this->_request->getRequestMethod());
    }

    private function _getBaseStringURI()
    {
        return $this->_request->getBaseStringURI();
    }

    private function _getNormalizedParameters()
    {
        return $this->_normalizeParameters($this->_request->getParameters());
    }

    /**
     * Normalizes the the parameters
     * @param <type> $parameters
     */
    protected function _normalizeParameters($parameters)
    {
        $encoded = $this->_encodeParameters($parameters);
        $sorted = $this->_sortParameters($encoded);
        $joined = $this->_joinParameters($sorted);

        return implode('&', $joined);
    }

    /**
     * Encodes the key-value pairs according to
     * http://tools.ietf.org/html/rfc5849#section-3.6
     *
     * @param array $parameters
     */
    private function _encodeParameters($parameters)
    {
        array_walk($parameters, array($this, '_encodeKeyValue'));

        return $parameters;
    }

    /**
     * Encodes a key/value pair, utf8
     *
     * @param string $value
     * @param string $key
     */
    private function _encodeKeyValue(&$value, &$key)
    {
        // Only encode strings
        $key = is_string($key) ? $this->_utf8Encode($key) : $key;
        $value = is_string($value) ? $this->_utf8Encode($value) : $value;
    }

    /**
     * Detects whether a string is UTF-8. If not, we will attempt to
     *
     * @param <type> $string
     * @return <type>
     */
    private function _utf8Encode($string)
    {
        // auto detects from ASCII,JIS,UTF-8,EUC-JP,SJIS
        $encoding = mb_detect_encoding($string);

        // If we cannot detect the encoding, throw an exception
        if($encoding === false)
        {
            throw new Exception\ParameterException(sprintf(
                    'Encoding of stringcould not be detected: ',
                    $string
            ));
        }

        /*
         * If we can detect the encoding, and it is not UTF-8, convert it.
         * The application should supply pre encoded parameters, but the
         * specification appears to indicate that they should be encoded at
         * encoding time.
         * ASCII,JIS,UTF-8,EUC-JP,SJIS
         */
        if($encoding != 'UTF-8')
        {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        return $string;
    }

    /**
     * Sort the parameters by key, then value if keys match.
     *
     * The strings should be sorted using 'byte value ordering'. Namely, the
     * position in the UTF-8 table where the character exists.
     *
     * http://tools.ietf.org/html/rfc3629#section-1
     *
     *   The byte-value lexicographic sorting order of UTF-8 strings is the
     *   same as if ordered by character numbers.  Of course this is of
     *   limited interest since a sort order based on character numbers is
     *   almost never culturally valid.
     *
     * Also, further comments from Eran Hammer-Lahav
     *
     * http://markmail.org/message/ppzg65eslngpov24
     * http://markmail.org/message/ppzg65eslngpov24
     *
     * @param <type> $parameters
     */
    private function _sortParameters($parameters)
    {
        // Break the key/value pairs down into a data array for comparison
        $data = array();
        foreach($parameters as $key => $value)
        {
            $data[] = array('key' => $key, 'value' => $value);
        }

        usort($data, array($this, '_utf8Sort'));

        // Reassemble key/value pairs
        $parameters = array();
        foreach($data as $row)
        {
            $parameters[$row['key']] = $row['value'];
        }

        return $parameters;

    }

    /**
     * Attempts to _utf8Compare by 'key', then 'value'
     *
     * @param array $a First key/value pair
     * @param array $b Second key/value pair
     * @return int Sorting order
     */
    private function _utf8Sort($a, $b)
    {
        if($a['key'] !== $b['key'])
        {
            return $this->_utf8Compare($a['key'], $b['key']);
        } else {
            return $this->_utf8Compare($a['value'], $b['value']);
        }
    }

    /**
     * Compare 2 UTF-8 strings by comparing positions in UTf-8 character table
     * @param string $a UTF-8 encoded string
     * @param string $b UTF-8 encoded string
     * @return int Order of $a compared to $b
     */
    private function _utf8Compare($a, $b)
    {
        $aOrd = utf8ToUnicode($a);
        $bOrd = utf8ToUnicode($b);

        $i = 0;
        $max = max(count($aOrd), count($bOrd));
        $sort = 0;

        do
        {
            // If both values exist
            if(array_key_exists($i, $aOrd) && array_key_exists($i, $aOrd))
            {
                // If both the same, continue
                if($aOrd[$i] == $bOrd[$i])
                {
                    continue;
                }

                // Which one is higher?
                elseif($aOrd[$i] < $bOrd[$i])
                {
                    $sort = -1;
                }
                else
                {
                    $sort = 1;
                }

            }
            // If a exists, b is higher
            elseif(array_key_exists($i, $aOrd))
            {
                $sort = 1;
            }
            // If b exists, a is higher
            else
            {
                $sort = -1;
            }
        }
        while ($sort === 0 && $i++ < $max - 1);

        return $sort;
    }

    /**
     * Joins each key/value pair with '='
     * 
     * @param array $parameters  key/value pairs
     */
    private function _joinParameters($parameters)
    {
        $joined = array();
        foreach($parameters as $key => $value)
        {
            $joined[] = $key . '=' . $value;
        }
        
        return $joined;
    }

}