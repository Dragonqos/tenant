<?php declare(strict_types=1);

namespace App\TenantBundle\Factory\ORM;

use App\TenantBundle\Factory\ResourceAbstractFactory;
use App\TenantBundle\Interfaces\TenantFactoryInterface;
use App\TenantBundle\Interfaces\UserFactoryInterface;

/**
 * Class ORMFactory
 * @package App\TenantBundle\Factory\ORM
 */
class ORMFactory implements ResourceAbstractFactory {

    private TenantFactoryInterface $tenantFactory;
    private UserFactoryInterface $userFactory;

    /**
     * ORMFactory constructor.
     * @param TenantFactoryInterface $tenantFactory
     * @param UserFactoryInterface $userFactory
     */
    public function __construct(TenantFactoryInterface $tenantFactory, UserFactoryInterface $userFactory)
    {
        $this->tenantFactory = $tenantFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * @return TenantFactoryInterface
     */
    public function createTenantFactory(): TenantFactoryInterface
    {
        return $this->tenantFactory;
    }

    /**
     * @return UserFactoryInterface
     */
    public function createTenantUserFactory(): UserFactoryInterface
    {
        return $this->userFactory;
    }

}