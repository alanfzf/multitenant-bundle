<?php

namespace Alanfzf\MultitenatBundle\Event;

use Alanfzf\MultitenatBundle\Dto\ConnectionParameters;
use Symfony\Contracts\EventDispatcher\Event;

class DatabaseSwitchEvent extends Event
{
    private ConnectionParameters $connectionParameters;

    public function __construct(ConnectionParameters $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;
    }

    public function getConnectionParameters(): ConnectionParameters
    {
        return $this->connectionParameters;
    }
}
