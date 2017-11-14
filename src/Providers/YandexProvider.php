<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class YandexProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://oauth.yandex.com/authorize';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://oauth.yandex.com/token';

    /**
     *
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://login.yandex.ru/info';

    protected function setProviderName()
    {
        $this->providerName = 'yandex';
    }

    protected function updateAuthorisationParams()
    {
        
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
            throw new \Exception('Yandex returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setUri($this->requestUserProfileUrl);
        $client->setMethod('GET');
        $header = ['Authorization' => 'Bearer ' . $token];
        $client->setHeaders($header);
        $params = [
            'format' => 'json'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->id) || isset($user->emails)
                || is_array($user->emails) || count($user->emails) > 0) {
            throw new \Exception('Yandex returned an error');
        }
        return [
            'name' => $user->real_name,
            'email' => $user->emails[0],
            'id' => $user->id,
            'provider' => $this->providerName
        ];
    }

}
