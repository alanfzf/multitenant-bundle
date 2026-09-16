<?php

namespace Alaanfzf\MultitenatBundle\Service;

use Alanfzf\MultitenatBundle\Doctrine\ORM\TenantEntityManager;
use Alanfzf\MultitenatBundle\Dto\ConnectionParameters;
use Alanfzf\MultitenatBundle\Event\DatabaseSwitchEvent;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MultiTenantService
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TenantEntityManager $tenantEntityManager,
    ) {}

    public function generateDiff(
        InputInterface $input,
        OutputInterface $output,
        ConnectionParameters $conn,
    ): void {
        $dependencyFactory = $this->getDependencyFactory($conn);
        $newInput = new ArrayInput([
            '--allow-empty-diff' => $input->getOption('allow-empty-diff'),
        ]);
        $newInput->setInteractive($input->isInteractive());
        $otherCommand = new DiffCommand($dependencyFactory);
        $otherCommand->run($newInput, $output);
    }

    public function migrateTenants(
        InputInterface $input,
        OutputInterface $output,
        ConnectionParameters $conn,
    ): void {
        $dependencyFactory = $this->getDependencyFactory($conn);
        $newInput = new ArrayInput([
            'version' => $input->getArgument('version'),
            '--dry-run' => $input->getOption('dry-run'),
            // '--query-time' => $input->getOption('query-time'),
            // '--allow-no-migration' => $input->getOption('allow-no-migration'),
        ]);
        $newInput->setInteractive($input->isInteractive());
        $command = new MigrateCommand($dependencyFactory);
        $command->run($newInput, $output);
    }

    private function getDependencyFactory(ConnectionParameters $newConnection): DependencyFactory
    {
        $this->eventDispatcher->dispatch(new DatabaseSwitchEvent($newConnection));

        $config = new ConfigurationArray([
            'migrations_paths' => [
                'DoctrineMigrations\Tenant' => $this->params->get('kernel.project_dir') . '/migrations/Tenant',
            ],
        ]);

        $em = $this->tenantEntityManager;

        return DependencyFactory::fromEntityManager(
            $config,
            new ExistingEntityManager($em),
        );
    }
}
