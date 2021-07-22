<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PostParameterLocator
 * @package App\TenantBundle\Locators
 */
class PostParameterLocator implements TenantLocatorInterface
{
    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return mixed
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        $tenantName = null;

        if($request->isMethod('POST')) {
            $tenantName = $request->request->get($parameterName);

            if (null === $tenantName) {
                throw new \RuntimeException(sprintf('POST parameter "%s" not found.', $parameterName));
            }
        }

        return $tenantName;
    }
}