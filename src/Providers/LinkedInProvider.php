<?php

namespace ZfThreeSocialAuth\Providers;

use ZfThreeSocialAuth\Providers\AbstractProvider\AbstractProvider;
use ZfThreeSocialAuth\Http\Client;

class LinkedInProvider extends AbstractProvider
{

    /**
     *
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     *
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     *
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://api.linkedin.com/v1/people/~:(id,email-address,formatted-name)';

    protected function setProviderName()
    {
        $this->providerName = 'linked_in';
    }

    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'r_basicprofile r_emailaddress w_share';
    }

    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        $this->accessParams['scope'] = 'r_basicprofile r_emailaddress w_share';
        return [];
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

        $headers = ['Content-Type' => 'application/json', 'x-li-format' => 'json'];
        $client->setHeaders($headers);
        $client->setParameterGet(['format' => 'json', 'oauth2_access_token' => $token]);
        $response = $client->doCleanSend();
        return $this->processUserProfile($response);
    }

    protected function processUserProfile($response)
    {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->formattedName) || !isset($user->emailAddress)
                || !isset($user->id)) {
            throw new \Exception('LinkedIn returned an error');
        }
        return [
            'name' => $user->formattedName,
            'email' => $user->emailAddress,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
    }

}
