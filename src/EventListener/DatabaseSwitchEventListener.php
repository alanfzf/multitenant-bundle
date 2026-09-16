<?php

namespace Alanfzf\MultiTenantBundle\EventListener;

use Alanfzf\MultiTenantBundle\Doctrine\DBAL\TenantConnection;
use Alanfzf\MultiTenantBundle\Doctrine\ORM\TenantEntityManager;
use Alanfzf\MultiTenantBundle\Event\DatabaseSwitchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DatabaseSwitchEventListener
{
    public function __construct(
        private readonly TenantEntityManager $tenantEntityManager,
    ) {}

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
