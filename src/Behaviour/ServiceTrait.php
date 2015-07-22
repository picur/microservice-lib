<?php

/*
 * This file is part of the MICROSERVICE LIB package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phppro\MicroService\Behaviour;

use RuntimeException;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
Trait ServiceTrait
{
    /**
     * @var array
     */
    protected $services = [];
    /**
     * @var array
     */
    protected $parameters = [];
    /**
     * @param string $method
     * @param array  $args
     */
    public function __call($method, $args)
    {
        throw new RuntimeException(
            sprintf("Unknown method %s::%s()", get_class($this), $method),
            500
        );
    }
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getService($key)
    {
        if (!isset($this->services[$key])) {
            throw new RuntimeException(
                sprintf("Service '%s' not set", $key),
                500
            );
        }

        return $this->services[$key];
    }
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    public function setService($key, $service)
    {
        $this->services[$key] = $service;

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getParameter($key, $default = null)
    {
        $value = $this->getParameterIfExists($key, $default);

        if (null === $value) {
            throw new \RuntimeException(
                sprintf("Parameter %s not set", $key),
                500
            );
        }

        return $value;
    }
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getParameterIfExists($key, $default = null)
    {
        if (!isset($this->parameters[$key])) {
            return $default;
        }

        return $this->parameters[$key];
    }
}
