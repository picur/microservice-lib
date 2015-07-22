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

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class Daemon
{
    /**
     * @var array
     */
    protected $sockets;
    /**
     * @var array
     */
    protected $callbacks;
    /**
     * @var bool
     */
    protected $started;
    /**
     * @var bool
     */
    protected $initialized;
    /**
     * @var mixed
     */
    protected $context;
    /**
     * @var array
     */
    protected $queues;
    /**
     * @var string
     */
    protected $name;
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name        = $name;
        $this->sockets     = ['in' => [], 'out' => []];
        $this->callbacks   = [];
        $this->started     = false;
        $this->initialized = false;
        $this->context     = null;
        $this->queues      = [];
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }
    /**
     * @return bool
     */
    public function isInitialized()
    {
        return true === $this->initialized;
    }
    /**
     * @return bool
     */
    public function isStarted()
    {
        return true === $this->started;
    }
    /**
     * @return bool
     */
    public function isStopped()
    {
        return !$this->isStarted();
    }
    /**
     * @param string $name
     * @param string $location
     *
     * @return $this
     */
    public function createReceiver($name, $location)
    {
        return $this->registerPull($name, $location, 'bind');
    }
    /**
     * @param string $name
     * @param string $location
     *
     * @return $this
     */
    public function createQueue($name, $location)
    {
        return $this->registerPush($name, $location, 'bind');
    }
    /**
     * @param string $name
     * @param string $location
     *
     * @return $this
     */
    public function listenQueue($name, $location)
    {
        return $this->registerPull($name, $location, 'connect');
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function registerLogger(\Closure $callback)
    {
        return $this->on('log', $callback);
    }
    /**
     * @param string $name
     * @param string $location
     * @param string $mode
     *
     * @return $this
     */
    public function registerPull($name, $location, $mode)
    {
        $this->sockets['in'][$name] = ['type' => 'pull', 'mode' => $mode, 'socket' => $location];

        return $this;
    }
    /**
     * @param string $name
     * @param string $location
     * @param string $mode
     *
     * @return $this
     */
    public function registerPush($name, $location, $mode)
    {
        $this->sockets['out'][$name] = ['type' => 'push', 'mode' => $mode, 'socket' => $location];

        return $this;
    }
    /**
     * @return array
     */
    public function getIncomingSockets()
    {
        return $this->sockets['in'];
    }
    /**
     * @return array
     */
    public function getOutgoingSockets()
    {
        return $this->sockets['out'];
    }
    /**
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function on($name, \Closure $callback)
    {
        if (!isset($this->callbacks[$name])) {
            $this->callbacks[$name] = [];
        }

        $this->callbacks[$name][] = $callback;

        return $this;
    }
    /**
     * @param string   $receiverName
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onMessageReceived($receiverName, \Closure $callback)
    {
        return $this->on(sprintf('%s.message.received', $receiverName), $callback);
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onStartup(\Closure $callback)
    {
        return $this->on('started', $callback);
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onShutdown(\Closure $callback)
    {
        return $this->on('stopped', $callback);
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onError(\Closure $callback)
    {
        return $this->on('exception', $callback);
    }
    /**
     * @return $this
     */
    public function init()
    {
        $this->context     = json_decode('{}');
        $this->started     = false;
        $this->initialized = false;
        $this->queues      = [];

        $this->dispatch('initializing');

        $this->initialized = true;

        $this->dispatch('initialized');

        return $this;
    }
    /**
     * @return $this
     */
    public function start()
    {
        $this->dispatch('starting');

        $this->started = true;

        $this->dispatch('started');

        return $this;
    }
    /**
     * @return $this
     */
    public function stop()
    {
        $this->dispatch('stopping');

        $this->started = false;

        $this->dispatch('stopped');

        return $this;
    }
    /**
     * @param string $eventName
     * @param mixed  $data
     *
     * @return $this
     */
    protected function dispatch($eventName, $data = [])
    {
        $result = null;

        if (!isset($this->callbacks[$eventName])) {
            return $result;
        }

        foreach($this->callbacks[$eventName] as $callback) {
            $result = call_user_func_array($callback, array_merge($data, [$this]));
        }

        return $result;
    }
    /**
     * @param string $name
     *
     * @return bool
     */
    public function isQueueEmpty($name)
    {
        return !isset($this->queues[$name]) || !count($this->queues[$name]);
    }
    /**
     * @param string $name
     * @param mixed  $msg
     *
     * @return $this
     */
    public function queue($name, $msg)
    {
        if (!isset($this->queues[$name])) {
            $this->queues[$name] = [];
        }

        $this->dispatch(sprintf('%s.queueing', $name), [$msg]);

        $this->queues[$name][] = $msg;

        $this->dispatch(sprintf('%s.queued', $name), [$msg]);

        $this->log('<comment>%s message queued (queue size: %d)</comment>', [ucfirst($name), count($this->queues[$name])]);

        return $this;
    }
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function unqueue($name)
    {
        $message = array_shift($this->queues[$name]);

        $this->dispatch(sprintf('%s.unqueueing', $name), [$message]);

        $this->dispatch(sprintf('%s.unqueued', $name), [$message]);

        $this->log('<comment>%s message unqueued (queue size: %d)</comment>', [ucfirst($name), count($this->queues[$name])]);

        return $message;
    }
    /**
     * @param \Exception $exception
     *
     * @return $this
     */
    public function error(\Exception $exception)
    {
        $this->dispatch('exception', [$exception]);

        return $this;
    }
    /**
     * @param string $text
     * @param array  $args
     *
     * @return $this
     */
    public function log($text, $args = [])
    {
        $this->dispatch('log', [$text, $args]);

        return $this;
    }
    /**
     * @param \Exception $e
     *
     * @return Daemon
     */
    public function logException(\Exception $e)
    {
        return $this->log('Exception #%d: %s', [$e->getCode(), $e->getMessage()]);
    }
    /**
     * @param string $name
     * @param array  $msg
     *
     * @return $this
     */
    public function receive($name, $msg)
    {
        return $this->dispatch(sprintf('%s.message.received', $name), [$msg]);
    }
}