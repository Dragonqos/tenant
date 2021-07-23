<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\DependencyInjection\Compiler;

use App\TenantBundle\DependencyInjection\Compiler\LocatorPass;
use App\TenantBundle\Locators\GetParameterLocator;
use App\TenantBundle\Locators\LocatorChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class LocatorPassTest
 * @package App\TenantBundle\Test\Unit\DependencyInjection\Compiler
 */
class LocatorPassTest extends TestCase
{
    protected CompilerPassInterface $pass;

    protected function setUp()
    {
        $this->pass = new LocatorPass();
    }

    protected function tearDown(): void
    {
        unset($this->pass);
    }

    public function testProcess()
    {
        $definition = self::getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder $containerBuilder */
        $containerBuilder = self::getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'findDefinition', 'findTaggedServiceIds'])
            ->getMock();
        $containerBuilder->expects(self::once())
            ->method('has')
            ->with(LocatorChain::class)
            ->will($this->returnValue(true));
        $containerBuilder->expects(self::once())
            ->method('findDefinition')
            ->with(LocatorChain::class)
            ->will($this->returnValue($definition));

        $services = [GetParameterLocator::class => [[]]];
        $containerBuilder->expects(self::once())
            ->method('findTaggedServiceIds')
            ->with(LocatorPass::LOAD_TENANT_LOCATORS_TAG)
            ->will($this->returnValue($services));

        $definition->expects(self::once())
            ->method('addMethodCall')
            ->with('addLocator', [new Reference(GetParameterLocator::class)]);

        $this->pass->process($containerBuilder);
    }
}
