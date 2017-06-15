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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('mes_maintenance');

        $root
            ->canBeEnabled()
            ->fixXmlConfig('ip_allowed', 'ips_allowed')
            ->children()
                ->arrayNode('ips_allowed')
                    ->info('IPs allowed to show the site even under maintenance.')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('controller')
                    ->treatNullLike('mes_maintenance.controller.default:showAction')
                    ->defaultValue('mes_maintenance.controller.default:showAction')
                    ->validate()
                    ->always(function ($controller) {
                        try {
                            explode(':', $controller, 2);

                            return $controller;
                        } catch (\Exception $e) {
                            throw new \InvalidArgumentException(sprintf("Incorrect format for '%s' option.\r\nCorrect format: ServiceId:ActionName", 'controller'));
                        }
                    })
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
