<?php

namespace ZfThreeSocialAuth\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use ZfThreeSocialAuth\Controller\SocialController;
use ZfThreeSocialAuth\Service\SocialManager;

class SocialControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialController
    {
        $socialManager = $container->get(SocialManager::class);
        return new SocialController($socialManager);
    }

}
