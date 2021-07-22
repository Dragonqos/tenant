<?php declare(strict_types=1);

namespace App\TenantBundle;

use App\TenantBundle\DependencyInjection\Compiler\SwitchHandlerPass;
use App\TenantBundle\DependencyInjection\Compiler\LocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class TenantBundle
 * @package App\TenantBundle
 */
class TenantBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SwitchHandlerPass());
        $container->addCompilerPass(new LocatorPass());
    }
}
