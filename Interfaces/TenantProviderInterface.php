<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\TenantInterface;

/**
 * Interface TenantProviderInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantProviderInterface {

    /**
     * @param $tenantIdentifier
     * @return TenantInterface|null
     */
    public function findByIdOrName($tenantIdentifier): ?TenantInterface;

    /**
     * @return iterable|null
     */
    public function getActiveTenants(): ?iterable;

    /**
     * @param TenantInterface $tenant
     * @return bool
     */
    public function save(TenantInterface $tenant): bool;

}