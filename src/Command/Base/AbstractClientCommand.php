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

use Phppro\MicroService\Client;
use Phppro\MicroService\Service\ClientService;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractClientCommand extends AbstractCommand
{
    /**
     * @return ClientService
     */
    public function getClientService()
    {
        return $this->getService('client');
    }
    /**
     * @param ClientService $service
     *
     * @return $this
     */
    public function setClientService(ClientService $service)
    {
        return $this->setService('client', $service);
    }
    /**
     * @param $name
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Client
     */
    protected function createClient($name, InputInterface $input, OutputInterface $output)
    {
        $that = $this;

        $client = new Client($name);

        $client->registerLogger(function ($text, $args) use ($that, $output) {
            $that->log($output, array_merge([$text], $args));
        });

        $client->onError(function (\Exception $e, Client $client) {
            $client->logException($e);
        });

        unset($input);

        return $client;
    }
}
