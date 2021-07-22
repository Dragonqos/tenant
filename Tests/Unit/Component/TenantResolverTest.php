<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Component;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\Component\TenantState;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TenantResolverTest
 * @package App\TenantBundle\Tests\Unit\Component
 */
class TenantResolverTest extends TestCase
{

    private \PHPUnit\Framework\MockObject\MockObject $provider;
    private TenantResolver $resolver;

    public function setUp()
    {
        $this->provider = self::getMockBuilder(TenantProviderInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $this->resolver = new TenantResolver(new TenantState(), $this->provider);
    }

    public function test_should_accept_handlers()
    {
        $mock = self::getMockBuilder(TenantSwitchHandlerInterface::class)->getMockForAbstractClass();
        $this->resolver->pushHandler($mock);

        $reflection = new \ReflectionClass($this->resolver);
        $prop = $reflection
            ->getProperty('handlers');
        $prop->setAccessible(true);

        $handlers = $prop->getValue($this->resolver);
        self::assertCount(1, $handlers);
    }

    public function test_should_return_null_when_nothing_loaded()
    {
        self::assertNull($this->resolver->getLoadedTenant());
    }

    public function test_should_throw_error_when_tenant_not_found()
    {
        self::expectException(\App\TenantBundle\Exceptions\TenantLoadingException::class);
        self::expectExceptionMessage('Tenant "1" id or name not found.');

        $this->provider->expects(self::once())->method('findByIdOrName')->with('1')->willReturn(null);
        $this->resolver->useTenant(1);
    }

    public function test_should_apply_tenant_without_executing_handlers()
    {
        $this->provider->expects(self::once())->method('findByIdOrName')->with('1')->willReturn(new Tenant());
        $result = $this->resolver->useTenant(1);

        self::assertFalse($result);
    }

    public function test_should_apply_handlers()
    {
        $tenant = new Tenant();
        $tenant->setId(1);
        $tenant->setName('test');

        $mock = self::getMockBuilder(TenantSwitchHandlerInterface::class)->getMockForAbstractClass();
        $mock->expects(self::at(0))->method('isHandling')->with($tenant)->willReturn(true);
        $mock->expects(self::at(1))->method('handle')->with($tenant)->willReturn(null);

        $this->provider->expects(self::once())->method('findByIdOrName')->with('1')->willReturn($tenant);
        $this->resolver->pushHandler($mock);

        $result = $this->resolver->useTenant(1);

        self::assertTrue($result);
        self::assertTrue($this->resolver->isTenantLoaded());
    }
}