<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class InstagramProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://instagram.com/oauth/authorize';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://api.instagram.com/oauth/access_token';

    /**
     *
     * @var type 
     */
    protected $profileUri = '';

    protected function setProviderName()
    {
        $this->providerName = 'instagram';
    }

    protected function updateAuthorisationParams()
    {
        unset($this->authorisationParams['scope']);
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        return [];
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        var_dump($result);
        die(__METHOD__);
    }

    protected function processUserProfile($response)
    {
        
    }

}
