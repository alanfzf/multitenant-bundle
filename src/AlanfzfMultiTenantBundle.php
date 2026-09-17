<?php

namespace Alanfzf\MultiTenantBundle;

use Alanfzf\MultiTenantBundle\Doctrine\DBAL\TenantConnection;
use Alanfzf\MultiTenantBundle\Doctrine\ORM\TenantEntityManager;
use Alanfzf\MultiTenantBundle\EventListener\DatabaseSwitchEventListener;
use Alanfzf\MultiTenantBundle\Service\MultiTenantService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class AlanfzfMultiTenantBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
        ->children()
            // tenant connection configuration
            ->arrayNode('tenant_connection')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('url')
                        ->defaultValue('%env(resolve:DATABASE_URL)%')
                    ->end()
                ->end()
            ->end()
            // tenant migration configuration
           ->arrayNode('tenant_migration')
               ->addDefaultsIfNotSet()
               ->children()
                   ->scalarNode('tenant_migration_namespace')
                       ->defaultValue('DoctrineMigrations\\Tenant')
                   ->end()
                   ->scalarNode('tenant_migration_path')
                       ->defaultValue('%kernel.project_dir%/migrations/Tenant')
                   ->end()
               ->end()
           ->end()
        ->end();
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        // migrations
        $container->parameters()
            ->set('tenant_doctrine_migration', [
                'namespace' => $config['tenant_migration']['tenant_migration_namespace'],
                'path' => $config['tenant_migration']['tenant_migration_path'],
            ]);

        $services = $container->services();

        $services
            ->set('tenant_entity_manager', TenantEntityManager::class)
            ->public()
            ->args([service('doctrine.orm.tenant_entity_manager')]);

        $services
            ->alias(TenantEntityManager::class, 'tenant_entity_manager');

        $services
            ->set(MultiTenantService::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $services
            ->set(DatabaseSwitchEventListener::class)
            ->autowire()
            ->autoconfigure();
    }


    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // TODO: check if this can be configurable in any way instead
        // of hardcoding
        $this->createMissingDir(
            $builder->getParameter('kernel.project_dir'),
            '%kernel.project_dir%/src/Entity/Tenant',
        );

        $builder->prependExtensionConfig('doctrine', [
            'dbal' => [
                'connections' => [
                    'tenant' => [
                        'url' => '%env(resolve:DATABASE_URL)%',
                        'wrapper_class' => TenantConnection::class,
                    ],
                ],
            ],
            'orm' => [
                'entity_managers' => [
                    'tenant' => [
                        'connection' => 'tenant',
                        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                        'mappings' => [
                            'AlanfzfMultiTenantBundle' => [
                                'type' => 'attribute',
                                'dir' => '%kernel.project_dir%/src/Entity/Tenant',
                                'prefix' => 'App\Entity\Tenant',
                                'alias' => 'Tenant',
                                'is_bundle' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function createMissingDir(string $projectDir, string $dir): void
    {
        $fileSystem = new Filesystem();
        $dir = str_replace('%kernel.project_dir%', '', $dir);
        $dir = sprintf("%s/%s", $projectDir, $dir);
        if (!$fileSystem->exists($dir)) {
            $fileSystem->mkdir($dir);
        }
    }
}
