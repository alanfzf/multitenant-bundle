<?php

namespace Alanfzf\MultitenatBundle;

use Alanfzf\MultitenatBundle\Doctrine\DBAL\TenantConnection;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AlanfzfMultiTenantBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
        ->children()
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
                                // use php 8 attributes for mappings
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
}
