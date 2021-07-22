<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetParameterLocator
 * @package App\TenantBundle\Locators
 */
class GetParameterLocator implements TenantLocatorInterface
{
    /**
     * @param Request        $request
     * @param string         $parameterName
     *
     * @return mixed
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        $tenantName = $request->get($parameterName);

        if (null === $tenantName) {
            throw new \RuntimeException(sprintf('GET parameter "%s" not found.', $parameterName));
        }

        return $tenantName;
    }
}