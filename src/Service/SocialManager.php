<?php

namespace ZfThreeSocialAuth\Service;

//use Application\Service\RootManager;
use ZfThreeSocialAuth\Options\ModuleOptions;
use ZfThreeSocialAuth\Http\Client;
use ZfThreeSocialAuth\Service\SocialAuthManager;
use Zend\Authentication\Result;

class SocialManager
{

    const SOCIAL_LOGIN = 'login';
    const SOCIAL_REGISTRATION = 'registration';
    const SOCIAL_LOGIN_OR_REGISTRATION = 'loginregistration';

    protected $client;
    protected $moduleOptions;
    protected $socialAuthManager;
    protected $log;
    protected $sessionContainer;

    public function __construct(ModuleOptions $options, SocialAuthManager $socialAuthManager, $sessionContainer)
    {
        $this->moduleOptions = $options;
        $this->socialAuthManager = $socialAuthManager;
        $this->sessionContainer = $sessionContainer;
    }

    public function setAction($action)
    {
        $this->sessionContainer->action = $action;
    }

    public function getAction()
    {
        return $this->sessionContainer->action;
    }

    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client();
            $options = array(
                'adapter' => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            );
            $this->client->setOptions($options);
        }
        return $this->client;
    }

    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    public function startProvider($providerName)
    {
        $enabledProviders = $this->getModuleOptions()->getEnabledProviders();
        if (in_array($providerName, $enabledProviders)) {
            $class = 'ZfThreeSocialAuth\Providers\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $providerName))) . 'Provider';
            if (class_exists($class)) {
                $provider = new $class($this);
                return $provider;
            }
        }
        return false;
    }

    public function handleSocialAuthRedirect($messenger, $clientRequestResult)
    {
        $result = new Result(Result::FAILURE_UNCATEGORIZED, null, ['an unknown error occured']);
        if (is_array($clientRequestResult) && array_key_exists('email', $clientRequestResult) && array_key_exists('name', $clientRequestResult)) {
            $clientRequestResult['action'] = $this->getAction();
            switch ($this->getAction()) {
                case self::SOCIAL_LOGIN :
                    $result = $this->socialAuthManager->completeSocialLogin($clientRequestResult);
                    break;
                case self::SOCIAL_REGISTRATION:
                    $result = $this->socialAuthManager->completeSocialRegistration($clientRequestResult);
                    break;
                case self::SOCIAL_LOGIN_OR_REGISTRATION:
                    $result = $this->socialAuthManager->completeSocialLoginOrRegistration($clientRequestResult);
                    break;
            }
        }
        return $this->addLoginMessages($messenger, $result);
    }

    protected function addLoginMessages($messenger, Result $result)
    {
        if (!$result->isValid()) {
            $messages = $result->getMessages();
            foreach ($messages as $message) {
                $messenger->setNamespace('error')->addMessage($message);
            }
        }
        return $result->isValid();
    }

}
