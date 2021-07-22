<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;
use App\TenantBundle\Interfaces\TenantInterface;

/**
 * Class NullHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class NullHandler implements TenantSwitchHandlerInterface
{
    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void
    {
    }

    /**
     * @param TenantInterface $tenant
     *
     * @return bool
     */
    public function isHandling(TenantInterface $tenant): bool
    {
        return true;
    }
}