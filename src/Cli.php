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
class Cli
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
     * @return ContainerBuilder
     */
    public function getContainerBuilder()
    {
        if (null === $this->containerBuilder) {
            $c = new ContainerBuilder();
            $loader = new YamlFileLoader($c, new FileLocator(__DIR__ . '/..'));
            $loader->load('config.yml.dist');
            try {
                $loader->load('config.yml');
            } catch (\Exception $e) {
                // no specific config.yml file to load
            }
            $loader = new YamlFileLoader($c, new FileLocator(__DIR__ . '/Resources/config'));
            $loader->load('services.yml');
            $this->containerBuilder = $c;
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