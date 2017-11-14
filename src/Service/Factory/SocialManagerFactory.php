<?php

namespace ZfThreeSocialAuth\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfThreeSocialAuth\Service\SocialManager;
use ZfThreeSocialAuth\Options\ModuleOptions;
use ZfThreeSocialAuth\Service\SocialAuthManager;

class SocialManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialManager
    {
        $moduleOptions = $container->get(ModuleOptions::class);
        $socialAuthManager = $container->get(SocialAuthManager::class);
        $sessionContainer = $container->get('social_saved_state');
        return new SocialManager($moduleOptions, $socialAuthManager, $sessionContainer);
    }

}
