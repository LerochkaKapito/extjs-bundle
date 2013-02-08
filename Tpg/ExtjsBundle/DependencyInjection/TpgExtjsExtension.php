<?php

namespace Tpg\ExtjsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TpgExtjsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $bundles = $container->getParameter('kernel.bundles');
        $list = array();
        if (isset($config['remoting'])) {
            foreach ($config['remoting']['bundles'] as $bundleName) {
                if (isset($bundles[$bundleName])) {
                    $list[$bundleName] = $bundles[$bundleName];
                }
            }
        } else {
            $list = $bundles;
        }

        $container->setParameter('tpg_extjs.remoting.bundles', $list);
    }

    public function getConfiguration(array $config, ContainerBuilder $container) {
        $bundles = $container->getParameter('kernel.bundles');
        return new Configuration(array_keys($bundles));
    }
}
