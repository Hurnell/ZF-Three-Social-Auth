<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class LiveProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://login.live.com/oauth20_authorize.srf';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://login.live.com/oauth20_token.srf';

    /**
     *
     * @var type 
     */
    protected $profileUri = 'https://apis.live.net/v5.0/me';

    //

    protected function setProviderName()
    {
        $this->providerName = 'live';
    }

    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'wl.basic wl.emails';
        $this->authorisationParams['response_type'] = 'code';
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        return [];
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('Windows Live returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setUri($this->profileUri);
        $client->setMethod('GET');
        $params = [
            'access_token' => $token
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->id) || !isset($user->emails)
                || !isset($user->name) || !isset($user->emails->account)
        ) {
            throw new \Exception('Windows Live returned an error');
        }
        return [
            'name' => $user->name,
            'email' => $user->emails->account,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
    }

}
