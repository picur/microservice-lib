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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class Application extends BaseApplication
{
    /**
     * @var string
     */
    protected $commandName;
    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $packageVersion = '@package_version@';

        if ('@' !== $packageVersion{0}) {
            $version = $packageVersion;
        }

        $packageName = '@package_name@';

        if ('@' !== $packageName{0}) {
            $name = $packageName;
        }

        parent::__construct($name, $version);
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    public function setCommandName($name)
    {
        $this->commandName = $name;

        return $this;
    }
    /**
     * @param InputInterface $input L'interface de saisie
     *
     * @return string
     */
    protected function getCommandName(InputInterface $input)
    {
        if (null !== $this->commandName) {
            return $this->commandName;
        }

        return parent::getCommandName($input);
    }

    /**
     * @return InputDefinition
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();

        if (null !== $this->commandName) {
            $inputDefinition->setArguments();
        }

        return $inputDefinition;
    }
}