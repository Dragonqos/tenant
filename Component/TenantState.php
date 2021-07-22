<?php declare(strict_types=1);

namespace App\TenantBundle\Component;

use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Interfaces\TenantStateInterface;

/**
 * Class TenantState
 * @package App\TenantBundle\Component
 */
class TenantState implements TenantStateInterface
{
    private bool $isLoaded = false;
    private ?TenantInterface $tenant = null;

    /**
     * @return TenantInterface|null
     */
    public function getTenant(): ?TenantInterface
    {
        if ($this->isLoaded()) {
            return $this->tenant;
        }

        return null;
    }

    /**
     * @param TenantInterface $tenant
     */
    public function setTenant(TenantInterface $tenant): void
    {
        $this->tenant = $tenant;
        $this->isLoaded = true;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    /**
     *
     */
    public function reset(): void
    {
        $this->isLoaded = false;
        $this->tenant = null;
    }
}