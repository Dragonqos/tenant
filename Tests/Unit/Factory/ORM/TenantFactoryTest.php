<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Factory\ORM;

use App\TenantBundle\Factory\ORM\TenantFactory;
use App\TenantBundle\Interfaces\TenantProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TenantFactoryTest
 * @package App\TenantBundle\Tests\Unit\Factory\ORM
 */
class TenantFactoryTest extends TestCase {

    private \PHPUnit\Framework\MockObject\MockObject $provider;
    private TenantFactory $factory;

    public function setUp()
    {
        $this->provider = self::getMockBuilder(TenantProviderInterface::class)->getMockForAbstractClass();
        $this->factory = new TenantFactory($this->provider, '127.0.0.1', 'test', 'pass', 3306);
    }

    public function test_create_without_data_fails()
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Tenant normalized data must be set first, before creating new tenant');

        $this->factory->createNew();
    }
}