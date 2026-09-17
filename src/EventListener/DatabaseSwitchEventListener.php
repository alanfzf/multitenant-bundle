<?php

namespace Alanfzf\MultiTenantBundle\EventListener;

use Alanfzf\MultiTenantBundle\Doctrine\DBAL\TenantConnection;
use Alanfzf\MultiTenantBundle\Event\DatabaseSwitchEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DatabaseSwitchEventListener
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {}

    #[AsEventListener()]
    public function onDatabaseSwitchEvent(DatabaseSwitchEvent $event): void
    {
        $newConnParams = $event->getConnectionParameters();
        /** @var \Doctrine\ORM\EntityManager  $em */
        $em = $this->registry->getManager('tenant');
        $em->clear();
        /** @var TenantConnection $connection */
        $connection = $em->getConnection();
        $connection->switchConnection($newConnParams->toArray());
    }
}
