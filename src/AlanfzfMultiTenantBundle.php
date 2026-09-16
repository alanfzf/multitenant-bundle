<?php

namespace Alanfzf\MultitenatBundle;

use Alanfzf\MultitenatBundle\Doctrine\DBAL\TenantConnection;
use Alanfzf\MultitenatBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
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
                        ->defaultValue('%env(DATABASE_URL)%')
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

            // tenant entity manager configuration
           ->arrayNode('tenant_entity_manager')
               ->addDefaultsIfNotSet()
               ->children()
                   ->scalarNode('tenant_naming_strategy')
                       ->defaultValue('doctrine.orm.naming_strategy.underscore_number_aware')
                   ->end()
                   ->arrayNode('mapping')
                       ->addDefaultsIfNotSet()
                       ->children()
                           ->scalarNode('dir')
                               ->defaultValue('%kernel.project_dir%/src/Entity/Tenant')
                           ->end()
                           ->scalarNode('prefix')
                               ->defaultValue('App\\Entity\\Tenant')
                           ->end()
                           ->scalarNode('alias')
                               ->defaultValue('Tenant')
                           ->end()
                           ->booleanNode('is_bundle')
                               ->defaultFalse()
                           ->end()
                       ->end()
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

        // Load the Doctrine configuration for the tenant connection and entity manager
        $builder->prependExtensionConfig('doctrine', [
            'dbal' => [
                'connections' => [
                    'tenant' => [
                        'url' => $config['tenant_connection']['url'],
                        'wrapper_class' => TenantConnection::class,
                    ],
                ],
            ],

            'orm' => [
                'entity_managers' => [
                    'tenant' => [
                        'connection' => 'tenant',
                        'naming_strategy' => $config['tenant_entity_manager']['tenant_naming_strategy'],
                        'mappings' => [
                            'AlanfzfMultiTenantBundle' => [
                                'type' => 'attribute',
                                'dir' => $config['tenant_entity_manager']['mapping']['dir'],
                                'prefix' => $config['tenant_entity_manager']['mapping']['prefix'],
                                'alias' => $config['tenant_entity_manager']['mapping']['alias'],
                                'is_bundle' => $config['tenant_entity_manager']['mapping']['is_bundle'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);


        $container->services()
        ->set('tenant_entity_manager', TenantEntityManager::class)
        ->args([
            service('doctrine.orm.tenant_entity_manager'),
        ])
        ->public();

        $container->alias(
            TenantEntityManager::class,
            'tenant_entity_manager',
        );
    }
}
