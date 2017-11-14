<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class FoursquareProvider extends AbstractProvider
{

    protected $baseAuthorisationUrl = 'https://foursquare.com/oauth2/authenticate';
    protected $requestAccessTokenUrl = 'https://foursquare.com/oauth2/access_token';
    protected $requestUserProfileUrl = 'https://api.foursquare.com/v2/users/self';

    protected function setProviderName()
    {
        $this->providerName = 'foursquare';
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        return [];
    }

    protected function updateAuthorisationParams()
    {
        
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('LinkedIn returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setMethod('GET');
        $client->setUri($this->requestUserProfileUrl);
        $client->setParameterGet(['oauth_token' => $token, 'v' => '20120609']);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $data = json_decode($response->getBody());
        if (200 != $response->getStatusCode()
                || !isset($data->response->user->id)
                || !isset($data->response->user->firstName)
                || !isset($data->response->user->lastName)
                || !isset($data->response->user->contact->email)) {
            throw new \Exception('Foursquare returned an error');
        }

        return [
            'name' => $data->response->user->firstName . ' ' . $data->response->user->lastName,
            'email' => $data->response->user->contact->email,
            'id' => $data->response->user->id,
            'provider' => $this->providerName
        ];
    }

}
