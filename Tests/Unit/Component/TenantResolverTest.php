<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Component;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\Component\TenantState;
use App\TenantBundle\Entity\Repository\TenantRepository;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TenantResolverTest
 * @package App\TenantBundle\Tests\Unit\Component
 */
class TenantResolverTest extends TestCase
{
    public function testInit()
    {
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);

        self::assertInstanceOf(TenantResolver::class, $resolver);
    }

    public function testPushHandlers()
    {
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $tenantHandlerMock = self::getMockBuilder(TenantSwitchHandlerInterface::class)->getMockForAbstractClass();

        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);
        $resolver->pushHandler($tenantHandlerMock);
    }

    public function testGetLoadedTenantReturnsNull()
    {
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);

        self::assertNull($resolver->getLoadedTenant());
    }

    /**
     * @expectedException \App\TenantBundle\Exceptions\TenantLoadingException
     * @expectedExceptionMessage Tenant "1" id or name not found.
     */
    public function testUseTenantREPOThrowsException()
    {
        $repoMock = self::getMockBuilder(TenantRepository::class)->disableOriginalConstructor()->setMethods(['findByIdOrName'])->getMock();
        $managerMock = self::getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->setMethods(['getManagerForClass'])->getMockForAbstractClass();

        $managerRegistryMock->expects(self::once())->method('getManagerForClass')->with(Tenant::class)->willReturn($managerMock);
        $managerMock->expects(self::once())->method('getRepository')->with(Tenant::class)->willReturn($repoMock);
        $repoMock->expects(self::once())->method('findByIdOrName')->with(1)->willReturn(null);

        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);
        $resolver->useTenant(1);
    }

    public function testUseTenantWhenNoHandlersRegistered()
    {
        $repoMock = self::getMockBuilder(TenantRepository::class)->disableOriginalConstructor()->setMethods(['findByIdOrName'])->getMock();
        $managerMock = self::getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->setMethods(['getManagerForClass'])->getMockForAbstractClass();

        $managerRegistryMock->expects(self::once())->method('getManagerForClass')->with(Tenant::class)->willReturn($managerMock);
        $managerMock->expects(self::once())->method('getRepository')->with(Tenant::class)->willReturn($repoMock);

        $repoMock->expects(self::once())->method('findByIdOrName')->with(1)->willReturn(new Tenant());

        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);
        $result = $resolver->useTenant(1);

        self::assertFalse($result);
    }

    public function testUseTenantWhenHandlersWereRegistered()
    {
        $repoMock = self::getMockBuilder(TenantRepository::class)->disableOriginalConstructor()->setMethods(['findByIdOrName'])->getMock();
        $managerMock = self::getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods(['getRepository'])->getMock();
        $managerRegistryMock = self::getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->setMethods(['getManagerForClass'])->getMockForAbstractClass();
        $tenantHandlerMock = self::getMockBuilder(TenantSwitchHandlerInterface::class)->getMockForAbstractClass();

        $tenant = new Tenant();
        $tenant->setId(1);
        $tenant->setName('test');

        $managerRegistryMock->expects(self::once())->method('getManagerForClass')->with(Tenant::class)->willReturn($managerMock);
        $managerMock->expects(self::once())->method('getRepository')->with(Tenant::class)->willReturn($repoMock);
        $repoMock->expects(self::once())->method('findByIdOrName')->with(1)->willReturn($tenant);

        $tenantHandlerMock->expects(self::once())->method('isHandling')->willReturn(true);
        $tenantHandlerMock->expects(self::once())->method('handle')->willReturn(null);

        $resolver = new TenantResolver(new TenantState(), $managerRegistryMock);
        $resolver->pushHandler($tenantHandlerMock);
        $result = $resolver->useTenant(1);

        self::assertTrue($result);
    }
}