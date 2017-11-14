<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class GoogleProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     *
     * @var string
     */
    protected $requestAccessTokenUrl = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     *
     * @var string
     */
    protected $exchangeUri = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     *
     * @var string
     */
    protected $profileUri = 'https://www.googleapis.com/plus/v1/people/me';

    /**
     *
     * @var array
     */
    protected $scopes = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    protected function setProviderName()
    {
        $this->providerName = 'google';
    }

    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = implode(' ', $this->scopes);
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
            throw new \Exception('Google returned an error');
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
            'alt' => 'json'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserprofile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->emails) || !is_array($user->emails)
                || !isset($user->displayName) || !isset($user->id)) {
            throw new \Exception('Google returned an error');
        }
        foreach ($user->emails as $email) {
            if ('account' == $email->type) {
                return [
                    'name' => $user->displayName,
                    'email' => $email->value,
                    'id' => $user->id,
                    'provider' => $this->providerName
                ];
            }
        }
        throw new \Exception('Google returned an error');
    }

}
