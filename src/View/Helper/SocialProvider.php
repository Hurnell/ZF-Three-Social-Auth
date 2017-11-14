<?php

namespace ZfThreeSocialAuth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use ZfThreeSocialAuth\Options\ModuleOptions;

class SocialProvider extends AbstractHelper
{

    /**
     *
     * @var ModuleOptions 
     */
    protected $moduleOptions;

    /**
     *
     * @var string
     */
    protected $substitutableUrl;

    /**
     * Constructor
     * @param ModuleOptions $moduleOptions
     */
    public function __construct(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;
    }

    /**
     * Render the provider list for login page
     * @return string
     */
    public function renderSocialLogin()
    {
        $html = '<div id="social-sign-in-div">';
        $html .= '<h1>' . $this->moduleOptions->getLoginHeader() . '</h1>';
        $html .= '<p>' . $this->moduleOptions->getLoginText() . '</p>';
        $html .= $this->getProviderList();
        $html .= '</div>';
        return $html;
    }

    /**
     * Render the provider list for registration page
     * @return string
     */
    public function renderSocialRegistration()
    {
        $html = '<div id="social-sign-in-div">';
        $html .= '<h1>' . $this->moduleOptions->getRegistrationHeader() . '</h1>';
        $html .= '<p>' . $this->moduleOptions->getRegistrationText() . '</p>';
        $html .= $this->getProviderList();
        $html .= '</div>';
        return $html;
    }

    /**
     * Render the provider list for registration page
     * @return string
     */
    public function renderSocialLoginOrRegistration()
    {
        $html = '<div id="social-sign-in-div">';
        $html .= '<h1>' . $this->moduleOptions->getLoginHeader() . '</h1>';
        $html .= '<p>' . $this->moduleOptions->getLoginText() . '</p>';
        $html .= $this->getProviderList();
        $html .= '</div>';
        return $html;
    }

    public function setBaseUrl($url)
    {
        $this->substitutableUrl = $url;
        return $this;
    }

    protected function getProviderList()
    {
        $providers = $this->moduleOptions->getEnabledProviders();
        $out = '';
        if (0 < count($providers)) {
            $out .= '<ul class="social-providers-list">';
            foreach ($providers as $provider) {
                $href = str_replace('substitutable-provider', $provider, $this->substitutableUrl);
                $out .= '<li>';
                $out .= '<a href="' . $href . '" title="">';
                $out .= '<img src="/img/providers/' . $provider . '.png" />';
                $out .= '</a>';
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }

}
