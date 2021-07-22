<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface TenantInterface
 * @package App\TenantBundle
 */
interface TenantInterface extends ResourceInterface, NameInterface, EnabledInterface, SettingsInterface {

    /**
     * @param TenantUserInterface $tenantUser
     * @return $this
     */
    public function removeUser(TenantUserInterface $tenantUser): self;

    /**
     * @param TenantUserInterface $tenantUser
     * @return $this
     */
    public function addUser(TenantUserInterface $tenantUser): self;

}