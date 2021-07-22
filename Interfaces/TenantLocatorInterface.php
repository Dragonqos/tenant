<?php declare(strict_types=1);

namespace App\TenantBundle\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface TenantLocatorInterface
 * @package App\TenantBundle\Interfaces
 */
interface TenantLocatorInterface {

    public function getTenantFromRequest(Request $request);

}