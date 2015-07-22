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

use Phppro\MicroService\NamedSocket;
use Phppro\MicroService\Behaviour\ServiceTrait;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ZmqService
{
    use ServiceTrait;
    /**
     * @return \ZMQContext
     */
    public function createContext()
    {
        return new \ZMQContext();
    }
    /**
     * @param array       $definitions
     * @param \ZMQContext $context
     *
     * @return array
     */
    public function createSockets($definitions, \ZMQContext $context)
    {
        $sockets = [];

        foreach($definitions as $name => $definition) {
            $socket = $this->createNamedSocket($name, $definition, $context);
            switch($definition['mode']) {
                case 'bind':
                    $socket->bind($definition['socket']);
                    break;
                case 'connect':
                    $socket->connect($definition['socket']);
                    break;
                default:
                    throw new \RuntimeException(sprintf("Unsupported socket mode '%s'", $definition['mode']), 412);
            }

            $sockets[$name] = $socket;
        }

        return $sockets;
    }
    /**
     * @param string      $name
     * @param array       $definition
     * @param \ZMQContext $context
     *
     * @return NamedSocket
     */
    public function createNamedSocket($name, $definition, \ZMQContext $context)
    {
        $socket = new NamedSocket($context, $this->getSocketTypeByName($definition['type']));

        $socket->setName($name);

        return $socket;
    }
    /**
     * @param string $name
     *
     * @return int
     */
    public function getSocketTypeByName($name)
    {
        switch($name) {
            case 'pull':      return \ZMQ::SOCKET_PULL;
            case 'push':      return \ZMQ::SOCKET_PUSH;
            case 'subscribe': return \ZMQ::SOCKET_SUB;
            case 'publish':   return \ZMQ::SOCKET_PUB;
            case 'request':   return \ZMQ::SOCKET_REQ;
            case 'reply':     return \ZMQ::SOCKET_REP;
            default:          throw new \RuntimeException(sprintf("Unknown socket type '%s'", $name), 412);
        }
    }
    /**
     * @param NamedSocket[] $in
     * @param NamedSocket[] $out
     *
     * @return \ZMQPoll
     */
    public function createPoller($in = [], $out = [])
    {
        $poller = new \ZMQPoll();

        foreach($in as $socket) {
            $poller->add($socket, \ZMQ::POLL_IN);
        }

        foreach($out as $socket) {
            $poller->add($socket, \ZMQ::POLL_OUT);
        }

        return $poller;
    }
}