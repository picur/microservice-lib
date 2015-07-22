<?php

/*
 * This file is part of the MICROSERVICE LIB package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phppro\MicroService\Test;

use Phppro\MicroService\Cli;

use PHPUnit_Framework_TestCase;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class CliTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $s = new Cli();

        $this->assertTrue(is_object($s));
    }
}