<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface TenantUserInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantUserInterface {

    /**
     * @return int
     */
    public function getUid(): int;

    /**
     * @param int $uid
     * @return mixed
     */
    public function setUid(int $uid);

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