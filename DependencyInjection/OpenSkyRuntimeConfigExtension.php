<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OpenSkyRuntimeConfigExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('service.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $prototype = $container->getDefinition('opensky.runtime_config.parameter_bag_prototype');

        foreach ($config['parameter_bags'] as $id => $bag_config)
        {
            $bag_definition = clone $prototype;
            $bag_definition->setAbstract(false);
            $bag_definition->replaceArgument(0, new Reference($bag_config['provider']));
            $bag_definition->setScope($bag_config['scope']);

            if ($bag_config['cascade']) {
                $bag_definition->addMethodCall('setContainer', array(new Reference('service_container')));
            }

            if ($bag_config['logging']['enabled']) {
                $bag_definition->addArgument(new Reference('opensky.runtime_config.logger'));
            }

            $container->setDefinition($id, $bag_definition);
        }

//        $container->setParameter('opensky.runtime_config.logger.level', $config['logging']['level']);
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'opensky_runtime_config';
    }
}
