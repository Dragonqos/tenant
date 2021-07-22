<?php declare(strict_types=1);

namespace App\TenantBundle\Factory;

use App\TenantBundle\Interfaces\TenantFactoryInterface;
use App\TenantBundle\Interfaces\UserFactoryInterface;

/**
 * Interface ResourceAbstractFactory
 * @package App\TenantBundle\Factory
 */
interface ResourceAbstractFactory {

    /**
     * @return TenantFactoryInterface
     */
    public function createTenantFactory(): TenantFactoryInterface;

    /**
     * @return UserFactoryInterface
     */
    public function createTenantUserFactory(): UserFactoryInterface;
}