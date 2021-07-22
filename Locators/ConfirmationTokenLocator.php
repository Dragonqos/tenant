<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Entity\TenantUser;
use App\TenantBundle\Interfaces\TenantLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConfirmationTokenLocator
 * @package App\TenantBundle\Locators
 */
class ConfirmationTokenLocator implements TenantLocatorInterface
{
    protected TenantUserProviderInterface $provider;

    /**
     * ConfirmationTokenLocator constructor.
     * @param TenantUserProviderInterface $provider
     */
    public function __construct(TenantUserProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param Request $request
     * @param string $parameterName
     * @return string
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant')
    {
        if($request->get('_route') === 'resetting_confirm' && $request->get('token', false)) {
            $target = $this->provider->findOneByConfirmationToken($request->get('token'));

            if($target instanceof TenantUser) {
                $tenant = $target->getTenant();

                if ($tenant instanceof Tenant) {
                    return $tenant->getName();
                }
            }

            throw new \RuntimeException(sprintf('User with "%s" confirmation token not found.', $request->get('token')));
        }

        throw new \RuntimeException('Not a confirmation token request, skipping.');
    }
}