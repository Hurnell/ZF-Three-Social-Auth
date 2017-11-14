<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class Aolrovider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://api.screenname.aol.com/auth/authorize';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = '';

    /**
     *
     * @var type 
     */
    protected $profileUri = '';

    protected function setProviderName()
    {
        $this->providerName = 'aol';
    }

    protected function updateAuthorisationParams()
    {
        
        $this->authorisationParams['scope'] = 'profile email';
    }

    protected function updateAccessParams($queryParams)
    {
        return [];
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        
    }

    protected function processUserProfile($response)
    {
        
    }

}
