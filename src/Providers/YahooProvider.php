<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class YahooProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://api.login.yahoo.com/oauth2/request_auth';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://api.login.yahoo.com/oauth2/get_token';
    protected $requestUserIdUrl = 'https://social.yahooapis.com/v1/user/';

    /**
     *
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://social.yahooapis.com/v1/user/me/profile';

    protected function setProviderName()
    {
        $this->providerName = 'yahoo';
    }

    protected function updateAuthorisationParams()
    {
        /**
         * Nothing need to be done here for Yahoo
         */
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        $header = ['Authorization' => $this->buildFirstAuthorisationHeader()];
        return $header;
    }

    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (isset($result->access_token) && isset($result->xoauth_yahoo_guid)) {
            $client->resetParameters();
            $headers = ['Authorization' => 'Bearer ' . $result->access_token];
            $client->setHeaders($headers);
            $client->setMethod('GET');
            $client->setUri($this->requestUserProfileUrl);
            $client->setParameterGet(['format' => 'json']);
            $response = $client->send();
            return $this->processUserProfile($response);
        }
        throw new \Exception('Yahoo returned an error');
    }

    protected function buildFirstAuthorisationHeader()
    {
        $out = 'Basic ';
        $clientId = $this->accessParams['client_id'];
        $clientSecret = $this->accessParams['client_secret'];
        $out .= base64_encode($clientId . ':' . $clientSecret);
        return $out;
    }

    protected function buildSecondAuthorisationHeader()
    {
        $out = 'Basic ';
        return $out;
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->profile) || !isset($user->profile->emails)
                || !isset($user->profile->guid)) {
            throw new \Exception('LinkedIn returned an error');
        }


        foreach ($user->profile->emails as $email) {
            if (isset($email->handle) && isset($email->type) && 'HOME' == $email->type) {
                return [
                    'name' => var_export($user, true),
                    'email' => $email->handle,
                    'id' => $user->profile->guid,
                    'provider' => $this->providerName
                ];
            }
        }
        throw new \Exception('LinkedIn returned an error');
    }

}
