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
class Client
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
    protected $initialized;
    /**
     * @var mixed
     */
    protected $context;
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
        $this->initialized = false;
        $this->context     = null;
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
     * @param string $name
     * @param string $location
     *
     * @return $this
     */
    public function createProducer($name, $location)
    {
        return $this->registerPush($name, $location, 'connect');
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
        $this->initialized = false;

        $this->dispatch('initializing');

        $this->initialized = true;

        $this->dispatch('initialized');

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
     * @param \Exception $exception
     *
     * @return $this
     */
    public function error(\Exception $exception)
    {
        $this->dispatch('exception', $exception);

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
     * @return mixed
     */
    public function execute()
    {
        return $this->dispatch('execute');
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onExecute(\Closure $callback)
    {
        return $this->on('execute', $callback);
    }
    /**
     * @param string $name
     * @param array  $msg
     *
     * @return $this
     */
    public function send($name, $msg)
    {
        return $this->dispatch('message.sent', [$name, $msg]);
    }
    /**
     * @param \Closure $callback
     *
     * @return $this
     */
    public function onMessageSent(\Closure $callback)
    {
        return $this->on('message.sent', $callback);
    }
}