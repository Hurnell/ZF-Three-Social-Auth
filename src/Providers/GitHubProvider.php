<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class GitHubProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://github.com/login/oauth/authorize';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://github.com/login/oauth/access_token';

    /**
     *
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://api.github.com/user';

    protected function setProviderName()
    {
        $this->providerName = 'git_hub';
    }

    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'user:email';
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['state'] = $queryParams['state'];
        return ['Accept' => 'application/json'];
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('GitHub returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setUri($this->requestUserProfileUrl);
        $client->setMethod('GET');
        $headers = ['Authorization' => 'token ' . $token];
        $client->setHeaders($headers);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode()
                || !isset($user->name)
                || !isset($user->email)
                || !isset($user->id)) {
            throw new \Exception('GitHub returned an error');
        }
        return [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
    }

}
