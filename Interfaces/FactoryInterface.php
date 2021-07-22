<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface NameInterface
 * @package App\TenantBundle\Interfaces
 */
interface FactoryInterface {

    public function createNew();
    public function withData(array $data): self;
}