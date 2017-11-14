<?php

namespace ZfThreeSocialAuth\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfThreeSocialAuth\Options\ModuleOptions;
use ZfThreeSocialAuth\Service\SocialAuthManager;

class SocialAuthManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialAuthManager
    {

        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $moduleOptions = $container->get(ModuleOptions::class);
        $userClass = $moduleOptions->getDoctrineUserEntity();
        $authService = $container->get($moduleOptions->getAuthenticationService());
        return new SocialAuthManager($entityManager, $userClass, $authService);
    }

}
