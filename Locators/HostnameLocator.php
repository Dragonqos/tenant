<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HostnameLocator
 * @package App\TenantBundle\Locators
 */
class HostnameLocator implements TenantLocatorInterface
{
    private string $pattern = '/^(?P<tenant>.+?)\.(.*?)\./';

    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return mixed
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        $host = $request->getHost();

        if (!preg_match($this->pattern, $host, $matches)) {
            throw new \RuntimeException(sprintf('Could not match tenant from host "%s"', $host));
        }

        return $matches['tenant'];
    }

    /**
     * Gets the REGEX pattern used to match a tenant
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Sets the REGEX pattern used to match a tenant
     *
     * @param string $pattern
     *
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }
}