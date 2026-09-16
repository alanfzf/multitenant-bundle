<?php

namespace Alanfzf\MultiTenantBundle\Event;

use Alanfzf\MultiTenantBundle\Dto\ConnectionParameters;
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
