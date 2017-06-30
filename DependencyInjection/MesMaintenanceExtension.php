<?php

/*
 * This file is part of the MesMaintenanceBundle package.
 *
 * (c) Francesco Cartenì <http://www.multimediaexperiencestudio.it/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mes\Misc\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * Class MesMaintenanceExtension.
 */
class MesMaintenanceExtension extends ConfigurableExtension
{
    /**
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $mergedConfig)) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('mes_maintenance.ips_allowed', $mergedConfig['ips_allowed']);
        $container->setParameter('mes_maintenance.debug', $container->getParameter('kernel.debug'));

        $container->findDefinition('mes_maintenance.controller_listener')
            ->replaceArgument(0, $this->createController($mergedConfig['controller']))
            ->replaceArgument(1, $this->createRequestMatcher($container, null, null, null, $mergedConfig['ips_allowed']))
            ->replaceArgument(3, $mergedConfig['ips_allowed']);
    }

    public function getNamespace()
    {
        return 'http://multimediaexperiencestudio.it/schema/dic/maintenance';
    }

    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * @param $controller
     *
     * @return array
     */
    private function createController($controller)
    {
        list($service, $method) = explode(':', $controller, 2);

        return array(
            new Reference($service),
            $method,
        );
    }

    /**
     * @param $container
     * @param null  $path
     * @param null  $host
     * @param array $methods
     * @param null  $ip
     * @param array $attributes
     *
     * @return Reference
     */
    private function createRequestMatcher($container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        $serialized = serialize(array(
            $path,
            $host,
            $methods,
            $ip,
            $attributes,
        ));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        // only add arguments that are necessary
        $arguments = array(
            $path,
            $host,
            $methods,
            $ip,
            $attributes,
        );
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container->register($id, 'Symfony\Component\HttpFoundation\RequestMatcher')
                  ->setPublic(false)
                  ->setArguments($arguments);

        return new Reference($id);
    }
}
