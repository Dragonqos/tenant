<?php declare(strict_types=1);

namespace App\TenantBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'tenant';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('request_attribute')
                    ->defaultValue('tenant')
                ->end()
                ->arrayNode('routes')
                    ->info('Routes on which tenant is required')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('anonymous')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('commands')
                    ->info('Commands on which tenant is required')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
