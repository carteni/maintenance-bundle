<?php

/*
 * This file is part of the MesMaintenanceBundle package.
 *
 * (c) Francesco Cartenì <http://www.multimediaexperiencestudio.it/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mes\Misc\Maintenance\Tests\DependencyInjection;

use Mes\Misc\MaintenanceBundle\DependencyInjection\MesMaintenanceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class MesMaintenanceExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerBuilder */
    private $configuration;

    protected function setup()
    {
        $this->configuration = new ContainerBuilder();
        $this->configuration->setParameter('kernel.debug', true);
    }

    protected function tearDown()
    {
        unset($this->configuration);
    }

    public function testContainerWithDefaultValues()
    {
        $loader = new MesMaintenanceExtension();
        $config = $this->getConfigWithDefaults();
        $loader->load(array($config), $this->configuration);

        $this->assertHasDefinition('mes_maintenance.controller_listener');
        $this->assertSame('10.10.10.0', $this->configuration->getParameter('mes_maintenance.ips_allowed')[0]);
    }

    public function testContainerWithEmptyConfig()
    {
        $loader = new MesMaintenanceExtension();
        $config = $this->getEmptyConfig();
        $loader->load(array($config), $this->configuration);

        $this->assertHasDefinition('mes_maintenance.controller_listener');
        $this->assertCount(0, $this->configuration->getParameter('mes_maintenance.ips_allowed'));
    }

    public function testContainerInNoMaintenanceMode()
    {
        $loader = new MesMaintenanceExtension();
        $config = $this->getMaintenanceDisabledConfig();
        $loader->load(array($config), $this->configuration);

        $this->assertNotHasDefinition('mes_maintenance.controller_listener');
    }

    /**
     * @return array
     */
    private function getEmptyConfig()
    {
        return array();
    }

    /**
     * @return array
     */
    private function getConfigWithDefaults()
    {
        $yaml = <<<'EOF'
ips_allowed: [10.10.10.0]
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @return array
     */
    private function getMaintenanceDisabledConfig()
    {
        $yaml = <<<'EOF'
enabled: false
EOF;

        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->configuration->hasDefinition($id) ?: $this->configuration->hasAlias($id)));
    }
}
