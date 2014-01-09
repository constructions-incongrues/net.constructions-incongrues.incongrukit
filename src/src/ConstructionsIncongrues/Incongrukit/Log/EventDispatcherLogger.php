<?php
namespace ConstructionsIncongrues\Incongrukit\Log;

use ConstructionsIncongrues\Event\LogEvent;
use Psr\Log\AbstractLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherLogger extends AbstractLogger
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function log($level, $message, array $context = array())
    {
        $this->eventDispatcher->dispatch('constructionsincongrues.log', new LogEvent($level, $message, $context));
    }
}
