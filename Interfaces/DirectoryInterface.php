<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface DirectoryInterface
 * @package App\TenantBundle\Interfaces
 */
interface DirectoryInterface {

    public function setPrefix(string $prefix): self;
}