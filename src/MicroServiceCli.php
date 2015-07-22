<?php

/*
 * This file is part of the MICROSERVICE LIB package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phppro\MicroService;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MicroServiceCli
{
    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;
    /**
     * @param string $applicationName
     *
     * @return int|void
     */
    public static function main($applicationName = null)
    {
        return (new static())->run($applicationName);
    }
    /**
     * @return $this
     */
    protected function loadCommonConfig()
    {
        $loader = new YamlFileLoader($this->containerBuilder, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('services.yml');

        return $this;
    }
    /**
     * @return $this
     */
    protected function loadConfig()
    {
        $configDir = $this->getConfigDir();
        if (null !== $configDir) {
            $loader = new YamlFileLoader($this->containerBuilder, new FileLocator($configDir));
            $loader->load('config.yml');
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function loadContainerBuilder()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->loadCommonConfig();
        $this->loadConfig();

        return $this;
    }
    /**
     * @return string
     */
    protected function getConfigDir()
    {
        return dirname((new \ReflectionClass($this))->getFileName());
    }
    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder()
    {
        if (null === $this->containerBuilder) {
            $this->loadContainerBuilder();
        }

        return $this->containerBuilder;
    }
    /**
     * @param ContainerBuilder $containerBuilder
     *
     * @return $this
     */
    public function setContainerBuilder(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;

        return $this;
    }
    /**
     * @param string $name
     *
     * @return Application
     */
    protected function getApplication($name = null)
    {
        return $this->getContainerBuilder()->get(sprintf('app.application%s', (isset($name) ? '.' : '') . $name));
    }
    /**
     * @param string $applicationName
     *
     * @return int|mixed
     *
     * @throws Exception
     */
    public function run($applicationName = null)
    {
        return $this->getApplication($applicationName)->run();
    }
}