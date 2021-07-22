<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface NameInterface
 * @package App\TenantBundle\Interfaces
 */
interface NameInterface {

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getName(): string;
}