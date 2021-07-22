<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface ResetInterface
 * @package App\TenantBundle\Interfaces
 */
interface ResetInterface {

    public function reset(): void;
}