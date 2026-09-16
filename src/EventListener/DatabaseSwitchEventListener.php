<?php

namespace Alanfzf\MultitenatBundle\EventListener;

use Alanfzf\MultitenatBundle\Doctrine\DBAL\TenantConnection;
use Alanfzf\MultitenatBundle\Doctrine\ORM\TenantEntityManager;
use Alanfzf\MultitenatBundle\Event\DatabaseSwitchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DatabaseSwitchEventListener
{
    public function __construct(
        private readonly TenantEntityManager $tenantEntityManager,
    ) {
    }

    #[AsEventListener()]
    public function onDatabaseSwitchEvent(DatabaseSwitchEvent $event): void
    {
        $newConnParams = $event->getConnectionParameters();
        $em = $this->tenantEntityManager;
        /** @var TenantConnection $connection */
        $connection = $em->getConnection();
        $connection->switchConnection($newConnParams->toArray());
    }
}
