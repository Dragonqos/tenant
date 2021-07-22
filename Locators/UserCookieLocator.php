<?php declare(strict_types=1);

namespace App\TenantBundle\Locators;

use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use Doctrine\Persistence\ManagerRegistry;
use Helix\SuperuserBundle\Entity\Superuser;
use App\TenantBundle\Entity\Tenant;
use App\TenantBundle\Entity\TenantUser;
use App\TenantBundle\Interfaces\TenantLocatorInterface;
use App\TenantBundle\Model\TenantUserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices;

/**
 * Class UserCookieLocator
 * @package App\TenantBundle\Locators
 */
class UserCookieLocator implements TenantLocatorInterface
{
    const TENANT_CACHE_PREFIX = 'tenant_';

    private TenantUserProviderInterface $provider;
    protected string $cookieParameterName;

    /**
     * UserCookieLocator constructor.
     * @param TenantUserProviderInterface $provider
     * @param string $cookieParameterName
     */
    public function __construct(TenantUserProviderInterface $provider, string $cookieParameterName)
    {
        $this->cookieParameterName = $cookieParameterName;
        $this->provider = $provider;
    }

    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return string|null
     */
    public function getTenantFromRequest(Request $request, string $parameterName = 'tenant'): ?string
    {
        if (null !== $tenantName = $this->restoreFromCookie($request)) {
            return $tenantName;
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function restoreFromCookie(Request $request): ?string
    {
        $cookie = $request->cookies->get($this->cookieParameterName);

        if(empty($cookie)) {
            return null;
        }

        $cookieParts = $this->decodeCookie($cookie);
        if(count($cookieParts) !== 4)  {
            return null;
        }

        [$class, $encodedUsername, $expires, $hash] = $cookieParts;

        $username = base64_decode($encodedUsername);

        $target = $this->provider->findOneByName($username);

        if ($target instanceof TenantUserInterface && ($tenant = $target->getTenant()) instanceof TenantInterface) {
            return $tenant->getName();
        }

        return null;
    }

    /**
     * @param $rawCookie
     *
     * @return array
     */
    protected function decodeCookie($rawCookie): array
    {
        return explode(AbstractRememberMeServices::COOKIE_DELIMITER, base64_decode($rawCookie));
    }
}