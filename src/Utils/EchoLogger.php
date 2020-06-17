<?php

namespace CaliforniaMountainSnake\LaravelDatabaseTestCase\Utils;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class EchoLogger extends AbstractLogger
{
    public const COLOR_OFF = "\033[0m";
    public const BLACK = "\033[0;30m";
    public const RED = "\033[0;31m";
    public const GREEN = "\033[0;32m";
    public const YELLOW = "\033[0;33m";
    public const BLUE = "\033[0;34m";
    public const PURPLE = "\033[0;35m";
    public const CYAN = "\033[0;36m";
    public const WHITE = "\033[0;37m";

    /**
     * @var string
     */
    protected $logLevel = LogLevel::DEBUG;

    /**
     * @var bool
     */
    protected $useBlankFirstMessage = true;

    /**
     * @var bool
     */
    private $isFirstMessagePrinted = false;

    /**
     * Set minimal log level.
     *
     * @param string $logLevel
     */
    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @param bool $use
     */
    public function useBlankFirstMessage(bool $use): void
    {
        $this->useBlankFirstMessage = $use;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->getLogPriority($level) < $this->getLogPriority($this->logLevel)) {
            return;
        }

        if (!$this->isFirstMessagePrinted && $this->useBlankFirstMessage) {
            $this->isFirstMessagePrinted = true;
            $this->log($level, '');
        }

        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                $color = self::RED;
                break;
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
            case LogLevel::INFO:
                $color = self::YELLOW;
                break;
            default:
            case LogLevel::DEBUG:
                $color = self::GREEN;
                break;
        }
        $contextJson = empty($context)
            ? ''
            : ' ' . json_encode($context, JSON_PRETTY_PRINT);

        echo $color . $message . $contextJson . self::COLOR_OFF . "\n";
    }

    /**
     * @param string $level
     *
     * @return int
     */
    protected function getLogPriority(string $level): int
    {
        return array_flip([
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ])[$level];
    }
}
