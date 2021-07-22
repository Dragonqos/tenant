<?php declare(strict_types=1);

namespace App\TenantBundle;

use App\TenantBundle\Interfaces\ResetInterface;

/**
 * Interface TenantInterface
 * @package App\TenantBundle
 */
interface TenantStateInterface extends ResetInterface {

    /**
     * @return TenantInterface|null
     */
    public function getTenant(): ?TenantInterface;

    /**
     * @param TenantInterface $tenant
     */
    public function setTenant(TenantInterface $tenant): void;

    /**
     * @return bool
     */
    public function isLoaded(): bool;
}