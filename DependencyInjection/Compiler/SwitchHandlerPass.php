<?php declare(strict_types=1);

namespace App\TenantBundle\DependencyInjection\Compiler;

use App\TenantBundle\Component\TenantResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SwitchHandlerPass
 * @package App\TenantBundle\DependencyInjection\Compiler
 */
class SwitchHandlerPass implements CompilerPassInterface
{
    const SWITCH_HANDLER_TAG = 'tenant.switch_handler';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(TenantResolver::class)) {
            throw new \InvalidArgumentException(
                sprintf('%s not registered, check services.yaml file. ', TenantResolver::class)
            );
        }

        $definition     = $container->findDefinition(TenantResolver::class);
        $taggedServices = $container->findTaggedServiceIds(self::SWITCH_HANDLER_TAG);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('pushHandler', [new Reference($id)]);
        }
    }
}