<?php

namespace ZfThreeSocialAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfThreeSocialAuth\Service\SocialManager;

//use Gettext\Translations;
//use Gettext\Merge;

class SocialController extends AbstractActionController
{

    const ROUTE_REDIRECT = 'social';

    /**
     *
     * @var SocialManager
     */
    protected $socialManager;
    protected $log;

    public function __construct(SocialManager $socialManager)
    {
        //$this->log = \Application\Log\Log::getInstance();
        $this->socialManager = $socialManager;
    }

    public function registerAction()
    {
        return new ViewModel();
    }

    public function loginOrRegisterAction()
    {
        return new ViewModel();
    }

    public function failedLoginAction()
    {
        return new ViewModel();
    }

    public function startLoginAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_LOGIN);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    public function startRegistrationAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_REGISTRATION);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    public function startLoginOrRegistrationAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_LOGIN_OR_REGISTRATION);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    protected function getRedirectUrl($action)
    {
        $url = false;
        $providerName = $this->params()->fromRoute('provider');
        if (false !== $provider = $this->socialManager->startProvider($providerName)) {
            $this->socialManager->setAction($action);
            $callback = $this->getCallbackUrl($providerName);
            $url = $provider->getRedirectRoute($callback);
        }
        return $url;
    }

    protected function getCallbackUrl($providerName)
    {
        return $this->url()->fromRoute(static::ROUTE_REDIRECT, ['action' => 'redirected', 'provider' => $providerName], ['force_canonical' => true]);
    }

    public function testAnywayAction()
    {
        $success = $this->socialManager->test();
    }

    public function redirectedAction()
    {
        $providerName = $this->params()->fromRoute('provider');
        $callback = $this->getCallbackUrl($providerName);
        $queryParams = $this->params()->fromQuery();
        $result = 'no-provider';
        if (false !== $provider = $this->socialManager->startProvider($providerName)) {
            $result = $provider->sendClientRequest($callback, $queryParams);
        }
        $success = $this->socialManager->handleSocialAuthRedirect($this->plugin('FlashMessenger'), $result);
        $route = true === $success ? 'home' : 'failed-social-login';
        $this->redirect()->toRoute($route);
    }

}
