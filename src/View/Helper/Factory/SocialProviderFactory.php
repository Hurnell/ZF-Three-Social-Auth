<?php

namespace ZfThreeSocialAuth\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfThreeSocialAuth\View\Helper\SocialProvider;
use ZfThreeSocialAuth\Options\ModuleOptions;

class SocialProviderFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialProvider
    {
        $moduleOptions = $container->get(ModuleOptions::class);
        return new SocialProvider($moduleOptions);
    }

}
