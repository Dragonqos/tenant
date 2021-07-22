<?php declare(strict_types=1);

namespace App\TenantBundle\EventListener;

use App\TenantBundle\Factory\ResourceAbstractFactory;
use App\TenantBundle\Interfaces\TenantUserInterface;
use App\TenantBundle\Interfaces\TenantUserProviderInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\User\UserInterface;
use App\TenantBundle\Component\TenantState;
use App\TenantBundle\Entity\TenantUser;
use App\TenantBundle\Exceptions\TenantLoadingException;

/**
 * Class UserSubscriber
 * @package App\TenantBundle\EventListener
 */
class UserSubscriber implements EventSubscriber
{
    private const SYNCED_USER_PROPERTIES = [
        'username', 'email',
        'salt', 'password', 'confirmationToken',
        'passwordRequestedAt'
    ];

    private TenantUserProviderInterface $provider;
    private TenantState $tenantState;
    private ResourceAbstractFactory $factory;

    /**
     * UserSubscriber constructor.
     * @param TenantUserProviderInterface $provider
     * @param TenantState $tenantState
     * @param ResourceAbstractFactory $factory
     */
    public function __construct(
        TenantUserProviderInterface $provider,
        TenantState $tenantState,
        ResourceAbstractFactory $factory
    ) {
        $this->provider = $provider;
        $this->tenantState = $tenantState;
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws TenantLoadingException
     */
    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->postPersist($event);
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws TenantLoadingException
     */
    public function postPersist(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();
        $tenant = $this->tenantState->getTenant();
        if ($object instanceof UserInterface && null !== $tenant) {

            $target = $this->findTarget($object);
            $tenant = $this->tenantState->getTenant();

            if (!$target) {
                $target = $this->factory->createTenantUserFactory()->withData([
                    'tenant' => $tenant,
                    'uid' => $object->getId()
                ])->createNew();
                if (!$this->tenantState->isLoaded()) {
                    throw TenantLoadingException::tenantNotFoundException('Tenant has not been loaded yet.');
                }
            }

            $this->syncObjects($object, $target);
            $this->provider->save($target);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();
        if ($object instanceof UserInterface) {
            $target = $this->findTarget($object);
            if ($target instanceof TenantUserInterface) {
                $this->provider->remove($target);
            }
        }
    }

    /**
     * @param UserInterface $object
     * @return TenantUserInterface|null
     */
    private function findTarget(UserInterface $object): ?TenantUserInterface
    {
        return $this->provider->findOneByName($object->getUsername());
    }

    /**
     * @param UserInterface       $original
     * @param TenantUser $target
     */
    protected function syncObjects(UserInterface $original, TenantUserInterface $target): void
    {
        foreach (self::SYNCED_USER_PROPERTIES as $propertyName) {
            $getName = 'get' . ucfirst($propertyName);
            $setName = 'set' . ucfirst($propertyName);
            $target->$setName($original->$getName());
        }

        $isEnabled = $original->isEnabled();
        $target->setEnabled($isEnabled);
    }
}