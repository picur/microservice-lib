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

use Symfony\Component\Console\Command\Command;
use Phppro\MicroService\Behaviour\ServiceTrait;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractCommand extends Command
{
    use ServiceTrait;
    /**
     * @param OutputInterface $output
     * @param string          $msg
     *
     * @return $this
     */
    public function log(OutputInterface $output, $msg)
    {
        $output->writeln(sprintf('[<comment>%s</comment>] %s', date_create()->format('c'), call_user_func_array('sprintf', is_array($msg) ? $msg : [$msg])));

        return $this;
    }
    /**
     * @param OutputInterface $output
     * @param \Exception      $e
     *
     * @return $this
     */
    public function logException(OutputInterface $output, \Exception $e)
    {
        return $this->log($output, ['<error>Exception #%d: %s</error>', $e->getCode(), $e->getMessage()]);
    }
}
