<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\DependencyInjection\Compiler;

use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\DependencyInjection\Compiler\SwitchHandlerPass;
use App\TenantBundle\SwitchHandlers\MysqlConnectionHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SwitchHandlerPassTest
 * @package App\TenantBundle\Test\Unit\DependencyInjection\Compiler
 */
class SwitchHandlerPassTest extends TestCase
{
    /**
     * @var CompilerPassInterface
     */
    protected $pass;

    protected function setUp()
    {
        $this->pass = new SwitchHandlerPass();
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
            ->with(TenantResolver::class)
            ->will($this->returnValue(true));
        $containerBuilder->expects(self::once())
            ->method('findDefinition')
            ->with(TenantResolver::class)
            ->will($this->returnValue($definition));

        $services = [MysqlConnectionHandler::class => [[]]];
        $containerBuilder->expects(self::once())
            ->method('findTaggedServiceIds')
            ->with(SwitchHandlerPass::SWITCH_HANDLER_TAG)
            ->will($this->returnValue($services));

        $definition->expects(self::once())
            ->method('addMethodCall')
            ->with('pushHandler', [new Reference(MysqlConnectionHandler::class)]);

        $this->pass->process($containerBuilder);
    }
}
