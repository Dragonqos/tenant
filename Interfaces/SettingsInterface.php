<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface SettingsInterface
 * @package App\TenantBundle\Interfaces
 */
interface SettingsInterface {

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param array $settings
     * @return $this
     */
    public function setSettings(array $settings): self;
}