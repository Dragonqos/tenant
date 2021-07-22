<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

/**
 * Interface TenantContextInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantContextInterface
{
    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getSettings();

    /**
     * @param bool $bool
     *
     * @return mixed
     */
    public function setActive($bool = true);

    /**
     * @return mixed
     */
    public function isActive();
}