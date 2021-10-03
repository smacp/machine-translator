<?php

declare(strict_types=1);

namespace smacp\MachineTranslator\Classes\Logger;

use Psr\Log\LoggerInterface;

/**
 * Class NullLogger
 *
 * @package smacp\MachineTranslator\Classes\Logger
 */
class NullLogger implements LoggerInterface
{
    /**
     * @param string $message
     * @param array  $context
     */
    public function debug($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function critical($message, array $context = []): void
    {

    }

    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = []): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function alert($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function warning($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function emergency($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function info($message, array $context = []): void
    {

    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function notice($message, array $context = []): void
    {

    }
}
