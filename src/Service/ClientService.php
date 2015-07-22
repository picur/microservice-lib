<?php

/*
 * This file is part of the MICROSERVICE LIB package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phppro\MicroService\Service;

use Phppro\MicroService\Client;
use Phppro\MicroService\NamedSocket;
use Phppro\MicroService\Behaviour\ServiceTrait;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ClientService
{
    use ServiceTrait;
    /**
     * @return ZmqService
     */
    public function getZmqService()
    {
        return $this->getService('zmq');
    }
    /**
     * @param ZmqService $service
     *
     * @return $this
     */
    public function setZmqService(ZmqService $service)
    {
        return $this->setService('zmq', $service);
    }
    /**
     * @param Client $client
     *
     * @return $this
     */
    public function run(Client $client)
    {
        $client->init();

        $context  = $client->getContext();

        $context->zmqContext = $this->getZmqService()->createContext();
        $context->zmqOutgoingSockets = $this->getZmqService()->createSockets($client->getOutgoingSockets(), $context->zmqContext);

        $client->onMessageSent(function ($socketName, $msg, Client $client) {
            $sockets = $client->getContext()->zmqOutgoingSockets;
            if (!isset($sockets[$socketName])) {
                throw new \RuntimeException(sprintf("Unable to send message: unknown outgoing socket '%s'", $socketName), 500);
            }
            /** @var NamedSocket $socket */
            $socket = $sockets[$socketName];
            $socket->send(json_encode($msg));
            $response = null;
            if ($socket->getSocketType() === \ZMQ::SOCKET_REQ) {
                $rawMsg = $socket->recv();
                $response = @json_decode($rawMsg, true);

                if (!$response) {
                    throw new \RuntimeException(sprintf("Unable to parse incoming message: %s", $rawMsg), 412);
                }
            }
            return $response;
        });

        $client->execute();

        return $this;
    }
}
