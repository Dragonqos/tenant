<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface TenantProviderInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantUserProviderInterface {

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByName(string $identifier): ?TenantUserInterface;

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByEmail(string $identifier): ?TenantUserInterface;

    /**
     * @param string $identifier
     * @return TenantUserInterface|null
     */
    public function findOneByConfirmationToken(string $identifier): ?TenantUserInterface;

    /**
     * @param string $identifier
     * @return bool
     */
    public function removeByTenant(string $identifier): bool;

    /**
     * @param TenantUserInterface $tenantUser
     * @return bool
     */
    public function save(TenantUserInterface $tenantUser): bool;

    /**
     * @param TenantUserInterface $tenantUser
     * @return bool
     */
    public function remove(TenantUserInterface $tenantUser): bool;

}