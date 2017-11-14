<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\ProviderInterface\ProviderInterface;
use ZfThreeSocialAuth\Service\SocialManager;

class TwitterProvider implements ProviderInterface
{

    protected $baseUrl = 'https://api.twitter.com/'; // oauth/authenticate
    protected $preUrl = 'oauth/request_token';
    protected $signatureMethod = 'HMAC-SHA1';
    protected $oauthVersion = '1.0';
    protected $postParams = [];
    protected $defaultParams = [];
    protected $action;

    /**
     * 
     * @param SocialManager $socialManager
     */
    public function __construct(SocialManager $socialManager)
    {
        $this->socialManager = $socialManager;
        $this->setProviderName();
    }

    protected function setProviderName()
    {
        $this->providerName = 'twitter';
    }

    public function getRedirectRoute($callback)
    {
        $this->callback = $callback;
        $this->setDefaultParams();
        $path = 'oauth/request_token';
        if (false !== $response = $this->makeRequest($path, 'POST')) {
            $result = [];
            parse_str($response->getBody(), $result);
            if (200 == $response->getStatusCode() && array_key_exists('oauth_callback_confirmed', $result)
                    && $result['oauth_callback_confirmed'] == 'true' && array_key_exists('oauth_token', $result)
                    && array_key_exists('oauth_token_secret', $result)) {
                return $this->baseUrl . 'oauth/authenticate' . '?' . 'oauth_token=' . $result['oauth_token'];
            }
        }
        throw new \Exception('Twitter returned an error');
    }

    public function sendClientRequest($callback, $queryParams)
    {
        $this->callback = $callback;
        if (array_key_exists('oauth_token', $queryParams) && array_key_exists('oauth_verifier', $queryParams)) {
            $this->setDefaultParams($queryParams['oauth_token'], $queryParams['oauth_verifier']);
            $path = 'oauth/access_token';
            if (false !== $response = $this->makeRequest($path, 'POST', ['oauth_verifier' => $queryParams['oauth_verifier']])) {
                $result = [];
                parse_str($response->getBody(), $result);
                if (200 == $response->getStatusCode()) {
                    $result = [];
                    parse_str($response->getBody(), $result);
                    return $this->getCredentials($result);
                }
            }
        }
        throw new \Exception('Twitter returned an error');
    }

    protected function getCredentials($queryParams)
    {
        if (is_array($queryParams) && array_key_exists('oauth_token', $queryParams)
                && array_key_exists('oauth_token_secret', $queryParams)) {
            $this->setDefaultParams($queryParams['oauth_token']);
            $path = '1.1/account/verify_credentials.json';
            $params = array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true');
            if (false !== $response = $this->makeRequest($path, 'GET', $params, $queryParams['oauth_token_secret'])) {
                $result = [];
                parse_str($response->getBody(), $result);
                if (200 == $response->getStatusCode()) {
                    $user = json_decode($response->getBody());
                    if (isset($user->email)) {
                        return [
                            'name' => var_export($user->name, true),
                            'email' => $user->email,
                            'id' => $user->id,
                            'provider' => $this->providerName
                        ];
                    }
                }
            }
        }
        throw new \Exception('Twitter returned an error');
    }

    protected function makeRequest($path, $method, $params = [], $tokenSecret = '')
    {

        $authorization = $this->buildAuthorisation($path, $method, $tokenSecret, $params);
        $client = $this->socialManager->getClient();
        $client->resetParameters();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => $authorization,
            'User-Agent' => 'TwitterOAuth (+https://twitteroauth.com) Adapted for Zend http client',
            'Expect' => '',
        ];
        switch (true) {
            case 'POST' == $method && count($params) > 0:
                $client->setParameterPost($params);
                break;
            case 'GET' == $method && count($params) > 0:
                $path .= '?' . http_build_query($params);
                break;
            default:
                break;
        }
        $client->setUri($this->baseUrl . $path);
        $client->setMethod($method);
        $client->setHeaders($headers);
        return $client->send();
    }

    protected function setDefaultParams($oauthToken = false, $oauthVerifier = false)
    {
        $this->defaultParams = [
            'oauth_consumer_key' => $this->socialManager->getModuleOptions()->getConsumerKey('twitter'),
            'oauth_nonce' => $this->getNonce(),
            'oauth_signature_method' => $this->signatureMethod,
            'oauth_timestamp' => $this->getTimestamp(),
            'oauth_version' => $this->oauthVersion,
            'oauth_extra' => 'action'
        ];
        if (false !== $oauthToken) {
            $this->defaultParams['oauth_token'] = $oauthToken;
        }
        if (false !== $oauthVerifier) {
            $this->defaultParams['oauth_verifier'] = $oauthVerifier;
        }
    }

    protected function buildAuthorisation($path, $method, $tokenSecret = '', $params = [])
    {
        $first = true;
        $this->defaultParams['oauth_signature'] = $this->getSignature(array_merge($this->defaultParams, $params), $path, $method, $tokenSecret);
        $out = 'OAuth';
        foreach ($this->defaultParams as $k => $v) {
            if (substr($k, 0, 5) != "oauth") {
                continue;
            }
            if (is_array($v)) {
                throw new TwitterOAuthException('Arrays not supported in headers');
            }
            $out .= ($first) ? ' ' : ', ';
            $out .= $this->urlencodeRfc3986($k) . '="' . $this->urlencodeRfc3986($v) . '"';
            $first = false;
        }
        return $out;
    }

    protected function getSignature($params, $path, $method, $tokenSecret = '')
    {

        $signatureBase = $this->getSignatureBaseString($params, $path, $method);

        $secret = $this->socialManager->getModuleOptions()->getSecret('twitter');
        $key_parts = [$secret, $tokenSecret];
        $key = implode('&', $this->urlencodeRfc3986($key_parts));

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }

    protected function getSignatureBaseString($params, $path, $method)
    {
        ksort($params);
        $parts = [
            $method,
            $this->baseUrl . $path,
            http_build_query($params)
        ];


        return implode('&', $this->urlencodeRfc3986($parts));
    }

    protected function getNonce()
    {
        return md5(microtime() . mt_rand());
    }

    protected function getTimestamp()
    {
        return time();
    }

    protected function urlencodeRfc3986($input)
    {
        $output = '';
        if (is_array($input)) {
            $output = array_map([$this, 'urlencodeRfc3986'], $input);
        } elseif (is_scalar($input)) {
            $output = rawurlencode($input);
        }
        return $output;
    }

}
