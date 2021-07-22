<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

use App\TenantBundle\TenantInterface;

/**
 * Interface TenantUserInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantUserInterface {

    /**
     * @param TenantInterface $tenant
     * @return $this
     */
    public function setTenant(TenantInterface $tenant): self;

    /**
     * @return TenantInterface
     */
    public function getTenant(): TenantInterface;

}