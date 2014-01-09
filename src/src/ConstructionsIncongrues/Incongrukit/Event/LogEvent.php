<?php

namespace ConstructionsIncongrues\Incongrukit\Event;

use Symfony\Component\EventDispatcher\Event;

class LogEvent extends Event
{
    private $context = array();
    private $level;
    private $message;

    public function __construct($level, $message, array $context = array())
    {
        $this->context = $context;
        $this->level = $level;
        $this->message = $message;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
