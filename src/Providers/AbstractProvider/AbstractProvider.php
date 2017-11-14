<?php

namespace ZfThreeSocialAuth\Providers\AbstractProvider;

use ZfThreeSocialAuth\Providers\ProviderInterface\ProviderInterface;
use ZfThreeSocialAuth\Service\SocialManager;
use Zend\Validator\Csrf;
use ZfThreeSocialAuth\Http\Client;

abstract class AbstractProvider implements ProviderInterface
{

    /**
     *
     * @var SocialManager 
     */
    protected $socialManager;

    /**
     *
     * @var Csrf 
     */
    protected $csrf;

    /**
     *
     * @var string
     */
    protected $providerName;

    /**
     * The basic parameters to append to the initial call to the provider
     * 
     * @var array 
     */
    protected $authorisationParams = [
        'client_id' => '',
        'redirect_uri' => '',
        'response_type' => 'code',
        'scope' => '',
        'state' => '',
    ];
    protected $accessParams = [
        'client_id' => '',
        'client_secret' => '',
        'code' => '',
        'redirect_uri' => '',
    ];

    /**
     * The URI that the provider returns to
     * 
     * @var string
     */
    protected $callback;
    
    /**
     * social login or registration
     * @var string 
     */
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

    abstract protected function setProviderName();

    abstract protected function updateAuthorisationParams();

    abstract protected function updateAccessParams($queryParams);

    abstract protected function handleAccessTokenResponse(Client $client, $response);

    abstract protected function processUserProfile($response);

    /**
     * 
     * @param string $callback
     * @param string $action
     * @return string
     */
    public function getRedirectRoute($callback)
    {
        $this->callback = $callback;
        $query = $this->getQuery();
        return $this->baseAuthorisationUrl . '?' . $query;
    }

    public function sendClientRequest($callback, $queryParams)
    {
        $this->checkReturnedQuery($queryParams);
        $this->accessParams['code'] = $queryParams['code'];
        $this->accessParams['client_id'] = $this->socialManager->getModuleOptions()->getClientId($this->providerName);
        $this->accessParams['client_secret'] = $this->socialManager->getModuleOptions()->getSecret($this->providerName);
        $this->accessParams['redirect_uri'] = $callback;
        $additionalHeaders = $this->updateAccessParams($queryParams);
        $client = $this->socialManager->getClient();
        $client->setUri($this->requestAccessTokenUrl);
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded',];
        $client->setHeaders(array_merge($headers, $additionalHeaders));
        $client->setMethod('POST');
        $client->setParameterPost($this->accessParams);
        $response = $client->send();
        if (200 == $response->getStatusCode()) {
            return $this->handleAccessTokenResponse($client, $response);
        }
        var_dump($response->getStatusCode());
        var_dump($response->getBody());
        die(__METHOD__);
    }

    protected function checkReturnedQuery($params)
    {
        if (!array_key_exists('code', $params) || !array_key_exists('state', $params) || !$this->checkCsrf($params['state'])) {
            throw new \Exception('the returned query failed');
        }
    }

    protected function getQuery()
    {
        $this->authorisationParams['state'] = $this->getCsrf();
        $this->authorisationParams['redirect_uri'] = $this->callback;
        $this->authorisationParams['client_id'] = $this->socialManager->getModuleOptions()->getClientId($this->providerName);
        $this->updateAuthorisationParams();
        return http_build_query($this->authorisationParams);
    }

    /**
     * 
     * @return Csrf
     */
    protected function setCsrf()
    {
        $this->csrf = new Csrf();
        return $this->csrf;
    }

    /**
     * 
     * @param boolean $regenerate
     * @return string
     */
    protected function getCsrf($regenerate = false)
    {
        return $this->setCsrf()->getHash($regenerate);
    }

    /**
     * Check whether the hash value matches the original created in getCsrf()
     * @param string $value
     * @return boolean
     */
    protected function checkCsrf($value)
    {
        return $this->setCsrf()->isValid($value);
    }

}
