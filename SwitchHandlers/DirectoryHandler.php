<?php declare(strict_types=1);

namespace App\TenantBundle\SwitchHandlers;

use App\TenantBundle\Interfaces\DirectoryInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;

/**
 * Class DirectoryHandler
 * @package App\TenantBundle\SwitchHandlers
 */
class DirectoryHandler implements TenantSwitchHandlerInterface
{
    private DirectoryInterface $directory;

    /**
     * DirectoryHandler constructor.
     *
     * @param DirectoryInterface $directory
     */
    public function __construct(DirectoryInterface $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param TenantInterface $tenant
     */
    public function handle(TenantInterface $tenant): void
    {
        if($this->isHandling($tenant)) {
            $this->directory->setPrefix((string) $tenant->getId());
        }
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