<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginRequestLocator
 * @package App\TenantBundle\Locators
 */
class LoginRequestLocator implements TenantLocatorInterface
{
    private TenantUserProviderInterface $provider;
    private string $trackParameter;

    /**
     * LoginRequestLocator constructor.
     * @param TenantUserProviderInterface $provider
     * @param string $trackParameter
     */
    public function __construct(TenantUserProviderInterface $provider, string $trackParameter = '_username')
    {
        $this->provider = $provider;
        $this->trackParameter = $trackParameter;
    }

    /**
     * @param Request $request
     * @param string $parameterName
     *
     * @return mixed
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant'): ?int
    {
        if ($request->isMethod('POST') && $request->request->has($this->trackParameter)) {
            $username = $request->request->get($this->trackParameter);

            $target = $this->provider->findOneByName($username);

            if ($target instanceof TenantUserInterface && ($tenant = $target->getTenant()) instanceof TenantInterface) {
                return $tenant->getId();
            }
        }

        return null;
    }
}