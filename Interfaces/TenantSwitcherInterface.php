<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface TenantSwitcherInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantSwitcherInterface
{

    /**
     * @param TenantContextInterface|string $tenant
     * @param bool                          $onlyActive
     *
     * @return mixed
     */
    public function useTenant($tenant, $onlyActive = true);

}