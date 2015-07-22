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

use Phppro\MicroService\Daemon;
use Phppro\MicroService\NamedSocket;
use Phppro\MicroService\Behaviour\ServiceTrait;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DaemonService
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
     * @param Daemon $daemon
     *
     * @return $this
     */
    public function run(Daemon $daemon)
    {
        /** @var NamedSocket[] $readable */
        $readable = [];
        /** @var NamedSocket[] $writable */
        $writable = [];

        $daemon->init();

        $context  = $daemon->getContext();

        $context->zmqContext = $this->getZmqService()->createContext();
        $context->zmqOutgoingSockets = $this->getZmqService()->createSockets($daemon->getOutgoingSockets(), $context->zmqContext);
        $context->zmqIncomingSockets = $this->getZmqService()->createSockets($daemon->getIncomingSockets(), $context->zmqContext);

        $daemon->start();

        while ($daemon->isStarted())
        {
            $in  = [];
            $out = [];


            foreach($context->zmqOutgoingSockets as $name => $socket) {
                if ($daemon->isQueueEmpty($name)) {
                    continue;
                }
                $out[] = $socket;
            }

            foreach($context->zmqIncomingSockets as $name => $socket) {
                $in[] = $socket;
            }

            $poller = $this->getZmqService()->createPoller($in, $out);
            $events = $poller->poll($readable, $writable, -1);
            $errors = $poller->getLastErrors();

            if (0 < count($errors)) {
                throw new \RuntimeException(sprintf("Socket polling error: %s", array_shift($errors)), 500);
            }

            if (0 >= $events) {
                throw new \RuntimeException("No socket polling event", 500);
            }

            foreach ($readable as $r) {
                try {
                    $rawMsg = $r->recv();
                    $msg = @json_decode($rawMsg, true);

                    if (!$msg) {
                        throw new \RuntimeException(sprintf("Unable to parse incoming message: %s", $rawMsg), 412);
                    }

                    $daemon->receive($r->getName(), $msg);
                } catch (\Exception $e) {
                    $daemon->error($e);
                }
            }

            foreach ($writable as $w) {
                try {
                    if ($daemon->isQueueEmpty($w->getName())) {
                        continue;
                    }

                    $w->send(json_encode($daemon->unqueue($w->getName())));
                } catch (\Exception $e) {
                    $daemon->error($e);
                }
            }
        }

        return $this;
    }
}
