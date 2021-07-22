<?php declare(strict_types=1);

namespace App\TenantBundle\Component;

use App\TenantBundle\Interfaces\TenantProviderInterface;
use App\TenantBundle\Interfaces\TenantInterface;
use App\TenantBundle\Exceptions\AccessDeniedException;
use App\TenantBundle\Exceptions\TenantLoadingException;
use App\TenantBundle\Interfaces\TenantSwitcherInterface;
use App\TenantBundle\Interfaces\TenantSwitchHandlerInterface;

/**
 * Class TenantResolver
 * @package App\TenantBundle\Component
 */
class TenantResolver implements TenantSwitcherInterface
{
    protected TenantState $tenantState;
    protected TenantProviderInterface $provider;
    protected iterable $configuration = [];
    private iterable $handlers = [];

    /**
     * TenantResolver constructor.
     * @param TenantState $tenantState
     * @param TenantProviderInterface $provider
     */
    public function __construct(TenantState $tenantState, TenantProviderInterface $provider)
    {
        $this->tenantState = $tenantState;
        $this->provider = $provider;
    }

    /**
     * @param array $configuration
     * @return $this
     */
    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @param TenantSwitchHandlerInterface $handler
     * @return $this
     */
    public function pushHandler(TenantSwitchHandlerInterface $handler): self
    {
        array_unshift($this->handlers, $handler);
        return $this;
    }

    /**
     * @param \App\TenantBundle\Interfaces\TenantContextInterface|string $tenant
     * @param bool $onlyActive
     * @return bool
     * @throws AccessDeniedException
     * @throws TenantLoadingException
     */
    public function useTenant($tenant, $onlyActive = true): bool
    {
        if (is_numeric($tenant) || is_string($tenant)) {
            $tenantId = $tenant;

            # skip tenant switch when this tenant is already loaded
            if($this->tenantState->getTenant() && $this->tenantState->getTenant()->getId() === (int) $tenantId) {
                return true;
            }

            $tenant = $this->provider->findByIdOrName((string) $tenantId);

            if (!$tenant instanceof TenantInterface) {
                throw TenantLoadingException::tenantNotFoundException(sprintf('Tenant "%s" id or name not found.', $tenantId));
            }
        }

        if (!$tenant instanceof TenantInterface) {
            throw TenantLoadingException::invalidTenantIdentifier(
                sprintf(
                    'Argument "$tenant" must be typeof %s or integer, "%s" given',
                    TenantInterface::class,
                    gettype($tenant)
                )
            );
        }

        if ($onlyActive && $tenant->isEnabled() === false) {
            throw AccessDeniedException::tenantDisabled(
                sprintf('Tenant "%s" "%s" account has been disabled',
                    $tenant->getId(),
                    $tenant->getName()
                ));
        }

        $this->tenantState->reset();

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling($tenant)) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($tenant)) {
                break;
            }

            next($this->handlers);
        }

        $this->tenantState->setTenant($tenant);
        return true;
    }

    /**
     * @return bool
     */
    public function isTenantLoaded(): bool
    {
        return $this->tenantState->isLoaded();
    }

    /**
     * @return TenantInterface|null
     */
    public function getLoadedTenant(): ?TenantInterface
    {
        if ($this->tenantState->isLoaded()) {
            return $this->tenantState->getTenant();
        }

        return null;
    }
}