<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface NameInterface
 * @package App\TenantBundle\Interfaces
 */
interface EnabledInterface {

    /**
     * @return bool
     */
    public function getEnabled(): bool;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $isEnabled
     * @return mixed
     */
    public function setEnabled(bool $isEnabled): self;
}