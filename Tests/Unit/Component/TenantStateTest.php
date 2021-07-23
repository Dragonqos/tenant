<?php declare(strict_types=1);

namespace App\TenantBundle\Tests\Unit\Component;

use App\TenantBundle\Component\TenantState;
use App\TenantBundle\Entity\Tenant;
use PHPUnit\Framework\TestCase;

/**
 * Class TenantStateTest
 * @package App\TenantBundle\Tests\Unit\Component
 */
class TenantStateTest extends TestCase
{
    private TenantState $state;

    public function setUp()
    {
        $this->state = new TenantState();
    }

    public function test_should_state_change()
    {
        self::assertEquals(false, $this->state->isLoaded());
        self::assertEquals(null, $this->state->getTenant());
        $this->state->setTenant(new Tenant());

        self::assertEquals(true, $this->state->isLoaded());

        $this->state->reset();
        self::assertEquals(false, $this->state->isLoaded());
        self::assertEquals(null, $this->state->getTenant());
    }
}