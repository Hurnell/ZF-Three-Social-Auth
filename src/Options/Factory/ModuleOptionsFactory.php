<?php

namespace ZfThreeSocialAuth\Options\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfThreeSocialAuth\Options\ModuleOptions;

class ModuleOptionsFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ModuleOptions
    {
        $config = $container->get('configuration');
        $socialConfig = [];
        if (is_array($config) && array_key_exists('social-config', $config) && is_array($config['social-config'])) {
            $socialConfig = $config['social-config'];
        }
        return new ModuleOptions($socialConfig);
    }

}
