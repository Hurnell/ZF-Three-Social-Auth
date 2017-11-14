<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class FacebookProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://www.facebook.com/v2.9/dialog/oauth';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://graph.facebook.com/v2.9/oauth/access_token';

    /**
     *
     * @var type 
     */
    protected $profileUri = 'https://graph.facebook.com/me';

    protected function setProviderName()
    {
        $this->providerName = 'facebook';
    }

    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'email';
        $this->authorisationParams['display'] = 'page';
    }

    protected function updateAccessParams($queryParams)
    {
        return [];
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('Facebook returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setUri($this->profileUri);
        $client->setMethod('GET');
        $params = [
            'access_token' => $token,
            'fields' => 'id,name,email'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode()
                || !isset($user->email)
                || !isset($user->name)
                || !isset($user->id)) {
            throw new \Exception('Facebook returned an error');
        }
        $result = [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
        return $result;
    }

}
