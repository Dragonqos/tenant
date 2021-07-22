<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HeaderLocator
 * @package App\TenantBundle\Locators
 */
class HeaderLocator implements TenantLocatorInterface
{
    /**
     * @param Request $request
     * @param string $parameterName
     * @return string|null
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        if (!$request->headers->has($parameterName)) {
            throw new \RuntimeException(sprintf('Header parameter "%s" not found.', $parameterName));
        }

        return $request->headers->get($parameterName);
    }
}