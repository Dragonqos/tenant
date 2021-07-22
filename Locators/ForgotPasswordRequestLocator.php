<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use App\TenantBundle\Interfaces\TenantLocatorInterface;
use App\TenantBundle\TenantInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ForgotPasswordRequestLocator
 * @package App\TenantBundle\Locators
 */
class ForgotPasswordRequestLocator implements TenantLocatorInterface
{
    protected TenantUserProviderInterface $provider;
    private SessionInterface $session;
    private string $trackParameter;
    private string $trackRoute;

    /**
     * ForgotPasswordRequestLocator constructor.
     * @param TenantUserProviderInterface $provider
     * @param SessionInterface $session
     * @param string $trackRoute
     * @param string $trackParameter
     */
    public function __construct(TenantUserProviderInterface $provider, SessionInterface $session, string $trackRoute = 'resetting', string $trackParameter = 'username')
    {
        $this->provider = $provider;
        $this->session = $session;
        $this->trackRoute = $trackRoute;
        $this->trackParameter = $trackParameter;
    }

    /**
     * @param Request $request
     * @param string $parameterName
     * @return int|null
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        if(
            $request->get('_route') === $this->trackRoute &&
            $request->getMethod() === 'POST' &&
            $request->request->has($this->trackParameter)
        ) {
            $username = $request->request->get($this->trackParameter);
            $target = $this->provider->findOneByName($username);

            if ($target instanceof TenantUserProviderInterface && ($tenant = $target->getTenant()) instanceof TenantInterface) {
                return $tenant->getId();
            }

            $this->session->getFlashBag()->add('error', 'User not found');
        }

        return null;
    }
}