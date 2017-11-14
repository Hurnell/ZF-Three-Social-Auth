<?php

namespace ZfThreeSocialAuth\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\Exception\BadMethodCallException;

class ModuleOptions extends AbstractOptions
{

    /**
     * Doctrine User Entity
     */
    protected $userEntity;

    /**
     *
     * @var authentication service to user  
     */
    protected $authenticationService;
    protected $registrationHeader;
    protected $loginHeader;
    protected $loginText;
    protected $registrationText;

    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = true;
    protected $availableProviders = [
        'facebook', 
        'foursquare',
        'git_hub',
        'google', 
        'linked_in', 
        'live',
        'twitter', 
        'yahoo',
        'yandex', 
    ];
    protected $clientIds = [];
    protected $secrets = [];
    protected $enabledProviders = [];

    /* OTHER PROVIDERS NOT IMPLEMENTED */
    /*
      'aol', //waiting for reply from The AOL Reader Team
      'bitbucket',
      'tumblr',
      'mailru',
      'weibo', //cannot register no phone avalable from NL
      'odnoklassniki',
      'vkontakte',
      'instagram',
     */

    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    public function __set($key, $value)
    {
        $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        if (is_callable([$this, $setter])) {
            $this->{$setter}($value);
            return;
        }

        if ($this->__strictMode__) {
            throw new BadMethodCallException(sprintf(
                    'The option "%s" does not have a callable "%s" ("%s") setter method which must be defined', $key, 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))), $setter
            ));
        }
    }

    /**
     * get an array of enabled providers
     *
     * @return array
     */
    public function getEnabledProviders()
    {
        $providers = [];
        foreach ($this->enabledProviders as $key => $value) {
            if (true == $value) {
                $providers[] = $key;
            }
        }
        return $providers;
    }

    public function getClientId($provider)
    {
        $result = false;
        if (array_key_exists($provider, $this->clientIds)) {
            $result = $this->clientIds[$provider];
        }
        return $result;
    }

    public function getConsumerKey($provider)
    {
        return $this->getClientId($provider);
    }

    public function getSecret($provider)
    {
        $result = false;
        if (array_key_exists($provider, $this->secrets)) {
            $result = $this->secrets[$provider];
        }
        return $result;
    }

    /* FACEBOOK */

    public function setFacebookEnabled($enabled)
    {
        if (in_array('facebook', $this->availableProviders)) {
            $this->enabledProviders['facebook'] = $enabled;
        }
    }

    public function setFacebookClientId($clientId)
    {
        $this->clientIds['facebook'] = $clientId;
    }

    public function setFacebookSecret($secret)
    {
        $this->secrets['facebook'] = $secret;
    }

    /* FOURSQUARE */

    public function setFoursquareEnabled($enabled)
    {
        if (in_array('foursquare', $this->availableProviders)) {
            $this->enabledProviders['foursquare'] = $enabled;
        }
    }

    public function setFoursquareClientId($clientId)
    {
        $this->clientIds['foursquare'] = $clientId;
    }

    public function setFoursquareSecret($secret)
    {
        $this->secrets['foursquare'] = $secret;
    }

    /* GOOGLE */

    public function setGoogleEnabled($enabled)
    {
        if (in_array('google', $this->availableProviders)) {
            $this->enabledProviders['google'] = $enabled;
        }
    }

    public function setGoogleClientId($clientId)
    {
        $this->clientIds['google'] = $clientId;
    }

    public function setGoogleSecret($secret)
    {
        $this->secrets['google'] = $secret;
    }


    /* GITHUB */

    public function setGitHubEnabled($enabled)
    {
        if (in_array('git_hub', $this->availableProviders)) {
            $this->enabledProviders['git_hub'] = $enabled;
        }
    }

    public function setGitHubClientId($clientId)
    {
        $this->clientIds['git_hub'] = $clientId;
    }

    public function setGitHubSecret($secret)
    {
        $this->secrets['git_hub'] = $secret;
    }

    /* LINEKDIN */

    public function setLinkedInEnabled($enabled)
    {
        if (in_array('linked_in', $this->availableProviders)) {
            $this->enabledProviders['linked_in'] = $enabled;
        }
    }

    public function setLinkedInClientId($clientId)
    {
        $this->clientIds['linked_in'] = $clientId;
    }

    public function setLinkedInSecret($secret)
    {
        $this->secrets['linked_in'] = $secret;
    }

    /* LIVE */

    public function setLiveEnabled($enabled)
    {
        if (in_array('live', $this->availableProviders)) {
            $this->enabledProviders['live'] = $enabled;
        }
    }

    public function setLiveClientId($clientId)
    {
        $this->clientIds['live'] = $clientId;
    }

    public function setLiveSecret($secret)
    {
        $this->secrets['live'] = $secret;
    }

    /* TWITTER */

    public function setTwitterEnabled($enabled)
    {
        if (in_array('twitter', $this->availableProviders)) {
            $this->enabledProviders['twitter'] = $enabled;
        }
    }

    public function setTwitterConsumerKey($clientId)
    {
        $this->clientIds['twitter'] = $clientId;
    }

    public function setTwitterConsumerSecret($secret)
    {
        $this->secrets['twitter'] = $secret;
    }

    /* YAHOO */

    public function setYahooEnabled($enabled)
    {
        if (in_array('yahoo', $this->availableProviders)) {
            $this->enabledProviders['yahoo'] = $enabled;
        }
    }

    public function setYahooClientId($clientId)
    {
        $this->clientIds['yahoo'] = $clientId;
    }

    public function setYahooSecret($secret)
    {
        $this->secrets['yahoo'] = $secret;
    }
    /* YANDEX */

    public function setYandexEnabled($enabled)
    {
        if (in_array('yandex', $this->availableProviders)) {
            $this->enabledProviders['yandex'] = $enabled;
        }
    }
    
    public function setYandexClientId($clientId)
    {
        $this->clientIds['yandex'] = $clientId;
    }

    public function setYandexSecret($secret)
    {
        $this->secrets['yandex'] = $secret;
    }

    /* GLOBAL */

    public function setDoctrineUserEntity($userEntity)
    {
        $this->userEntity = $userEntity;
    }

    public function getDoctrineUserEntity()
    {
        return $this->userEntity;
    }

    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    public function setLoginHeader($loginHeader)
    {
        $this->loginHeader = $loginHeader;
    }

    public function getLoginHeader()
    {
        return $this->loginHeader;
    }

    public function setLoginText($loginText)
    {
        $this->loginText = $loginText;
    }

    public function getLoginText()
    {
        return $this->loginText;
    }

    public function setRegistrationHeader($registrationHeader)
    {
        $this->registrationHeader = $registrationHeader;
    }

    public function getRegistrationHeader()
    {
        return $this->registrationHeader;
    }

    public function setRegistrationText($registrationText)
    {
        $this->registrationText = $registrationText;
    }

    public function getRegistrationText()
    {
        return $this->registrationText;
    }

}
