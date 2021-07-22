<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

use App\TenantBundle\TenantInterface;

/**
 * Interface TenantSwitchHandlerInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantSwitchHandlerInterface
{
    /**
     * @param TenantInterface $tenant
     *
     * @return bool
     */
    public function isHandling(TenantInterface $tenant): bool;

    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void;
}