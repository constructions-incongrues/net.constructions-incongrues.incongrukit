<?php

namespace ConstructionsIncongrues\Incongrukit\Console\Output;

use ConstructionsIncongrues\Incongrukit\Event\LogEvent;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;

class EventConsoleOutput extends ConsoleOutput
{
    private $mapLevels = array(
        LogLevel::EMERGENCY => 'error',
        LogLevel::ALERT     => 'error',
        LogLevel::CRITICAL  => 'error',
        LogLevel::ERROR     => 'error',
        LogLevel::WARNING   => 'comment',
        LogLevel::NOTICE    => 'info',
        LogLevel::INFO      => null,
        LogLevel::DEBUG     => null,
    );

    public function writelnFromLogEvent(LogEvent $event)
    {
        $style = null;
        if (isset($this->mapLevels[$event->getLevel()])) {
            $style = $this->mapLevels[$event->getLevel()];
        }
        $message = sprintf(
            '%s %s',
            $event->getMessage(),
            json_encode($event->getContext(), JSON_UNESCAPED_SLASHES)
        );
        if (!is_null($style)) {
            $message = sprintf('<%s>%s</%s>', $style, $message, $style);
        }
        $this->writeln($message);
    }
}
