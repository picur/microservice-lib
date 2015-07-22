<?php

/*
 * This file is part of the MICROSERVICE LIB package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phppro\MicroService\Command\Base;

use Phppro\MicroService\Daemon;
use Phppro\MicroService\Service\DaemonService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractDaemonCommand extends AbstractCommand
{
    /**
     * @return DaemonService
     */
    public function getDaemonService()
    {
        return $this->getService('daemon');
    }
    /**
     * @param DaemonService $service
     *
     * @return $this
     */
    public function setDaemonService(DaemonService $service)
    {
        return $this->setService('daemon', $service);
    }
    /**
     * @param $name
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Daemon
     */
    protected function createDaemon($name, InputInterface $input, OutputInterface $output)
    {
        $that = $this;

        $daemon = new Daemon($name);

        $daemon->onStartup(function (Daemon $daemon) {
            $daemon->log('<info>%s daemon started.</info>', [ucfirst($daemon->getName())]);
            unset($data);
        });

        $daemon->registerLogger(function ($text, $args) use ($that, $output) {
            $that->log($output, array_merge([$text], $args));
        });

        $daemon->onError(function (\Exception $e, Daemon $daemon) {
            $daemon->logException($e);
        });

        unset($input);

        return $daemon;
    }
}
