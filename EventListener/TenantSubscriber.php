<?php declare(strict_types=1);

namespace App\TenantBundle\EventListener;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\Component\TenantResolver;
use App\TenantBundle\Exceptions\TenantLoadingException;
use App\TenantBundle\Locators\LocatorChain;
use App\TenantBundle\TenantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class TenantSubscriber
 * @package App\TenantBundle\EventListener
 */
class TenantSubscriber implements EventSubscriberInterface
{
    private TenantProviderInterface $provider;
    protected LocatorChain $locatorChain;
    protected TenantResolver $tenantResolver;
    protected iterable $routes = [];
    protected bool $isAnonymousAllowed = true;
    protected bool $isPathAllowed = false;

    /**
     * TenantSubscriber constructor.
     * @param TenantProviderInterface $provider
     * @param TenantResolver $tenantResolver
     * @param LocatorChain $locatorChain
     * @param array $routes
     */
    public function __construct(
        TenantProviderInterface $provider,
        TenantResolver $tenantResolver,
        LocatorChain $locatorChain,
        array $routes = []
    )
    {
        $this->provider = $provider;
        $this->tenantResolver = $tenantResolver;
        $this->locatorChain = $locatorChain;
        $this->routes = $routes;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
            KernelEvents::CONTROLLER => ['onController', 10]
        ];
    }

    /**
     * @param RequestEvent $event
     * @throws TenantLoadingException
     * @throws \App\TenantBundle\Exceptions\AccessDeniedException
     */
    public function onRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        try {
            if ($tenantId = $this->locatorChain->locate($request)) {

                $tenant = $this->provider->findByIdOrName($tenantId);
                if ($tenant instanceof TenantInterface && $this->tenantResolver->useTenant($tenant)) {
                    return;
                }
            }
        } catch (\RuntimeException $e) {

        }
    }

    /**
     * @param ControllerEvent $event
     * @throws TenantLoadingException
     */
    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if ($this->isPathAllowed($request) && $this->isAnonymousAllowed === false && $this->tenantResolver->isTenantLoaded() === false) {
            $event->stopPropagation();
            throw TenantLoadingException::tenantIdentifierNotFound('Could not locate tenant from request.');
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isPathAllowed(Request $request): bool
    {
        $this->isPathAllowed = false;
        $matcher = new RequestMatcher();

        $matcher->matchScheme(['http', 'https']);

        foreach ($this->routes as $path) {
            $pattern = is_array($path)
                ? $path['path']
                : $path;

            $matcher->matchPath($pattern);
            if ($matcher->matches($request)) {
                $this->isAnonymousAllowed = $path['anonymous'] ?? true;
                $this->isPathAllowed = true;
                return true;
            }
        }

        return $this->isPathAllowed;
    }

}