<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Interfaces\TenantInterface;

/**
 * Interface TenantProviderInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantProviderInterface {

    /**
     * @return iterable
     */
    public function findAll(): iterable;

    /**
     * @param string $tenantIdentifier
     * @return \App\TenantBundle\Interfaces\TenantInterface|null
     */
    public function findByIdOrName(string $tenantIdentifier): ?TenantInterface;

    /**
     * @return iterable|null
     */
    public function getActiveTenants(): ?iterable;

    /**
     * @param TenantInterface $tenant
     * @return bool
     */
    public function save(TenantInterface $tenant): bool;

    /**
     * @param \App\TenantBundle\Interfaces\TenantInterface $tenant
     * @return bool
     */
    public function remove(TenantInterface $tenant): bool;

}