<?php declare(strict_types=1);

namespace App\TenantBundle\DependencyInjection\Compiler;

use App\TenantBundle\Locators\LocatorChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class LocatorPass
 * @package App\TenantBundle\DependencyInjection\Compiler
 */
class LocatorPass implements CompilerPassInterface
{
    const LOAD_TENANT_LOCATORS_TAG = 'helix.tenant.locators';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(LocatorChain::class)) {
            throw new \InvalidArgumentException(
                sprintf('%s not registered, check services.yaml file. ', LocatorChain::class)
            );
        }

        $definition     = $container->findDefinition(LocatorChain::class);
        $taggedServices = $container->findTaggedServiceIds(self::LOAD_TENANT_LOCATORS_TAG);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addLocator', [new Reference($id)]);
        }
    }
}